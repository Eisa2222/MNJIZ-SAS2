@extends('layouts.layoutMaster')

@section('title', 'إدارة الاعتمادات')

@section('breadcrumb')
    <li><a href="#">إدارة النظام</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إدارة الاعتمادات</a>
        <i class="ti ti-star favorite-icon" data-page-name="إدارة الاعتمادات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/css/toastr.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/accreditation-requests/approval-management.js'])
    <script>
        window.APPROVAL_ROUTES = {
            approve: @json(route('approval-flows.approve', ['flow' => '_FLOW_', 'level' => '_LEVEL_'])),
            addLevel: @json(route('approval-flows.add-level', ['flow' => '_FLOW_'])),
            deleteLevel: @json(route('approval-flows.delete-level', ['flow' => '_FLOW_', 'level' => '_LEVEL_']))
        };
        window.CSRF_TOKEN = @json(csrf_token());

        window.FLOWS_DATA = @json(
            $flows->map(function ($collection) {
                return $collection->map(function ($flow) {
                    return [
                        'id' => $flow->id,
                        'levels' => $flow->levels->values(),
                    ];
                });
            }));
    </script>
@endsection

@section('page-style')
    @vite(['resources/css/approval-management.css'])
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="approval-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4>إدارة الاعتمادات</h4>
                <p>إدارة وتنظيم مستويات الاعتماد للعمليات المختلفة</p>
            </div>
            <div class="text-primary">
                <i class="fas fa-check-circle fa-3x"
                    style="color: var(--bs-primary) !important; opacity: 1 !important;"></i>
            </div>
        </div>
    </div>

    {{-- Approval Flows --}}
    @foreach ($flowLabels as $type => $title)
        @php
            $flow = $flows[$type]->first() ?? null;
        @endphp

        <div class="flow-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="flow-title mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    {{ $title }}
                </h5>
                @if ($flow)
                    <button type="button" class="btn btn-outline-primary btn-sm add-level-btn py-1 px-2"
                        data-flow="{{ $flow->id }}" title="إضافة مستوى جديد">
                        <i class="fas fa-plus me-1"></i>
                        <span class="d-none d-sm-inline">إضافة مستوى</span>
                    </button>
                @endif
            </div>

            @if (!$flow)
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h6>لا توجد بيانات اعتماد</h6>
                    <p class="mb-0">لم يتم إنشاء أي مستويات اعتماد لهذا النوع بعد</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table approval-table" data-flow="{{ $flow->id }}">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

    @include('system_administration.credentials_management.partials._employee-selection-modal', [
        'employees' => $employees,
    ])
@endsection


