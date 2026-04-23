@extends('layouts.layoutMaster')

@section('title', 'مسيرات الرواتب')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> مسيرات الرواتب</a>
        <i class="ti ti-star favorite-icon" data-page-name="مسيرات الرواتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/hr/employees/employees.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    {{-- <div class="row g-2 mb-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الموظفون</span>
                            <div class="d-flex align-items-center my-1">

                                <h4 class="mb-0 me-2">{{ $totalEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">إجمالي الموظفين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users-group ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">مورد حالي</span>
                            <div class="d-flex align-items-center my-1">

                                <h4 class="mb-0 me-2">{{ $currentEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف حالي</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-briefcase ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">متاح للإستدعاء</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $availableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف متاح للإستدعاء</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-circle-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">متاح جزئيا</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $partiallyAvailableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف متاح جزئيا</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-clock-hour-3 ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading"> غير المتاحين</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $unavailableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف غير متاح</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-pause ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

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
            {{-- <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-hr_status_id">حالة الموظف</label>
                    <select id="filter-hr_status_id" class="form-control select2" data-placeholder="اختر حالة الموظف">
                        <option value=""></option>
                        @foreach ($hrStatus as $status)
                            <option value="{{ $status->id }}" {{ old('status') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-role">المسمى الوظيفي</label> <!-- تغيير المسمى الوظيفي إلى الدور -->
                    <select id="filter-role" class="form-control select2" data-placeholder="اختر المسمى الوظيفي">
                        <option value=""></option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-insurance_status">حالة التأمينات</label>
                    <select id="filter-insurance_status" class="form-control select2"
                        data-placeholder="اختر حالة التأمينات">
                        <option value=""></option>
                        <option value="مضاف">مضاف</option>
                        <option value="مضاف غير رسمي">مضاف غير رسمي</option>
                        <option value="مستبعد">مستبعد</option>
                        <option value="غير مسجل">غير مسجل</option>
                    </select>
                </div>
            </div> --}}
            <hr class="mt-10">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('hr.payrolls.wps.generate') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="generateModalLabel">توليد الرواتب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="year" class="form-label">السنة</label>
                            <select name="year" id="year" class="form-control select2">
                                @for ($y = 2024; $y <= now()->format('Y'); $y++)
                                    <option value="{{ $y }}" {{ $y == now()->format('Y') ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="month" class="form-label">الشهر</label>
                            <select name="month" id="month" class="form-control select2">
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">توليد</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // تهيئة Select2
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            // تعديل تهيئة Select2 في الموديل
            $('#generateModal .select2').select2({
                dropdownParent: $('#generateModal'),
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });


            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });


            // تعريف الشهور
            const monthsData = {
                @foreach ($months as $num => $name)
                    {{ $num }}: "{{ $name }}",
                @endforeach
            };

            // الحصول على السنة والشهر الحاليين
            const currentYear = {{ now()->format('Y') }};
            const currentMonth = {{ now()->format('m') }};

            // وظيفة لتحديث قائمة الشهور بناءً على السنة المختارة
            function updateAvailableMonths() {
                // الحصول على السنة المختارة
                const selectedYear = parseInt($('#year').val());
                const monthSelect = $('#month');

                // تفريغ قائمة الشهور الحالية
                monthSelect.empty();

                // عدد الشهور المضافة
                let addedMonths = 0;

                // إضافة الشهور بناءً على السنة المختارة
                for (let m = 1; m <= 12; m++) {
                    // في السنة الحالية، أضف الشهور التي انقضت بالإضافة للشهر الحالي
                    if (selectedYear < currentYear || (selectedYear === currentYear && m <= currentMonth)) {
                        const formattedMonth = String(m).padStart(2, '0');
                        monthSelect.append(new Option(monthsData[m], formattedMonth));
                        addedMonths++;
                    }
                }

                // إعادة تهيئة Select2 بعد تحديث الخيارات
                monthSelect.trigger('change');

                // تحديد أول خيار افتراضي إذا كانت هناك خيارات
                if (addedMonths > 0) {
                    monthSelect.val(monthSelect.find('option:first').val()).trigger('change');
                }
            }

            // استدعاء وظيفة تحديث الشهور عند تغيير السنة
            $(document).on('change', '#year', function() {
                updateAvailableMonths();
            });

            // تحديث الشهور عند فتح المودال
            $('#generateModal').on('shown.bs.modal', function() {
                setTimeout(updateAvailableMonths, 100);
            });

            // ====== نهاية كود تحديث الشهور ======
        });
    </script>
@endsection
