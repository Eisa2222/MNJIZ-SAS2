@extends('layouts.layoutMaster')

@section('title', 'إضافة حملة')

@section('breadcrumb')
    <li><a href="#">التسويق</a></li>
    <li><a href="{{ route('marketing.campaign-management.index') }}">إدارة الحملات الإعلانية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة حملة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة حملة" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/marketing/campaign-management/campaign-management-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل الحملة الإعلانية </span>
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
                    <form id="form" action="{{ route('marketing.campaign-management.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                {{-- نوع المحتوى --}}
                                {{-- اسم الحملة --}}
                                <div class="col-md-6">
                                    <label for="campaign_name" class="form-label">اسم الحملة</label>
                                    <input type="text" name="campaign_name" id="campaign_name" class="form-control"
                                        required value="{{ old('campaign_name') }}">
                                </div>

                                {{-- نوع الحملة --}}
                                <div class="col-md-6">
                                    <label for="content_type_id" class="form-label">نوع الحملة</label>
                                    <select class="form-select select2" name="content_type_id" id="content_type_id"
                                        data-placeholder="اختر نوع الحملة" required>
                                        <option value=""></option>
                                        @foreach ($contentTypes as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('content_type_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- القسم --}}
                                <div class="col-md-6">
                                    <label for="campaign_section_id" class="form-label">قسم الحملة</label>
                                    <select class="form-select select2" name="campaign_section_id" id="campaign_section_id"
                                        data-placeholder="اختر قسم الحملة">
                                        <option value=""></option>
                                        @foreach ($campaignSections as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('campaign_section_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- الهدف --}}
                                <div class="col-md-6">
                                    <label for="content_purpose_id" class="form-label">الهدف من الحملة</label>
                                    <select class="form-select select2" name="content_purpose_id" id="content_purpose_id"
                                        data-placeholder="اختر الهدف من الحملة " required>
                                        <option value=""></option>
                                        @foreach ($contentPurposes as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('content_purpose_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- المنصة --}}
                                <div class="col-md-6">
                                    <label for="social_id" class="form-label">المنصة الإعلانية</label>
                                    <select class="form-select select2" name="social_id" id="social_id"
                                        data-placeholder="اختر المنصة الإعلانية" required>
                                        <option value=""></option>
                                        @foreach ($socialPlatforms as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('social_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- الميزانية --}}
                                <div class="col-md-6">
                                    <label for="budget" class="form-label">الميزانية الإجمالية</label>
                                    <input type="number" step="0.01" name="budget" id="budget" class="form-control"
                                        value="{{ old('budget') }}">
                                </div>

                                {{-- تاريخ البدء --}}
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">تاريخ بدء الحملة</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date') }}">
                                </div>

                                {{-- تاريخ الانتهاء --}}
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">تاريخ نهاية الحملة</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="{{ old('end_date') }}">
                                </div>

                                {{-- الجمهور المستهدف --}}
                                <div class="col-md-6">
                                    <label for="target_audience_id" class="form-label">الجمهور المستهدف</label>
                                    <select class="form-select select2" name="target_audience_id" id="target_audience_id"
                                        data-placeholder="اختر الجمهور المستهدف">
                                        <option value=""></option>
                                        @foreach ($targetAudiences as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('target_audience_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                {{-- النص الإعلاني --}}
                                <div class="col-md-12">
                                    <label for="text" class="form-label">النص الإعلاني</label>
                                    <textarea name="text" id="text" rows="3" class="form-control">{{ old('text') }}</textarea>
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
