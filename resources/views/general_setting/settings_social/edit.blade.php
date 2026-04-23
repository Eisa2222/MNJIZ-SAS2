@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات موقع التواصل')

@section('breadcrumb')
    <li><a href="{{ route('settings-social.index') }}">إعدادات مواقع التواصل</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل بيانات موقع التواصل</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات موقع التواصل" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/settings/settings_social-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">بيانات الاتصال الرئيسية</span>
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
                    <form id="form" action="{{ route('settings-social.update', $setting->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">



                                <div class="col-12 col-md-6 mb-4">
                                    <label for="api_key" class="form-label">API KEY</label>
                                    <input type="password" name="api_key" id="api_key" class="form-control" required
                                        placeholder="••••••••" />
                                    @error('api_key')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 mb-4">
                                    <label for="api_secret" class="form-label">API SECRET</label>
                                    <input type="password" name="api_secret" id="api_secret" required class="form-control"
                                        placeholder="••••••••" />
                                    @error('api_secret')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 mb-4">
                                    <label for="access_token" class="form-label">ACCESS TOKEN</label>
                                    <input type="password" name="access_token" id="access_token" required
                                        class="form-control" placeholder="••••••••" />
                                    @error('access_token')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 mb-4">
                                    <label for="access_token_secret" class="form-label">ACCESS TOKEN SECRET</label>
                                    <input type="password" name="access_token_secret" id="access_token_secret" required
                                        class="form-control" placeholder="••••••••" />
                                    @error('access_token_secret')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
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
