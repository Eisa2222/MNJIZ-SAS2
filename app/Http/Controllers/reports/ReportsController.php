<?php

namespace App\Http\Controllers\reports;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Http\Controllers\Controller;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsSector;
use App\Models\general_setting\SettingsStagePriceOffer;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ReportsController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:تقرير العملاء')->only('customersReport');
        $this->middleware('can:تقرير العروض')->only('offersReport');
        $this->middleware('can:تقرير العقود')->only('contractsReport');
        $this->middleware('can:تقرير الوكالات')->only('powerOfAttorneyReport');
        $this->middleware('can:تقرير الخصوم')->only('opponentsReport');
        $this->middleware('can:تقرير المشاريع')->only('projectsReport');
        $this->middleware('can:تقرير الدعاوى')->only('lawsuitsReport');
        $this->middleware('can:تقرير الجلسات')->only('sessionsReport');
        $this->middleware('can:تقرير الموظفين')->only('employeesReport');
    }

    /*
    |--------------------------------------------------------------------------
    | customersReport
    |--------------------------------------------------------------------------
    */
    public function customersReport()
    {
        $colorsUnique = Settings::current()->colors;

        // استرجاع جميع العملاء مع العلاقات
        $customers = Customers::with([
            'status',
            'nationality',
            'relationshipManager',
            'marketingChannel',
            'sector'
        ])->get();

        // إصلاح بيانات حالة العملاء
        $charstatusData = Customers::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->clientStatus ? $item->clientStatus->name : 'غير محدد',
                    'count' => $item->count
                ];
            });

        // إصلاح بيانات قنوات التسويق
        $charMarketingChannel = Customers::select('marketing_channel_id', DB::raw('count(*) as count'))
            ->groupBy('marketing_channel_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->marketingChannel_function ? $item->marketingChannel_function->name : 'غير محدد',
                    'count' => $item->count
                ];
            });

        // باقي الإحصائيات مع معالجة القيم الفارغة
        $statusData = SettingsClientStatus::withCount('customers')->orderBy('customers_count', 'desc')->get();

        $statusLabels = $statusData->pluck('name');
        $statusCounts = $statusData->pluck('customers_count');

        $nationalityData = SettingsCountry::withCount('customers')
            ->orderBy('customers_count', 'desc')
            ->get();
        $nationalityLabels = $nationalityData->pluck('name');
        $nationalityCounts = $nationalityData->pluck('customers_count');

        $marketingChannelData = SettingsMarketingChannel::withCount('customers')
            ->orderBy('customers_count', 'desc')
            ->get();
        $marketingChannelLabels = $marketingChannelData->pluck('name');
        $marketingChannelCounts = $marketingChannelData->pluck('customers_count');

        $sectorData = SettingsSector::withCount('customers')
            ->orderBy('customers_count', 'desc')
            ->get();
        $sectorLabels = $sectorData->pluck('name');
        $sectorCounts = $sectorData->pluck('customers_count');

        return view('reports.customers', compact(
            'customers',
            'statusLabels',
            'statusCounts',
            'nationalityLabels',
            'nationalityCounts',
            'marketingChannelLabels',
            'marketingChannelCounts',
            'sectorLabels',
            'sectorCounts',
            'charstatusData',
            'colorsUnique',
            'charMarketingChannel'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | offersReport
    |--------------------------------------------------------------------------
    */
    public function offersReport()
    {
        // منطق تقرير العروض
        // إجمالي العروض
        $totalOffers = Offers::count();

        // العروض حسب مرحلة العرض
        // $offersByStage = SettingsStagePriceOffer::withCount('offers')->get()->map(function ($stage) {
        //     return [
        //         'name' => $stage->name,
        //         'count' => $stage->offers_count,
        //     ];
        // });

        // جلب آخر 10 عروض بناءً على تاريخ الإنشاء
        $lastTenOffers = Offers::with('relationshipManager')
            ->orderBy('id', 'desc') // ترتيب حسب تاريخ الإنشاء أو يمكنك استخدام `id` إذا كان هو المحدد للأحدث
            ->limit(10)
            ->get();

        // حساب عدد العروض لكل مدير من بين العروض العشرة الأخيرة
        $offersByManager = $lastTenOffers
            ->groupBy('relationship_manager_id')
            ->map(function ($offers, $managerId) {
                return [
                    'name' => $offers->first()->relationshipManager->name ?? 'غير محدد',
                    'count' => $offers->count(),
                ];
            })
            ->values(); // لإعادة القيم في شكل مصفوفة مرتبة

        // العروض حسب العميل
        // حساب عدد العروض لكل عميل من بين العروض العشرة الأخيرة
        $offersByCustomer = $lastTenOffers
            ->groupBy('customer_id')
            ->map(function ($offers, $customerId) {
                return [
                    'name' => $offers->first()->customer->name ?? 'غير محدد',
                    'count' => $offers->count(),
                ];
            })
            ->values(); // لإعادة القيم كقائمة مرتبة


        $offersByStartStatus = [
            [
                'name' => 'بدأت',
                'count' => Offers::where('start_date', '<=', Carbon::today())->count(),
            ],
            [
                'name' => 'لم تبدأ',
                'count' => Offers::where('start_date', '>', Carbon::today())->count(),
            ],
        ];

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;
        // بيانات الجدول
        $offers = Offers::with(['customer', 'relationshipManager'])->get();

        return view('reports.offers', compact(
            'totalOffers',
            // 'offersByStage',
            'offersByManager',
            'offersByCustomer',
            'colorsUnique',
            'offers',
            'offersByStartStatus'
        ));
        return view('reports.offers', compact('offers'));
    }

    /*
    |--------------------------------------------------------------------------
    | convertNumberToHijriMonthName
    |--------------------------------------------------------------------------
    */
    function convertNumberToHijriMonthName($monthNumber)
    {
        $hijriMonths = [
            1 => 'محرم',
            2 => 'صفر',
            3 => 'ربيع الأول',
            4 => 'ربيع الثاني',
            5 => 'جمادى الأولى',
            6 => 'جمادى الآخرة',
            7 => 'رجب',
            8 => 'شعبان',
            9 => 'رمضان',
            10 => 'شوال',
            11 => 'ذو القعدة',
            12 => 'ذو الحجة'
        ];
        return $hijriMonths[$monthNumber] ?? 'شهر غير معروف';
    }


    /*
    |--------------------------------------------------------------------------
    | contractsReport
    |--------------------------------------------------------------------------
    */
    public function contractsReport()
    {
        // إجمالي العقود
        $totalContracts = Contract::count();

        // العقود حسب حالة العقد
        $contractsByStatus = SettingsContractStatus::withCount('contracts')->get()->map(function ($status) {
            return [
                'name' => $status->name,
                'count' => $status->contracts_count,
            ];
        });

        // جلب آخر 10 عقود بناءً على تاريخ الإنشاء
        $lastTenContracts = Contract::with(['contractManager', 'customer', 'relationshipManager', 'offer'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // العقود حسب مدير العقد من بين العقود العشرة الأخيرة
        $contractsByManager = $lastTenContracts
            ->groupBy('contract_manager_id')
            ->map(function ($contracts, $managerId) {
                return [
                    'name' => $contracts->first()->contractManager->name ?? 'غير محدد',
                    'count' => $contracts->count(),
                ];
            })
            ->values();

        // العقود حسب مدير العلاقة من بين العقود العشرة الأخيرة
        $contractsByRelationshipManager = $lastTenContracts
            ->groupBy('relationship_manager_id')
            ->map(function ($contracts, $managerId) {
                return [
                    'name' => $contracts->first()->relationshipManager->name ?? 'غير محدد',
                    'count' => $contracts->count(),
                ];
            })
            ->values();

        // العقود حسب العميل من بين العقود العشرة الأخيرة
        $contractsByCustomer = $lastTenContracts
            ->groupBy('customer_id')
            ->map(function ($contracts, $customerId) {
                return [
                    'name' => $contracts->first()->customer->name ?? 'غير محدد',
                    'count' => $contracts->count(),
                ];
            })
            ->values();

        // العقود حسب حالة البدء
        $contractsByStartStatus = [
            [
                'name' => 'بدأت',
                'count' => Contract::where('contract_start_date', '<=', Carbon::today())->count(),
            ],
            [
                'name' => 'لم تبدأ',
                'count' => Contract::where('contract_start_date', '>', Carbon::today())->count(),
            ],
        ];

        // العقود حسب شهر الإغلاق المتوقع (الشهور الهجرية)
        // مصفوفة أسماء الشهور الهجرية


        // مثال للاستخدام
        $contractsByExpectedClosureMonth = Contract::select('expected_closure_date')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->expected_closure_date)) {
                    try {
                        // تحويل التاريخ الهجري إلى ميلادي
                        $gregorianDate = Hijri::DateToGregorianFromDMY(
                            substr($item->expected_closure_date, 8, 2),
                            substr($item->expected_closure_date, 5, 2),
                            substr($item->expected_closure_date, 0, 4)
                        );

                        // استخدام التاريخ الميلادي الجديد للحصول على الشهر الهجري
                        $hijriMonth = (int) Hijri::Date('m', $gregorianDate);

                        return $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'expected_closure_date' => $item->expected_closure_date
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count') // ترتيب تنازلي بناءً على العدد
            ->values(); // إعادة ضبط المفاتيح

        // العقود حسب الوقت المتبقي (تصنيفات)
        $contractsByTimeRemaining = [
            [
                'name' => 'أقل من شهر',
                'count' => Contract::where('contract_end_date', '<=', Carbon::today()->addMonth())->count(),
            ],
            [
                'name' => '1-3 أشهر',
                'count' => Contract::whereBetween('contract_end_date', [Carbon::today()->addMonth(), Carbon::today()->addMonths(3)])->count(),
            ],
            [
                'name' => 'أكثر من 3 أشهر',
                'count' => Contract::where('contract_end_date', '>', Carbon::today()->addMonths(3))->count(),
            ],
        ];

        // العقود حسب العرض (آخر 10 عروض)
        $contractsByOffer = $lastTenContracts
            ->groupBy('offer_id')
            ->map(function ($contracts, $offerId) {
                return [
                    'name' => $contracts->first()->offer->offer_name ?? 'غير محدد',
                    'count' => $contracts->count(),
                ];
            })
            ->values();

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        // بيانات الجدول (إن وجدت)
        $contracts = Contract::with(['customer', 'contractManager', 'contract_status'])->get();

        return view('reports.contracts', compact(
            'totalContracts',
            'contractsByStatus',
            'contractsByManager',
            'contractsByCustomer',
            'contractsByStartStatus',
            'contractsByExpectedClosureMonth',
            'contractsByTimeRemaining',
            'contractsByOffer',
            'contractsByRelationshipManager',
            'colorsUnique',
            'contracts'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | powerOfAttorneyReport
    |--------------------------------------------------------------------------
    */
    public function powerOfAttorneyReport()
    {
        // إجمالي الوكالات
        $totalPowers = PowerOfAttorney::count();

        // الوكالات حسب الحالة
        $powersByStatus = PowerOfAttorney::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $this->getStatusName($item->status),
                    'count' => $item->count,
                ];
            });

        // جلب آخر 10 وكالات بناءً على تاريخ الإنشاء
        $lastTenPowers = PowerOfAttorney::with(['agents', 'customers'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();




        $powersByIssuedMonth = PowerOfAttorney::select('date_issued')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->date_issued)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->date_issued)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'date_issued' => $item->date_issued
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // الوكالات حسب الوقت المتبقي (تصنيفات)
        $powersByTimeRemaining = [
            [
                'name' => 'أقل من شهر',
                'count' => PowerOfAttorney::where('date_expiry', '<=', Carbon::today()->addMonth())->count(),
            ],
            [
                'name' => '1-3 أشهر',
                'count' => PowerOfAttorney::whereBetween('date_expiry', [Carbon::today()->addMonth(), Carbon::today()->addMonths(3)])->count(),
            ],
            [
                'name' => 'أكثر من 3 أشهر',
                'count' => PowerOfAttorney::where('date_expiry', '>', Carbon::today()->addMonths(3))->count(),
            ],
        ];

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        // تحويل بيانات الحالة إلى نصوص
        $powersByStatus = $powersByStatus->map(function ($item) {
            return [
                'name' => $item['name'],
                'count' => $item['count'],
            ];
        });

        return view('reports.power_of_attorney', compact(
            'totalPowers',
            'powersByStatus',
            'powersByIssuedMonth',
            'powersByTimeRemaining',
            'colorsUnique'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | getStatusName
    |--------------------------------------------------------------------------
    */
    private function getStatusName($status)
    {
        $statuses = [
            'active' => 'سارية',
            'expired' => 'منتهية',
            'revoked' => 'ملغاة',
        ];

        return $statuses[$status] ?? $status;
    }

    /*
    |--------------------------------------------------------------------------
    | opponentsReport
    |--------------------------------------------------------------------------
    */
    public function opponentsReport()
    {
        // إجمالي الخصوم
        $totalOpponents = Opponent::count();

        // الخصوم حسب النوع (أفراد أو مؤسسة)
        $opponentsByType = Opponent::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->map(function ($item) {

                return [
                    'name' => $this->getTypeName($item->type->value),
                    'count' => $item->count,
                ];
            });


        // الخصوم حسب المدينة
        $opponentsByRegion = Opponent::with('region')
            ->select('settings_region_id', DB::raw('count(*) as count'))
            ->groupBy('settings_region_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->region->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الخصوم حسب شهر الإنشاء (هجري)
        $opponentsByCreatedMonth = Opponent::select('created_at')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->created_at)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->created_at)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'created_at' => $item->date_issued
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();



        // أعلى 10 خصوم حسب عدد القضايا
        $opponentsWithLawsuitCounts = Opponent::withCount(['lawsuitsAsPlaintiff', 'lawsuitsAsDefendant'])
            ->orderByDesc(DB::raw('lawsuits_as_plaintiff_count + lawsuits_as_defendant_count'))
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->lawsuits_as_plaintiff_count + $item->lawsuits_as_defendant_count,
                ];
            });

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        return view('reports.opponents', compact(
            'totalOpponents',
            'opponentsByType',
            'opponentsByRegion',
            'opponentsByCreatedMonth',
            'opponentsWithLawsuitCounts',
            'colorsUnique'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | getTypeName
    |--------------------------------------------------------------------------
    */
    private function getTypeName($type)
    {
        $types = [
            'individual' => 'أفراد',
            'company' => 'مؤسسة',
        ];

        return $types[$type] ?? $type;
    }

    /*
    |--------------------------------------------------------------------------
    | projectsReport
    |--------------------------------------------------------------------------
    */
    public function projectsReport()
    {
        // إجمالي المشاريع
        $totalProjects = Project::count();

        // المشاريع حسب الحالة
        $projectsByStatus = Project::select('new_status_id', DB::raw('count(*) as count'))
            ->groupBy('new_status_id')
            ->with('project_status')
            ->get()
            ->map(function ($item) {
                return [
                    'project_status' => $item->project_status,
                    'count' => $item->count,
                ];
            });

        // المشاريع حسب مدير المشروع
        $lastTenProjects = Project::with('manager_user', 'contracts')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $projectsByManager = $lastTenProjects
            ->groupBy('manager_user_id')
            ->map(function ($projects, $managerId) {
                return [
                    'name' => $projects->first()->manager_user->name ?? 'غير محدد',
                    'count' => $projects->count(),
                ];
            })
            ->values();

        // المشاريع حسب العقد
        $projectsByContract = $lastTenProjects
            ->flatMap(function ($project) {
                return $project->contracts->map(function ($contract) {
                    return [
                        'name' => $contract->contract_name ?? 'غير محدد',
                        'contract_id' => $contract->id
                    ];
                });
            })
            ->groupBy('contract_id')
            ->map(function ($contracts) {
                return [
                    'name' => $contracts->first()['name'],
                    'count' => $contracts->count(),
                ];
            })
            ->values();

        // المشاريع حسب شهر البدء (هجري)
        $projectsByStartMonth = Project::select('start_date')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->start_date)) {
                    try {
                        // تحويل التاريخ الهجري إلى ميلادي
                        $gregorianDate = Hijri::DateToGregorianFromDMY(
                            substr($item->start_date, 8, 2),
                            substr($item->start_date, 5, 2),
                            substr($item->start_date, 0, 4)
                        );

                        // استخدام التاريخ الميلادي الجديد للحصول على الشهر الهجري
                        $hijriMonth = (int) Hijri::Date('m', $gregorianDate);

                        return $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'start_date' => $item->start_date
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // المشاريع حسب الوقت المتبقي
        $projectsByTimeRemaining = [
            [
                'name' => 'أقل من شهر',
                'count' => Project::where('contractual_closure', '>', Carbon::today())
                    ->where('contractual_closure', '<=', Carbon::today()->addMonth())
                    ->count(),
            ],
            [
                'name' => '1-3 أشهر',
                'count' => Project::where('contractual_closure', '>', Carbon::today())
                    ->whereBetween('contractual_closure', [Carbon::today()->addMonth(), Carbon::today()->addMonths(3)])
                    ->count(),
            ],
            [
                'name' => 'أكثر من 3 أشهر',
                'count' => Project::where('contractual_closure', '>', Carbon::today()->addMonths(3))
                    ->count(),
            ],
        ];

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        return view('reports.projects', compact(
            'totalProjects',
            'projectsByStatus',
            'projectsByManager',
            'projectsByContract',
            'projectsByStartMonth',
            'projectsByTimeRemaining',
            'colorsUnique'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | lawsuitsReport
    |--------------------------------------------------------------------------
    */
    public function lawsuitsReport()
    {
        // إجمالي الدعاوى
        $totalLawsuits = Lawsuit::count();

        // الدعاوى حسب الحالة
        $lawsuitsByStatus = Lawsuit::select('lawsuit_status', DB::raw('count(*) as count'))
            ->groupBy('lawsuit_status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $this->getLawsuitStatusName($item->lawsuit_status->value),
                    'count' => $item->count,
                ];
            });

        // الدعاوى حسب المحكمة الرئيسية
        $lawsuitsByMainCourt = Lawsuit::with('main_court')
            ->select('main_courts_id', DB::raw('count(*) as count'))
            ->groupBy('main_courts_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->main_court->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الدعاوى حسب المدينة
        $lawsuitsByRegion = Lawsuit::with('region')
            ->select('regions_id', DB::raw('count(*) as count'))
            ->groupBy('regions_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->region->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الدعاوى حسب نوع الدعوى
        // جلب آخر 10 دعاوى مرتبة تنازليًا
        $lastTenLawsuits = Lawsuit::with('lawsuit_type', 'category',)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // تجميع الدعاوى حسب نوع الدعوى من بين آخر 10 دعاوى
        $lawsuitsByType = $lastTenLawsuits
            ->groupBy('lawsuit_type_id')
            ->map(function ($lawsuits, $typeId) {
                return [
                    'name' => $lawsuits->first()->lawsuit_type->name ?? 'غير محدد',
                    'count' => $lawsuits->count(),
                ];
            })
            ->values(); // لتحويل النتائج إلى مجموعة مرتبة


        // الدعاوى حسب التصنيف الرئيسي
        $lawsuitsByCategory = $lastTenLawsuits
            ->groupBy('category_id')
            ->map(function ($lawsuits, $categoryId) {
                return [
                    'name' => $lawsuits->first()->category->name ?? 'غير محدد',
                    'count' => $lawsuits->count(),
                ];
            })
            ->values();

        // الدعاوى حسب شهر الإنشاء (هجري)
        $lawsuitsByCreatedMonth = Lawsuit::select('created_at')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->created_at)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->created_at)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'created_at' => $item->date_issued
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // الدعاوى حسب المستخدم
        // تجميع الدعاوى حسب المستخدم من بين آخر 10 دعاوى
        $lawsuitsByUser = $lastTenLawsuits
            ->groupBy('user_id')
            ->map(function ($lawsuits, $userId) {
                return [
                    'name' => $lawsuits->first()->user->name ?? 'غير محدد',
                    'count' => $lawsuits->count(),
                ];
            })
            ->values();

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        return view('reports.lawsuits', compact(
            'totalLawsuits',
            'lawsuitsByStatus',
            'lawsuitsByMainCourt',
            'lawsuitsByRegion',
            'lawsuitsByType',
            'lawsuitsByCategory',
            'lawsuitsByCreatedMonth',
            'lawsuitsByUser',
            'colorsUnique'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | getLawsuitStatusName
    |--------------------------------------------------------------------------
    */
    private function getLawsuitStatusName($status)
    {
        $statuses = [
            'active' => 'نشطة',
            'inactive' => 'مغلقة',
        ];

        return $statuses[$status] ?? $status;
    }


    /*
    |--------------------------------------------------------------------------
    | sessionsReport
    |--------------------------------------------------------------------------
    */
    public function sessionsReport()
    {
        // إجمالي الجلسات
        $totalSessions = Session::count();

        // الجلسات حسب الحالة
        $sessionsByStatus = Session::select('session_status', DB::raw('count(*) as count'))
            ->groupBy('session_status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->session_status,
                    'count' => $item->count,
                ];
            });

        // الجلسات حسب الأهمية
        // $sessionsByImportance = Session::select('session_importance', DB::raw('count(*) as count'))
        //     ->groupBy('session_importance')
        //     ->get()
        //     ->map(function ($item) {
        //         return [
        //             'name' => $item->session_importance,
        //             'count' => $item->count,
        //         ];
        //     });

        // الجلسات حسب نوع الجلسة
        $sessionsByType = Session::with('sessionType')
            ->select('session_type', DB::raw('count(*) as count'))
            ->groupBy('session_type')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->sessionType->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الجلسات حسب درجة الجهة
        $sessionsByEntityRank = Session::with('entity_rank')
            ->select('entity_ranks_id', DB::raw('count(*) as count'))
            ->groupBy('entity_ranks_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->entity_rank->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الجلسات حسب شهر الجلسة (هجري)
        $sessionsByMonth = Session::select('session_date')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->session_date)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->session_date)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'session_date' => $item->session_date
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // الجلسات حسب المستخدمين المكلفين
        // جلب آخر 10 جلسات مرتبة حسب الأحدث
        $lastTenSessions = Session::orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // تجميع الموظفين المكلفين حسب عدد التكليفات لكل موظف من بين آخر 10 جلسات
        $sessionsByAssignedUser = $lastTenSessions

            ->flatten()
            ->groupBy('id')
            ->map(function ($items, $key) {
                return [
                    'name' => $items->first()->name ?? 'غير محدد',
                    'count' => count($items),
                ];
            })
            ->values(); // تحويل النتائج إلى مجموعة مرتبة


        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        return view('reports.sessions', compact(
            'totalSessions',
            'sessionsByStatus',
            // 'sessionsByImportance',
            'sessionsByType',
            'sessionsByEntityRank',
            'sessionsByMonth',
            'sessionsByAssignedUser',
            'colorsUnique'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | employeesReport
    |--------------------------------------------------------------------------
    */
    public function employeesReport()
    {
        // إجمالي الموظفين
        $totalEmployees = Employees::count();

        // الموظفين حسب الجنسية
        $employeesByNationality = Employees::with('country')
            ->select('nationality', DB::raw('count(*) as count'))
            ->groupBy('nationality')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->country->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            })->sortByDesc('count') // ترتيب تنازلي حسب الحقل count
            ->values(); // إعادة ضبط المفاتيح

        // الموظفين حسب المسمى الوظيفي
        $employeesByJobTitle = Employees::select('job_title', DB::raw('count(*) as count'))
            ->groupBy('job_title')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->job_title ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب حالة الموارد البشرية (hr_status)
        $employeesByHRStatus = Employees::with('hrStatus')
            ->select('hr_status_id', DB::raw('count(*) as count'))
            ->groupBy('hr_status_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->hrStatus->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب تصنيف الموارد البشرية (hr_classification)
        $employeesByHRClassification = Employees::with('hrClassification')
            ->select('hr_classification_id', DB::raw('count(*) as count'))
            ->groupBy('hr_classification_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->hrClassification->name ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب مجال المعرفة (knowledge_area)
        $employeesByKnowledgeArea = Employees::select('knowledge_area', DB::raw('count(*) as count'))
            ->groupBy('knowledge_area')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->knowledge_area ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب نوع الرخصة (license_type)
        $employeesByLicenseType = Employees::select('license_type', DB::raw('count(*) as count'))
            ->groupBy('license_type')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->license_type ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب الدرجة العلمية (qualification_degree)
        $employeesByQualificationDegree = Employees::select('qualification_degree', DB::raw('count(*) as count'))
            ->groupBy('qualification_degree')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->qualification_degree ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الموظفين حسب شهر الميلاد (هجري)
        $employeesByBirthMonth = Employees::select('birth_date')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->birth_date)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->birth_date)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'birth_date' => $item->birth_date
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // الموظفين حسب شهر بدء العقد (هجري)
        $employeesByContractStartMonth = Employees::select('contract_start_date')
            ->get()
            ->groupBy(function ($item) {
                if (!empty($item->contract_start_date)) {
                    try {
                        // تحويل التاريخ الميلادي إلى الشهر الهجري باستخدام مكتبة Hijri مباشرة
                        $hijriMonth = Hijri::Date('m', Carbon::parse($item->contract_start_date)->format('Y-m-d'));
                        return (int) $hijriMonth;
                    } catch (\Exception $e) {
                        Log::error("Failed to convert to Hijri month: " . $e->getMessage(), [
                            'contract_start_date' => $item->contract_start_date
                        ]);
                        return 'غير صالح';
                    }
                } else {
                    return 'غير متاح';
                }
            })
            ->map(function ($items, $month) {
                $monthName = is_numeric($month) ? $this->convertNumberToHijriMonthName($month) : $month;
                return [
                    'month' => $monthName,
                    'count' => count($items),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // الموظفين حسب حالة التأمين (insurance_status)
        $employeesByInsuranceStatus = Employees::select('insurance_status', DB::raw('count(*) as count'))
            ->groupBy('insurance_status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->insurance_status ?? 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // الألوان الفريدة للمخططات
        $colorsUnique = Settings::current()->colors;

        return view('reports.employees', compact(
            'totalEmployees',
            'employeesByNationality',
            'employeesByJobTitle',
            'employeesByHRStatus',
            'employeesByHRClassification',
            'employeesByKnowledgeArea',
            'employeesByLicenseType',
            'employeesByQualificationDegree',
            'employeesByBirthMonth',
            'employeesByContractStartMonth',
            'employeesByInsuranceStatus',
            'colorsUnique'
        ));
    }
}
