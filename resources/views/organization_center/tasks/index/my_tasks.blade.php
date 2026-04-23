    @extends('layouts.layoutMaster')

    @section('title', 'مهامي')

    @section('breadcrumb')
        <li><a href="#">مركز تنظيم الاعمال</a></li>
        <li class="breadcrumb-item d-flex align-items-center">
            <a href="#">مهامي</a>
            <i class="ti ti-star favorite-icon" data-page-name="مهامي" data-page-url="{{ url()->current() }}"
                onclick="toggleFavorite(event, this)"></i>
        </li>
    @endsection

    @section('vendor-style')
        @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @endsection

    @section('vendor-script')
        @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
        {!! $dataTable->scripts() !!}
        @vite(['resources/assets/js/organization-center/tasks/my-tasks-datatable.js', 'resources/assets/js/organization-center/tasks/filter.js'])

    @endsection

    @section('page-script')
        <script>
            window.appUrls = {
                taskComplate: "{{ route('organization-center.tasks.toggle-completion', ':task') }}",
                stepComplate: "{{ route('organization-center.tasks.steps.toggle-completion', ':stepId') }}",
                stepApproval: "{{ route('organization-center.tasks.steps.toggle-approval', ':stepId') }}",
            };
        </script>

        @vite(['resources/assets/js/organization-center/tasks/task-index.js'])
    @endsection


    @section('toastr')
        @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
    @endsection

    @section('page-style')
        @vite(['resources/css/dataTable.css'])
    @endsection


    @section('content')
        <div class="row g-4 mb-4">
            <!-- إجمالي المهام -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">المهام</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $totalTasks }}</h4>
                                </div>
                                <small class="mb-0">إجمالي المهام</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-primary">
                                    <i class="ti ti-list-check ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- المهام قيد الانتظار -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">المهام قيد الانتظار</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $pendingTasks }}</h4>
                                </div>
                                <small class="mb-0">عدد المهام قيد الانتظار</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-secondary">
                                    <i class="ti ti-hourglass-empty ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- المهام قيد التنفيذ -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">المهام قيد التنفيذ</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $inProgressTasks }}</h4>
                                </div>
                                <small class="mb-0">عدد المهام قيد التنفيذ</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-warning">
                                    <i class="ti ti-loader ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- المهام المكتملة -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">المهام المكتملة</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $completedTasks }}</h4>
                                </div>
                                <small class="mb-0">عدد المهام المكتملة</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-success">
                                    <i class="ti ti-circle-check ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="filters row g-3">
                    <div class="col-md-4">
                        <label for="filter-priority">الأولوية</label>
                        <select id="filter-priority" class="form-control select2" data-placeholder="اختر الأولوية">
                            <option value=""></option>
                            @foreach ($taskPriority as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- فلتر مجال المهمة -->
                    <div class="col-md-4">
                        <label for="filter-field">مجال المهمة</label>
                        <select id="filter-field" class="form-control select2" data-placeholder="اختر مجال المهمة">
                            <option value=""></option>
                            @foreach ($taskFields as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- فلتر حالة المهمة -->
                    <div class="col-md-4">
                        <label for="filter-status">حالة المهمة</label>
                        <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                            <option value=""></option>
                            @foreach ($taskStatus as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>


                </div>
                <hr class="mt-10">
                {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
            </div>
        </div>

        <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectReasonModalLabel">سبب الرفض</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="rejectReasonText" class="form-control" placeholder="أدخل سبب الرفض هنا"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirmRejectBtn" class="btn btn-danger">رفض</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
