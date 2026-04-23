@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الطلب ')

@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.purchase-requests.index') }}">طلبات المشتريات </a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الطلب </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الطلب " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
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
                        تفاصيل الطلب
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">الطلب</td>
                                <td class="fw-bold">{{ $purchaseRequest->item_name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">التصنيف</td>
                                <td>{{ $purchaseRequest->category->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الكمية</td>
                                <td>{{ $purchaseRequest->item_quantity }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">تاريخ الطلب</td>
                                <td>{{ $purchaseRequest->created_at }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>{{ $purchaseRequest->status->label() }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">ملاحظات</td>
                                <td>{{ $purchaseRequest->item_description }}</td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-md-4">
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
                            <span class="timeline-point bg-primary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $purchaseRequest->created_at }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $purchaseRequest->created_by) }}">
                                        {{ $purchaseRequest->createdBy->getRawNameAttribute() }}
                                    </a>
                                </small>
                            </div>
                        </li>

                        @if ($purchaseRequest->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-warning"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        اخر تحديث بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $purchaseRequest->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $purchaseRequest->updated_by) }}">
                                            {{ $purchaseRequest->updatedBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
