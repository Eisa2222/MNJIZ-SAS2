<?php

namespace App\Helpers;

use App\Models\Hr\Attendance\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceHelper
{
    /**
     * جلب بيانات الحضور والانصراف للمستخدم الحالي.
     */
    public static function getCurrentAttendance()
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $today = Carbon::today();

        // جلب سجل الحضور الحالي للمستخدم
        return Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();
    }

    /**
     * تحديد ما إذا كان المستخدم قد سجل الحضور اليوم.
     *
     * @return bool
     */
    public static function isCheckedIn()
    {
        return self::getCurrentAttendance() && self::getCurrentAttendance()->check_in_time;
    }

    /**
     * تحديد ما إذا كان المستخدم قد سجل الانصراف اليوم.
     *
     * @return bool
     */
    public static function isCheckedOut()
    {
        return self::getCurrentAttendance() && self::getCurrentAttendance()->check_out_time;
    }
}
