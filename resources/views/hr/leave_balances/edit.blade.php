@extends('layouts.layoutMaster')

@section('title', 'تعديل رصيد إجازة')

@section('breadcrumb')
<li><a href="#"> الموارد البشرية</a></li>
<li><a href="{{ route('hr.leave-balances.index') }}">أرصدة الإجازات</a></li>
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تعديل رصيد إجازة</a>
    <i class="ti ti-star favorite-icon" data-page-name="تعديل رصيد إجازة" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('vendor-style')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

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
                            <span class="bs-stepper-subtitle">البيانات الاساسية</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="bs-stepper-content">
                <form id="leave-balance-form" action="{{ route('hr.leave-balances.update', $balance->id) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div id="balance-info" class="content">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="employee_name">الموظف</label>
                                <input type="text" id="employee_name" class="form-control"
                                    value="{{ $balance->employee->name ?? '' }}" disabled>
                                <input type="hidden" name="employee_id" value="{{ $balance->employee_id }}">
                            </div>
                            <div class="col-md-6">
                                <label for="total_days" class="form-label">إجمالي الأيام
                                    <i id="total-days-tooltip" class="fa fa-question-circle text-primary"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="إجمالي عدد أيام الإجازة المستحقة للموظف"></i>
                                </label>
                                <input type="number" id="total_days" name="total_days" class="form-control"
                                    value="{{ old('total_days', is_int($balance->total_days) ? $balance->total_days : number_format($balance->total_days, 4, '.', '')) }}"
                                    min="0" step="0.0001" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="used_days" class="form-label">الأيام المستخدمة
                                    <i id="used-days-tooltip" class="fa fa-question-circle text-primary"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="عدد أيام الإجازة التي استخدمها الموظف"></i>
                                </label>
                                <input type="number" id="used_days" name="used_days" class="form-control"
                                    value="{{ old('used_days', is_int($balance->used_days) ? $balance->used_days : number_format($balance->used_days, 4, '.', '')) }}"
                                    min="0" step="0.0001" required>
                            </div>
                            <div class="col-md-6">
                                <label for="remaining_days" class="form-label">الأيام المتبقية</label>
                                <input type="text" id="remaining_days" name="remaining_days" class="form-control"
                                    value="{{ old('remaining_days', is_int($balance->remaining_days) ? $balance->remaining_days : number_format($balance->remaining_days, 4, '.', '')) }}"
                                    readonly disabled>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="ti ti-arrow-right ti-xs me-2"></i>السابق
                            </button>
                            <button class="btn btn-primary btn-submit">تحديث</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection