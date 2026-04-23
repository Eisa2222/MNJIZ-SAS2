@extends('layouts.layoutMaster')

@section('title', 'اعتماد إخلاء الطرف')

@section('breadcrumb')
    <li><a href="#">طلبات الاعتماد</a></li>
    <li><a href="{{ route('accreditation-requests.clearance-certificate.index') }}">اعتماد إخلاء الطرف</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الطلب</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الطلب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل طلب إخلاء الطرف</h5>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="fw-bold text-dark" style="width: 30%">السبب</th>
                                    <td>{{ $certificate->reason }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold text-dark">ملاحظات الموظف</th>
                                    <td>{{ $certificate->notes ?? 'لا توجد ملاحظات' }}</td>
                                </tr>
                                @if ($certificate->status !== \App\Models\Self_services\ClearanceCertificate::STATUS_PENDING)
                                    <tr>
                                        <th class="fw-bold text-dark">حالة الطلب</th>
                                        <td>
                                            <span
                                                class="badge badge-sm bg-{{ $certificate->status == 'approved' ? 'success' : ($certificate->status == 'rejected' ? 'danger' : 'secondary') }}  px-5 py-1">
                                                {{ $certificate->status_name }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($certificate->status === \App\Models\Self_services\ClearanceCertificate::STATUS_PENDING)
                <div class="d-flex justify-content-end gap-2 mt-4">

                    <form action="{{ route('accreditation-requests.clearance-certificate.approve', $certificate->id) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm px-4">
                            قبول الطلب
                        </button>
                    </form>

                    <form action="{{ route('accreditation-requests.clearance-certificate.reject', $certificate->id) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger btn-sm px-4">
                            رفض الطلب
                        </button>
                    </form>

                </div>
            @endif
        </div>
        @if ($certificate->status !== \App\Models\Self_services\ClearanceCertificate::STATUS_PENDING)
            <div class="card-footer text-center">
                <a href="{{ route('accreditation-requests.clearance-certificate.index') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-back me-1"></i> العودة للقائمة الرئيسية
                </a>
            </div>
        @endif
    </div>
@endsection
