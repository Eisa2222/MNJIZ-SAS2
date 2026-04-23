@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use App\Helpers\SettingsHelper;
    use App\Helpers\AttendanceHelper;

    $containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
    $work_start_time = SettingsHelper::get('work_start_time');
    $work_end_time = SettingsHelper::get('work_end_time');
@endphp

<style>
    .hidden-btn {
        display: none !important;
    }

    .visible-btn {
        display: inline-block !important;
    }

    /* نمط المؤقت */
    .countdown {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 5px;
    }
</style>
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var workStartTime = "{{ \Carbon\Carbon::parse($work_start_time)->format('H:i') }}";
        var workEndTime = "{{ \Carbon\Carbon::parse($work_end_time)->format('H:i') }}";

        // دالة لتحويل وقت H:i إلى دقائق منذ منتصف الليل
        function timeToMinutes(time) {
            var parts = time.split(':').map(Number);
            return parts[0] * 60 + parts[1];
        }

        // دالة للتحقق من الحالة الحالية بناءً على الوقت
        function getCurrentStatus() {
            var now = new Date();
            var currentTime = now.getHours() * 60 + now.getMinutes();

            var workStartInMinutes = timeToMinutes(workStartTime);
            var workEndInMinutes = timeToMinutes(workEndTime);

            if (currentTime >= workEndInMinutes) {
                return 'check_out';
            }
            // تحديد حالة 'check_in' إذا كان الوقت الحالي بعد أو يساوي وقت الحضور
            else if (currentTime >= workStartInMinutes) {
                return 'check_in';
            } else {
                return 'none';
            }
        }

        // تحديث الأزرار بناءً على الحالة
        function updateButtons() {
            var status = getCurrentStatus();

            var checkInBtn = document.getElementById('check-in-btn');
            var checkOutBtn = document.getElementById('check-out-btn');

            if (checkInBtn && checkOutBtn) {
                if (status === 'check_out') {
                    // عرض زر تسجيل الانصراف
                    checkOutBtn.classList.remove('hidden-btn');
                    checkOutBtn.classList.add('visible-btn');
                    // إخفاء زر تسجيل الحضور
                    checkInBtn.classList.remove('visible-btn');
                    checkInBtn.classList.add('hidden-btn');
                } else if (status === 'check_in') {
                    // عرض زر تسجيل الحضور
                    checkInBtn.classList.remove('hidden-btn');
                    checkInBtn.classList.add('visible-btn');
                    // إخفاء زر تسجيل الانصراف
                    checkOutBtn.classList.remove('visible-btn');
                    checkOutBtn.classList.add('hidden-btn');
                } else {
                    // إخفاء كلا الزرين
                    checkInBtn.classList.remove('visible-btn');
                    checkInBtn.classList.add('hidden-btn');
                    checkOutBtn.classList.remove('visible-btn');
                    checkOutBtn.classList.add('hidden-btn');
                }
            }
        }

        // تحديث الأزرار عند تحميل الصفحة
        updateButtons();

    });
</script>


<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
        id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif

@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20])</span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
        </a>
        @if (isset($menuHorizontal))
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                <i class="ti ti-x ti-md align-middle"></i>
            </a>
        @endif
    </div>
@endif

@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-md"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

    @if (!isset($menuHorizontal))
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                    <i class="ti ti-search ti-md me-2 me-lg-4 ti-lg"></i>
                    <span class="d-none d-md-inline-block text-muted fw-normal">ابحـث (Ctrl+/)</span>
                </a>
            </div>
        </div>
        <!-- /Search -->
    @endif

    <ul class="navbar-nav flex-row align-items-center ms-auto">
        @if (isset($menuHorizontal))
            <!-- Search -->
            <li class="nav-item navbar-search-wrapper">
                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill search-toggler"
                    href="javascript:void(0);">
                    <i class="ti ti-search ti-md"></i>
                </a>
            </li>
            <!-- /Search -->
        @endif

        @if (SettingsHelper::get('manual_attendance_enabled'))
            <div class="d-flex align-items-center">
                <!-- زر تسجيل الحضور -->
                @if (!AttendanceHelper::isCheckedIn())
                    <form id="check-in-form" class="m-0" action="{{ route('attendances.check_in') }}" method="POST">
                        @csrf
                        <input type="hidden" name="latitude" id="check-in-latitude">
                        <input type="hidden" name="longitude" id="check-in-longitude">
                        <button type="button" id="check-in-btn" class="btn btn-primary me-3">
                            تسجيل الحضور
                        </button>
                    </form>
                @else
                    <!-- عرض وقت الحضور وزر تسجيل الانصراف -->
                    <div class="me-3">
                        @if (AttendanceHelper::getCurrentAttendance()->check_in_time)
                            <span class="countdown">
                                وقت الحضور: {{ AttendanceHelper::getCurrentAttendance()->check_in_time }}
                            </span>
                        @endif
                    </div>
                    @if (!AttendanceHelper::isCheckedOut())
                        <form id="check-out-form" class="m-0" action="{{ route('attendances.check_out') }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="latitude" id="check-out-latitude">
                            <input type="hidden" name="longitude" id="check-out-longitude">
                            <button type="button" id="check-out-btn" class="btn btn-danger">
                                تسجيل الانصراف
                            </button>
                        </form>
                    @else
                        <!-- عرض وقت الانصراف -->
                        <div class="ms-3">
                            @if (AttendanceHelper::getCurrentAttendance()->check_out_time)
                                <span class="countdown">
                                    وقت الانصراف:
                                    {{ AttendanceHelper::getCurrentAttendance()->check_out_time }}
                                </span>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endif
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // دالة لالتقاط الموقع الجغرافي وإرسال النموذج
                // دالة لالتقاط الموقع الجغرافي وإرسال النموذج
                function captureLocation(formId, latitudeId, longitudeId) {
                    if (!navigator.geolocation) {
                        toastr.error('المتصفح الخاص بك لا يدعم تحديد الموقع الجغرافي.', 'خطأ');
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            var accuracy = position.coords.accuracy; // الدقة بالمتر
                            // يمكنك عرض رسالة للمستخدم إذا كانت الدقة أقل من المطلوب
                            if (accuracy > 50) { // على سبيل المثال، إذا كانت الدقة تزيد عن 50 مترًا
                                toastr.warning(
                                    "قد يكون الموقع الملتقط تقريبيًا. تأكد من تفعيل GPS للحصول على نتائج دقيقة."
                                );
                            }
                            // تحديث الحقول
                            document.getElementById(latitudeId).value = parseFloat(position.coords.latitude.toFixed(
                                6));
                            document.getElementById(longitudeId).value = parseFloat(position.coords.longitude
                                .toFixed(6));

                            document.getElementById(formId).submit();
                        },
                        function(error) {
                            toastr.error('فشل في الحصول على الموقع الجغرافي. يرجى السماح بتحديد الموقع في المتصفح.',
                                'خطأ');
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );

                }


                // إضافة مستمع للأزرار فقط إذا كانت موجودة
                var checkInBtn = document.getElementById('check-in-btn');
                if (checkInBtn) {
                    checkInBtn.addEventListener('click', function() {
                        captureLocation('check-in-form', 'check-in-latitude', 'check-in-longitude');
                    });
                }

                var checkOutBtn = document.getElementById('check-out-btn');
                if (checkOutBtn) {
                    checkOutBtn.addEventListener('click', function() {
                        captureLocation('check-out-form', 'check-out-latitude', 'check-out-longitude');
                    });
                }
            });
        </script>
        <!-- Microsoft alert -->
        <li class="nav-item dropdown-language dropdown">
            <a class="nav-link btn btn-text-secondary btn-icon rounded-pill position-relative"
                href="{{ Auth::user()->microsoft_id ? '#' : route('microsoft.login') }}"
                title="{{ Auth::user()->microsoft_id ? 'حسابك مرتبط بحساب مايكروسوفت.' : 'حسابك غير مرتبط بحساب مايكروسوفت. للحصول على جميع مزايا النظام، يرجى ربط حسابك الآن.' }}">
                <!-- أيقونة مايكروسوفت -->
                <img src="{{ asset('assets/img/branding/ms.png') }}"
                    style="width: 20px !important; height: 20px !important;">
                <!-- العلامة الملونة بجانب الأيقونة -->
                <span
                    class="position-absolute p-1
            {{ Auth::user()->microsoft_id ? 'bg-success' : 'bg-danger' }}
            border border-light rounded-circle blink"
                    style="top: 4px !important; left: 25px !important;"
                    title="{{ Auth::user()->microsoft_id ? 'مرتبط بحساب مايكروسوفت' : 'غير مرتبط بحساب مايكروسوفت' }}">
                </span>
            </a>
        </li>
        <!-- Microsoft alert -->

        @include('layouts.sections.navbar.settings')


        @if ($configData['hasCustomizer'] == true)
            <!-- Style Switcher -->
            <li class="nav-item dropdown-style-switcher dropdown">
                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                    href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class='ti ti-md'></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                            <span class="align-middle"><i class='ti ti-sun ti-md me-3'></i>فاتح</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                            <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>داكن</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                            <span class="align-middle"><i
                                    class="ti ti-device-desktop-analytics ti-md me-3"></i>النظام</span>
                        </a>
                    </li>
                </ul>

            </li>
            <!-- / Style Switcher -->
        @endif

        <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
            <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <span class="position-relative">
                    <i class="ti ti-bell ti-md"></i>
                    @if (Auth::user()->unreadNotifications->count() > 0)
                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border">
                            {{-- {{ Auth::user()->unreadNotifications->count() }} --}}
                        </span>
                    @endif

                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-0">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">الإشعارات</h6>
                        <div class="d-flex align-items-center h6 mb-0">
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span id="notification-badge" class="badge bg-label-primary me-2">
                                    {{ Auth::user()->unreadNotifications->count() }} جديد
                                </span>

                                <a href="javascript:void(0)"
                                    class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"
                                    onclick="markAllNotificationsAsRead()">
                                    <i class="ti ti-mail-opened text-heading"></i>
                                </a>
                            @endif

                        </div>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container overflow-auto" style="max-height: 300px;">
                    <ul class="list-group list-group-flush">
                        {{-- {{ dd(Auth::user()->notifications) }} --}}
                        @forelse (Auth::user()->notifications as $notification)
                            <li
                                class="list-group-item list-group-item-action dropdown-notifications-item {{ is_null($notification->read_at) ? '' : 'marked-as-read' }}">
                                <a href="{{ $notification->data['action_url'] }}" class="d-flex"
                                    onclick="markNotificationAsRead('{{ $notification->id }}')">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-shrink-0 me-1">
                                            <div class="avatar">
                                                {{-- <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ strtoupper(substr($notification->data['message'], 0, 2)) }}
                                                </span> --}}

                                                @if (isset($notification->data['image']) && $notification->data['image'])
                                                    <img class="w-100"
                                                        src="{{ Storage::url($notification->data['image']) }}"
                                                        alt="meeting image" style="object-fit: contain;" />
                                                @else
                                                    <img class="w-100"
                                                        src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                        alt="default image" style="object-fit: contain;" />
                                                @endif


                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="small mb-1">{{ $notification->data['message'] }}</h6>
                                            <small
                                                class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="javascript:void(0)" class="dropdown-notifications-read"
                                                onclick="markNotificationAsRead('{{ $notification->id }}')">
                                                @if (!$notification->read_at)
                                                    <!-- Assuming 'read_at' is null if unread -->
                                                    <span class="badge badge-dot"></span>
                                                @endif
                                            </a>
                                            <a href="javascript:void(0)" class="dropdown-notifications-archive"
                                                onclick="deleteNotification('{{ $notification->id }}')">
                                                <span class="ti ti-x"></span>
                                            </a>
                                        </div>
                                    </div>
                                </a>

                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">لا توجد إشعارات</li>
                        @endforelse
                    </ul>
                </li>
                <li class="border-top">
                    <div class="d-grid p-4">
                        <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifications.index') }}">
                            <small class="align-middle">عرض كل الإشعارات</small>
                        </a>
                    </div>
                </li>
            </ul>
        </li>

        <script>
            // دالة لإظهار التوستر
            function showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const toastHTML = `
                    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                // إضافة التوستر إلى الحاوية
                document.getElementById('toast-container').insertAdjacentHTML('beforeend', toastHTML);
                // تهيئة وعرض التوستر
                const toastElement = document.getElementById(toastId);
                const bsToast = new bootstrap.Toast(toastElement, {
                    delay: 3000
                });
                bsToast.show();
                // إزالة التوستر بعد إخفائه
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }

            // دالة لتحديث العدادات بناءً على العدد المحدث
            function updateUnreadCount(count) {
                // تحديث الشارة بجانب أيقونة الجرس
                const notificationBadge = document.getElementById('notification-badge');
                if (notificationBadge) {
                    if (count > 0) {
                        notificationBadge.textContent = count > 99 ? '99+' : `${count} جديد`;
                        notificationBadge.style.display = 'inline-block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }

                // تحديث الشارة في رأس القائمة المنسدلة
                const notificationHeaderBadge = document.getElementById('notification-header-badge');
                if (notificationHeaderBadge) {
                    notificationHeaderBadge.textContent = `${count} جديد`;
                }
            }

            // دالة تحديد جميع الإشعارات كمقروءة
            function markAllNotificationsAsRead() {
                fetch("{{ route('notifications.markAllAsRead') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                            // تحديث حالة الإشعارات في القائمة المنسدلة
                            document.querySelectorAll('.dropdown-notifications-item').forEach(item => {
                                item.classList.add('marked-as-read');
                                const readBadge = item.querySelector('.dropdown-notifications-read .badge-dot');
                                if (readBadge) {
                                    readBadge.remove();
                                }
                            });
                        } else {
                            showToast('حدث خطأ أثناء محاولة تحديد الإشعارات كمقروءة.', 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }

            // دالة تحديد إشعار واحد كمقروء
            function markNotificationAsRead(notificationId) {
                fetch(`/notifications/${notificationId}/mark-as-read`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث حالة الإشعار في القائمة المنسدلة
                            const notificationItem = document.getElementById(`notification-${notificationId}`);
                            if (notificationItem) {
                                notificationItem.classList.add('marked-as-read');
                                const readBadge = notificationItem.querySelector('.dropdown-notifications-read .badge-dot');
                                if (readBadge) {
                                    readBadge.remove();
                                }
                            }
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }

            // دالة لحذف إشعار
            function deleteNotification(notificationId) {

                fetch(`/notifications/${notificationId}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // إزالة الإشعار من القائمة المنسدلة
                            const notificationItem = document.getElementById(`notification-${notificationId}`);
                            if (notificationItem) {
                                notificationItem.remove();
                            }
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }
        </script>

        @php
            $employee = \App\Models\Hr\Employees\Employees::where('user_id', Auth::user()->id)->first();
        @endphp
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    {{-- <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt class="rounded-circle"> --}}
                    <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('assets/img/avatars/1.png') }}"
                        alt="User Image" class="rounded-circle">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">


                @if ($employee)
                    <li>
                        <a class="dropdown-item mt-0" href="{{ Route('account.employee.profile', $employee->id) }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-online">
                                        {{-- الجلب من قاعدة البيانات للمستخدم المتصل --}}
                                        <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                            alt class="rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">
                                        @if (Auth::check())
                                            {{ Auth::user()->name }}
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ Auth::user()->getRoleNames()->implode(', ') }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item mt-0" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-online">
                                        {{-- الجلب من قاعدة البيانات للمستخدم المتصل --}}
                                        <img src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->profile_picture) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                            alt class="rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">
                                        @if (Auth::check())
                                            {{ Auth::user()->name }}
                                        @else
                                            John Doe
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ Auth::user()->getRoleNames()->implode(', ') }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                @endif

                <li>
                    <div class="dropdown-divider my-1 mx-n2"></div>
                </li>

                @if ($employee)
                    <li>
                        <a class="dropdown-item" href="{{ Route('account.employee.profile', $employee->id) }}">
                            <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">الملف الشخصي</span>
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="{{ route('account.employee.password.edit') }}">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-lock me-3 ti-md"></i>
                                <span class="flex-grow-1 align-middle">تغير كلمة المرور</span>
                            </span>
                        </a>

                    </li>
                @endif


                @if (Auth::check())
                    <li>
                        <div class="dropdown-divider my-1 mx-n2"></div>
                    </li>
                    <li>
                        <div class="d-grid px-2 pt-2 pb-1">
                            <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <small class="align-middle">تسجيل الخروج</small>
                                <i class="ti ti-logout ms-2 ti-14px"></i>
                            </a>
                        </div>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                    </form>
                @else
                    <li>
                        <div class="d-grid px-2 pt-2 pb-1">
                            <a class="btn btn-sm btn-danger d-flex"
                                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                <small class="align-middle">Login</small>
                                <i class="ti ti-login ms-2 ti-14px"></i>
                            </a>
                        </div>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

<!-- Search Small Screens -->
<div class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
    <input type="text"
        class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
        placeholder="Search..." aria-label="Search...">
    <i class="ti ti-x search-toggler cursor-pointer"></i>
</div>
<!--/ Search Small Screens -->
@if (isset($navbarDetached) && $navbarDetached == '')
    </div>
@endif
</nav>
<!-- / Navbar -->
