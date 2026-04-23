    @extends('layouts.layoutMaster')

    @section('title', 'إدارة المحتوى')

    @section('breadcrumb')
        <li><a href="#">التسويق</a></li>
        <li class="breadcrumb-item d-flex align-items-center">
            <a href="#">إدارة المحتوى</a>
            <i class="ti ti-star favorite-icon" data-page-name="إدارة المحتوى" data-page-url="{{ url()->current() }}"
                onclick="toggleFavorite(event, this)"></i>
        </li>
    @endsection

    @section('vendor-style')
        @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @endsection

    @section('vendor-script')
        @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
        {!! $dataTable->scripts() !!}
    @endsection

    @section('page-script')
        @vite(['resources/assets/js/marketing/content-management/content-management.js'])
    @endsection


    @section('toastr')
        @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
    @endsection

    @section('page-style')
        @vite(['resources/css/dataTable.css'])
    @endsection


    @section('content')
        <div class="row g-4 mb-4">
            <!-- إجمالي المحتوى -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">إجمالي المحتوى</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $totalContent }}</h4>
                                </div>
                                <small class="mb-0">جميع المحتويات</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="fas fa-layer-group"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مسودات -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">مسودات</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $draftContent }}</h4>
                                </div>
                                <small class="mb-0">محتوى غير مكتمل</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مجدولة -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">مجدولة</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $scheduledContent }}</h4>
                                </div>
                                <small class="mb-0">في انتظار النشر</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- منشورة -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">منشورة</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $publishedContent }}</h4>
                                </div>
                                <small class="mb-0">محتوى منشور</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fas fa-share-alt"></i>
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
                        <label for="filter-type">نوع المحتوى</label>
                        <select id="filter-type" class="form-control select2" data-placeholder="اختر  نوع المحتوى">
                            <option value=""></option>
                            @foreach ($contentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter-pattern">نمط النشر</label>
                        <select id="filter-pattern" class="form-control select2" data-placeholder="اختر نمط النشر">
                            <option value=""></option>
                            @foreach ($publishingPatterns as $pattern)
                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter-publication-status">الحالة</label>
                        <select id="filter-publication-status" class="form-control select2" data-placeholder="اختر الحالة">
                            <option value=""></option>
                            @foreach ($contentStatus as $item)
                                <option value="{{ $item->value }}">{{ $item->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr class="mt-10">
                {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
            </div>
        </div>


    @endsection
