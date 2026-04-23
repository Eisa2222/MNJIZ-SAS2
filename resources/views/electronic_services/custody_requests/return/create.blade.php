@extends('layouts.layoutMaster')

@section('title', 'طلب إرجاع عهدة')

@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.custody-requests.index') }}">طلبات العهد</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">طلب إرجاع عهدة</a>
        <i class="ti ti-star favorite-icon" data-page-name="طلب إرجاع عهدة" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/electronic-services/custody_requests_wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#return-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">بيانات الإرجاع</span>
                                <span class="bs-stepper-subtitle">تفاصيل الإرجاع</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">
                    <form id="form" action="{{ route('account.electronic-services.custody-requests.return.store') }}"
                        method="POST">
                        @csrf
                        <div id="return-info" class="content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="parent_request_id" class="form-label">
                                        طلب العهدة الأصلي <span class="text-danger">*</span>
                                    </label>
                                    <select id="parent_request_id" name="parent_request_id"
                                        class="form-select select2 @error('parent_request_id') is-invalid @enderror"
                                        data-placeholder="اختر طلب العهدة الأصلي">
                                        <option value=""></option>
                                        @foreach ($assignRequests as $req)
                                            <option value="{{ $req->id }}"
                                                {{ old('parent_request_id') == $req->id ? 'selected' : '' }}>
                                                {{ $req->item->name }}
                                                ({{ $req->item->serial_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_request_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="return_status" class="form-label">سبب الارجاع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="return_status" name="return_status" class="form-select select2"
                                        data-placeholder="اختر سبب الارجاع" required>
                                        <option value="">اختر سبب الارجاع</option>
                                        @foreach ($statusOptions as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('return_status') == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-12">
                                    <label for="notes" class="form-label">ملاحظة</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                        placeholder="اضف ملاحظة">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex flex-row-reverse mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">
                                    حفظ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
