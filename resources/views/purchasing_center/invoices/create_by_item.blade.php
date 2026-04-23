@extends('layouts.layoutMaster')

@section('title', 'إضافة مشتريات')

@section('breadcrumb')

    <li><a href="#"> المشتريات</a></li>
    <li><a href="{{ route('purchasing-center.purchase-requests.index') }}"> طلبات المشتريات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة مشتريات جديدة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة مشتريات جديدة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-invoice.scss')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/offcanvas-send-invoice.js', 'resources/assets/js/app-invoice-add.js'])
@endsection

@section('content')
    <div class="row invoice-add">
        <!-- نموذج الفاتورة الرئيسي -->
        <form id="invoiceForm" action="{{ route('purchasing-center.purchase-requests.invoices.storeByItem',$purchase_request->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- إضافة فاتورة -->
                <div class="col-lg-9 col-12 mb-lg-0 mb-6">
                    <div class="card invoice-preview-card p-sm-12 p-6">
                        <div class="card-body invoice-preview-header rounded">
                            <div class="d-flex flex-wrap flex-column flex-sm-row justify-content-between text-heading">
                                <div class="mb-md-0 mb-6">
                                    <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                                        <div class="app-brand-logo demo">@include('_partials.macros', ['height' => 22, 'withbg' => ''])</div>
                                        <span class="app-brand-text fw-bold fs-6 ms-50">
                                            {{ $setting->office_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-5 col-8 pe-0 ps-0 ps-md-2">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5 mb-2 d-md-flex align-items-center justify-content-end">
                                            <span class="h6 text-capitalize mb-0 text-nowrap">فاتورة</span>
                                        </dt>
                                        <dd class="col-sm-7">
                                            <div class="input-group input-group-merge disabled">
                                                <span class="input-group-text">#</span>
                                                <input type="text" class="form-control" disabled
                                                    value="{{ $next_number }}" id="" />
                                            </div>
                                        </dd>
                                        <dt class="col-sm-5 mb-2 d-md-flex align-items-center justify-content-end">
                                            <span class="fw-normal h6">تاريخ الإصدار</span>
                                        </dt>
                                        <dd class="col-sm-7">
                                            <input type="text" name="" disabled class="form-control"
                                                value="{{ date('Y-m-d') }}" />

                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6">
                        <div class="card-body pt-0 px-0 mb-6">
                            <div class="source-item">
                                <div class="mb-4" data-repeater-list="invoice_items">
                                    <div class="repeater-wrapper pt-0" data-repeater-item>
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-6">
                                                <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                    <p class="h6 mb-1">الاسم</p>
                                                    <input type="text" name="item_name" required
                                                        value="{{ $purchase_request->item_name }}"
                                                        class="form-control invoice-item-name mb-5"
                                                        placeholder="اسم العنصر" />
                                                </div>
                                                <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                    <p class="h6 mb-1">التصنيف</p>
                                                    <select name="purchase_category_id" required
                                                        class="form-control select2 mb-6" data-placeholder="اختر التصنيف">
                                                        <option value=""></option>
                                                        @foreach ($purchase_category as $item)
                                                            <option value="{{ $item->id }}"
                                                                {{ $purchase_request->purchase_category_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                    <p class="h6 mb-1">الكمية</p>
                                                    <input type="text" name="item_quantity" required   value="{{ $purchase_request->item_quantity }}"
                                                        class="form-control invoice-item-qty" placeholder="1" min="1"
                                                        max="50" />
                                                </div>
                                                <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                    <p class="h6 mb-1">سعر الوحدة</p>
                                                    <input type="text" name="item_price" required   value="{{ $purchase_request->item_price }}"
                                                        class="form-control invoice-item-price mb-5" placeholder="24"
                                                        min="12" />
                                                </div>
                                                <div class="col-md-12 col-12 mb-md-0 mb-4">
                                                    <p class="h6 mb-1">ملاحظة </p>
                                                    <textarea name="item_description" id="item_description" rows="2" class="form-control" placeholder="  تفاصيل أو ملاحظات حول هذا العنصر">{{$purchase_request->item_description }}</textarea>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                                <i class="ti ti-x ti-lg cursor-pointer" data-repeater-delete></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-sm btn-primary" data-repeater-create>
                                            <i class='ti ti-plus ti-14px me-1_5'></i>إضافة عنصر
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- إجراءات الفاتورة -->
                <div class="col-lg-3 col-12 invoice-actions">
                    <div class="card mb-6">
                        <div class="card-body">
                            <div>
                                <label for="attachment" class="form-label">المرفق</label>
                                <div class="mb-3">
                                    <input type="file" name="attachment" class="form-control" id="attachment">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary d-grid w-100 my-5">
                                <span class="d-flex align-items-center justify-content-center text-nowrap">
                                    <i class="ti ti-send ti-xs me-2"></i>حفظ الفاتورة
                                </span>
                            </button>
                        </div>
                    </div>

                </div>
                <!-- /إجراءات الفاتورة -->
            </div>
        </form>
    </div>

@endsection
