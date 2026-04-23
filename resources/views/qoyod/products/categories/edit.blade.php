@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات تصنيف المنتجات ')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.categories.index') }}"> أصناف المنتجات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> تعديل بيانات تصنيف المنتجات </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات  تصنيف المنتجات "
            data-page-url="{{ url()->current() }}" onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/qoyod/products/categories/categories-request-wizard.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل تصنيف المنتجات</span>
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
                    <form id="form" action="{{ route('qoyod.categories.update',$category['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div id="basic-info" class="content dstepper-block">

                            <div class="row  g-3">

                                <div class="col-md-6">
                                    <label for="name" class="form-label">اسم الصنف</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $category['name']) }}" required />
                                </div>



                                <div class="col-sm-6">
                                    <label class="form-label" for="parent_id">الصنف الاساس</label>
                                    <select class="select2 form-select" id="parent_id" name="parent_id"
                                        data-placeholder="اختر الصنف الاساس">
                                        <option value=""></option>
                                        @foreach ($basic_category['categories'] as $item)
                                            <option value="{{ $item['id'] }}"
                                                {{ old('parent_id',$category['parent_id']) == $item['id'] ? 'selected' : '' }}>
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <span class="text-danger">*</span>
                                    <textarea name="description" class="form-control" required id="description" rows="3">{{ old('description', $category['description']) }}</textarea>
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
