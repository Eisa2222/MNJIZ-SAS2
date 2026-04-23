@extends('layouts.layoutMaster')

@section('title','عرض النموذج')

@section('breadcrumb')
    <li><a href="{{ route('settings-templates.index') }}">النماذج</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> {{ Str::limit($template->name, 25) }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="عرض النموذج" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection





@section('content')
<div class="card ">

    <div class="card-body">
        <div class="m-5">
            <div style="display: flex; justify-content: space-between; align-items: center;">

                @if ($template->show_header)
                <div>
                    <img src="{{ asset('logo.svg') }}" alt="Header Image" style="height: auto; max-width: 200px;">
                </div>
                @endif
            </div>

            <span class="float-end mt-4" style="font-size: 14px; color: #555;">
                {{ \Carbon\Carbon::now()->locale('ar_EG')->isoFormat('dddd, D MMMM YYYY') }}
            </span>

            <div class="card-body mt-10 ql-editor" >
                {!! $processedContent !!}
            </div>

            @if ($template->show_seal || $template->show_footer)
            <div class="print-footer">
                <!-- صورة التوقيع -->
                @if ($template->show_seal)
                <div class="editor-footer">
                    <img src="{{ asset('sig.png') }}" alt="Signature Image"
                        style="max-width: 150px; float: left; margin-left: 65px; margin-bottom: 50px;">
                </div>
                @endif
                <!-- صورة التذييل -->
                @if ($template->show_footer)
                <div class="editor-footer">
                    <img src="{{ asset('footer.svg') }}" alt="Footer Image">
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

@endsection
