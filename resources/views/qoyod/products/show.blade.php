@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المنتج')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.products.index') }}">إدارة المنتجات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل المنتج</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المنتج" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-5">
        <div class="col-12 col-md-8 mb-4">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل المنتج
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">نوع المنتج </td>
                                <td>{{ $productType }}</td>

                            </tr>
                            <tr>
                                <td class="fw-bold">الرقم التسلسلي</td>
                                <td>{{ $product['sku'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الاسم بالعربي </td>
                                <td>{{ $product['name_ar'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الاسم بالانجليزي</td>
                                <td>{{ $product['name_en'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الوصف</td>
                                <td>{{ $product['description'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الصنف </td>
                                <td>{{ $categoryName }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">وحدة القياس</td>
                                <td>{{ $unitName }}</td>
                            </tr>
                            @if ($product['type'] == 'Service')
                                <tr>
                                    <td class="fw-bold">سعر البيع</td>
                                    <td>{{ $product['selling_price'] }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">حساب المبيعات </td>
                                    <td>{{ $salesAccountName }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="fw-bold">سعر الشراء</td>
                                    <td>{{ $product['buying_price'] }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">حساب المصروفات </td>
                                    <td>{{ $expenseAccountName }}</td>
                                </tr>
                            @endif


                            <tr>
                                <td class="fw-bold">الضريبة</td>
                                <td>{{ $taxType }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">سبب الضريبة</td>
                                <td>{{ $specialTaxReasonName }}</td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            {{-- سجل النشاط --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <ul class="timeline ">
                        <li class="timeline-item">
                            <span class="timeline-point bg-secondary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ \Carbon\Carbon::parse($product['created_at'])->format('Y-m-d H:i') }}
                                </small>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <span class="timeline-point"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    اخر تحديث بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ \Carbon\Carbon::parse($product['updated_at'])->format('Y-m-d H:i') }}
                                </small>
                                <small>
                                    <b> بواسطة</b>
                                    {{ empty($product['updated_by']) ? 'غير محدد' : $product['updated_by'] }}
                                </small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>



@endsection
