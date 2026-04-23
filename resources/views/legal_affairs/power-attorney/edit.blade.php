@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الوكالة')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.power-attorney.index') }}"> الوكالات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تعديل بيانات الوكالة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الوكالة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    {{-- التاريخ الهجري   --}}
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/form-wizard-validation-power-attorney.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: $(this).data('placeholder') || 'اختر خيارًا',
                    allowClear: true,
                    closeOnSelect: !$(this).prop('multiple'),
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
            });
        });
        window.moment = moment;

        function loadHijriDatePicker() {
            // تحميل مكتبة التقويم الهجري بعد التأكد من تحميل مكتبة moment.js
            if (typeof window.moment === 'undefined') {
                console.error('Moment.js is not loaded. Please check the script path.');
                return;
            }

            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker(); // استدعاء تهيئة التقويم
            };
            script.onerror = function() {
                console.error('Failed to load Hijri Datepicker library.');
            };
        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,
                    // format: 'iYYYY-iMM-iDD'
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>
@endsection

@section('content')
    <!-- Form Wizard -->
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- مراحل النموذج -->
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل الوكالة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الإضافية</span>
                                <span class="bs-stepper-subtitle">تفاصيل إضافية عن الوكالة</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="power-form" action="{{ route('legal-affairs.power-attorney.update', $powerAttorney->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="power_name" class="form-label">اسم الوكالة</label>
                                    <input type="text" id="power_name" name="power_name" class="form-control"
                                        value="{{ old('power_name', $powerAttorney->power_name) }}" required />
                                </div>
                                <div class="col-md-6">
                                    <label for="power_number" class="form-label">رقم الوكالة</label>
                                    <input type="text" id="power_number" name="power_number" class="form-control"
                                        value="{{ old('power_number', $powerAttorney->power_number) }}" required />
                                </div>


                                <div class="col-md-6">
                                    <label for="customers" class="form-label">العملاء</label>
                                    <select id="customers" name="customers[]" class="w-100 select2" data-style="btn-default"
                                        required multiple data-actions-box="true" data-live-search="true"
                                        data-placeholder="اختر العميل">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                data-content="<i class='ti ti-rosette-discount-check-filled text-success'></i> {{ $customer->name }}"
                                                {{ collect(old('customers', $powerAttorney->customers->pluck('id')->toArray()))->contains($customer->id) ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="agents" class="form-label">الوكلاء</label>
                                    <select id="agents" name="agents[]" class="w-100 select2" data-style="btn-default"
                                        required multiple data-actions-box="true" data-live-search="true"
                                        data-placeholder="اختر الوكلاء">
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ collect(old('agents', $powerAttorney->agents->pluck('id')->toArray()))->contains($employee->id) ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="date_issued" class="form-label">تاريخ الإصدار</label>
                                    <input type="text" id="date_issued" name="date_issued"
                                        class="form-control hijri-picker"
                                        value="{{ old('date_issued', $powerAttorney->hijri_date_issued) }}" required
                                        autocomplete="off" onkeydown="return false;" />
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="date_expiry" class="form-label">تاريخ الانتهاء</label>
                                    <input type="text" id="date_expiry" name="date_expiry"
                                        class="form-control hijri-picker"
                                        value="{{ old('date_expiry', $powerAttorney->hijri_date_expiry) }}"
                                        autocomplete="off" onkeydown="return false;" />
                                </div>

                                <div class="col-md-12" id="expiry-warning"
                                    style="display:{{ $powerAttorney->date_expiry < now() ? 'block' : 'none' }}">
                                    <div class="alert alert-primary" role="alert">
                                        <strong>هذه الوكالة منتهية !</strong> إذا قمت بتعديل تاريخ الانتهاء إلى تاريخ حديث،
                                        سيتم تحديث حالة الوكالة إلى
                                        <strong>سارية</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="additional-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="file_attachment" class="form-label">ارفق الوكالة </label>
                                    <input type="file" id="file_attachment" name="file_attachment"
                                        class="form-control" />
                                    @if ($powerAttorney->file_attachment)
                                        <div class="mt-2 text-end">
                                            <a href="{{ asset('storage/' . $powerAttorney->file_attachment) }}"
                                                target="_blank" class="btn btn-sm btn-info">عرض المرفق الحالي</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $powerAttorney->notes) }}</textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
