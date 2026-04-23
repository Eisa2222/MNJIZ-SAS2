@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات المشتريات')

@section('breadcrumb')
    <li><a href="{{ route('purchasing-center.purchase-requests.invoices.index') }}"> المشتريات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تعديل بيانات المشتريات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات المشتريات" data-page-url="{{ url()->current() }}"
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
    <div class="row invoice-edit">
        <!-- نموذج تعديل الفاتورة -->
        <form id="invoiceForm" action="{{ route('purchasing-center.purchase-requests.invoices.update', $invoice->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- بيانات الفاتورة -->
                <div class="col-lg-9 col-12 mb-lg-0 mb-6">
                    <div class="card invoice-preview-card p-sm-12 p-6">
                        <div class="card-body invoice-preview-header rounded">
                            <div class="d-flex flex-wrap flex-column flex-sm-row justify-content-between text-heading">
                                <div class="mb-md-0 mb-6">
                                    <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                                        <div class="app-brand-logo demo">
                                            @include('_partials.macros', ['height' => 22, 'withbg' => ''])
                                        </div>
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
                                                    value="{{ $invoice->invoice_number }}" />
                                            </div>
                                        </dd>
                                        <dt class="col-sm-5 mb-2 d-md-flex align-items-center justify-content-end">
                                            <span class="fw-normal h6">تاريخ الإصدار</span>
                                        </dt>
                                        <dd class="col-sm-7">
                                            <input type="text" name="invoice_date" disabled class="form-control"
                                                value="{{ $invoice->invoice_date }}" />
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6">
                        <div class="card-body pt-0 px-0 mb-6">
                            <div class="source-item">
                                <!-- تكرار عناصر الفاتورة -->
                                <div class="mb-4" data-repeater-list="invoice_items">
                                    @foreach ($invoice->purchases as $purchase)
                                        <div class="repeater-wrapper pt-0" data-repeater-item>
                                            <div class="d-flex border rounded position-relative pe-0">
                                                <div class="row w-100 p-6">
                                                    <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                        <p class="h6 mb-1">الاسم</p>
                                                        <input type="text" name="item_name"
                                                            value="{{ $purchase->item_name }}" required
                                                            class="form-control invoice-item-name mb-5"
                                                            placeholder="اسم العنصر" />
                                                    </div>
                                                    <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                        <p class="h6 mb-1">التصنيف</p>
                                                        <select name="purchase_category_id" required
                                                            class="form-control select2 mb-6"
                                                            data-placeholder="اختر التصنيف">
                                                            <option value=""></option>
                                                            @foreach ($purchase_category as $item)
                                                                <option value="{{ $item->id }}"
                                                                    {{ $purchase->purchase_category_id == $item->id ? 'selected' : '' }}>
                                                                    {{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <p class="h6 mb-1">سعر الوحدة</p>
                                                        <input type="text" name="item_price"
                                                            value="{{ $purchase->item_price }}" required
                                                            class="form-control invoice-item-price mb-5" placeholder="24"
                                                            min="12" />
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <p class="h6 mb-1">الكمية</p>
                                                        <input type="text" name="item_quantity"
                                                            value="{{ $purchase->item_quantity }}" required
                                                            class="form-control invoice-item-qty" placeholder="1"
                                                            min="1" max="50" />
                                                    </div>
                                                    <div class="col-md-12 col-12 mb-md-0 mb-4">
                                                        <p class="h6 mb-1">ملاحظة</p>
                                                        <textarea name="item_description" rows="2" class="form-control" placeholder="  تفاصيل أو ملاحظات حول هذا العنصر">{{ $purchase->item_description }}</textarea>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                                    <i class="ti ti-x ti-lg cursor-pointer" data-repeater-delete></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
                    <!-- جزء المرفقات المعدل في صفحة التعديل -->

                    <div class="card mb-6">
                        <div class="card-body">
                            <div>
                                <label for="attachment" class="form-label">المرفق</label>
                                <div class="mb-3">
                                    <input type="file" name="attachment" class="form-control" id="attachment">
                                    @if ($invoice->attachment)
                                        <small class="text-muted">اترك هذا الحقل فارغًا للاحتفاظ بالمرفق الحالي</small>
                                    @endif
                                </div>

                                @if ($invoice->attachment)
                                    <div class="d-flex flex-column gap-3 mb-4">
                                        <a href="{{ asset('storage/' . $invoice->attachment) }}" target="_blank"
                                            class="btn btn-info">
                                            <i class="ti ti-eye me-1"></i> عرض المرفق الحالي
                                        </a>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remove_attachment"
                                                name="remove_attachment" value="1">
                                            <label class="form-check-label" for="remove_attachment">
                                                حذف المرفق الحالي
                                            </label>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-3">
                                        لا يوجد مرفق حالي
                                    </div>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary d-grid w-100 my-3">
                                <span class="d-flex align-items-center justify-content-center text-nowrap">
                                    <i class="ti ti-send ti-xs me-2"></i>حفظ التعديلات
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- إضافة سكريبت JavaScript لتعطيل حقل الملف عند اختيار حذف المرفق -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const removeAttachmentCheckbox = document.getElementById('remove_attachment');
                            const attachmentInput = document.getElementById('attachment');

                            if (removeAttachmentCheckbox && attachmentInput) {
                                removeAttachmentCheckbox.addEventListener('change', function() {
                                    if (this.checked) {
                                        attachmentInput.disabled = true;
                                        attachmentInput.classList.add('bg-light');
                                    } else {
                                        attachmentInput.disabled = false;
                                        attachmentInput.classList.remove('bg-light');
                                    }
                                });
                            }
                        });
                    </script>

                </div>
            </div>
        </form>
    </div>
@endsection
