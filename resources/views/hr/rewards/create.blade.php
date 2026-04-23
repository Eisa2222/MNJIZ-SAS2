@extends('layouts.layoutMaster')

@section('title', 'إضافة مكافأة')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('hr.rewards.index') }}">المكافأت</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة مكافأة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة مكافأة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/hr/rewards/rewards-request-wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل المكافأة </span>
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
                    <form id="form" action="{{ route('hr.rewards.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">
                                        الموظف
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="employee_id" name="employee_id" class="form-select select2"
                                        data-placeholder="اختر الموظف" required>
                                        <option value=""></option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->getRawNameAttribute() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="reward_type" class="form-label">نوع المكافأة</label>
                                    <select id="reward_type" name="reward_type" class="form-select select2"
                                        data-placeholder="اختر نوع المكافأة" required>
                                        <option value="">اختر النوع</option>
                                        @foreach ($rewardTypes as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('reward_type') == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label for="amount" class="form-label">القيمة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" id="amount" name="amount" class="form-control"
                                        value="{{ old('amount') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="reward_date" class="form-label">تاريخ المكافأة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="reward_date" name="reward_date" class="form-control"
                                        value="{{ old('reward_date') }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">الوصف/الملاحظات</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
