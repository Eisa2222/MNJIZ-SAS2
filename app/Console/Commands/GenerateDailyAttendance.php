<?php

namespace App\Console\Commands;

use App\Helpers\SettingsHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDailyAttendance extends Command
{

    /*
    |--------------------------------------------------------------------------
    | The name and signature of the console command.
    |--------------------------------------------------------------------------
    */
    protected $signature = 'attendance:generate-daily-attendance {--date=}';

    /*
    |--------------------------------------------------------------------------
    | Description of the command.
    |--------------------------------------------------------------------------
    */
    protected $description = 'Generate daily attendance snapshot (present/leave/absent) after attendance closing time';

    /*
    |--------------------------------------------------------------------------
    | Handle the command.
    |--------------------------------------------------------------------------
    | Execute the console command.
    */
    public function handle(): int
    {
        $target = Carbon::parse($this->option('date') ?? today())
            ->toDateString();

        if (!$this->option('date')) {
            $attendanceEndTime = SettingsHelper::get('attendance_end_time');
            $currentTime = Carbon::now();

            if ($attendanceEndTime) {
                $closingTime = Carbon::parse($target . ' ' . $attendanceEndTime);

                if ($currentTime->lt($closingTime)) {
                    $this->info("الوقت الحالي قبل وقت إغلاق الحضور ({$attendanceEndTime}). سيتم تأجيل التنفيذ.");
                    return self::SUCCESS;
                }
            } else {
                $endOfDay = Carbon::parse($target)->endOfDay()->subMinutes(30);

                if ($currentTime->lt($endOfDay)) {
                    $this->info("سيتم تنفيذ الأمر في وقت لاحق قبل نهاية اليوم.");
                    return self::SUCCESS;
                }
            }
        }

        $this->info("Generating daily attendance for {$target}");

        $weeklyDaysOff = SettingsHelper::get('weekly_days_off', ['friday', 'saturday']);
        $dayName = strtolower(Carbon::parse($target)->format('l'));

        if (in_array($dayName, $weeklyDaysOff)) {
            $this->warn("{$target} is a day off ({$dayName}), skipping.");
            return self::SUCCESS;
        }


        $workStartTime = SettingsHelper::get('work_start_time', '08:00:00');
        $workEndTime = SettingsHelper::get('work_end_time', '16:00:00');


        DB::transaction(function () use ($target, $workStartTime, $workEndTime) {

            DB::table('attendances')
                ->where('date', $target)
                ->update(['day_status' => null]);


            DB::table('attendances')
                ->where('date', $target)
                ->whereNotNull('check_in_time')
                ->whereNotNull('check_out_time')
                ->update(['day_status' => 'present']);


            DB::table('attendances')
                ->where('date', $target)
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->update(['day_status' => 'present']);

            // تحديث: جلب معلومات الإجازات مع نوع الإجازة
            $leaveRequests = DB::table('leave_requests as lr')
                ->join('employees as e', 'lr.employee_id', '=', 'e.id')
                ->whereNull('lr.deleted_at')   // ⬅️ مهم جدًا
                ->whereNull('e.deleted_at')    // ⬅️ في حال تم حذف الموظف أيضًا
                ->where('lr.status', 'approved')
                ->whereDate('lr.start_date', '<=', $target)
                ->whereDate('lr.end_date', '>=', $target)
                ->select('e.user_id', 'lr.leave_type_id')
                ->get();


            if ($leaveRequests->isNotEmpty()) {
                $existing = DB::table('attendances')
                    ->where('date', $target)
                    ->pluck('user_id')
                    ->toArray();

                foreach ($leaveRequests as $leaveRequest) {
                    if (!in_array($leaveRequest->user_id, $existing)) {
                        // إدراج سجل جديد للإجازة مع نوع الإجازة
                        DB::table('attendances')->insert([
                            'user_id'               => $leaveRequest->user_id,
                            'date'                  => $target,
                            'check_in_time'         => null,
                            'check_out_time'        => null,
                            'latitude'              => null,
                            'longitude'             => null,
                            'scheduled_start_time'  => $workStartTime,
                            'scheduled_end_time'    => $workEndTime,
                            'late_minutes'          => 0,
                            'early_arrival_minutes' => 0,
                            'early_leave_minutes'   => 0,
                            'overtime_minutes'      => 0,
                            'day_status'            => 'leave',
                            'leave_type_id'         => $leaveRequest->leave_type_id, // حفظ نوع الإجازة
                            'created_at'            => "{$target} 00:00:00",
                            'updated_at'            => "{$target} 00:00:00",
                        ]);
                    } else {
                        // تحديث السجل الموجود بنوع الإجازة
                        DB::table('attendances')
                            ->where('user_id', $leaveRequest->user_id)
                            ->where('date', $target)
                            ->update([
                                'day_status' => 'leave',
                                'leave_type_id' => $leaveRequest->leave_type_id
                            ]);
                    }
                }
            }

            // --------------------------------------------
            // معالجة الإجازات العامة النشطة فقط
            // --------------------------------------------
            $globalLeaves = DB::table('settings_leave_types')
                ->whereNull('deleted_at')
                ->where('is_global', true)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $target)
                ->whereDate('end_date', '>=', $target)
                ->get();


            if ($globalLeaves->isNotEmpty()) {
                // نحصل على سجلات الحضور المسجلة بالفعل لهذا اليوم
                $existing = DB::table('attendances')
                    ->where('date', $target)
                    ->pluck('user_id')
                    ->toArray();

                // نحصل على الموظفين النشطين فقط
                $activeUserIds = DB::table('employees')
                    ->whereNull('deleted_at')
                    ->pluck('user_id')
                    ->toArray();

                foreach ($globalLeaves as $leave) {
                    foreach ($activeUserIds as $uid) {
                        if (!in_array($uid, $existing)) {
                            DB::table('attendances')->insert([
                                'user_id'               => $uid,
                                'date'                  => $target,
                                'check_in_time'         => null,
                                'check_out_time'        => null,
                                'latitude'              => null,
                                'longitude'             => null,
                                'scheduled_start_time'  => $workStartTime,
                                'scheduled_end_time'    => $workEndTime,
                                'late_minutes'          => 0,
                                'early_arrival_minutes' => 0,
                                'early_leave_minutes'   => 0,
                                'overtime_minutes'      => 0,
                                'day_status'            => 'leave',
                                'leave_type_id'         => $leave->id,
                                'created_at'            => "{$target} 00:00:00",
                                'updated_at'            => "{$target} 00:00:00",
                            ]);
                            $existing[] = $uid; // نمنع التكرار في حالة تعدد الإجازات العامة
                        }
                    }
                }
            }

            // التعامل مع الغياب (مع قيمة null لنوع الإجازة)
            $allUserIds = DB::table('employees')->whereNull('deleted_at')->pluck('user_id')->toArray();
            $occupied   = DB::table('attendances')
                ->where('date', $target)
                ->pluck('user_id')
                ->toArray();

            $toAbsent = array_diff($allUserIds, $occupied);

            foreach ($toAbsent as $uid) {
                DB::table('attendances')->insert([
                    'user_id'               => $uid,
                    'date'                  => $target,
                    'check_in_time'         => null,
                    'check_out_time'        => null,
                    'latitude'              => null,
                    'longitude'             => null,
                    'scheduled_start_time'  => $workStartTime,
                    'scheduled_end_time'    => $workEndTime,
                    'late_minutes'          => 0,
                    'early_arrival_minutes' => 0,
                    'early_leave_minutes'   => 0,
                    'overtime_minutes'      => 0,
                    'day_status'            => 'absent',
                    'leave_type_id'         => null, // لا يوجد نوع إجازة للغياب
                    'created_at'            => "{$target} 00:00:00",
                    'updated_at'            => "{$target} 00:00:00",
                ]);
            }
        });

        $this->info("Daily attendance generated for {$target}.");
        return self::SUCCESS;
    }
}
