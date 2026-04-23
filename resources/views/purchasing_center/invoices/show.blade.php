@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الفاتورة')

@section('breadcrumb')
    <li><a href="{{ route('purchasing-center.purchase-requests.invoices.index') }}"> المشتريات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تفاصيل الفاتورة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الفاتورة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite('resources/assets/vendor/libs/flatpickr/flatpickr.scss')
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-invoice.scss')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/offcanvas-add-payment.js', 'resources/assets/js/offcanvas-send-invoice.js'])
@endsection


@section('content')

    <div class="row invoice-preview">
        <!-- Invoice -->
        <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6">
            <div class="card invoice-preview-card p-sm-12 p-6">
                <div class="card-body invoice-preview-header rounded">
                    <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column">
                        <div class="mb-xl-0 mb-6 text-heading">
                            <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                                <div class="app-brand-logo demo">@include('_partials.macros', ['height' => 22, 'withbg' => ''])</div>
                                <span class="app-brand-text fw-bold fs-6 ms-50">
                                    {{ $setting->office_name }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-6">فاتورة #{{ $invoice->invoice_number }}</h5>
                            <div class="mb-1 text-heading">
                                <span>تاريخ الاصدار</span>
                                <span>{{ $invoice->invoice_date }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 table-responsive border border-bottom-0 border-top-0 rounded">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>التصنيف</th>
                                <th>سعر الوحدة</th>
                                <th>الكمية</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->purchases as $purchase)
                                <tr>
                                    <td class="text-nowrap text-heading">{{ $purchase->item_name }}</td>
                                    <td class="text-nowrap">{{ $purchase->category->name }}</td>
                                    <td>{{ $purchase->item_price }}</td>
                                    <td>{{ $purchase->item_quantity }}</td>
                                    <td>{{ $purchase->item_price * $purchase->item_quantity }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3"></td>
                                <td class="fw-bold">المجموع</td>
                                <td class="fw-bold">{{ $invoice->total_amount }}</td>
                            </tr>
                        </tbody>
                        </tbody>
                    </table>
                </div>


                <hr class="mt-0 mb-6">
                <div class="card-body p-0 text-end">
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-1 small">
                                <span class="me-2 h6">اصدرت بواسطة:</span>
                                <a href="{{ route('account.employee.profile', $invoice->user->employee->id) }}">
                                    <span>{{ $invoice->user->name }}</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Invoice -->

        <!-- Invoice Actions -->
        <div class="col-xl-3 col-md-4 col-12 invoice-actions">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('purchasing-center.purchase-requests.invoices.pdf', $invoice->id) }}" class="btn btn-primary d-grid w-100 mb-4">
                        تحميل PDF
                    </a>
                    <div class="d-flex mb-4">
                        <a href="{{ route('purchasing-center.purchase-requests.invoices.edit', $invoice->id) }}" class="btn btn-label-secondary d-grid w-100">
                            تعديل
                        </a>
                    </div>
                </div>
            </div>

            <!-- المرفق -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">المرفق</h5>
                </div>
                <div class="card-body">
                    @if ($invoice->attachment)
                        <div class="d-flex align-items-center">

                            <i class="ti ti-file me-2"></i>
                            <a href="{{ asset('storage/' . $invoice->attachment) }}" class="text-body" download style="word-break: break-all;">تحميل الملف</a>
                            </a>

                            @if (file_exists(storage_path('app/public/' . $invoice->attachment)))
                                <span class="text-muted ms-auto">
                                    {{ round(filesize(storage_path('app/public/' . $invoice->attachment)) / 1024) }}
                                    كيلوبايت
                                </span>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0 text-center">لا يوجد مرفق</p>
                    @endif
                </div>
            </div>
            <!-- /المرفق -->
        </div>
    </div>

@endsection
