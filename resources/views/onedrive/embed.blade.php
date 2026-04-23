@extends('layouts.layoutMaster')

@section('content')
<div class="container">
    <h1>View File</h1>

    <iframe src="{{ $embedLink }}" width="100%" height="800px" frameborder="0"></iframe>
</div>
@endsection
