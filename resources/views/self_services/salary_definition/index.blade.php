@extends('layouts.layoutMaster')

@section('title', 'تعريف بالراتب')


@section('breadcrumb')
    <li><a href="{{ route('account.electronic-services.violations-penalties.index') }}"> الخدمات الذاتية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعريف بالراتب </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعريف بالراتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('content')
    <div class="card mb-6">
        <form id="" method="POST" action="{{ route('account.self-services.salary-definition.submit') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <div class="card-body pt-7">
                <div class="row">
                    <div class="mb-4 col-md-6">
                        <label for="recipient" class="form-label">إسم الجهة الطالبة</label>
                        <input class="form-control" type="text" id="recipient" name="recipient"
                            value=""
                            placeholder=" إسم الجهة الطالبة" />
                        @error('recipient')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-3">اصدار التعريف بالراتب</button>
                </div>
            </div>
        </form>

    </div>
@endsection
