<?php

namespace App\Console\Commands;

use App\Console\Concerns\IteratesTenants;
use App\Helpers\SettingsHelper;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyAttendance extends Command
{
    use IteratesTenants;

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
    protected $description = 'Per-tenant daily attendance snapshot (present / leave / absent) after attendance closing time.';

    /*
    |--------------------------------------------------------------------------
    | Handle the command — runs for every active tenant.
    |--------------------------------------------------------------------------
    */
    public function handle(): int
    {
        return $this->perTenant(function (Tenant $tenant) {
            $this->runForTenant($tenant);
        });
    }

    /**
     * Original single-tenant logic, now parameterized by tenant id. All raw
     * DB::table() bulk-ops MUST filter by tenant_id explicitly — they bypass
     * TenantScope. Failing to scope them would corrupt data across tenants.
     */
    protected function runForTenant(Tenant $tenant): void
    {
        $tenantId = $tenant->id;

        $target = Carbon::parse($this->option('date') ?? today())
            ->toDateString();

        if (!$this->option('date')) {
            $attendanceEndTime = SettingsHelper::get('attendance_end_time');
            $currentTime = Carbon::now();

            if ($attendanceEndTime) {
                $closingTime = Carbon::parse($target . ' ' . $attendanceEndTime);

                if ($currentTime->lt($closingTime)) {
                    $this->info("  tenant={$tenant->slug}: before attendance close ({$attendanceEndTime}), skipping.");
                    return;
                }
            } else {
                $endOfDay = Carbon::parse($target)->endOfDay()->subMinutes(30);

                if ($currentTime->lt($endOfDay)) {
                    $this->info("  tenant={$tenant->slug}: before EOD, skipping.");
                    return;
                }
            }
        }

        $this->info("  tenant={$tenant->slug}: generating attendance for {$target}");

        $weeklyDaysOff = SettingsHelper::get('weekly_days_off', ['friday', 'saturday']);
        $dayName = strtolower(Carbon::parse($target)->format('l'));

        if (in_array($dayName, $weeklyDaysOff)) {
            $this->warn("  tenant={$tenant->slug}: {$target} is {$dayName} (day off), skipping.");
            return;
        }

        $workStartTime = SettingsHelper::get('work_start_time', '08:00:00');
        $workEndTime = SettingsHelper::get('work_end_time', '16:00:00');

        DB::transaction(function () use ($tenantId, $target, $workStartTime, $workEndTime) {

            // Every DB::table() call below MUST be scoped by tenant_id —
            // raw query builder BYPASSES TenantScope. Without the filter,
            // this command would trample other tenants' attendance rows.
            DB::table('attendances')
                ->where('tenant_id', $tenantId)
                ->where('date', $target)
                ->update(['day_status' => null]);

            DB::table('attendances')
                ->where('tenant_id', $tenantId)
                ->where('date', $target)
                ->whereNotNull('check_in_time')
                ->whereNotNull('check_out_time')
                ->update(['day_status' => 'present']);

            DB::table('attendances')
                ->where('tenant_id', $tenantId)
                ->where('date', $target)
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->update(['day_status' => 'present']);

            $leaveRequests = DB::table('leave_requests as lr')
                ->join('employees as e', 'lr.employee_id', '=', 'e.id')
                ->where('lr.tenant_id', $tenantId)
                ->where('e.tenant_id', $tenantId)
                ->whereNull('lr.deleted_at')
                ->whereNull('e.deleted_at')
                ->where('lr.status', 'approved')
                ->whereDate('lr.start_date', '<=', $target)
                ->whereDate('lr.end_date', '>=', $target)
                ->select('e.user_id', 'lr.leave_type_id')
                ->get();

            if ($leaveRequests->isNotEmpty()) {
                $existing = DB::table('attendances')
                    ->where('tenant_id', $tenantId)
                    ->where('date', $target)
                    ->pluck('user_id')
                    ->toArray();

                foreach ($leaveRequests as $leaveRequest) {
                    if (!in_array($leaveRequest->user_id, $existing)) {
                        DB::table('attendances')->insert([
                            'tenant_id'             => $tenantId,
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
                            'leave_type_id'         => $leaveRequest->leave_type_id,
                            'created_at'            => "{$target} 00:00:00",
                            'updated_at'            => "{$target} 00:00:00",
                        ]);
                    } else {
                        DB::table('attendances')
                            ->where('tenant_id', $tenantId)
                            ->where('user_id', $leaveRequest->user_id)
                            ->where('date', $target)
                            ->update([
                                'day_status'    => 'leave',
                                'leave_type_id' => $leaveRequest->leave_type_id,
                            ]);
                    }
                }
            }

            // settings_leave_types currently stays global (shared lookup)
            // — is_global = platform-wide national holidays, not tenant-specific.
            $globalLeaves = DB::table('settings_leave_types')
                ->whereNull('deleted_at')
                ->where('is_global', true)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $target)
                ->whereDate('end_date', '>=', $target)
                ->get();

            if ($globalLeaves->isNotEmpty()) {
                $existing = DB::table('attendances')
                    ->where('tenant_id', $tenantId)
                    ->where('date', $target)
                    ->pluck('user_id')
                    ->toArray();

                $activeUserIds = DB::table('employees')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->pluck('user_id')
                    ->toArray();

                foreach ($globalLeaves as $leave) {
                    foreach ($activeUserIds as $uid) {
                        if (!in_array($uid, $existing)) {
                            DB::table('attendances')->insert([
                                'tenant_id'             => $tenantId,
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
                            $existing[] = $uid;
                        }
                    }
                }
            }

            // Mark absentees.
            $allUserIds = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->whereNull('deleted_at')
                ->pluck('user_id')
                ->toArray();
            $occupied = DB::table('attendances')
                ->where('tenant_id', $tenantId)
                ->where('date', $target)
                ->pluck('user_id')
                ->toArray();

            $toAbsent = array_diff($allUserIds, $occupied);

            foreach ($toAbsent as $uid) {
                DB::table('attendances')->insert([
                    'tenant_id'             => $tenantId,
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
                    'leave_type_id'         => null,
                    'created_at'            => "{$target} 00:00:00",
                    'updated_at'            => "{$target} 00:00:00",
                ]);
            }
        });

        $this->info("  tenant={$tenant->slug}: daily attendance generated for {$target}.");
    }
}
