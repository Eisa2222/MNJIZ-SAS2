@extends('layouts.layoutMaster')

@section('title', 'طلب إخلاء طرف')


@section('breadcrumb')
    <li><a href="#"> الخدمات الذاتية</a></li>
    <li><a href="{{ route('account.self-services.clearance-certificate.index') }}"> إخلاء طرف </a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">طلب إخلاء طرف </a>
        <i class="ti ti-star favorite-icon" data-page-name="طلب إخلاء طرف" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('content')
    <div class="card mb-6">
        <form id="" method="POST" action="{{ route('account.self-services.clearance-certificate.store') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <div class="card-body pt-7">
                <div class="row">
                    <div class="row">

                        <div class="col-md-6 mb-4">
                            <label class="form-label">سبب إخلاء الطرف </label>
                            <input type="text" name="reason" required class="form-control" placeholder="أدخل سبب الطلب">
                        </div>
                    
                        <div class="col-12 mb-4">
                            <label class="form-label">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" placeholder="أي ملاحظات أو تفاصيل إضافية"></textarea>
                        </div>
                    
                    </div>
                    

                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-3">تقديم الطلب</button>
                </div>
            </div>
        </form>

    </div>
@endsection
