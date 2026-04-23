@extends('layouts.layoutMaster')

@section('title', 'إضافة رصيد إجازة')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.leave-balances.index') }}">أرصدة الإجازات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> إضافة رصيد إجازة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة رصيد إجازة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

{{-- Vendor Styles --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

{{-- Page Scripts --}}
@section('page-script')
    @vite(['resources/assets/js/leave-balance-wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#balance-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">بيانات الرصيد</span>
                                <span class="bs-stepper-subtitle">الموظف، السنة، عدد الأيام</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">
                    <form id="leave-balance-form" action="{{ route('hr.leave-balances.store') }}" method="POST">
                        @csrf
                        <div id="balance-info" class="content">
                            {{-- الصف الأول --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="employee_id">الموظف</label>
                                    <select id="employee_id" name="employee_id" class="form-select select2"
                                        data-placeholder="اختر الموظف" required>
                                        <option value=""></option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <div class="col-md-6">
                                    <label class="form-label" for="year">السنة</label>
                                    <select id="year" name="year" class="form-select select2"
                                        data-placeholder="اختر السنة" required>
                                        <option value=""></option>
                                        @for ($y = date('Y') + 1; $y >= 2000; $y--)
                                            <option value="{{ $y }}" {{ old('year') == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div> --}}
                            </div>

                            {{-- الصف الثاني --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="total_days">إجمالي الأيام
                                        <i id="total-days-tooltip" class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="إجمالي عدد أيام الإجازة المستحقة للموظف"></i>
                                    </label>
                                    <input type="number" id="total_days" name="total_days" class="form-control"
                                        value="{{ old('total_days', 0) }}" min="0" step="0.5" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="used_days">الأيام المستخدمة
                                        <i id="used-days-tooltip" class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="عدد أيام الإجازة التي استخدمها الموظف"></i>
                                    </label>
                                    <input type="number" id="used_days" name="used_days" class="form-control"
                                        value="{{ old('used_days', 0) }}" min="0" step="0.5" required>
                                </div>
                            </div>

                            {{-- الصف الثالث - الرصيد المتبقي --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="remaining_days">الأيام المتبقية</label>
                                    <input type="text" id="remaining_days" name="remaining_days" class="form-control"
                                        value="{{ old('remaining_days', 0) }}" readonly>
                                </div>
                            </div>

                            {{-- أزرار التنقل --}}
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                    <span class="d-sm-inline d-none">السابق</span>
                                </button>
                                <button class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
