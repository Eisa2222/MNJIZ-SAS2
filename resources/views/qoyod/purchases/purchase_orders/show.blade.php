@extends('layouts.layoutMaster')

@section('title', 'تفاصيل أمر الشراء')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.purchase-orders.index') }}">أوامر الشراء</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل أمر الشراء</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل أمر الشراء" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل أمر الشراء
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">المرجع</td>
                                <td>{{ $purchaseOrder['reference'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">المورد</td>
                                <td>{{ $purchaseOrder['contact_id'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تاريخ الإصدار</td>
                                <td>{{ $purchaseOrder['issue_date'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تاريخ الاستحقاق</td>
                                <td>{{ $purchaseOrder['expiry_date'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>{{ $purchaseOrder['status'] == 'Approved' ? 'موافق عليه' : 'مسودة' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الموقع</td>
                                <td>{{ $purchaseOrder['inventory_name_from_relation'] }}</td>
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
                        <li class="timeline-item py-5">
                            <span class="timeline-point bg-secondary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ \Carbon\Carbon::parse($purchaseOrder['created_at'])->format('Y-m-d H:i') }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    {{ empty($purchaseOrder['created_by']) ? 'غير محدد' : $purchaseOrder['created_by'] }}
                                </small>
                            </div>
                        </li>
                        <li class="timeline-item py-5">
                            <span class="timeline-point"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    اخر تحديث بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ \Carbon\Carbon::parse($purchaseOrder['updated_at'])->format('Y-m-d H:i') }}
                                </small>
                                <small>
                                    <b> بواسطة</b>
                                    {{ empty($purchaseOrder['updated_by']) ? 'غير محدد' : $purchaseOrder['updated_by'] }}
                                </small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- البنود --}}
        {{-- البنود --}}
        <div class="col-12 ">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-barcode text-warning me-2"></i>
                        المنتجات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <thead>
                                <tr>
                                    <td class="fw-bold">#</td>
                                    <td class="fw-bold">المنتج</td>
                                    <td class="fw-bold">الكمية</td>
                                    <td class="fw-bold">سعر الوحدة</td>
                                    <td class="fw-bold">الخصم</td>
                                    <td class="fw-bold">الإجمالي قبل الضريبة</td>
                                    <td class="fw-bold">الضريبة %</td>
                                    <td class="fw-bold">قيمة الضريبة</td>
                                    <td class="fw-bold">الإجمالي</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrder['line_items'] as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ number_format($item['unit_price'], 2) }}</td>
                                        <td>{{ number_format($item['discount_amount'], 2) }}</td>
                                        <td>
                                            {{ number_format($item['quantity'] * $item['unit_price'] - $item['discount_amount'], 2) }}
                                        </td>
                                        <td>{{ $item['tax_percent'] }}%</td>
                                        <td>{{ number_format($item['unrounded_vat_value'], 2) }}</td>
                                        <td>{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="fw-bold text-danger">إجمالي الخصم</td>
                                    <td class="fw-bold">الإجمالي قبل الضريبة</td>
                                    <td></td>
                                    <td class="fw-bold">إجمالي الضريبة</td>
                                    <td class="fw-bold">المجموع النهائي</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="fw-bold">{{ number_format($purchaseOrder['total_discount'] ?? 0, 2) }} <span
                                            class="icon-saudi_riyal"></span></td>
                                    <td class="fw-bold">{{ number_format($purchaseOrder['subtotal'] ?? 0, 2) }} <span
                                            class="icon-saudi_riyal"></span></td>
                                    <td></td>
                                    <td class="fw-bold">{{ number_format($purchaseOrder['tax_amount'] ?? 0, 2) }} <span
                                            class="icon-saudi_riyal"></span></td>
                                    <td class="fw-bold">{{ number_format($purchaseOrder['total_amount'] ?? 0, 2) }} <span
                                            class="icon-saudi_riyal"></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- ملخص المبالغ -->
        <div class="col-7"></div>
        <div class="col-5">
            <div class="card p-5">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">الإجمالي قبل الضريبة</span>
                    <span class="fw-bold">{{ number_format($purchaseOrder['subtotal'] ?? 0, 2) }}
                        <span class="icon-saudi_riyal mx-2"></span>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">قيمة الضريبة</span>
                    <span class="fw-bold">{{ number_format($purchaseOrder['tax_amount'] ?? 0, 2) }}
                        <span class="icon-saudi_riyal mx-2"></span>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-3">
                    <span class="fw-bold text-primary ">المجموع النهائي</span>
                    <span class="fw-bold text-primary ">{{ number_format($purchaseOrder['total_amount'] ?? 0, 2) }}
                        <span class="icon-saudi_riyal mx-2"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

@endsection
