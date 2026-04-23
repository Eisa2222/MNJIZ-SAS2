@extends('layouts.layoutMaster')

@section('title', 'إضافة نتائج الحملة')

@section('breadcrumb')
    <li><a href="#">التسويق</a></li>
    <li><a href="{{ route('marketing.campaign-management.index') }}">إدارة الحملات الإعلانية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة نتائج الحملة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة نتائج الحملة" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/marketing/campaign-management/campaign-results-management-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل نتائج الحملة الإعلانية </span>
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
                    <form id="form" action="{{ route('marketing.campaign-management.campaign-results.store',$campaign_management->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                {{-- تاريخ التقرير --}}
                                <div class="col-md-6">
                                    <label for="report_date" class="form-label">تاريخ التقرير</label>
                                    <input type="date" name="report_date" id="report_date" class="form-control"
                                        value="{{ old('report_date',$campaign_result?$campaign_result->report_date->format("Y-m-d"):'') }}" required>
                                </div>

                                {{-- الإنفاق الفعلي --}}
                                <div class="col-md-6">
                                    <label for="spend" class="form-label">الإنفاق الفعلي</label>
                                    <input type="number" name="spend" id="spend" step="0.01" class="form-control"
                                        value="{{ old('spend',$campaign_result->spend??'') }}">
                                </div>

                                {{-- مرات الظهور --}}
                                <div class="col-md-6">
                                    <label for="impressions" class="form-label">مرات الظهور</label>
                                    <input type="number" name="impressions" id="impressions" class="form-control"
                                        value="{{ old('impressions',$campaign_result->impressions??'') }}">
                                </div>

                                {{-- النقرات --}}
                                <div class="col-md-6">
                                    <label for="clicks" class="form-label">عدد النقرات</label>
                                    <input type="number" name="clicks" id="clicks" class="form-control"
                                        value="{{ old('clicks',$campaign_result->clicks??'') }}">
                                </div>

                                {{-- معدل النقر CTR --}}
                                <div class="col-md-6">
                                    <label for="ctr" class="form-label">معدل النقر (CTR)</label>
                                    <input type="number" name="ctr" id="ctr" step="0.01" class="form-control"
                                        value="{{ old('ctr',$campaign_result->ctr??'') }}">
                                </div>

                                {{-- تكلفة النقرة CPC --}}
                                <div class="col-md-6">
                                    <label for="cpc" class="form-label">تكلفة النقرة (CPC)</label>
                                    <input type="number" name="cpc" id="cpc" step="0.01" class="form-control"
                                        value="{{ old('cpc',$campaign_result->cpc??'') }}">
                                </div>

                                {{-- عدد التحويلات --}}
                                <div class="col-md-6">
                                    <label for="conversions" class="form-label">عدد التحويلات</label>
                                    <input type="number" name="conversions" id="conversions" class="form-control"
                                        value="{{ old('conversions',$campaign_result->conversions??'') }}">
                                </div>

                                {{-- قيمة التحويلات --}}
                                <div class="col-md-6">
                                    <label for="conversion_value" class="form-label">قيمة التحويلات</label>
                                    <input type="number" name="conversion_value" id="conversion_value" step="0.01"
                                        class="form-control" value="{{ old('conversion_value',$campaign_result->conversion_value??'') }}">
                                </div>

                                {{-- ROAS --}}
                                <div class="col-md-6">
                                    <label for="roas" class="form-label">العائد على الإنفاق (ROAS)</label>
                                    <input type="number" name="roas" id="roas" step="0.01"
                                        class="form-control" value="{{ old('roas',$campaign_result->roas??'') }}">
                                </div>

                                {{-- ملاحظات --}}
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes',$campaign_result->notes??'') }}</textarea>
                                </div>
                            </div>


                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
