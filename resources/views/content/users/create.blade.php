@extends('layouts.layoutMaster')

@section('title', 'إضافة مستخدم جديد')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/forms-pickers.js', 'resources/assets/js/extended-ui-sweetalert2.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-6">
            <div class="card">
                <h5 class="card-header">إضافة مستخدم جديد</h5>
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
                    @if (session('success'))
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    title: 'تم الحفظ بنجاح!',
                                    text: '{{ session('success') }}',
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

                    <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- الاسم الكامل -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="name" class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name') }}" required />
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email') }}" required />
                            </div>

                            <!-- رقم الهاتف -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone') }}" required />
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط
                                    </option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                </select>
                            </div>

                            <!-- الجنسية -->
                            {{-- <div class="col-md-4 col-12 mb-6">
                            <label for="nationality" class="form-label">الجنسية</label>
                            <input type="text" class="form-control" id="nationality" name="nationality"
                                   value="{{ old('nationality') }}" required />
                        </div> --}}


                            <div class="col-md-6">
                                <label for="nationality" class="form-label">الجنسية</label>
                                <select id="nationality" name="nationality" class="form-select select2" required
                                    data-placeholder="اختر الجنسية">
                                    <option value=""></option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ old('nationality') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <!-- كلمة المرور -->
                            <div class="col-md-4 col-12 mb-6">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" id="password" name="password" required />
                            </div>

                            <!-- تأكيد كلمة المرور -->
                            <div class="col-md-4 col-12 mb-6">
                                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required />
                            </div>

                            <!-- الوظيفة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="job" class="form-label">الوظيفة</label>
                                <input type="text" class="form-control" id="job" name="job"
                                    value="{{ old('job') }}" required />
                            </div>

                            <!-- الصورة -->
                            <div class="col-md-6 col-12 mb-6">
                                <label for="image" class="form-label">الصورة</label>
                                <input type="file" class="form-control" id="image" name="image" />
                            </div>

                            <!-- الأدوار -->
                            <div class="col-md-12 col-12 mb-6">
                                <label for="roles" class="form-label">المسمى الوظيفي</label>
                                <select id="roles" name="roles[]" class="form-select" multiple required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ collect(old('roles'))->contains($role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- زر الإرسال -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">إضافة المستخدم</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
