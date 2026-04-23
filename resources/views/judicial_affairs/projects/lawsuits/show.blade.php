@extends('judicial_affairs.projects.layout')

@section('sections')
    @if (isset($lawsuits) && $lawsuits->isNotEmpty())
        <!-- شريط البحث برقم الدعوى -->
        <div class="col-md-12 ">
            <form method="GET" action="{{ route('projects.lawsuits', $project->id) }}" class="d-flex">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control" placeholder="ابحث برقم الدعوى"
                        aria-label="ابحث برقم الدعوى" value="{{ request('search') }}">

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="row g-3">
            @foreach ($lawsuits as $lawsuit)
                <div class="col-12">
                    <div class="card shadow-sm d-flex flex-row align-items-stretch">
                        <!-- زر السهم لعرض/إخفاء التحديثات -->
                        <button id="toggleUpdatesBtn-{{ $lawsuit->id }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center"
                            style="width: 70px; border-radius: 0; height: auto;"
                            onclick="toggleUpdates({{ $lawsuit->id }})">
                            <i class="fas fa-arrow-down fa-2x text-white"></i>
                        </button>

                        <div class="card-body flex-grow-1">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <strong class="">اسم الدعوى</strong>
                                        <a href="{{ route('legal-affairs.lawsuits.show', $lawsuit->id) }}">
                                            <div class="text-primary fw-bold mt-1 small">{{ $lawsuit->name }}</div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <strong class="">رقم الدعوى</strong>
                                        <a href="{{ route('legal-affairs.lawsuits.show', $lawsuit->id) }}">

                                            <div class="text-primary fw-bold mt-1 small">{{ $lawsuit->lawsuit_number }}
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <strong class="">تاريخ الإنشاء</strong>
                                        <div class="text-primary fw-bold mt-1 small">{{ $lawsuit->hijri_created }}</div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <strong class="">الدائرة</strong>
                                        <div class="text-primary fw-bold mt-1 small">{{ $lawsuit->circle }}</div>
                                    </div>
                                </div>

                                <div class="col-1">
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-text-secondary rounded-pill" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <form id="delete-form-{{ $lawsuit->id }}" class="mb-0"
                                                    action="{{ route('legal-affairs.lawsuits.destroy', $lawsuit->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger"
                                                        onclick="confirmDelete({{ $lawsuit->id }})">
                                                        حذف الدعوى
                                                    </button>
                                                </form>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قائمة الجلسات الخاصة بكل دعوى -->
                    <div id="updates-{{ $lawsuit->id }}" class="mt-3" style="display: none;">
                        <div class="row g-3">
                            @if ($lawsuit->sessions->count() > 0)
                                @foreach ($lawsuit->sessions->sortByDesc('created_at')->take(3) as $session)
                                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3 pb-1">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar me-2">
                                                            <i class="ti ti-calendar ti-xl me-1_5 text-primary"></i>
                                                        </div>
                                                        <div class="me-1 text-heading h5 mb-0">
                                                            <a class="small fw-bold"
                                                                href="{{ route('legal-affairs.sessions.show', $session->id) }}">
                                                                {{ Str::limit($session->session_name, 50) }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3 pb-1">
                                                    <p class="mb-2 small">
                                                        <span class="fw-bold">تاريخ وزمن الجلسة :</span>
                                                        <small class="text-primary fw-bold">
                                                            {{ $session->hijri_session_date }} -
                                                            {{ \Carbon\Carbon::parse($session->session_time)->format('H:i') }}
                                                        </small>
                                                    </p>
                                                </div>

                                                <ul class="list-group list-group-flush">
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center flex-wrap p-0">
                                                        <div class="d-flex flex-wrap align-items-center">
                                                            <ul
                                                                class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                                                @foreach ($session->assignedUsers->take(3) as $employee)
                                                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                                        data-bs-placement="bottom" class="avatar pull-up"
                                                                        aria-label="{{ $employee->name }}"
                                                                        data-bs-original-title="{{ $employee->name }}">
                                                                        <img class="rounded-circle"
                                                                            src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                            alt="Avatar">
                                                                    </li>
                                                                @endforeach

                                                                @if ($session->assignedUsers->count() > 3)
                                                                    <li class="avatar">
                                                                        <span type="button"
                                                                            class="avatar-initial rounded-circle pull-up text-heading"
                                                                            data-bs-placement="bottom"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#employeesModal"
                                                                            title="عرض جميع المكلفين">
                                                                            +{{ $session->assignedUsers->count() - 3 }}</span>
                                                                    </li>
                                                                @endif

                                                                @if ($session->assignedUsers->count() == 0)
                                                                    <li class="mb-4">
                                                                        لا يوجد مكلفين
                                                                    </li>
                                                                @endif

                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>

                                                <!-- مودال عرض جميع الموظفين -->
                                                <div class="modal fade" id="employeesModal" tabindex="-1"
                                                    aria-labelledby="employeesModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="employeesModalLabel">قائمة
                                                                    الموظفين
                                                                    المخصصين
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <ul class="list-group list-group-flush">
                                                                    @foreach ($session->assignedUsers as $employee)
                                                                        <li
                                                                            class="list-group-item d-flex align-items-center">
                                                                            <div class="me-3">
                                                                                <img class="rounded-circle"
                                                                                    src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                                    alt="Avatar" width="50"
                                                                                    height="50" loading="lazy">
                                                                            </div>
                                                                            <div>
                                                                                <strong>{{ $employee->name }}</strong>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">إغلاق</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 text-center">

                                    <div class="card p-12">
                                        حاليًا، لا توجد أي جلسات مرتبطة بهذه الدعوى.
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>



        <style>
            .custom-pagination .text-muted {
                display: none !important;
            }
        </style>
        <!-- Pagination -->
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <div class="custom-pagination">
                {{ $lawsuits->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <div class="alert alert-info">
            لا توجد دعاوى مرتبطة بهذا المشروع.
        </div>
    @endif

    <script>
        function toggleUpdates(lawsuitId) {
            const updatesContainer = document.getElementById('updates-' + lawsuitId);
            const toggleBtnIcon = document.querySelector('#toggleUpdatesBtn-' + lawsuitId + ' i');

            if (updatesContainer.style.display === 'none') {
                updatesContainer.style.display = 'block';
                toggleBtnIcon.classList.remove('fa-arrow-down');
                toggleBtnIcon.classList.add('fa-arrow-up');
            } else {
                updatesContainer.style.display = 'none';
                toggleBtnIcon.classList.remove('fa-arrow-up');
                toggleBtnIcon.classList.add('fa-arrow-down');
            }
        }
    </script>
@endsection
