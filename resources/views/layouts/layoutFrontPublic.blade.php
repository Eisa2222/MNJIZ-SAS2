@php
    $configData = App\Helpers\Helpers::appClasses();
    $isFront = true;
@endphp

@section('layoutContent')
    @extends('layouts.commonMaster')

    @yield('content')
@endsection
