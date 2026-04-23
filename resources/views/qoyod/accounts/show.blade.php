@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الحساب ')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.accounts.index') }}">الحسابات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> تفاصيل الحساب </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل  الحساب" data-page-url="{{ url()->current() }}"
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
                        تفاصيل الحساب
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">كود الحساب </td>
                                <td>{{ $account['code'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الاسم بالعربي</td>
                                <td>{{ $account['name_ar'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الاسم بالانجليزي</td>
                                <td>{{ $account['name_en'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">النوع</td>
                                <td>{{ $account['type_label'] }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">نوع المجموعة </td>
                                <td>{{ $account['group_type'] }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">نوع الحساب </td>
                                <td>
                                    @if ($account['type_of_account'] == 'Debit')
                                        مدين
                                    @elseif($account['type_of_account'] == 'Credit')
                                        دائن
                                    @else
                                        {{ $account['type_of_account'] }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الرصيد الحالي</td>
                                <td>{{ number_format($account['balance'],2)  }}  <span class="icon-saudi_riyal"></span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>{{ $account['status'] == 'Active' ? 'نشط' : 'غير نشط' }}</td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
