@canany([
    'إعدادات النظام',
    'النماذج',
    'الصلاحيات الوظيفية',
    'إدارة الإعتمادات',
    'سجل النشاطات',
    'سجل
    الرسائل',
    'اقسام المشاريع والقضايا',
    'حالات العقود',
    'المنتجات',
    'حالات العميل',
    'قنوات التسويق',
    'القطاعات',
    'المدن',
    'قائمة الدول',
    'مواقع التواصل',
    'قائمة البنوك',
    'حالات الموارد البشرية',
    'تصنيف الموارد البشرية',
    'تصنيف المخالفات',
    'أنواع المخالفات',
    'أنواع
    الإجازات',
    'تصنيفات الاصول',
    'مرجعية الاصول',
    'أنواع الحملات والمحتوى',
    'أنماط النشر',
    'أهداف الحملات والمحتوى',
    'أقسام الحملات',
    'الجمهور
    المستهدف',
    'المحكمة',
    'درجة الجهة',
    'التصنيفات الرئيسية',
    'التصنيفات الفرعية',
    'أنواع الدعاوى',
    'أنواع
    الجلسات',
    'أنواع الاحكام',
    'تصنيف المشتريات',
    ])

    <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
        <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill waves-effect"
            href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
            <i class="icon-base ti ti-settings tabler-layout-grid-add icon-22px text-heading"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
            <div class="d-flex justify-content-between dropdown-menu-header border-bottom">
                <div class="dropdown-header d-flex align-items-center py-3">
                    <h6 class="mb-0 me-auto">الإعدادات العامة </h6>
                </div>
                <a href="{{ route('general-settings.index') }}" class="my-auto mx-5 small">
                    عرض الكل
                </a>
            </div>
            <div class="dropdown-shortcuts-list scrollable-container ps">
                <div class="row row-bordered overflow-visible g-0">

                    @canany([
                        'إعدادات النظام',
                        'النماذج',
                        'الصلاحيات الوظيفية',
                        'إدارة الإعتمادات',
                        'سجل النشاطات',
                        'سجل
                        الرسائل',
                        ])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-primary">
                                <i class="icon-base ti ti-settings icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.system') }}" class="stretched-link">إعدادات النظام</a>
                            <small>ضبط الإعدادات العامة وسلوك النظام</small>
                        </div>
                    @endcanany


                    @canany(['اقسام المشاريع والقضايا', 'حالات العقود', 'المنتجات', 'حالات العميل', 'قنوات التسويق',
                        'القطاعات', 'المدن', 'قائمة الدول', 'مواقع التواصل', 'قائمة البنوك'])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-info">
                                <i class="icon-base ti ti-building-warehouse icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.operations') }}" class="stretched-link">مركز
                                العمليات</a>
                            <small>إعدادات المشاريع والعقود</small>
                        </div>
                    @endcanany


                    @canany([
                        'حالات الموارد البشرية',
                        'تصنيف الموارد البشرية',
                        'تصنيف المخالفات',
                        'أنواع المخالفات',
                        'أنواع
                        الإجازات',
                        'تصنيفات الاصول',
                        'مرجعية الاصول',
                        ])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-success">
                                <i class="icon-base ti ti-users icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.hr') }}" class="stretched-link">الموارد البشرية</a>
                            <small>إعداد حالات الموظفين والتصنيفات</small>
                        </div>
                    @endcanany

                    @canany([
                        'أنواع الحملات والمحتوى',
                        'أنماط النشر',
                        'أهداف الحملات والمحتوى',
                        'أقسام الحملات',
                        'الجمهور
                        المستهدف',
                        ])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-warning">
                                <i class="icon-base ti ti-speakerphone icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.marketing') }}" class="stretched-link">إعدادات
                                التسويق</a>
                            <small>تصنيفات الحملات والمحتوى</small>
                        </div>
                    @endcanany


                    @canany([
                        'المحكمة',
                        'درجة الجهة',
                        'التصنيفات الرئيسية',
                        'التصنيفات الفرعية',
                        'أنواع الدعاوى',
                        'أنواع
                        الجلسات',
                        'أنواع الاحكام',
                        ])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-danger">
                                <i class="icon-base ti ti-scale icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.legal') }}" class="stretched-link">الشؤون
                                القانونية</a>
                            <small>أنواع الجلسات والدعاوى</small>
                        </div>
                    @endcanany

                    @canany(['تصنيف المشتريات'])
                        <div class="dropdown-shortcuts-item col-md-6">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3 bg-label-secondary">
                                <i class="icon-base ti ti-shopping-cart icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('general-settings.purchasing') }}" class="stretched-link">المشتريات</a>
                            <small>إعدادات الموردين والتصنيفات</small>
                        </div>
                    @endcanany

                </div>
            </div>

        </div>
    </li>
@endcanany
