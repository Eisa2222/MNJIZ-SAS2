<?php

namespace App\Http\Controllers\dashboard;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\PowerOfAttorney\PowerOfAttorneyStatus;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();
        $userId = auth()->id();

        // لجلب الوان المخططات
        $colorsUnique = Settings::find(1)->colors;


        /*
        |--------------------------------------------------------------------------
        | customers
        |--------------------------------------------------------------------------
        */
        // تحقق من وجود أي صلاحيات متعلقة بالعملاء
        $hasCustomerPermissions = auth()->user()->can('كل العملاء') || auth()->user()->can('العملاء الخاصين بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $customers          = collect();
        $charCustomers      = collect();
        $totalCustomers     = 0;

        if ($hasCustomerPermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل العملاء')) {
                $baseQuery = Customers::query();
            } elseif (auth()->user()->can('العملاء الخاصين بي')) {
                $baseQuery = Customers::where('created_by', auth()->id());
            }

            // جلب أحدث 5 عملاء
            $customers = (clone $baseQuery)->latest()->take(5)->get();

            // تجميع العملاء حسب القطاع مع حساب عدد العملاء
            $charCustomers = (clone $baseQuery)->select('sector_id', DB::raw('count(*) as count'))
                ->groupBy('sector_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name'  => $item->sector->name ?? "غير محدد", // قم بتعديل الاسم بناءً على احتياجك
                        'count' => $item->count
                    ];
                });

            // حساب إجمالي عدد العملاء
            $totalCustomers = (clone $baseQuery)->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Offers
        |--------------------------------------------------------------------------
        */
        // تحقق من وجود أي صلاحيات متعلقة بالعروض
        $hasOfferPermissions = auth()->user()->can('كل العروض') || auth()->user()->can('العروض الخاصة بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $offers = collect();
        $charOffers = collect();
        $totalOffers = 0;

        if ($hasOfferPermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل العروض')) {
                $baseOfferQuery = Offers::query();
            } elseif (auth()->user()->can('العروض الخاصة بي')) {
                $baseOfferQuery = Offers::where('created_by', auth()->user()->employee->id);
            }

            $offers = (clone $baseOfferQuery)->orderBy('start_date', 'desc')->take(5)->get();

            // العروض
            try {
                $charOffers = (clone $baseOfferQuery)->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' =>  $item->status->label() ?? '',
                            'count' => $item->count
                        ];
                    });
            } catch (\Exception $e) {
                $charOffers = collect();
            }
            $totalOffers = (clone $baseOfferQuery)->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Contract
        |--------------------------------------------------------------------------
        */
        // تحقق من وجود أي صلاحيات متعلقة بالعقود
        $hasContractPermissions = auth()->user()->can('كل العقود') || auth()->user()->can('العقود الخاصة بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $contracts = collect();
        $charContracts = collect();
        $totalContracts = 0;

        if ($hasContractPermissions) {

            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل العقود')) {
                $baseContractQuery = Contract::query();
            } elseif (auth()->user()->can('العقود الخاصة بي')) {
                $baseContractQuery = Contract::where('created_by', auth()->user()->employee->id);
            }

            $contracts = (clone $baseContractQuery)->where(function ($query) use ($today) {
                $query->where('contract_end_date', '>=', $today)
                    ->orWhereNull('contract_end_date');
            })
                ->orderByRaw('ABS(DATEDIFF(contract_start_date, ?))', [$today->toDateString()])
                ->take(5)
                ->get();


            // العقود
            $charContracts = (clone $baseContractQuery)->select('contract_status_id', DB::raw('count(*) as count'))
                ->groupBy('contract_status_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' =>  $item->contract_status->name ?? '',
                        'count' => $item->count
                    ];
                });


            $totalContracts = (clone $baseContractQuery)->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Projects
        |--------------------------------------------------------------------------
        */

        // تحقق من وجود أي صلاحيات متعلقة بالمشاريع
        // تحقق من وجود أي صلاحيات متعلقة بالمشاريع
        $hasProjectPermissions = auth()->user()->can('كل المشاريع') || auth()->user()->can('المشاريع الخاصة بي') || auth()->user()->can('الإعتماد الفني للمشاريع');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $projects = collect();
        $charProjects = collect();
        $totalProjects = 0;

        $onTrackPercentage = 0;
        $newPercentage = 0;
        $closePercentage = 0;


        if ($hasProjectPermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            $baseProjectQuery = Project::query();

            // تطبيق فلتر المشاريع الخاصة بي إذا كان المستخدم يملك هذه الصلاحية فقط
            if (auth()->user()->can('المشاريع الخاصة بي') && !auth()->user()->can('كل المشاريع')) {
                $baseProjectQuery = Project::userRelated();
            }
            $projects = (clone $baseProjectQuery)->orderBy('created_at', 'desc')->take(5)->get()->map(function ($project) {

                if ($project->start_date_gregorian && $project->contractual_closure_gregorian) {
                    $startDate = Carbon::parse($project->start_date_gregorian);
                    $contractualClosure = Carbon::parse($project->contractual_closure_gregorian);
                    $totalDuration = $startDate->diffInDays($contractualClosure); // إجمالي مدة المشروع

                    if ($totalDuration > 0) {
                        $elapsedDuration = $startDate->diffInDays(Carbon::now()); // الأيام التي مرت منذ بداية المشروع
                        $progress = min(100, max(0, ($elapsedDuration / $totalDuration) * 100)); // حساب نسبة التقدم
                    } else {
                        $progress = 100; // إذا كانت مدة المشروع صفرًا أو التاريخين متساويين
                    }

                    $project->progress = round($progress, 2); // تقريب النسبة
                } else {
                    $project->progress = 0; // إذا كانت التواريخ غير متوفرة
                }

                return $project;
            });

            // المشاريع
            $charProjects = (clone $baseProjectQuery)->select('new_status_id', DB::raw('count(*) as count'))
                ->groupBy('new_status_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' =>  $item->project_status->name ?? '',
                        'count' => $item->count
                    ];
                });

            $totalProjects = (clone $baseProjectQuery)->count();

            // الاحصائيات
            $on_track_projects = (clone $baseProjectQuery)->where('new_status_id', 2)->count();
            $closed_projects = (clone $baseProjectQuery)->where('new_status_id', 5)->count();
            $new_projects = (clone $baseProjectQuery)->where('new_status_id', 1)->count();


            $onTrackPercentage = $totalProjects > 0 ? ($on_track_projects / $totalProjects) * 100 : 0;
            $newPercentage = $totalProjects > 0 ? ($new_projects / $totalProjects) * 100 : 0;
            $closePercentage = $totalProjects > 0 ? ($closed_projects / $totalProjects) * 100 : 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Opponents
        |--------------------------------------------------------------------------
        */

        // تحقق من وجود أي صلاحيات متعلقة بالخصوم
        $hasOpponentPermissions = auth()->user()->can('كل الخصوم') || auth()->user()->can('الخصوم الخاصين بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $opponents = collect();
        $charOpponents = collect();
        $totalOppents = 0;

        if ($hasOpponentPermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل الخصوم')) {
                $baseOpponentQuery = Opponent::query();
            } elseif (auth()->user()->can('الخصوم الخاصين بي')) {
                $baseOpponentQuery = Opponent::where('created_by', auth()->id());
            }


            $opponents =  (clone $baseOpponentQuery)->latest()->take(5)->get();

            $charOpponents =  (clone $baseOpponentQuery)->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' =>  $item->type == 'individual' ? 'فرد' : 'شخصية اعتبارية',
                        'count' => $item->count
                    ];
                });

            $totalOppents =  (clone $baseOpponentQuery)->count();
        }


        /*
        |--------------------------------------------------------------------------
        | power Of Attorney
        |--------------------------------------------------------------------------
        */

        // تحقق من وجود أي صلاحيات متعلقة بالوكالات
        $hasPowerOfAttorneyPermissions = auth()->user()->can('كل الوكالات') || auth()->user()->can('الوكالات الخاصة بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $powerOfAttorney = collect();
        $charPowerOfAttorney = collect();
        $totalPowerOfAttorney = 0;

        if ($hasPowerOfAttorneyPermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل الوكالات')) {
                $basePowerOfAttorneyQuery = PowerOfAttorney::query();
            } elseif (auth()->user()->can('الوكالات الخاصة بي')) {
                $basePowerOfAttorneyQuery = PowerOfAttorney::where('created_by', auth()->id());
            }

            $powerOfAttorney = (clone $basePowerOfAttorneyQuery)->latest()->take(5)->get();

            $charPowerOfAttorney = (clone $basePowerOfAttorneyQuery)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    $statusEnum = PowerOfAttorneyStatus::tryFrom($item->status->value);
                    return [
                        'name'  => $statusEnum ? $statusEnum->label() : 'غير محدد',
                        'count' => $item->count,
                        'color' => $statusEnum ? $statusEnum->color() : 'secondary',
                        'status_value' => $item->status
                    ];
                });

            $totalPowerOfAttorney = (clone $basePowerOfAttorneyQuery)->count();
        }

        /*
        |--------------------------------------------------------------------------
        | lawsuits
        |--------------------------------------------------------------------------
        */
        // تحقق من وجود أي صلاحيات متعلقة بالدعاوى
        $hasLawsuitPermissions = auth()->user()->can('كل الدعاوى') || auth()->user()->can('الدعاوى الخاصة بي');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $lawsuits = collect();
        $chartLawsuits = collect();
        $totalLawsuit = 0;
        $lawsuitStatusCounts = [];
        $categories = collect();
        $chartData = collect();

        $commercialPercentage = 0;
        $civilPercentage = 0;
        $criminalPercentage = 0;




        /*
        |--------------------------------------------------------------------------
        | Sessin
        |--------------------------------------------------------------------------
        | Description of this section.
        */
        $sessions = 0;
        $allsessions = collect();
        $sessionType = collect();

        $activeSessions = 0;
        $pendingSessions = 0;
        $closedSessions = 0;

        $activePercentage = 0;
        $closedPercentage = 0;

        $hasSessionPermissions = auth()->user()->can('كل الجلسات') || auth()->user()->can('الجلسات الخاصة بي');


        if ($hasSessionPermissions) {

            if (auth()->user()->can('كل الجلسات')) {
                $baseSessionQuery = Session::query();
            } elseif (auth()->user()->can('الجلسات الخاصة بي')) {
                $userId         = auth()->user()->id;
                $employeeId     = auth()->user()->employee->id;

                $baseSessionQuery = Session::where(function ($q) use ($userId, $employeeId) {
                    // الجلسات التي أنشأها المستخدم
                    $q->where('created_by', $employeeId)
                        // أو الجلسات التي هو مكلف فيها
                        ->orWhereHas('assignedUsers', function ($subQuery) use ($userId) {
                            $subQuery->where('assigned_to', $userId);
                        });
                });
            }

            $sessions = (clone $baseSessionQuery)->count();

            $allsessions = (clone $baseSessionQuery)->where('session_date', '>=', $today)
                ->where('session_status',  SessionStatus::Active)
                ->orderByProximity()
                ->take(5)
                ->get();

            $sessionType = (clone $baseSessionQuery)->select('session_type', DB::raw('count(*) as count'))
                ->groupBy('session_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' =>  $item->sessionType->name ?? "غير محدد", // قم بتعديل الاسم بناءً على احتياجك
                        'count' => $item->count
                    ];
                });

            // الاحصائيات
            $activeSessions  = (clone $baseSessionQuery)->where('session_status', SessionStatus::Active)->count();
            $closedSessions  = (clone $baseSessionQuery)->where('session_status', SessionStatus::Inactive)->count();
            $pendingSessions = (clone $baseSessionQuery)->where('session_status', SessionStatus::PendingSessionControl)->count();


            $activePercentage = $sessions > 0 ? ($activeSessions / $sessions) * 100 : 0;
            $closedPercentage = $sessions > 0 ? ($closedSessions / $sessions) * 100 : 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        $hasEmployeePermissions = auth()->user()->can('كل الموظفين');

        // متغيرات افتراضية في حالة عدم وجود صلاحيات
        $employees = collect();


        $baseEmployeeQuery = null;

        if ($hasEmployeePermissions) {
            // تحديد الاستعلام الأساسي بناءً على الصلاحيات
            if (auth()->user()->can('كل الموظفين')) {
                $baseEmployeeQuery = Employees::whereHas('user', function ($query) {
                    $query->where('status', 'active');
                });
            }

            $employees = (clone $baseEmployeeQuery)->latest()->take(5)->get();
        }



        /*
        |--------------------------------------------------------------------------
        | Tasks
        |--------------------------------------------------------------------------
        */
        $tasks = Task::whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])->where(function ($query) use ($userId) {
            $query->whereHas('assignedUsers', function ($subQuery) use ($userId) {
                $subQuery->where('users.id', $userId);
            })
                ->orWhereHas('steps', function ($subQuery) use ($userId) {
                    $subQuery
                        ->whereHas('assignedUsers', function ($stepQuery) use ($userId) {
                            $stepQuery->where('users.id', $userId);
                        });
                });
        })
            ->orderBy('created_at', 'desc')
            ->take(5)->get();


        /*
        |--------------------------------------------------------------------------
        | end function
        |--------------------------------------------------------------------------
        */


        // New Version
        return view('content.dashboard.dashboards', compact(
            // New Version
            'colorsUnique',

            // customer
            'customers',
            'charCustomers',
            'totalCustomers',

            // opponents
            'opponents',
            'totalOppents',
            'charOpponents',

            // offers
            'offers',
            'charOffers',
            'totalOffers',

            // contarcts
            'contracts',
            'charContracts',
            'totalContracts',

            // projects
            'projects',
            'charProjects',
            'totalProjects',
            'onTrackPercentage',
            'newPercentage',
            'closePercentage',

            // PowerOfAttorney
            'powerOfAttorney',
            'charPowerOfAttorney',
            'totalPowerOfAttorney',

            // lawsuits
            'lawsuits',
            'chartLawsuits',
            'totalLawsuit',
            'categories',
            'lawsuitStatusCounts',
            'chartData',
            'commercialPercentage',
            'civilPercentage',
            'criminalPercentage',

            // Sessions
            'sessions',
            'allsessions',
            // 'sessions_new',
            'sessionType',
            'activeSessions',
            'closedSessions',
            'pendingSessions',
            'activePercentage',
            'closedPercentage',

            // Employee
            'employees',

            // Tasks
            'tasks',

        ));
    }
}
