@extends('layouts.layoutMaster')

@section('title', 'تذكرة رقم - ' . $support->ticket_number)

@section('breadcrumb')
    <li><a href="{{ route('userSuppports') }}">الدعم الفني </a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تذكرة رقم - {{ $support->ticket_number }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="التذاكر" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-invoice.scss')
@endsection

@section('vendor-script')
    @vite([])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

{{-- @section('page-script')
    @vite([])
@endsection --}}

@section('content')

    <div class="row invoice-preview">
        <!-- Invoice -->
        <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6">
            <div class="card invoice-preview-card p-sm-12 p-6">
                <div class="card-body invoice-preview-header rounded">
                    <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column">
                        <div class="mb-xl-0 mb-6 text-heading">
                            <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                                <div class="">
                                    <img src="{{ asset('etmam.png') }}" height="50" alt="" class="w-30 h-30">

                                </div>
                                <span class="app-brand-text fw-bold fs-4 ms-50">

                                </span>
                            </div>
                            <p class="mb-2"><strong> المستخدم :</strong> {{ $support->user->name }}</p>
                            <p class="mb-2"><strong> تذكرة :</strong> {{ $support->ticket_classification }}</p>
                            <p class="mb-2"><strong>الأولوية :</strong> {{ $support->priority }}</p>

                        </div>
                        <div>
                            <p class="mb-6"><span> تذكرة رقم :</span>{{ $support->ticket_number }}</p>
                            <div class="mb-1 text-muted mb-2">
                                <span>تاريخ التذكرة:</span>
                                <span>
                                    {{ \Carbon\Carbon::parse($support->created_at)->isoFormat('dddd, D MMMM YYYY, h:mm A') }}</span>

                            </div>
                            <div class="text-muted">
                                <span>تاريخ المعالجة:</span>
                                <span>
                                    {{ \Carbon\Carbon::parse($support->reply_date)->isoFormat('dddd, D MMMM YYYY, h:mm A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0">
                    <div class="row">
                        <div class="col-xl-6 col-md-12 col-sm-5 col-12 mb-xl-0 mb-md-6 mb-sm-0 mb-6">
                            <h6>عنوان الرسالة </h6>
                            <p class="mb-1">

                                {{ $support->title }}
                            </p>

                        </div>
                        <div class="col-xl-6 col-md-12 col-sm-7 col-12">
                            <h6>الوصف</h6>
                            <p>
                                {{ $support->notes }}
                            </p>
                        </div>

                        <div class="col-12">
                            <h6>الرد</h6>
                            @if ($support->reply != '')
                                <p>
                                    {{ $support->reply }}
                                </p>
                            @else
                                <p>لم يتم الرد بعد</p>
                            @endif

                        </div>
                    </div>
                </div>

                @if ($support->status == 'تمت المعالجة')

                    <div class="table-responsive">
                        <table class="table m-0 table-borderless d-flex flex-col justify-content-end">
                            <tbody>
                                <tr>
                                    <td class="align-top pe-6 ps-0 py-6">
                                        @if ($support->processedBy)
                                            <p class="mb-1">
                                                <span class="me-2 h6">قام بمعالجة الطلب :</span>
                                                <span>{{ $support->processedBy->name ?? '' }}</span>
                                            </p>
                                        @endif
                                        <span> إتمام لتقنية نظم المعلومات ❤️</span>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                @endif

                <hr class="mt-0 mb-6">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-12">
                            @if ($support->reply == '')
                                <div>
                                    <span>شكرًا جزيلاً لتواصلكم معنا وثقتكم في خدماتنا.</span>
                                    <sanp>نود أن نبلغكم بأننا قد استلمنا طلبكم، وسنعمل على مراجعته في أقرب وقت ممكن.</sanp>
                                </div>
                            @else
                                <div>
                                    <span>شكرًا جزيلاً لتواصلكم معنا وثقتكم في خدماتنا.</span>
                                    <sapn>نسعى دائمًا لتقديم الأفضل لكم، ونتطلع إلى خدمتكم مجددًا بكل سرور.</sapn>

                                </div>
                            @endif
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

                    {{-- <button class="btn btn-label-secondary d-grid w-100 mb-4">
                    Download
                </button> --}}
                    <div class=" mb-4">
                        <a class="btn btn-label-secondary d-grid w-100 me-4" target="_blank"
                            href="{{ url($support->id . '/print') }}">
                            طباعة
                        </a>

                    </div>
                    <button class="btn btn-success d-grid w-100" data-bs-toggle="offcanvas"
                        data-bs-target="#addPaymentOffcanvas">
                        <span class="d-flex align-items-center justify-content-center text-nowrap">قائمة الردود</span>
                    </button>
                </div>
            </div>
        </div>
        <!-- /Invoice Actions -->
    </div>

    <!-- Offcanvas -->
    @include('supports/_partials/user_add_replies')
    <!-- /Offcanvas -->
@endsection
