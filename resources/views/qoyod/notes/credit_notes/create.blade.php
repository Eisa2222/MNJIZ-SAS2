@extends('layouts.layoutMaster')

@section('title', 'إضافة إشعار دائن')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.credit-notes.index') }}">الإشعارات الدائنة</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> إضافة إشعار دائن </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة إشعار دائن" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('page-script')
    @vite(['resources/assets/js/qoyod/notes/credit-notes/credit-notes-request-wizard.js'])
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/css/qoyod/purchase-orders.css', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/qoyod/notes/credit-notes/credit-notes.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script>
        const productOptions = `{!! collect($products)->map(
                fn($p) => "<option value='{$p['id']}' data-price='{$p['buying_price']}'>{$p['sku']} - {$p['name_ar']}</option>",
            )->implode('') !!}`;
    </script>
    
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
                                <span class="bs-stepper-subtitle">العملاء والبيانات العامة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#details-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">تفاصيل فاتورة المبيعات</span>
                                <span class="bs-stepper-subtitle">التفاصيل الإضافية لفاتورة المبيعات</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form" action="{{ route('qoyod.credit-notes.store') }}" method="POST">
                        @csrf

                        <!--- الخطوة الاولى --->
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                {{-- العميل (contact_id) --}}
                                <div class="col-md-6">
                                    <label for="contact_id" class="form-label">العملاء <span
                                            class="text-danger">*</span></label>
                                    <select id="contact_id" name="contact_id" class="form-select select2"
                                        data-placeholder="اختر العميل">
                                        <option value="" selected disabled>اختر العميل</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- فاتورة المبيعات الأصلية --}}
                                <div class="col-md-6">
                                    <label for="parent_id" class="form-label">فاتورة المبيعات الأصلية
                                        <span class="text-danger">*</span></label>
                                    <select id="parent_id" name="parent_id" class="form-select select2"
                                        data-placeholder="اختر الفاتورة">
                                        <option value="">اختر الفاتورة</option>
                                        {{-- سيتم ملؤها تلقائيًا بواسطة JavaScript --}}
                                    </select>
                                    {{-- مؤشر التحميل --}}
                                    <div id="loadingIndicator" class="text-primary mt-2" style="display: none;">
                                        <i class="ti ti-loader ti-spin me-1"></i> جارٍ تحميل الفواتير...
                                    </div>
                                </div>

                                {{-- تواريخ الإصدار والاستحقاق --}}
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label">تاريخ الإصدار <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="issue_date" name="issue_date" class="form-control" required>
                                </div>



                                {{-- الموقع (inventory_id) --}}
                                <div class="col-md-6">
                                    <label for="inventory_id" class="form-label">الموقع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="inventory_id" name="inventory_id" class="form-select select2"
                                        data-placeholder="اختر الموقع">
                                        <option value=""></option>
                                        @foreach ($inventories as $inventory)
                                            <option value="{{ $inventory['id'] }}">{{ $inventory['ar_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- الحالة --}}
                                <div class="col-md-6">
                                    <label for="status" class="form-label">الحالة <span
                                            class="text-danger">*</span></label>
                                    <select id="status" name="status" class="select2 form-select"
                                        data-placeholder="اختر الحالة ">
                                        <option value="Draft" selected>مسودة</option>
                                        <option value="Approved">موافق عليها</option>
                                    </select>
                                </div>


                                {{-- سبب الاصدار --}}
                                <div class="col-md-12">
                                    <label for="issuance_reason" class="form-label">سبب الاصدار
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="issuance_reason" name="issuance_reason" rows="2" class="form-control">{{ old('issuance_reason') }}</textarea>
                                </div>

                                {{-- ملاحظات --}}
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                                </div>

                                {{-- الشروط والأحكام --}}
                                <div class="col-md-12">
                                    <label for="terms_conditions" class="form-label">الشروط والأحكام</label>
                                    <textarea id="terms_conditions" name="terms_conditions" rows="2" class="form-control">{{ old('terms_conditions') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!--- الخطوة الثانية بجدول واحد مع صفين لكل بند --->
                        <div id="details-info" class="content dstepper-block">
                            <div class="row">
                                <div class="col-12">
                                    <!-- رأس القسم -->
                                    <div class="d-flex justify-content-end align-items-center mb-3">
                                        <button type="button" id="addLineItem" class="btn btn-primary btn-sm">
                                            <i class="ti ti-plus me-1"></i>
                                            إضافة جديد
                                        </button>
                                    </div>

                                    <!-- جدول واحد للبنود -->
                                    <div class="table-responsive">
                                        <table id="lineItemsTable" class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2"
                                                        style="width: 4%; vertical-align: middle; font-size: 0.9rem;">#
                                                    </th>
                                                    <th style="width: 24%; font-size: 0.9rem;">المنتج</th>
                                                    <th style="width: 16%; font-size: 0.9rem;">الوصف</th>
                                                    <th style="width: 8%; font-size: 0.9rem;">الكمية</th>
                                                    <th style="width: 12%; font-size: 0.9rem;">سعر الوحدة</th>
                                                    <th style="width: 10%; font-size: 0.9rem;">الخصم</th>
                                                    <th rowspan="2"
                                                        style="width: 4%; vertical-align: middle; font-size: 0.9rem;">
                                                        إجراءات</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <!-- سيتم إدراج البنود هنا -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- المجاميع أسفل الجدول -->
                                    <div class="row mt-4">
                                        <div class="col-md-7"></div>
                                        <div class="col-md-5">
                                            <div class="summary-box">
                                                <div class="d-flex justify-content-between py-3 border-bottom">
                                                    <span class="text-primary fw-semibold">الإجمالي قبل
                                                        الضريبة</span>
                                                    <span class="fw-bold" id="subtotalDisplay">0.00 <span
                                                            class="icon-saudi_riyal"></span></span>
                                                </div>
                                                <div class="d-flex justify-content-between py-3 border-bottom">
                                                    <span class="text-primary fw-semibold">قيمة الضريبة</span>
                                                    <span class="fw-bold" id="taxDisplay">0.00 <span
                                                            class="icon-saudi_riyal"></span></span>
                                                </div>
                                                <div class="d-flex justify-content-between py-3 rounded">
                                                    <span class="text-primary fw-bold">المجموع</span>
                                                    <span class="fw-bold text-primary" id="totalDisplay">0.00
                                                        <span class="icon-saudi_riyal"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- رسالة عند عدم وجود بنود -->
                                    <div id="emptyMessage" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ti ti-package ti-lg mb-2 d-block"></i>
                                            <p class="mb-2">لا توجد بنود حتى الآن</p>
                                            <small>انقر على "إضافة جديد" لإضافة منتجات</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <span class="d-sm-inline-block d-none me-sm-2">حفظ</span>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>




@endsection
