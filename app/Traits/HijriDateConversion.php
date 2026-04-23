<?php

namespace App\Traits;

use Alkoumi\LaravelHijriDate\Hijri as LaravelHijriDateHijri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait HijriDateConversion
{
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->hijriDateFields)) {
            if (empty($value)) {
                parent::setAttribute($key, null);
            } else {
                parent::setAttribute($key, $this->convertToGregorian($value));
            }
        } else {
            parent::setAttribute($key, $value);
        }
    }

    protected function convertToGregorian($value)
    {
        if ($this->isHijriDate($value)) {
            // استبدال أي فواصل مائلة `/` بشرطات `-` لتوحيد التنسيق
            $value = str_replace('/', '-', $value);
            // تقسيم التاريخ الهجري إلى سنة، شهر، ويوم
            list($year, $month, $day) = explode('-', $value);
            // تحويل التاريخ الهجري إلى ميلادي باستخدام المكتبة
            $gregorianDate = LaravelHijriDateHijri::DateToGregorianFromDMY($day, $month, $year);
            // Log::info("تحويل التاريخ الهجري $value إلى ميلادي: $gregorianDate");
            // تخزين التاريخ الميلادي في قاعدة البيانات بتنسيق 'Y-m-d'
            return Carbon::parse($gregorianDate)->format('Y-m-d');
        } else {
            // Log::info("التاريخ المدخل $value ليس تاريخًا هجريًا.");
            // إذا كان التاريخ ميلاديًا بالفعل، قم بتخزينه مباشرة
            return Carbon::parse($value)->format('Y-m-d');
        }
    }

    protected function isHijriDate($date)
    {
        // التحقق مما إذا كان التاريخ الهجري بتنسيق YYYY-MM-DD أو YYYY/MM/DD
        if (preg_match('/^\d{4}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
            $year = substr($date, 0, 4);
            // التحقق مما إذا كانت السنة هجريّة (على سبيل المثال، أكبر من 1300 وأقل من 1600)
            if ($year >= 1300 && $year <= 1600) {
                return true;
            }
        }
        return false;
    }

    protected function convertToHijri($value)
    {
        if (empty($value)) {
            return null;
        }
        return LaravelHijriDateHijri::ShortDate($value);
    }
}
