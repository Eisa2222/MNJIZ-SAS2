@php
    use Illuminate\Support\Facades\Route;
    $configData = App\Helpers\Helpers::appClasses();
    use App\Helpers\Helpers;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- ! Hide app brand if navbar-full -->
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ route('dashboard') }}" class="app-brand-link">
                <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20])</span>
                <span class="app-brand-text demo menu-text fw-bold">
                    {{ App\Helpers\SettingsHelper::get('logo_text') }}
                </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
            </a>
        </div>
    @endif

    <ul class="menu-inner py-1">
        <!-- لوحة القيادة -->
        <li class="menu-item @if (Route::is('dashboard')) active open @endif">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>لوحة القيادة</div>
            </a>
        </li>

        @php
            // Retrieve favorites from cache, or fetch and cache if not exists
            $userFavorites = Cache::remember('user_favorites_' . Auth::id(), now()->addHours(24), function () {
                return Auth::user() ? \App\Models\Favorite::where('user_id', Auth::id())->get() : collect();
            });
        @endphp

        @if ($userFavorites->count() > 0)
            <li class="menu-item @if (Route::is('favorites.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-device-imac-heart"></i>
                    <div>المفضلة</div>
                </a>
                <ul class="menu-sub">
                    @if ($userFavorites->count() > 0)
                        @foreach ($userFavorites as $favorite)
                            <li class="menu-item">
                                <a href="{{ $favorite->page_url }}" class="menu-link">
                                    <div>{{ $favorite->page_name }}</div>
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li class="menu-item text-muted px-3 py-2">
                            <div>لا توجد صفحات مفضلة</div>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if (auth()->user()->hasRole('Technecal_Support'))
            <li class="menu-item @if (Route::is('supports.*')) active @endif">
                <a href="{{ route('supports.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-file-description"></i>
                    <div>قائمة التذاكر </div> <span class="text-danger samll mx-1" style="font-size: 12px">خاص الدعم
                        الفني</span>
                </a>
            </li>
        @endif

        <!-- التقارير -->
        @canany(['تقرير العملاء', 'تقرير العروض', 'تقرير العقود', 'تقرير الوكالات', 'تقرير الخصوم', 'تقرير المشاريع',
            'تقرير الدعاوى', 'تقرير الجلسات', 'تقرير الموظفين'])

            <li class="menu-item @if (Route::is('reports.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-report"></i>
                    <div>التقارير</div>
                </a>
                <ul class="menu-sub">
                    @can('تقرير العملاء')
                        <li class="menu-item @if (Route::is('reports.customers')) active @endif">
                            <a href="{{ route('reports.customers') }}" class="menu-link">
                                <div>{{ __('تقرير العملاء') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير العروض')
                        <li class="menu-item @if (Route::is('reports.offers')) active @endif">
                            <a href="{{ route('reports.offers') }}" class="menu-link">
                                <div>{{ __('تقرير العروض') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير العقود')
                        <li class="menu-item @if (Route::is('reports.contracts')) active @endif">
                            <a href="{{ route('reports.contracts') }}" class="menu-link">
                                <div>{{ __('تقرير العقود') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير الوكالات')
                        <li class="menu-item @if (Route::is('reports.powerOfAttorney')) active @endif">
                            <a href="{{ route('reports.powerOfAttorney') }}" class="menu-link">
                                <div>{{ __('تقرير الوكالات') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير الخصوم')
                        <li class="menu-item @if (Route::is('reports.opponents')) active @endif">
                            <a href="{{ route('reports.opponents') }}" class="menu-link">
                                <div>{{ __('تقرير الخصوم') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير المشاريع')
                        <li class="menu-item @if (Route::is('reports.projects')) active @endif">
                            <a href="{{ route('reports.projects') }}" class="menu-link">
                                <div>{{ __('تقرير المشاريع') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير الدعاوى')
                        <li class="menu-item @if (Route::is('reports.lawsuits')) active @endif">
                            <a href="{{ route('reports.lawsuits') }}" class="menu-link">
                                <div>{{ __('تقرير الدعاوى') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير الجلسات')
                        <li class="menu-item @if (Route::is('reports.sessions')) active @endif">
                            <a href="{{ route('reports.sessions') }}" class="menu-link">
                                <div>{{ __('تقرير الجلسات') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('تقرير الموظفين')
                        <li class="menu-item @if (Route::is('reports.employees')) active @endif">
                            <a href="{{ route('reports.employees') }}" class="menu-link">
                                <div>{{ __('تقرير الموظفين') }}</div>
                            </a>
                        </li>
                        <li class="menu-item @if (Route::is('reports.attendance.*')) active @endif">
                            <a href="{{ route('reports.attendance.index') }}" class="menu-link">
                                <div>{{ __('تقرير الحضور و الانصراف') }}</div>
                            </a>
                        </li>
                        <li class="menu-item @if (Route::is('reports.leave-reports.index')) active @endif">
                            <a href="{{ route('reports.leave-reports.index') }}" class="menu-link">
                                <div>{{ __('تقرير أرصدة الموظفين') }}</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        <!-- الخدمات  -->
        {{-- prettier-ignore-start --}}
        @canany(['طلبات الإجازات الخاصة بي', 'طلبات المشتريات الخاصة بي', 'الإنتهاكات و العقوبات الخاصة بي', 'تعريف
        بالراتب', 'تثبيت راتب', 'إفادة
        تدريب', 'إخلاء طرف'])
        <li class="menu-header small">
            <span class="menu-header-text"> الخدمات </span>
        </li>

        @canany(['طلبات الإجازات الخاصة بي', 'طلبات المشتريات الخاصة بي', 'الإنتهاكات و العقوبات الخاصة بي','طلبات العهد
        الخاصة بي'])
        <li
            class="menu-item @if (request()->routeIs('account.electronic-services.purchase-requests.*') ||
                                request()->routeIs('account.electronic-services.violations-penalties.*') ||
                                request()->routeIs('account.electronic-services.custody-requests.*') ||
                                request()->routeIs('account.electronic-services.leave-requests.*')) active open @endif">

            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-world"></i>
                <div> الخدمات الإلكترونية </div>
            </a>
            <ul class="menu-sub">
                {{-- @can('طلبات المشتريات الخاصة بي')
                <li class="menu-item @if (Route::is('account.electronic-services.purchase-requests.*')) active @endif">

                    <a href="{{ route('account.electronic-services.purchase-requests.index') }}" class="menu-link">
                        <div>
                            طلبات المشتريات
                        </div>
                    </a>

                </li>
                @endcan --}}

                @can('طلبات الإجازات الخاصة بي')
                <li class="menu-item @if (Route::is('account.electronic-services.leave-requests.*')) active @endif">
                    <a href="{{ route('account.electronic-services.leave-requests.index') }}" class="menu-link">
                        <div>
                            طلبات الإجازات
                        </div>
                    </a>
                </li>
                @endcan

                @can('الإنتهاكات و العقوبات الخاصة بي')
                <li
                    class="menu-item @if (Route::is('account.electronic-services.violations-penalties.*')) active @endif">
                    <a href="{{ route('account.electronic-services.violations-penalties.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-calendar-time"></i>
                        <div>الإنتهاكات و العقوبات </div>
                    </a>
                </li>
                @endcan

                @can('طلبات العهد الخاصة بي')
                <li class="menu-item @if (Route::is('account.electronic-services.custody-requests.*')) active @endif">
                    <a href="{{ route('account.electronic-services.custody-requests.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-calendar-time"></i>
                        <div>طلبات العهد</div>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcan

        @canany(['تعريف بالراتب', 'تثبيت راتب', 'إفادة تدريب', 'إخلاء طرف'])
        <li class="menu-item @if (request()->routeIs('account.self-services.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user"></i>
                <div> الخدمات الذاتية </div>
            </a>

            <ul class="menu-sub">
                @can('تعريف بالراتب')
                <li class="menu-item @if (Route::is('account.self-services.salary-definition.index')) active @endif">
                    <a href="{{ route('account.self-services.salary-definition.index') }}" class="menu-link">
                        <div>تعريف بالراتب</div>
                    </a>
                </li>
                @endcan

                @can('تثبيت راتب')
                <li class="menu-item @if (Route::is('account.self-services.salary-fixation.index')) active @endif">
                    <a href="{{ route('account.self-services.salary-fixation.index') }}" class="menu-link">
                        <div>تثبيت راتب</div>
                    </a>
                </li>
                @endcan


                @if (Auth::user()->employee->license_type?->value == "trainee_lawyer")
                @can('إفادة تدريب')
                <li
                    class="menu-item @if (Route::is('account.self-services.training-certificate.index*')) active @endif">
                    <a href="{{ route('account.self-services.training-certificate.index') }}" class="menu-link">
                        <div>إفادة تدريب</div>
                    </a>
                </li>
                @endcan
                @endif


                @can('إخلاء طرف')
                <li
                    class="menu-item @if (Route::is('account.self-services.clearance-certificate.index')) active @endif">
                    <a href="{{ route('account.self-services.clearance-certificate.index') }}" class="menu-link">
                        <div>إخلاء طرف</div>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcan
        @endcan
        {{-- prettier-ignore-end --}}

        <!-- مركز العمليات -->
        @canany([
            'كل العملاء',
            'العملاء الخاصين بي',
            // العروض
            'كل العروض',
            'العروض الخاصة بي',
            // العقود
            'كل العقود',
            'العقود الخاصة بي',
            'كل العقود الإستثنائية',
            'العقود الإستثنائية الخاصة بي',
            ])
            <li class="menu-header small">
                <span class="menu-header-text">مركز العمليات</span>
            </li>

            <li class="menu-item @if (
                (request()->routeIs('operations-center.customers*') &&
                    !request()->routeIs('operations-center.customers.trashed')) ||
                    (request()->routeIs('operations-center.offers.*') &&
                        !request()->routeIs('operations-center.offers.trashed') &&
                        !request()->routeIs('operations-center.offers.offer-study.*')) ||
                    (request()->routeIs('operations-center.contracts.*') &&
                        !request()->routeIs('operations-center.contracts.trashed')) ||
                    request()->routeIs('operations-center.exceptional-contracts.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-briefcase"></i>
                    <div>مركز العمليات</div>
                </a>
                <ul class="menu-sub">
                    @canany(['كل العملاء', 'العملاء الخاصين بي'])
                        <!-- العملاء -->
                        <li class="menu-item @if (request()->routeIs('operations-center.customers.*') && !request()->routeIs('operations-center.customers.trashed')) active @endif">
                            <a href="{{ route('operations-center.customers.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-users-group"></i>
                                <div> العملاء</div>
                            </a>
                        </li>
                    @endcan
                    @canany(['كل العروض', 'العروض الخاصة بي'])
                        <!-- العروض -->
                        <li class="menu-item @if (request()->routeIs('operations-center.offers.*') &&
                                !request()->routeIs('operations-center.offers.trashed') &&
                                !request()->routeIs('operations-center.offers.offer-study.*')) active @endif">
                            <a href="{{ route('operations-center.offers.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-tag"></i>
                                <div> العروض</div>
                            </a>
                        </li>
                    @endcan
                    @canany(['كل العقود', 'العقود الخاصة بي'])
                        <!-- العقود -->
                        <li class="menu-item @if (request()->routeIs('operations-center.contracts.*') && !request()->routeIs('operations-center.contracts.trashed')) active @endif">
                            <a href="{{ route('operations-center.contracts.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-file-text"></i>
                                <div> العقود</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['كل العقود الإستثنائية', 'العقود الإستثنائية الخاصة بي'])
                        <!-- العقود الإستثنائية -->
                        <li class="menu-item @if (request()->routeIs('operations-center.exceptional-contracts.*')) active @endif">
                            <a href="{{ route('operations-center.exceptional-contracts.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-file-text"></i>
                                <div> العقود الإستثنائية</div>
                            </a>
                        </li>
                    @endcan

                </ul>
            </li>
        @endcan

        <!-- مكتب ادارة المشاريع -->
        @canany(['كل المشاريع', 'المشاريع الخاصة بي', 'الإعتماد الفني للمشاريع'])
            <li class="menu-header small">
                <span class="menu-header-text"> مكتب ادارة المشاريع </span>
            </li>

            <!-- المشاريع -->
            <li class="menu-item @if (request()->routeIs('projects.*') && !request()->routeIs('projects.trashed')) active @endif">
                <a href="{{ route('projects.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-briefcase"></i>
                    <div> المشاريع</div>
                </a>
            </li>

        @endcan

        <!-- الشؤون القانونية -->
        @canany([
            'كل الخصوم',
            'الخصوم الخاصين بي',
            // الوكالات
            'كل الوكالات',
            'الوكالات الخاصة بي',
            // الدعاوى
            'كل الدعاوى',
            'الدعاوى الخاصة بي',

            'كل الجلسات',
            'الجلسات الخاصة بي',
            ])
            <li class="menu-header small">
                <span class="menu-header-text"> الشؤون القانونية</span>
            </li>
            <li class="menu-item  @if (
                (request()->routeIs('legal-affairs.lawsuits.*') && !request()->routeIs('legal-affairs.lawsuits.trashed')) ||
                    request()->routeIs('legal-affairs.power-attorney.*') ||
                    request()->routeIs('legal-affairs.sessions.*') ||
                    request()->routeIs('legal-affairs.opponents.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-scale"></i>
                    <div>الشؤون القانونية</div>
                </a>
                <ul class="menu-sub">

                    @canany(['كل الخصوم', 'الخصوم الخاصين بي'])
                        <!-- الخصوم -->
                        <li class="menu-item @if (request()->routeIs('legal-affairs.opponents.*')) active @endif">
                            <a href="{{ route('legal-affairs.opponents.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-gavel"></i>
                                <div> الخصوم</div>
                            </a>
                        </li>
                    @endcan
                    @canany(['كل الوكالات', 'الوكالات الخاصة بي'])
                        <!-- الوكالات -->
                        <li class="menu-item @if (request()->routeIs('legal-affairs.power-attorney.*')) active @endif">
                            <a href="{{ route('legal-affairs.power-attorney.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-user-plus"></i>
                                <div> الوكالات</div>
                            </a>
                        </li>
                    @endcan
                    <!-- الدعاوى -->
                    @canany(['كل الدعاوى', 'الدعاوى الخاصة بي'])
                        <li class="menu-item @if (request()->routeIs('legal-affairs.lawsuits.*') && !request()->routeIs('legal-affairs.lawsuits.trashed')) active @endif">
                            <a href="{{ route('legal-affairs.lawsuits.index') }}" class="menu-link">
                                <i class="menu-icon ti ti-scale"></i>

                                <div> الدعاوى</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['كل الجلسات', 'الجلسات الخاصة بي'])
                        <li class="menu-item @if (request()->routeIs('legal-affairs.sessions.*') && !request()->routeIs('legal-affairs.sessions.trashed')) active @endif">
                            <a href="{{ route('legal-affairs.sessions.index') }}" class="menu-link">
                                <i class="menu-icon ti ti-scale"></i>

                                <div> الجلسات</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        <!-- الموارد البشرية-->
        @canany([
            'اللوائح و السياسات',
            'كل الموظفين',
            'الحضور والانصراف',
            'طلبات الإجازات',
            'إدارة المرتبات',
            'إدارة
            السلف',
            'إدارة المكافأت',
            'إدارة الخصومات',
            'أرصدة الإجازات',
            'إدارة الانتهاكات والعقوبات',
            'إدارة الاصول',
            'طلبات العهد',
            'طلبات تحديث البيانات',
            'تنبيهات التجديدات',
            ])
            <li class="menu-header small">
                <span class="menu-header-text">الموارد البشرية</span>
            </li>
            <li class="menu-item @if (
                (Route::is('hr.employees.*') && !request()->routeIs('hr.employees.trashed')) ||
                    Route::is('hr.company-policy.*') ||
                    Route::is('attendances.*') ||
                    Route::is('hr.leave-requests.index') ||
                    Route::is('hr.payrolls.wps.*') ||
                    Route::is('hr.violations-penalties.*') ||
                    Route::is('hr.advances.*') ||
                    Route::is('hr.rewards.*') ||
                    Route::is('hr.custody.items.*') ||
                    Route::is('hr.custody.requests.*') ||
                    Route::is('hr.modification-requests.*') ||
                    Route::is('hr.leave-balances.*') ||
                    Route::is('hr.alerts.*') ||
                    Route::is('hr.deductions.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-user"></i>
                    <div>الموارد البشرية</div>
                </a>
                <ul class="menu-sub">

                    @canany(['اللوائح و السياسات'])
                        <li class="menu-item @if (Route::is('hr.company-policy.*')) active @endif">
                            <a href="{{ route('hr.company-policy.index') }}" class="menu-link">
                                <div>اللوائح و السياسات</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['كل الموظفين'])
                        <!--   إدارة الموظفين -->
                        <li class="menu-item @if (Route::is('hr.employees.*') &&
                                !request()->routeIs('hr.employees.trashed') &&
                                !request()->routeIs('hr.employees.archive.*')) active @endif">
                            <a href="{{ route('hr.employees.index') }}" class="menu-link">
                                <div>الموظفين</div>
                            </a>
                        </li>

                        <li class="menu-item @if (Route::is('hr.employees.archive.*') && !request()->routeIs('hr.employees.trashed')) active @endif">
                            <a href="{{ route('hr.employees.archive.index') }}" class="menu-link">
                                <div> الموظفين المؤرشفين</div>
                            </a>
                        </li>
                    @endcan

                    @can('الحضور والانصراف')
                        <!-- الحضور والإنصراف -->
                        <li class="menu-item @if (Route::is('attendances.*')) active @endif">
                            <a href="{{ route('attendances.index') }}" class="menu-link">
                                <div>الحضور والإنصراف</div>
                            </a>
                        </li>
                    @endcan

                    @can('طلبات الإجازات')
                        <li class="menu-item @if (Route::is('hr.leave-requests.index')) active @endif">
                            <a href="{{ route('hr.leave-requests.index') }}" class="menu-link">
                                <div> طلبات الإجازات </div>
                            </a>
                        </li>
                    @endcan

                    @can('إدارة المرتبات')
                        <!-- إدارة المرتبات -->
                        <li class="menu-item @if (Route::is('hr.payrolls.wps.*')) active @endif">
                            <a href="{{ route('hr.payrolls.wps.index') }}" class="menu-link">
                                <div>مسيرات الرواتب</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['إدارة السلف'])
                        <li class="menu-item @if (Route::is('hr.advances.*')) active @endif">
                            <a href="{{ route('hr.advances.index') }}" class="menu-link">
                                <div> السلف</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['إدارة المكافأت'])
                        <li class="menu-item @if (Route::is('hr.rewards.*')) active @endif">
                            <a href="{{ route('hr.rewards.index') }}" class="menu-link">
                                <div> المكافأت</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['إدارة الخصومات'])
                        <li class="menu-item @if (Route::is('hr.deductions.*')) active @endif">
                            <a href="{{ route('hr.deductions.index') }}" class="menu-link">
                                <div> الخصومات</div>
                            </a>
                        </li>
                    @endcan

                    @can('أرصدة الإجازات')
                        <!-- إدارة المرتبات -->
                        <li class="menu-item @if (Route::is('hr.leave-balances.*')) active @endif">
                            <a href="{{ route('hr.leave-balances.index') }}" class="menu-link">
                                <div>أرصدة الإجازات</div>
                            </a>
                        </li>
                    @endcan

                    @can('إدارة الانتهاكات والعقوبات')
                        <!--  إدارة الانتهاكات والعقوبات -->
                        <li class="menu-item @if (Route::is('hr.violations-penalties.*')) active @endif">
                            <a href="{{ route('hr.violations-penalties.index') }}" class="menu-link">
                                <div> إدارة الانتهاكات والعقوبات</div>
                            </a>
                        </li>
                    @endcan

                    @can('إدارة الاصول')
                        <li class="menu-item @if (Route::is('hr.custody.items.*')) active @endif">
                            <a href="{{ route('hr.custody.items.index') }}" class="menu-link">
                                <div> إدارة الاصول</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['طلبات العهد'])
                        <li class="menu-item @if (Route::is('hr.custody.requests.*')) active @endif">
                            <a href="{{ route('hr.custody.requests.index') }}" class="menu-link">
                                <div> طلبات العهد</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['طلبات تحديث البيانات'])
                        <li class="menu-item @if (Route::is('hr.modification-requests.*')) active @endif">
                            <a href="{{ route('hr.modification-requests.index') }}" class="menu-link">
                                <div> طلبات تحديث البيانات</div>
                            </a>
                        </li>
                    @endcan

                    @canany(['تنبيهات التجديدات'])
                        <li class="menu-item @if (Route::is('hr.alerts.*')) active @endif">
                            <a href="{{ route('hr.alerts.index') }}" class="menu-link">
                                <div>تنبيهات التجديدات</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @canany(['إدارة المحتوى', 'إدارة الحملات'])
            <li class="menu-header small">
                <span class="menu-header-text">التسويق</span>
            </li>
            <li class="menu-item @if (Route::is('marketing.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-trending-up"></i>
                    <div>التسويق</div>
                </a>
                <ul class="menu-sub">

                    @can('إدارة المحتوى')
                        <li class="menu-item @if (Route::is('marketing.content-management.*')) active @endif">
                            <a href="{{ route('marketing.content-management.index') }}" class="menu-link">
                                <div> إدارة المحتوى</div>
                            </a>
                        </li>
                    @endcan

                    @can('إدارة الحملات')
                        <li class="menu-item @if (Route::is('marketing.campaign-management.*')) active @endif">
                            <a href="{{ route('marketing.campaign-management.index') }}" class="menu-link">
                                <div> إدارة الحملات </div>
                            </a>
                        </li>
                    @endcan

                </ul>
            </li>
        @endcan

        @can('دفعات العقود')
            <li class="menu-header small">
                <span class="menu-header-text">الإدارة المالية</span>
            </li>
            <li class="menu-item @if (Route::is('financial.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-brand-cashapp"></i>
                    <div>الإدارة المالية</div>
                </a>
                <ul class="menu-sub">

                    <li class="menu-item @if (Route::is('financial.contract-payments.*')) active @endif">
                        <a href="{{ route('financial.contract-payments.index') }}" class="menu-link">
                            <div> دفعات العقود </div>
                        </a>
                    </li>

                </ul>
            </li>
        @endcan

        <!-- Qoyod -->
        {{-- @canany(['صلاحيات منصة قيود'])
            <li class="menu-header small">
                <span class="menu-header-text">قيود</span>
            </li>
            <li class="menu-item @if (route::is('qoyod.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-adjustments"></i>
                    <div> قيود</div>
                </a>
                <ul class="menu-sub">

                    <!--    الحسابات -->
                    <li class="menu-item @if (Route::is('qoyod.accounts.*')) active @endif">
                        <a href="{{ route('qoyod.accounts.index') }}" class="menu-link">
                            <div> الحسابات</div>
                        </a>
                    </li>

                    <!--    أصناف المنتجات -->
                    <li class="menu-item @if (Route::is('qoyod.categories.*')) active @endif">
                        <a href="{{ route('qoyod.categories.index') }}" class="menu-link">
                            <div> أصناف المنتجات</div>
                        </a>
                    </li>

                    <!--    وحدات قياس المنتجات -->
                    <li class="menu-item @if (Route::is('qoyod.product-unit-types.*')) active @endif">
                        <a href="{{ route('qoyod.product-unit-types.index') }}" class="menu-link">
                            <div> وحدات قياس المنتجات</div>
                        </a>
                    </li>

                    <!--    إدارة المنتجات -->
                    <li class="menu-item @if (Route::is('qoyod.products.*')) active @endif">
                        <a href="{{ route('qoyod.products.index') }}" class="menu-link">
                            <div> إدارة المنتجات</div>
                        </a>
                    </li>

                    <!--    المواقع -->
                    <li class="menu-item @if (Route::is('qoyod.inventories.*')) active @endif">
                        <a href="{{ route('qoyod.inventories.index') }}" class="menu-link">
                            <div> المواقع</div>
                        </a>
                    </li>

                    <!--    الموردين -->
                    <li class="menu-item @if (Route::is('qoyod.vendors.*')) active @endif">
                        <a href="{{ route('qoyod.vendors.index') }}" class="menu-link">
                            <div> الموردين</div>
                        </a>
                    </li>

                    <!--     أوامر الشراء -->
                    <li class="menu-item @if (Route::is('qoyod.purchase-orders.*')) active @endif">
                        <a href="{{ route('qoyod.purchase-orders.index') }}" class="menu-link">
                            <div> أوامر الشراء</div>
                        </a>
                    </li>

                    <!--    فواتير المشتريات -->
                    <li class="menu-item @if (Route::is('qoyod.bills.*')) active @endif">
                        <a href="{{ route('qoyod.bills.index') }}" class="menu-link">
                            <div> فواتير المشتريات</div>
                        </a>
                    </li>

                    <!--     مدفوعات فواتير المشتريات -->
                    <li class="menu-item @if (Route::is('qoyod.bill-payments.*')) active @endif">
                        <a href="{{ route('qoyod.bill-payments.index') }}" class="menu-link">
                            <div> مدفوعات فواتير المشتريات</div>
                        </a>
                    </li>

                    <!--    الإشعارات المدينة  -->
                    <li class="menu-item @if (Route::is('qoyod.debit-notes.*')) active @endif">
                        <a href="{{ route('qoyod.debit-notes.index') }}" class="menu-link">
                            <div> الإشعارات المدينة </div>
                        </a>
                    </li>

                    <!--    العملاء -->
                    <li class="menu-item @if (Route::is('qoyod.customers.*')) active @endif">
                        <a href="{{ route('qoyod.customers.index') }}" class="menu-link">
                            <div> العملاء</div>
                        </a>
                    </li>

                    <!--    عروض الأسعار  -->
                    <li class="menu-item @if (Route::is('qoyod.quotes.*')) active @endif">
                        <a href="{{ route('qoyod.quotes.index') }}" class="menu-link">
                            <div> عروض الأسعار </div>
                        </a>
                    </li>

                    <!--    فواتير المبيعات  -->
                    <li class="menu-item @if (Route::is('qoyod.invoices.*')) active @endif">
                        <a href="{{ route('qoyod.invoices.index') }}" class="menu-link">
                            <div> فواتير المبيعات </div>
                        </a>
                    </li>

                    <!--    مدفوعات فواتير المبيعات  -->
                    <li class="menu-item @if (Route::is('qoyod.invoice-payments.*')) active @endif">
                        <a href="{{ route('qoyod.invoice-payments.index') }}" class="menu-link">
                            <div> مدفوعات فواتير المبيعات </div>
                        </a>
                    </li>

                    <!--    الإشعارات الدائنة  -->
                    <li class="menu-item @if (Route::is('qoyod.credit-notes.*')) active @endif">
                        <a href="{{ route('qoyod.credit-notes.index') }}" class="menu-link">
                            <div> الإشعارات الدائنة </div>
                        </a>
                    </li>

                    <!--    الإيصالات  -->
                    <li class="menu-item @if (Route::is('qoyod.receipts.*')) active @endif">
                        <a href="{{ route('qoyod.receipts.index') }}" class="menu-link">
                            <div> الإيصالات </div>
                        </a>
                    </li>

                    <!--    قيود اليومية  -->
                    <li class="menu-item @if (Route::is('qoyod.journal-entries.*')) active @endif">
                        <a href="{{ route('qoyod.journal-entries.index') }}" class="menu-link">
                            <div> قيود اليومية </div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan --}}

        <!-- مركز تنظيم الأعمال -->
        @canany([
            'كل المهام',
            'المهام الخاصة بي',
            'مهام مسندة للأخرين',
            'ارشيف المهام',

            // المساعد الذكي
            'المساعد الذكي',

            // التقويم
            'التقويم',
            ])
            <li class="menu-header small">
                <span class="menu-header-text"> مركز تنظيم الأعمال </span>
            </li>

            @canany(['كل المهام', 'المهام الخاصة بي', 'مهام مسندة للأخرين', 'ارشيف المهام'])
                <!-- إدارة المهام -->
                <li class="menu-item @if (Route::is('organization-center.tasks.*')) active open @endif ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-clipboard-check"></i>
                        <div>إدارة المهام</div>
                    </a>
                    <ul class="menu-sub">
                        @can('كل المهام')
                            <li class="menu-item @if (Route::is('organization-center.tasks.index')) active @endif">
                                <a href="{{ route('organization-center.tasks.index') }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-users"></i>
                                    <div>كل المهام </div>
                                </a>
                            </li>
                        @endcan
                        @can('المهام الخاصة بي')
                            <li class="menu-item @if (Route::is('organization-center.tasks.my-tasks')) active @endif">
                                <a href="{{ route('organization-center.tasks.my-tasks') }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-users"></i>
                                    <div>مهامي </div>
                                </a>
                            </li>
                        @endcan
                        @can('مهام مسندة للأخرين')
                            <li class="menu-item @if (Route::is('organization-center.tasks.assigned-tasks')) active @endif">
                                <a href="{{ route('organization-center.tasks.assigned-tasks') }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-users"></i>
                                    <div>مهام مسندة للاخرين </div>
                                </a>
                            </li>
                        @endcan
                        @can('ارشيف المهام')
                            <li class="menu-item @if (Route::is('organization-center.tasks.trashed')) active @endif">
                                <a href="{{ route('organization-center.tasks.trashed') }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-users"></i>
                                    <div>ارشيف المهام</div>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @if (\App\Helpers\Helpers::hasAnyApprovalAssignment())
                <li class="menu-item @if (Route::is('approval-workflow.*')) active open @endif">
                    <a href="{{ route('approval-workflow.unified.index') }}" class="menu-link ">
                        <i class="menu-icon tf-icons ti ti-box-multiple"></i>
                        <div>طلبات الإعتماد</div>
                    </a>
                </li>
            @endif

            {{-- @if (\App\Helpers\Helpers::hasAnyApprovalAssignment())
        <li class="menu-item @if (Route::is('approval-workflow.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-box-multiple"></i>
                <div>طلبات الإعتماد</div>
            </a>

            <ul class="menu-sub">
                @if (\App\Helpers\Helpers::isAssignedToApprovalType('offer'))
                <li class="menu-item @if (Route::is('approval-workflow.offers.*')) active @endif">
                    <a href="{{ route('approval-workflow.offers.index') }}" class="menu-link">
                        <div>إعتماد العروض</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('contract'))
                <li class="menu-item @if (Route::is('approval-workflow.contracts.*')) active @endif">
                    <a href="{{ route('approval-workflow.contracts.index') }}" class="menu-link">
                        <div>إعتماد العقود</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('leave'))
                <li class="menu-item @if (Route::is('approval-workflow.leave-requests.*')) active @endif">
                    <a href="{{ route('approval-workflow.leave-requests.index') }}" class="menu-link">
                        <div>إعتماد الإجازات</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('wps'))
                <li class="menu-item @if (Route::is('approval-workflow.wps-payrolls.*')) active @endif">
                    <a href="{{ route('approval-workflow.wps-payrolls.index') }}" class="menu-link">
                        <div>إعتماد مسيرات الرواتب</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('clearance_certificate'))
                <li class="menu-item @if (Route::is('approval-workflow.clearance-certificates.*')) active @endif">
                    <a href="{{ route('approval-workflow.clearance-certificates.index') }}" class="menu-link">
                        <div>إعتماد إخلاء الطرف</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('advance'))
                <li class="menu-item @if (Route::is('approval-workflow.advances.*')) active @endif">
                    <a href="{{ route('approval-workflow.advances.index') }}" class="menu-link">
                        <div>إعتماد السلف</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('reward'))
                <li class="menu-item @if (Route::is('approval-workflow.rewards.*')) active @endif">
                    <a href="{{ route('approval-workflow.rewards.index') }}" class="menu-link">
                        <div>إعتماد المكافآت</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('custody'))
                <li class="menu-item @if (Route::is('approval-workflow.custodies.*')) active @endif">
                    <a href="{{ route('approval-workflow.custodies.index') }}" class="menu-link">
                        <div>إعتماد العهد</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('deduction'))
                <li class="menu-item @if (Route::is('approval-workflow.deductions.*')) active @endif">
                    <a href="{{ route('approval-workflow.deductions.index') }}" class="menu-link">
                        <div>إعتماد الخصومات</div>
                    </a>
                </li>
                @endif

                @if (\App\Helpers\Helpers::isAssignedToApprovalType('content'))
                <li class="menu-item @if (Route::is('approval-workflow.content.*')) active @endif">
                    <a href="{{ route('approval-workflow.content.index') }}" class="menu-link">
                        <div>إعتماد المحتوى</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif --}}

            {{-- @can('المساعد الذكي')
                <li class="menu-item @if (Route::is('legal-ai.*')) active open @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-cpu"></i>
                        <div>{{ __('المساعد القانوني') }}</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if (Route::is('legal-ai.chat.dashboard')) active @endif">
                            <a href="{{ route('legal-ai.chat.dashboard') }}" class="menu-link">
                                <div>{{ __('المساعد الذكي') }}</div>
                            </a>
                        </li>
                        <li class="menu-item @if (Route::is('legal-ai.summarize.dashboard')) active @endif">
                            <a href="{{ route('legal-ai.summarize.dashboard') }}" class="menu-link">
                                <div>{{ __('تلخيص القضايا') }}</div>
                            </a>
                        </li>
                        <li class="menu-item @if (Route::is('legal-ai.drafting.dashboard')) active @endif">
                            <a href="{{ route('legal-ai.drafting.dashboard') }}" class="menu-link">
                                <div>{{ __('صياغة المذكرات') }}</div>
                            </a>
                        </li>
                        <li class="menu-item @if (Route::is('legal-ai.precedents.dashboard')) active @endif">
                            <a href="{{ route('legal-ai.precedents.dashboard') }}" class="menu-link">
                                <div>{{ __('السوابق القضائية') }}</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan --}}

            @can('التقويم')
                <!-- Calendar -->
                <li class="menu-item @if (Route::is('calendar.*')) active @endif">
                    <a href="{{ route('calendar.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-calendar"></i>
                        <div>{{ __('التقويم') }}</div>
                    </a>
                </li>
            @endcan

        @endcan

        @canany([
            'الدردشة',
            'البريد الإلكتروني',
            'كل الإجتماعات',
            'الإجتماعات الخاصة بي',
            'إضافة إجتماع',
            'حذف إجتماع',
            'تعديل
            إجتماع',
            'الدعم الفني',
            ])

            <li class="menu-header small">
                <span class="menu-header-text"> التفاعل الجماعي</span>
            </li>

            @can('الدردشة')
                <li class="menu-item @if (Route::is('chat.*')) active @endif">
                    <a href="{{ route('chat.index') }}" class="menu-link d-flex align-items-center justify-content-between">
                        <span class="d-flex align-items-center">
                            <i class="menu-icon tf-icons ti ti-messages"></i>
                            <div>{{ __('الدردشة') }}</div>
                        </span>
                        @if (App\Helpers\Helpers::hasUnreadMessages())
                            @php $unreadCount = App\Helpers\Helpers::getUnreadMessagesCount(); @endphp
                            <span class="badge rounded-pill bg-primary ms-2"
                                style="font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; padding: 0;"
                                id="sidebar-chat-notification">
                                {{ App\Helpers\Helpers::formatMessageCount($unreadCount) }}
                            </span>
                        @endif
                    </a>
                </li>
            @endcan

            <!-- البريد الإلكتروني -->
            @can('البريد الإلكتروني')
                <li class="menu-item @if (Route::is('emails.*')) active @endif">
                    <a href="{{ route('emails.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-mail"></i>
                        <div>{{ __('البريد الإلكتروني') }}</div>
                    </a>
                </li>
            @endcan

            <!-- Teams -->
            @canany(['كل الإجتماعات', 'الإجتماعات الخاصة بي'])
                <li class="menu-item @if (Route::is('microsoft.teams.*')) active @endif">
                    <a href="{{ route('microsoft.teams.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-video"></i> <!-- استبدل 'ti-video' بالأيقونة التي تختارها -->
                        <div>{{ __('إدارة الإجتماعات') }}</div>
                    </a>
                </li>
            @endcan

            @can('الدعم الفني')
                <li class="menu-item @if (Route::is('userSuppports')) active @endif">
                    <a href="{{ route('userSuppports') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-headset"></i>
                        <div>{{ __('الدعم الفني') }}</div>
                    </a>
                </li>
            @endcan

        @endcan

        @canany([
            'إدارة الملفات',
            'ارشيف العملاء',
            'ارشيف العروض',
            'ارشيف العقود',
            'ارشيف الدعاوى',
            'ارشيف الجلسات',
            'ارشيف
            المشاريع',
            'ارشيف الموظفين',
            ])

            @can('إدارة الملفات')
                <li class="menu-header small">
                    <span class="menu-header-text"> إدارة الملفات</span>
                </li>
                <li class="menu-item @if (Route::is('onedrive.*')) active @endif">
                    <a href="{{ route('onedrive.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-file"></i>
                        <div>إدارة الملفات</div>
                    </a>
                </li>
            @endcan

            {{-- @canany([
    'ارشيف العملاء',
    'ارشيف العروض',
    'ارشيف العقود',
    'ارشيف الدعاوى',
    'ارشيف الجلسات',
    'ارشيف المشاريع',
    'ارشيف
        الموظفين',
])
        <li class="menu-item @if (Route::is('operations-center.customers.trashed') || Route::is('offers.trashed') || Route::is('operations-center.contracts.trashed') || Route::is('legal-affairs.lawsuits.trashed') || Route::is('legal-affairs.sessions.trashed') || Route::is('projects.trashed') || Route::is('tasks.trashed') || Route::is('hr.employees.trashed')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-archive"></i>
                <div>الأرشيف</div>
            </a>
            <ul class="menu-sub">
                @can('ارشيف العملاء')
                <li class="menu-item @if (Route::is('operations-center.customers.trashed')) active @endif">
                    <a href="{{ route('operations-center.customers.trashed') }}" class="menu-link">
                        <div>{{ __(' العملاء') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف العروض')
                <li class="menu-item @if (Route::is('offers.trashed')) active @endif">
                    <a href="{{ route('operations-center.offers.trashed') }}" class="menu-link">
                        <div>{{ __('العروض') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف العقود')
                <li class="menu-item @if (Route::is('operations-center.contracts.trashed')) active @endif">
                    <a href="{{ route('operations-center.contracts.trashed') }}" class="menu-link">
                        <div>{{ __(' العقود') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف الدعاوى')
                <li class="menu-item @if (Route::is('legal-affairs.lawsuits.trashed')) active @endif">
                    <a href="{{ route('legal-affairs.lawsuits.trashed') }}" class="menu-link">
                        <div>{{ __(' الدعاوى') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف الجلسات')
                <li class="menu-item @if (Route::is('legal-affairs.sessions.trashed')) active @endif">
                    <a href="{{ route('legal-affairs.sessions.trashed') }}" class="menu-link">
                        <div>{{ __(' الجلسات') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف المشاريع')
                <li class="menu-item @if (Route::is('projects.trashed')) active @endif">
                    <a href="{{ route('projects.trashed') }}" class="menu-link">
                        <div>{{ __(' المشاريع') }}</div>
                    </a>
                </li>
                @endcan
                @can('ارشيف الموظفين')
                <li class="menu-item @if (Route::is('hr.employees.trashed')) active @endif">
                    <a href="{{ route('hr.employees.trashed') }}" class="menu-link">
                        <div>{{ __(' الموظفين') }}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan --}}
        @endcan

        {{-- @canany(['طلباتي', 'طلبات المشتريات', 'المشتريات'])
            <li class="menu-header small">
                <span class="menu-header-text"> مركز المشتريات </span>
            </li>

            <li class="menu-item @if (Route::is('purchasing-center.purchase-requests.index*') || Route::is('purchasing-center.purchase-requests.invoices.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
                    <div>المشتريات</div>
                </a>
                <ul class="menu-sub">

                    @can('طلبات المشتريات')
                        <li class="menu-item @if (Route::is('purchasing-center.purchase-requests.index*')) active @endif">
                            <a href="{{ route('purchasing-center.purchase-requests.index') }}" class="menu-link">
                                <div>طلبات المشتريات</div>
                            </a>
                        </li>
                    @endcan

                    @can('المشتريات')
                        <!-- المشتريات -->
                        <li class="menu-item @if (Route::is('purchasing-center.purchase-requests.invoices.*')) active @endif">
                            <a href="{{ route('purchasing-center.purchase-requests.invoices.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-chart-line"></i>
                                <div>المشتريات</div>
                            </a>
                        </li>
                    @endcan

                </ul>
            </li>
        @endcan --}}

        {{-- @canany(['إدارة القاعات'])
            <li class="menu-header small">
                <span class="menu-header-text">قاعات الإجتماعات</span>
            </li>

            <li class="menu-item @if (Route::is('meeting-rooms.*')) active @endif">
                <a href="{{ route('meeting-rooms.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-calendar-time"></i>
                    <div>قاعات الإجتماعات</div>
                </a>
            </li>
        @endcan --}}

        @canany(['إدارة الإستبيانات'])
            <li class="menu-header small">
                <span class="menu-header-text">إدارة الإستبيانات</span>
            </li>

            <li class="menu-item @if (Route::is('surveys.*')) active @endif">
                <a href="{{ route('surveys.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-device-imac-search"></i>
                    <div>إدارة الإستبيانات</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>
