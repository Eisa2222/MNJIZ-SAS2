@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات المستخدم')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
        // 'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        // 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
        'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
        'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
        'resources/assets/vendor/libs/pickr/pickr-themes.scss',
        'resources/assets/vendor/libs/animate-css/animate.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        // 'resources/assets/vendor/libs/moment/moment.js',
        // 'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        // 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
        'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
        'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js',
        'resources/assets/vendor/libs/pickr/pickr.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/forms-pickers.js', 'resources/assets/js/extended-ui-sweetalert2.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-6">
            <div class="card">
                <h5 class="card-header">تعديل بيانات المستخدم</h5>
                <div class="card-body">

                    <!-- عرض رسائل التحقق -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- عرض SweetAlert لرسالة النجاح -->
                    @if (session('success_edit'))
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    title: 'تم التعديل بنجاح!',
                                    text: '{{ session('success_edit') }}',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary waves-effect waves-light'
                                    },
                                    buttonsStyling: false,
                                    timer: 1000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                    @endif

                    <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- الاسم الكامل -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="name" class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $user->name) }}" required />
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" required />
                            </div>

                            <!-- رقم الهاتف -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone', $user->phone) }}" required />
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                                        نشط</option>
                                    <option value="inactive"
                                        {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                    <option value="pending"
                                        {{ old('status', $user->status) == 'pending' ? 'selected' : '' }}>معلق</option>
                                </select>
                            </div>

                            <!-- الجنسية -->
                            <div class="col-md-4">
                                <label for="nationality" class="form-label">الجنسية</label>
                                <select id="nationality" name="nationality" class="form-select select2" required
                                    data-placeholder="اختر الجنسية">
                                    <option value=""></option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ old('nationality', $user->nationality) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- كلمة المرور -->
                            <div class="col-md-4 col-12 mb-6">
                                <label for="password" class="form-label">كلمة المرور (اتركها فارغة إذا لم ترغب في
                                    التغيير)</label>
                                <input type="password" class="form-control" id="password" name="password" />
                            </div>

                            <!-- تأكيد كلمة المرور -->
                            <div class="col-md-4 col-12 mb-6">
                                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" />
                            </div>

                            <!-- الوظيفة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="job" class="form-label">الوظيفة</label>
                                <input type="text" class="form-control" id="job" name="job"
                                    value="{{ old('job', $user->job) }}" required />
                            </div>

                            <!-- الصورة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="image" class="form-label">الصورة (اتركها فارغة إذا لم ترغب في
                                    التغيير)</label>
                                <input type="file" class="form-control" id="image" name="image" />
                                @if ($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="صورة المستخدم" width="100"
                                        class="mt-2" />
                                @endif
                            </div>

                            <!-- الأدوار -->
                            <div class="col-md-12 col-12 mb-6">
                                <label for="roles" class="form-label">المسمى الوظيفي </label>
                                <select id="roles" name="roles[]" class="form-select select2" multiple required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user->roles->pluck('id')->contains($role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- زر الإرسال -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">تحديث </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
