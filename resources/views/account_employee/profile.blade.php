@extends('layouts.layoutMaster')

@section('title', 'الملف الشخصي للموظف')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">الملف الشخصي </a>
        <i class="ti ti-star favorite-icon" data-page-name="الملف الشخصي" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss'])

@endsection

@section('vendor-script')
    <script>
        window.appUrls = {
            taskComplate: "{{ route('organization-center.tasks.toggle-completion', ':task') }}",
            stepComplate: "{{ route('organization-center.tasks.steps.toggle-completion', ':stepId') }}",
            stepApproval: "{{ route('organization-center.tasks.steps.toggle-approval', ':stepId') }}",
        };
    </script>
    @vite(['resources/assets/js/organization-center/tasks/task-profile.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-profile.js'])
@endsection


@section('content')

    <script>
        // دالة استخراج معلمة من الـ URL
        function getParameterByName(name) {
            var url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        $(document).ready(function() {
            // استخراج معلمة "tab" من الـ URL
            var tab = getParameterByName('tab');
            if (tab) {
                // التأكد من وجود رابط التبويب الذي يحمل الـ hash المناسب وتفعيله
                var tabLink = $('.nav a[href="#' + tab + '"]');
                if (tabLink.length) {
                    tabLink.tab('show');
                }
            }

            // عند تغيير التبويب، نقوم بتحديث الـ URL بحيث يتم حفظ قيمة التبويب الحالي
            $('.nav a').on('shown.bs.tab', function(e) {
                var targetHash = e.target.hash; // الحصول على الـ hash الجديد
                if (targetHash) {
                    history.replaceState(null, null, '?tab=' + targetHash.substring(1));
                }
            });
        });
    </script>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">

                <div class="background-image-container"
                    style="position: relative; width: 100%; height: 100%; overflow: hidden;">
                    <img src="{{ $employee->background_image ? asset('storage/' . $employee->background_image) : asset('profile/gray.jpg') }}"
                        alt="صورة الغلاف" class="rounded-top" style="width: 100%; height: 100%; object-fit: cover;">
                    @if (auth()->check() && auth()->id() === $employee->user->id)
                        <button type="button" class="btn btn-primary btn-sm edit-button" data-bs-toggle="modal"
                            data-bs-target="#editBackgroundImageModal"
                            style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                            <i class="ti ti-pencil"></i>تغيير
                        </button>
                    @endif
                </div>
                <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                    <div class="profile-image-container mt-n5 mx-sm-0 mx-auto"
                        style="position: relative; display: inline-block;">
                        <img src="{{ $employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture) ? asset('storage/' . $employee->profile_picture) : asset('assets/img/avatars/1.png') }}"
                            alt="صورة الموظف" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img"
                            style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">

                        <!-- زر تحديث الصورة الشخصية -->
                        @if (auth()->check() && auth()->id() === $employee->user->id)
                            <button type="button" class="btn btn-primary btn-sm edit-button" data-bs-toggle="modal"
                                data-bs-target="#editProfilePictureModal"
                                style="margin-right: 30px; position: absolute; bottom: 0; right: 0; opacity: 0; transition: opacity 0.3s;">
                                <i class="ti ti-pencil"></i>
                            </button>
                        @endif

                    </div>
                    <div class="flex-grow-1 mt-3 mt-lg-5">
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h5 class="mb-2 mt-lg-6 fw-bold">{{ $employee->name }}</h5>
                                <ul
                                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                    @isset($employee->job_title)
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i class='ti ti-briefcase ti-lg  text-primary'></i>
                                            <span class="fw-bold small">
                                                {{ $employee->job_title }}
                                            </span>
                                        </li>
                                    @endisset

                                    @isset($employee->country->name)
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i class='ti ti-map-pin ti-lg  text-primary'></i>
                                            <span class="fw-bold small">
                                                {{ $employee->country->name ?? 'غير محدد' }}
                                            </span>
                                        </li>
                                    @endisset

                                    @isset($employee->contract_start_date)
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i class='ti ti-calendar ti-lg  text-primary'></i>
                                            <span class="fw-bold small">
                                                تاريخ بداية العقد
                                                {{ optional($employee->contract_start_date)->format('Y-m-d') ?? '' }}
                                            </span>
                                        </li>
                                    @endisset

                                    @isset($employee->contract_end_date)
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i
                                                class='ti ti-calendar-event ti-lg {{ $employee->is_contract_expired ? 'text-danger' : 'text-success' }}'></i>
                                            <span class="fw-bold small">
                                                تاريخ انتهاء العقد: {{ $employee->contract_end_date->format('Y-m-d') }}
                                                @if ($employee->is_contract_expired)
                                                    <span class="badge bg-danger ms-1">{{ $employee->contract_status }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endisset

                                </ul>
                            </div>
                            @if (auth()->check() && auth()->id() !== $employee->user_id)
                                <a href="{{ route('chat.index', ['user_id' => $employee->user_id]) }}"
                                    class="btn btn-primary" title="بدء دردشة فورية مع {{ $employee->name }}">
                                    دردشة
                                    <i class="ti ti-message-2-up chat-icon mx-2"></i>
                                </a>
                            @else
                                <a href="{{ Route('account.employee.profile.edit', $employee->id) }}"
                                    class="btn btn-primary">
                                    تعديل الملف الشخصي
                                    <i class="ti ti-settings mx-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <div class="row ">
                <div class="col-md-12">
                    <div class="nav-align-top d-flex">
                        <ul class="card p-3 nav nav-pills flex-column flex-sm-row mb-3 gap-2 gap-lg-0">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#general_info_tab">
                                    البيانات الاساسية
                                </a>
                            </li>

                            @if (auth()->user()->can('كل الموظفين') || auth()->id() == $employee->user_id)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#additional_info_tab">
                                        البيانات التفصيلية
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#customers_tab">
                                    علاقات العملاء
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#projects_tab">
                                    المشاريع
                                </a>
                            </li>

                            @if (auth()->user()->can('كل الموظفين') || auth()->id() == $employee->user_id)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tasks_tab">
                                        المهام
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#custody_tab">
                                        العهد
                                    </a>
                                </li>
                            @endif

                        </ul>


                    </div>
                </div>
            </div>

            <!-- edit profile imag -->
            <div class="modal fade" id="editProfilePictureModal" tabindex="-1"
                aria-labelledby="editProfilePictureModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog">
                    <form action="{{ route('account.employee.profile.picture.update', $employee->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header pb-5">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                                <h5 class="modal-title" id="editProfilePictureModalLabel">تحديث الصورة الشخصية</h5>
                            </div>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">اختر صورة جديدة</label>
                                    <input class="form-control" type="file" id="profile_picture"
                                        name="profile_picture" accept="image/*" required>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <img id="previewImage"
                                        src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                        alt="صورة المعاينة" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">تحديث</button>
                                <button type="button" class="btn btn-secondary mx-0"
                                    data-bs-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- edit background imag -->
            <div class="modal fade" id="editBackgroundImageModal" tabindex="-1"
                aria-labelledby="editBackgroundImageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog">
                    <form action="{{ route('account.employee.profile.background.update', $employee->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header pb-5">
                                <h5 class="modal-title" id="editBackgroundImageModalLabel">تحديث صورة الخلفية</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="background_image" class="form-label">اختر صورة خلفية جديدة</label>
                                    <small class="text-danger">يجب أن تكون أبعاد صورة الخلفية 1693 × 376 بكسل</small>
                                    <input class="form-control" type="file" id="background_image"
                                        name="background_image" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <img id="previewBackgroundImage"
                                        src="{{ $employee->background_image ? asset('storage/' . $employee->background_image) : asset('assets/img/pages/profile-banner.png') }}"
                                        alt="صورة الخلفية المعاينة" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">تحديث</button>

                                <button type="button" class="btn btn-secondary mx-0"
                                    data-bs-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-content p-0">
                @include('account_employee._partials.general_info_tab')

                @if (auth()->user()->can('كل الموظفين') || auth()->id() == $employee->user_id)
                    @include('account_employee._partials.additional_info_tab')
                    @include('account_employee._partials.tasks_tab')
                    @include('account_employee._partials.custody_tab')
                @endif

                @include('account_employee._partials.customers_tab')
                @include('account_employee._partials.projects_tab')

            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // إضافة تأثير إظهار الأزرار عند المرور بالماوس
                const backgroundContainer = document.querySelector('.background-image-container');
                const backgroundEditButton = backgroundContainer.querySelector('.edit-button');

                backgroundContainer.addEventListener('mouseover', () => {
                    backgroundEditButton.style.opacity = '1';
                });

                backgroundContainer.addEventListener('mouseout', () => {
                    backgroundEditButton.style.opacity = '0';
                });

                const profileContainer = document.querySelector('.profile-image-container');
                const profileEditButton = profileContainer.querySelector('.edit-button');

                profileContainer.addEventListener('mouseover', () => {
                    profileEditButton.style.opacity = '1';
                });

                profileContainer.addEventListener('mouseout', () => {
                    profileEditButton.style.opacity = '0';
                });
            });

            document.getElementById('background_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const img = new Image();
                img.src = URL.createObjectURL(file);

                img.onload = function() {
                    if (this.width !== 1693 || this.height !== 376) {
                        Swal.fire({
                            icon: 'error',
                            text: 'يجب أن تكون أبعاد صورة الخلفية 1693x376 بكسل.',
                            customClass: {
                                confirmButton: 'btn btn-success waves-effect waves-light'
                            },
                            confirmButtonText: 'حسناً'
                        });
                        e.target.value = ''; // إعادة تعيين حقل الإدخال
                    }
                };
            });
        </script>
    </div>
@endsection
