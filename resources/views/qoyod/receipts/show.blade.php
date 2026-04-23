@extends('layouts.layoutMaster')


@section('title', 'تفاصيل إشعار دائن')


@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.receipts.index') }}"> الإيصالات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الإيصال</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل  الإيصال" data-page-url="{{ url()->current() }}"
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
        <div class="col-12">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الإيصال
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">رقم المرجع</td>
                                <td>{{ $receipt['reference'] }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الجهة</td>
                                <td>{{ $receipt['contact_name'] ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الحساب</td>
                                <td>{{ $receipt['account_name'] }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">النوع</td>
                                <td>{{ $receipt['kind'] == 'paid' ? 'صرف' : 'قبض' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">المبلغ</td>
                                <td>{{ number_format($receipt['amount'], 2) }} <span class="icon-saudi_riyal"></span> </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">التاريخ </td>
                                <td>{{ $receipt['date'] }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الوصف</td>
                                <td>{{ $receipt['description'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 ">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-barcode text-warning me-2"></i>
                        تخصيصات السند
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body px-5">
                    @if (empty($receipt['allocations']))
                        <p class="fw-bold text-center text-primary">لا يوجد بيانات حاليا </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped small text-center">
                                <thead>
                                    <tr>
                                        <td class="fw-bold">#</td>
                                        <td class="fw-bold">التاريخ</td>
                                        <td class="fw-bold">المخصص له</td>
                                        <td class="fw-bold">رقم المرجع</td>
                                        <td class="fw-bold">المبلغ </td>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($receipt['allocations'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item['date'])->format('Y-m-d') }}</td>
                                            <td>
                                                @if ($item['allocatee_type'] == 'Invoice')
                                                    فاتورة مبيعات
                                                @elseif($item['allocatee_type'] == 'Bill')
                                                    فاتورة مشتريات
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>{{ $item['allocatee_name']??"-" }}</td>
                                            <td>{{ number_format($item['amount'], 2) }} <span
                                                    class="icon-saudi_riyal"></span></td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="fw-bold">المجموع</td>
                                        <td class="fw-bold">
                                            {{ number_format($receipt['total'], 2) }} <span
                                                class="icon-saudi_riyal"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

@endsection
