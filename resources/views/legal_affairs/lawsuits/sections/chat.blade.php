@extends('layouts.layoutMaster')

@section('title', 'الدردشة - التطبيقات')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.scss')
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/app-chat.js')
@endsection

@section('content')
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            <!-- Sidebar Left -->
            <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
                <div
                    class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-6 pt-12">
                    <div class="avatar avatar-xl avatar-online chat-sidebar-avatar">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية" class="rounded-circle">
                    </div>
                    <h5 class="mt-4 mb-0">محمد أحمد</h5>
                    <span>مشرف عام</span>
                    <i class="ti ti-x ti-lg cursor-pointer close-sidebar" data-bs-toggle="sidebar" data-overlay
                        data-target="#app-chat-sidebar-left"></i>
                </div>
                <div class="sidebar-body px-6 pb-6">
                    <div class="my-6">
                        <label for="chat-sidebar-left-user-about" class="text-uppercase text-muted mb-1">حول</label>
                        <textarea id="chat-sidebar-left-user-about" class="form-control chat-sidebar-left-user-about" rows="3"
                            maxlength="120">أهلاً بكم، نحن هنا لدعمكم على مدار الساعة.</textarea>
                    </div>
                    <div class="my-6">
                        <p class="text-uppercase text-muted mb-1">الحالة</p>
                        <div class="d-grid gap-2 pt-2 text-heading ms-2">
                            <div class="form-check form-check-success">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="active"
                                    id="user-active" checked>
                                <label class="form-check-label" for="user-active">متصل الآن</label>
                            </div>
                            <div class="form-check form-check-warning">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="away"
                                    id="user-away">
                                <label class="form-check-label" for="user-away">بالخارج</label>
                            </div>
                            <div class="form-check form-check-danger">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="busy"
                                    id="user-busy">
                                <label class="form-check-label" for="user-busy">مشغول</label>
                            </div>
                            <div class="form-check form-check-secondary">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="offline"
                                    id="user-offline">
                                <label class="form-check-label" for="user-offline">غير متصل</label>
                            </div>
                        </div>
                    </div>
                    <div class="my-6">
                        <p class="text-uppercase text-muted mb-1">الإعدادات</p>
                        <ul class="list-unstyled d-grid gap-4 ms-2 pt-2 text-heading">
                            <li class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class='ti ti-lock ti-md me-1'></i>
                                    <span class="align-middle">التحقق بخطوتين</span>
                                </div>
                                <div class="form-check form-switch mb-0 me-1">
                                    <input type="checkbox" class="form-check-input" checked />
                                </div>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class='ti ti-bell ti-md me-1'></i>
                                    <span class="align-middle">الإشعارات</span>
                                </div>
                                <div class="form-check form-switch mb-0 me-1">
                                    <input type="checkbox" class="form-check-input" />
                                </div>
                            </li>
                            <li>
                                <i class="ti ti-user-plus ti-md me-1"></i>
                                <span class="align-middle">دعوة الأصدقاء</span>
                            </li>
                            <li>
                                <i class="ti ti-trash ti-md me-1"></i>
                                <span class="align-middle">حذف الحساب</span>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex mt-6">
                        <button class="btn btn-primary w-100" data-bs-toggle="sidebar" data-overlay
                            data-target="#app-chat-sidebar-left">تسجيل الخروج<i
                                class='ti ti-logout ti-16px ms-2'></i></button>
                    </div>
                </div>
            </div>
            <!-- /Sidebar Left-->

            <!-- Chat & Contacts -->
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="d-flex align-items-center me-6 me-lg-0">
                        <div class="flex-shrink-0 avatar avatar-online me-4" data-bs-toggle="sidebar"
                            data-overlay="app-overlay-ex" data-target="#app-chat-sidebar-left">
                            <img class="user-avatar rounded-circle cursor-pointer"
                                src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية">
                        </div>
                        <div class="flex-grow-1 input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control chat-search-input" placeholder="بحث..."
                                aria-label="بحث..." aria-describedby="basic-addon-search31">
                        </div>
                    </div>
                    <i class="ti ti-x ti-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block"
                        data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
                </div>
                <div class="sidebar-body">
                    <!-- Chats -->
                    <ul class="list-unstyled chat-contact-list py-2 mb-0" id="chat-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">الدردشات</h5>
                        </li>

                        <!-- User 1 -->
                        <li class="chat-contact-list-item mb-1">
                            <a class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="الصورة الرمزية"
                                        class="rounded-circle">
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">علي سالم</h6>
                                        <small class="text-muted">قبل 5 دقائق</small>
                                    </div>
                                    <small class="chat-contact-status text-truncate">مدير مبيعات</small>
                                </div>
                            </a>
                        </li>

                        <!-- User 2 -->
                        <li class="chat-contact-list-item active mb-1">
                            <a class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar avatar-offline">
                                    <img src="{{ asset('assets/img/avatars/3.png') }}" alt="الصورة الرمزية"
                                        class="rounded-circle">
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate fw-normal m-0">فاطمة الشمري</h6>
                                        <small class="text-muted">قبل 30 دقيقة</small>
                                    </div>
                                    <small class="chat-contact-status text-truncate">مدير مشاريع</small>
                                </div>
                            </a>
                        </li>

                        <!-- User 3 -->
                        <li class="chat-contact-list-item mb-0">
                            <a class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar avatar-busy">
                                    <span class="avatar-initial rounded-circle bg-label-success">أ.م</span>
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate fw-normal m-0">أحمد مصطفى</h6>
                                        <small class="text-muted">قبل يوم</small>
                                    </div>
                                    <small class="chat-contact-status text-truncate">مطور برمجيات</small>
                                </div>
                            </a>
                        </li>

                        <!-- يمكنك تكرار نفس الكود لمزيد من المستخدمين -->

                    </ul>
                    <!-- Contacts -->
                    <ul class="list-unstyled chat-contact-list mb-0 py-2" id="contact-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">جهات الاتصال</h5>
                        </li>

                        <!-- استخدام قاعدة البيانات لعرض المستخدمين -->
                        @foreach (\App\Models\User::all() as $user)
                            <li class="chat-contact-list-item">
                                <a class="d-flex align-items-center">
                                    <div class="flex-shrink-0 avatar">
                                        <img src="{{ asset('storage/images/' . basename($user->image)) }}"
                                            alt="الصورة الرمزية" class="rounded-circle" width="50" height="50">
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $user->name }}</h6>
                                        <small class="chat-contact-status text-truncate">{{ $user->job }}</small>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <!-- /Chat contacts -->

            <!-- بقية الكود كما هو دون تغيير -->
            <!-- Chat History -->
            <div class="col app-chat-history">
                <div class="chat-history-wrapper">
                    <div class="chat-history-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex overflow-hidden align-items-center">
                                <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4"
                                    data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
                                <div class="flex-shrink-0 avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية"
                                        class="rounded-circle" data-bs-toggle="sidebar" data-overlay
                                        data-target="#app-chat-sidebar-right">
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <h6 class="m-0 fw-normal">فاطمة الشمري</h6>
                                    <small class="user-status text-body">مدير مشاريع</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i
                                    class="ti ti-phone ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                                <i
                                    class="ti ti-video ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                                <i
                                    class="ti ti-search ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-icon btn-text-secondary text-secondary rounded-pill dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown" aria-expanded="true" id="chat-header-actions"><i
                                            class="ti ti-dots-vertical ti-md"></i></button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="chat-header-actions">
                                        <a class="dropdown-item" href="javascript:void(0);">عرض جهة الاتصال</a>
                                        <a class="dropdown-item" href="javascript:void(0);">إسكات الإشعارات</a>
                                        <a class="dropdown-item" href="javascript:void(0);">حظر جهة الاتصال</a>
                                        <a class="dropdown-item" href="javascript:void(0);">مسح الدردشة</a>
                                        <a class="dropdown-item" href="javascript:void(0);">الإبلاغ</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history-body">
                        <ul class="list-unstyled chat-history">
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">كيف أقدر أخدمك اليوم؟ حنا دائماً هنا لدعمك! 😊</p>
                                        </div>
                                        <div class="text-end text-muted mt-1">
                                            <i class='ti ti-checks ti-16px text-success me-1'></i>
                                            <small>10:00 AM</small>
                                        </div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message">
                                <div class="d-flex overflow-hidden">
                                    <div class="user-avatar flex-shrink-0 me-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">مرحباً عبدالله، عندي استشارة قانونية بخصوص عقد إيجار.</p>
                                            <p class="mb-0">أحتاج رأيك في بعض البنود. 🤔</p>
                                        </div>
                                        <div class="chat-message-text mt-2">
                                            <p class="mb-0">هل ممكن تعطيني توجيهاتك؟</p>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <small>10:02 AM</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">
                                                {{ config('variables.templateName') ? config('variables.templateName') : 'اسم القالب' }}
                                                يجي مع أفضل الحلول القانونية اللي تقدر تعتمد عليها.
                                            </p>
                                        </div>
                                        <div class="text-end text-muted mt-1">
                                            <i class='ti ti-checks ti-16px text-success me-1'></i>
                                            <small>10:03 AM</small>
                                        </div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message">
                                <div class="d-flex overflow-hidden">
                                    <div class="user-avatar flex-shrink-0 me-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">واضح إنه شغل مرتب ومنظم. 😃</p>
                                        </div>
                                        <div class="chat-message-text mt-2">
                                            <p class="mb-0">بالتأكيد هذا يناسب قضيتي الحالية.</p>
                                        </div>
                                        <div class="chat-message-text mt-2">
                                            <p class="mb-0">كيف أقدر أطلب الخدمة؟</p>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <small>10:05 AM</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">تقدر تطلب الخدمة مباشرة من الموقع.</p>
                                        </div>
                                        <div class="text-end text-muted mt-1">
                                            <i class='ti ti-checks ti-16px text-success me-1'></i>
                                            <small>10:06 AM</small>
                                        </div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message">
                                <div class="d-flex overflow-hidden">
                                    <div class="user-avatar flex-shrink-0 me-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">راح أطلبها فوراً. 👍</p>
                                        </div>
                                        <div class="chat-message-text mt-2">
                                            <p class="mb-0">شكراً جزيلاً.</p>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <small>10:08 AM</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">رائع! لا تتردد تتواصل معنا لأي استفسار.</p>
                                        </div>
                                        <div class="text-end text-muted mt-1">
                                            <i class='ti ti-checks ti-16px text-success me-1'></i>
                                            <small>10:10 AM</small>
                                        </div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message">
                                <div class="d-flex overflow-hidden">
                                    <div class="user-avatar flex-shrink-0 me-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <p class="mb-0">هل عندك ملفات العقد والمستندات الضرورية لقضية
                                                {{ config('variables.templateName') ? config('variables.templateName') : 'اسم القالب' }}؟
                                            </p>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <small>10:15 AM</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1 w-50">
                                        <div class="chat-message-text">
                                            <p class="mb-0">نعم، الملفات كاملة ومرفقة مع الخدمة.</p>
                                        </div>
                                        <div class="text-end text-muted mt-1">
                                            <i class='ti ti-checks ti-16px me-1'></i>
                                            <small>10:15 AM</small>
                                        </div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-4">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="الصورة الرمزية"
                                                class="rounded-circle">
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <!-- Chat message form -->
                    <div class="chat-history-footer shadow-xs">
                        <form class="form-send-message d-flex justify-content-between align-items-center ">
                            <input class="form-control message-input border-0 me-4 shadow-none"
                                placeholder="اكتب رسالتك هنا...">
                            <div class="message-actions d-flex align-items-center">
                                <i
                                    class="speech-to-text ti ti-microphone ti-md btn btn-sm btn-text-secondary btn-icon rounded-pill cursor-pointer text-heading"></i>
                                <label for="attach-doc" class="form-label mb-0">
                                    <i
                                        class="ti ti-paperclip ti-md cursor-pointer btn btn-sm btn-text-secondary btn-icon rounded-pill mx-1 text-heading"></i>
                                    <input type="file" id="attach-doc" hidden>
                                </label>
                                <button class="btn btn-primary d-flex send-msg-btn">
                                    <span class="align-middle d-md-inline-block d-none">إرسال</span>
                                    <i class="ti ti-send ti-16px ms-md-2 ms-0"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Chat History -->

            <!-- Sidebar Right -->
            <div class="col app-chat-sidebar-right app-sidebar overflow-hidden" id="app-chat-sidebar-right">
                <div
                    class="sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-6 pt-12">
                    <div class="avatar avatar-xl avatar-online chat-sidebar-avatar">
                        <img src="{{ asset('assets/img/avatars/4.png') }}" alt="الصورة الرمزية" class="rounded-circle">
                    </div>
                    <h5 class="mt-4 mb-0">فاطمة الشمري</h5>
                    <span>مدير مشاريع</span>
                    <i class="ti ti-x ti-lg cursor-pointer close-sidebar d-block" data-bs-toggle="sidebar" data-overlay
                        data-target="#app-chat-sidebar-right"></i>
                </div>
                <div class="sidebar-body p-6 pt-0">
                    <div class="my-6">
                        <p class="text-uppercase mb-1 text-muted">حول</p>
                        <p class="mb-0">لقد تم إثبات أن المستخدم سيشتت انتباهه المحتوى القابل للقراءة.</p>
                    </div>
                    <div class="my-6">
                        <p class="text-uppercase mb-1 text-muted">المعلومات الشخصية</p>
                        <ul class="list-unstyled d-grid gap-4 mb-0 ms-2 py-2 text-heading">
                            <li class="d-flex align-items-center">
                                <i class='ti ti-mail ti-md'></i>
                                <span class="align-middle ms-2">fatima@example.com</span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class='ti ti-phone-call ti-md'></i>
                                <span class="align-middle ms-2">+966(123) 456 - 7890</span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class='ti ti-clock ti-md'></i>
                                <span class="align-middle ms-2">من الإثنين إلى الجمعة 10 صباحاً - 8 مساءً</span>
                            </li>
                        </ul>
                    </div>
                    <div class="my-6">
                        <p class="text-uppercase text-muted mb-1">الخيارات</p>
                        <ul class="list-unstyled d-grid gap-4 ms-2 py-2 text-heading">
                            <li class="cursor-pointer d-flex align-items-center">
                                <i class='ti ti-badge ti-md'></i>
                                <span class="align-middle ms-2">إضافة علامة</span>
                            </li>
                            <li class="cursor-pointer د-flex align-items-center">
                                <i class='ti ti-star ti-md'></i>
                                <span class="align-middle ms-2">جهة اتصال مهمة</span>
                            </li>
                            <li class="cursor-pointer د-flex align-items-center">
                                <i class='ti ti-photo ti-md'></i>
                                <span class="align-middle ms-2">وسائط مشتركة</span>
                            </li>
                            <li class="cursor-pointer د-flex align-items-center">
                                <i class='ti ti-trash ti-md'></i>
                                <span class="align-middle ms-2">حذف جهة الاتصال</span>
                            </li>
                            <li class="cursor-pointer د-flex align-items-center">
                                <i class='ti ti-ban ti-md'></i>
                                <span class="align-middle ms-2">حظر جهة الاتصال</span>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex mt-6">
                        <button class="btn btn-danger w-100" data-bs-toggle="sidebar" data-overlay
                            data-target="#app-chat-sidebar-right">حذف جهة الاتصال<i
                                class='ti ti-trash ti-16px ms-2'></i></button>
                    </div>
                </div>
            </div>
            <!-- /Sidebar Right -->

            <div class="app-overlay"></div>
        </div>
    </div>
@endsection
