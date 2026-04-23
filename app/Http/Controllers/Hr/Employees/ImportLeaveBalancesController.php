<?php

namespace App\Http\Controllers\Hr\Employees;

use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsLeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\LeaveBalance;

// موديل أنواع الإجازات

class ImportLeaveBalancesController extends Controller
{
    /**
     * استيراد أرصدة الإجازات السنوية من JSON
     */
    public function import(Request $request)
    {
        Log::info('===== بدء importLeaveBalances =====');

        // 1) جلب JSON من الطلب أو مثال افتراضي
        $json = $request->input('json') ?: <<<'JSON'
[
    {
        "nickname": "حسين الزهراني",
        "name": "حسين عبدالله علي الزهراني",
        "id_number": "1079951073",
        "work_email": "hussain@alburhan.sa",
        "total_days": "142",
        "remaining_days": "142"
    },
    {
        "nickname": "سعيد القرني",
        "name": "سعيد محمد سعيد القرني",
        "id_number": "1048403065",
        "work_email": "saeed@alburhan.sa",
        "total_days": "142",
        "remaining_days": "142"
    },
    {
        "nickname": "أنس المقرفي",
        "name": "أنس عبدالله سعيد المقرفي",
        "id_number": "1095707855",
        "work_email": "a.almgrafi@alburhan.sa",
        "total_days": "45",
        "remaining_days": "11"
    },
    {
        "nickname": "محمد الأشبح",
        "name": "محمد يوسف قاسم عبدالقوي",
        "id_number": "2152080608",
        "work_email": "mhmdashbh@alburhan.sa",
        "total_days": "134",
        "remaining_days": "92.5"
    },
    {
        "nickname": "عبدالرحمن سهلي",
        "name": "عبدالرحمن محمد عبدالرحمن سهلي",
        "id_number": "1121477523",
        "work_email": "asahli@alburhan.sa",
        "total_days": "62",
        "remaining_days": "26"
    },
    {
        "nickname": "يحيى حكمي",
        "name": "يحيى علي يحي حكمي",
        "id_number": "108747756",
        "work_email": "y.hakami@alburhan.sa",
        "total_days": "109",
        "remaining_days": "2"
    },
    {
        "nickname": "محمد القرني",
        "name": "محمد عبدالله بن سعد ال سعدي القرني",
        "id_number": "1088380300",
        "work_email": "malqarni@alburhan.sa",
        "total_days": "58",
        "remaining_days": "8"
    },
    {
        "nickname": "عبدالله سحاري",
        "name": "عبدالله جبران بن جابر سحاري",
        "id_number": "1114740259",
        "work_email": "asahhari@alburhan.sa",
        "total_days": "58",
        "remaining_days": "19"
    },
    {
        "nickname": "رهف عمار",
        "name": "رهف منصور عبدالله بن عمار",
        "id_number": "1097786725",
        "work_email": "rammar@alburhan.sa",
        "total_days": "36",
        "remaining_days": "16"
    },
    {
        "nickname": "علي القرني",
        "name": "علي أحمد محمد القرني",
        "id_number": "1101673489",
        "work_email": "alialqarni@alburhan.sa",
        "total_days": "36",
        "remaining_days": "5"
    },
    {
        "nickname": "عبدالله الخطيب",
        "name": "عبدالله عبدالمجيد جمال الدين",
        "id_number": "2122087352",
        "work_email": "ajmaluddin@alburhan.sa",
        "total_days": "34",
        "remaining_days": "14"
    },
    {
        "nickname": "سامي القرني",
        "name": "سامي محمد سعد القرني",
        "id_number": "1104050974",
        "work_email": "salqarni@alburhan.sa",
        "total_days": "29",
        "remaining_days": "7.5"
    },
    {
        "nickname": "عبدالله البهلال",
        "name": "عبدالله فريح صالح البهلال",
        "id_number": "1029773106",
        "work_email": "aalbihlal@alburhan.sa",
        "total_days": "28",
        "remaining_days": "8"
    },
    {
        "nickname": "جواهر الجهني",
        "name": "جواهر حسن حمدي الجهني",
        "id_number": "1098733015",
        "work_email": "jaljehani@alburhan.sa",
        "total_days": "24",
        "remaining_days": "16"
    },
    {
        "nickname": "أحمد الزهراني",
        "name": "أحمد عبدالله خضر الطفيلي الزهراني",
        "id_number": "1044942405",
        "work_email": "",
        "total_days": "24",
        "remaining_days": "24"
    },
    {
        "nickname": "جود العمر",
        "name": "جود وليد عمر العمر",
        "id_number": "1103371397",
        "work_email": "jalomar@alburhan.sa",
        "total_days": "18",
        "remaining_days": "15"
    },
    {
        "nickname": "سارا الشهري",
        "name": "سارا ذياب عبدالرحمن الشهري",
        "id_number": "1064010000",
        "work_email": "salshehri@alburhan.sa",
        "total_days": "17",
        "remaining_days": "6"
    },
    {
        "nickname": "الصديق عبدالباقي حسين محمد",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "105",
        "remaining_days": "105"
    },
    {
        "nickname": "كريم عبدالمطلب",
        "name": "كريم عبدالمطلب حسن جاد",
        "id_number": "2380613188",
        "work_email": "kgad@alburhan.sa",
        "total_days": "74",
        "remaining_days": "-11.5"
    },
    {
        "nickname": "أحمد الحارثي",
        "name": "أحمد صالح أحمد العجي",
        "id_number": "2152682726",
        "work_email": "aalaji@alburhan.sa",
        "total_days": "75",
        "remaining_days": "28.5"
    },
    {
        "nickname": "لين الشبانه",
        "name": "لين بدر عبدالعزيز الشبانه",
        "id_number": "1105527012",
        "work_email": "lalsha@alburhan.sa",
        "total_days": "67",
        "remaining_days": "23.5"
    },
    {
        "nickname": "زول فهد",
        "name": "زول فهد قاسم آدم",
        "id_number": "2505220919",
        "work_email": "",
        "total_days": "52",
        "remaining_days": "52"
    },
    {
        "nickname": "محمد ابراهيم",
        "name": "محمد ابراهيم يوسف ابراهيم",
        "id_number": "2091228367",
        "work_email": "malyoussef@alburhan.sa",
        "total_days": "14",
        "remaining_days": "14"
    },
    {
        "nickname": "أماني الحارثي",
        "name": "أماني ساير ناصر الحارثي",
        "id_number": "1072765108",
        "work_email": "aalharthi@alburhan.com",
        "total_days": "6",
        "remaining_days": "6"
    },
    {
        "nickname": "Elina Idrisova",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "52",
        "remaining_days": "52"
    },
    {
        "nickname": "Foster Hoppie",
        "name": "فوستر انطون هوبي",
        "id_number": "2282743067",
        "work_email": "",
        "total_days": "38",
        "remaining_days": "38"
    },
    {
        "nickname": "أحمد الكناني",
        "name": "أحمد حسن خاطر الزهراني",
        "id_number": "1049069279",
        "work_email": "",
        "total_days": "45",
        "remaining_days": "45"
    },
    {
        "nickname": "شهد صالح منصور الراجحي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "104",
        "remaining_days": "104"
    },
    {
        "nickname": "أيمن عبدالله عسيري",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "145",
        "remaining_days": "145"
    },
    {
        "nickname": "روان عبدالرحمن عبدالله الحسين",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "96",
        "remaining_days": "96"
    },
    {
        "nickname": "علي ظافر زاهر العلياني",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "91",
        "remaining_days": "91"
    },
    {
        "nickname": "حنان أحمد يزيد الحكمي الفيفي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "93",
        "remaining_days": "93"
    },
    {
        "nickname": "مهند عبدالرحمن الصادق القايدي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "126",
        "remaining_days": "126"
    },
    {
        "nickname": "عبدالله محمد نافل الحارثي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "92",
        "remaining_days": "92"
    },
    {
        "nickname": "سارة سعد ناصر الموسى",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "91",
        "remaining_days": "91"
    },
    {
        "nickname": "أحمد لاحق أحمد عسيري",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "105",
        "remaining_days": "105"
    },
    {
        "nickname": "أحمد سعيد الخبتي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "",
        "remaining_days": ""
    },
    {
        "nickname": "سعيد أحمد سعيد ال معبر",
        "name": "",
        "id_number": "1090438498",
        "work_email": "Smoaber@alburhan.sa",
        "total_days": "100",
        "remaining_days": "100"
    },
    {
        "nickname": "تاج السر عبدالله الأمين",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "",
        "remaining_days": ""
    },
    {
        "nickname": "الجوهرة اللحيدان",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "83",
        "remaining_days": "83"
    },
    {
        "nickname": "أفنان سعد محمد الدوسري",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "109",
        "remaining_days": "109"
    },
    {
        "nickname": "سحر بنت عواض بن عميش الغنامي العتيبي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "71",
        "remaining_days": "71"
    },
    {
        "nickname": "نور عبدالله محمد الرماح",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "68",
        "remaining_days": "68"
    },
    {
        "nickname": "صالح راشد رافعة الغامدي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "107",
        "remaining_days": "107"
    },
    {
        "nickname": "أسماء أحمد حميد مكرمي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "69",
        "remaining_days": "69"
    },
    {
        "nickname": "أسماء المطيري",
        "name": "أسماء فيحان نور المطيري",
        "id_number": "1086931431",
        "work_email": "aalmotairi@alburhan.sa",
        "total_days": "67",
        "remaining_days": "50"
    },
    {
        "nickname": "رنا عايض القحطاني",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "114",
        "remaining_days": "114"
    },
    {
        "nickname": "هدى سلطان الملحم",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "122",
        "remaining_days": "122"
    },
    {
        "nickname": "لما ناصر القويفلي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "120",
        "remaining_days": "120"
    },
    {
        "nickname": "بشاير عبدالله إبراهيم المقرن",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "115",
        "remaining_days": "115"
    },
    {
        "nickname": "نواف أحمد قاسم معيني",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "114",
        "remaining_days": "114"
    },
    {
        "nickname": "رحيل آل عائض",
        "name": "رحيل حسين محمد آل عائض",
        "id_number": "1109310381",
        "work_email": "",
        "total_days": "41",
        "remaining_days": "41"
    },
    {
        "nickname": "سازيد الرحمن ابيدور رحمن",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "119",
        "remaining_days": "119"
    },
    {
        "nickname": "معالي آل مفرح",
        "name": "معالي مهدي سعيد آل مفرح",
        "id_number": "1128868724",
        "work_email": "aaa@sssss",
        "total_days": "36",
        "remaining_days": "34"
    },
    {
        "nickname": "لمياء العتيبي",
        "name": "لمياء نايف داموك الدلبحي العتيبي",
        "id_number": "1107651934",
        "work_email": "lalotaibi@alburhan.sa",
        "total_days": "46",
        "remaining_days": "24.5"
    },
    {
        "nickname": "عبدالكريم أبو دبيل",
        "name": "عبدالكريم بن مسفر بن مريع ال بودبيل",
        "id_number": "1104140494",
        "work_email": "abudabeel@alburhan.sa",
        "total_days": "50",
        "remaining_days": "10.5"
    },
    {
        "nickname": "حسن محمد حسن آل وافي العمري",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "91",
        "remaining_days": "91"
    },
    {
        "nickname": "حسن يحيى حسن حكمي",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "87",
        "remaining_days": "87"
    },
    {
        "nickname": "أحمد لاحق محمد آل سلطان عسيري",
        "name": "",
        "id_number": "",
        "work_email": "",
        "total_days": "86",
        "remaining_days": "86"
    }
]
JSON;
        $items = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('خطأ فى JSON: ' . json_last_error_msg());
            return response()->json([
                'message' => 'Invalid JSON',
                'error'   => json_last_error_msg(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2) الحصول على معرف نوع الإجازة السنوية
        $leaveTypeId = SettingsLeaveType::where('name', 'السنوية')->value('id');
        if (!$leaveTypeId) {
            Log::error('نوع الإجازة السنوية غير موجود في الإعدادات');
            return response()->json(['message' => 'Annual leave type not found'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $index => $item) {
                Log::info("معالجة العنصر #{$index}", $item);

                // 4) حساب الأيام (دون تعديل السالب)
                $total     = is_numeric($item['total_days'])     ? (float)$item['total_days']     : 0;
                $remaining = is_numeric($item['remaining_days']) ? (float)$item['remaining_days'] : 0;

                // تخطي الموظفين الذين رصيدهم صفر تماماً
                if ($total === 0 && $remaining === 0) {
                    Log::info("تخطى العنصر #{$index} – الرصيد صفر");
                    continue;
                }

                // 3) البحث عن الموظف بناءً على عدة حقول (id_number, work_email, name, nickname)
                $employee = Employees::where(function ($q) use ($item) {
                    if (!empty($item['id_number'])) {
                        $q->orWhere('id_number', trim($item['id_number']));
                    }
                    if (!empty($item['work_email'])) {
                        $q->orWhere('work_email', trim($item['work_email']));
                    }
                    if (!empty($item['name'])) {
                        $q->orWhere('name', 'like', '%' . trim($item['name']) . '%');
                    }
                    if (!empty($item['nickname'])) {
                        $q->orWhere('nickname', 'like', '%' . trim($item['nickname']) . '%');
                    }
                })->first();

                if (!$employee) {
                    Log::warning("تخطى العنصر #{$index} – موظف غير موجود");
                    continue;
                }

                // 5) تجميع بيانات الرصيد وحفظ last_accrued_at بتاريخ اليوم
                $used = $total - $remaining;

                $data = [
                    'employee_id'     => $employee->id,
                    'leave_type_id'   => $leaveTypeId,
                    'year'            => Carbon::now()->year,
                    'total_days'      => $total,
                    'used_days'       => $used,
                    'remaining_days'  => $remaining,
                    'last_accrued_at' => Carbon::now()->toDateString(),
                    'last_updated_by' => Auth::id() ?? null,
                ];

                // 6) إدخال السجل
                LeaveBalance::create($data);
                Log::info("تم إنشاء رصيد اجازة لموظف id={$employee->id}");
            }

            DB::commit();
            Log::info('تم تأكيد المعاملة – انتهاء importLeaveBalances بنجاح');
            return response()->json(['message' => 'تم استيراد أرصدة الإجازات بنجاح']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('فشل importLeaveBalances: ' . $e->getMessage());
            return response()->json([
                'message' => 'خطأ أثناء الاستيراد',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
