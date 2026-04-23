@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الرسالة')

@section('breadcrumb')
    <li><a href="{{ route('messageLogs.index') }}">سجلات الرسائل</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الرسالة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الرسالة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('page-script')
    <script>
        // أي سكريبتات إضافية للصفحة
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">تفاصيل الرسالة</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>المرسل:</strong> {{ $log->sender ? $log->sender->name : 'غير محدد' }}
                </div>
                <div class="col-md-6">
                    <strong>منصة الرسالة:</strong> {{ $log->platform }}
                </div>
            </div>

            <div class="mb-3">
                <strong>نص الرسالة:</strong>
                <p>{{ $log->message_text }}</p>
            </div>

            <!-- عرض المستلمين فقط إذا كانت القائمة غير فارغة -->
            @if ($customerList->isNotEmpty() || $employeeList->isNotEmpty() || $opponentList->isNotEmpty())
                <div class="mb-3">
                    <strong>المستلمون:</strong>
                    <div class="row">
                        <!-- قسم العملاء -->
                        @if ($customerList->isNotEmpty())
                            <div class="col-md-4">
                                <h6>العملاء:</h6>
                                <ul class="list-group mb-3">
                                    @foreach ($customerList as $customer)
                                        <li class="list-group-item">
                                            <i class="ti ti-user-check text-success mx-1"></i>
                                            {{ $customer->name }} (رقم الاتصال: {{ $customer->contact_number }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- قسم الموظفين -->
                        @if ($employeeList->isNotEmpty())
                            <div class="col-md-4">
                                <h6>الموظفين:</h6>
                                <ul class="list-group mb-3">
                                    @foreach ($employeeList as $employee)
                                        <li class="list-group-item">
                                            <i class="ti ti-user text-info mx-1"></i>
                                            {{ $employee->name }} (رقم الجوال: {{ $employee->mobile }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- قسم الخصوم -->
                        @if ($opponentList->isNotEmpty())
                            <div class="col-md-4">
                                <h6>الخصوم:</h6>
                                <ul class="list-group mb-3">
                                    @foreach ($opponentList as $opponent)
                                        <li class="list-group-item">
                                            <i class="ti ti-user-x text-danger mx-1"></i>
                                            {{ $opponent->name }} (رقم الاتصال: {{ $opponent->phone }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>تاريخ الإرسال:</strong> {{ \Carbon\Carbon::parse($log->created_at)->isoFormat('D MMMM YYYY') }}
                </div>
            </div>

            {{-- <a href="{{ route('messageLogs.index') }}" class="btn btn-secondary">عودة</a> --}}
        </div>
    </div>
@endsection
