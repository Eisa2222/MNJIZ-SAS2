@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المورد')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.vendors.index') }}">الموردين</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل المورد</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المورد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection



@section('vendor-style')
    @vite(['resources/assets/css/tab.css','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss','resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/css/tab.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/tab.js','resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js','resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js','resources/assets/js/tab.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs justify-content-between border-bottom-0 flex-column">
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#vendor_details">
                            <i class="ti ti-user ti-sm me-1_5"></i>
                            <span class="align-middle">تفاصيل المورد</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#vendor_bills">
                            <i class="ti ti-invoice ti-sm me-1_5"></i>
                            <span class="align-middle">الفواتير</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#microsoft">
                            <i class="ti ti-receipt ti-sm me-1_5"></i>
                            <span class="align-middle">السندات</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">
                <!-- التبويبات الجانبية   -->
                @include('qoyod.vendors.partials.vendor_details')
                @include('qoyod.vendors.partials.vendor_bills')
            </div>
        </div>
    </div>


@endsection
