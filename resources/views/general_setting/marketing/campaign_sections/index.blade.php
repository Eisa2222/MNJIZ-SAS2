@extends('layouts.layoutMaster')

@section('title', 'إعدادات أقسام الحملات ')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات أقسام الحملات
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات أقسام الحملات " data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/hr/advances/advances.js'])
    <script>
        $(document).ready(function() {

            // =============================
            // إضافة قسم جديد
            // =============================
            $('#addSectionModal form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.text();

                // تعطيل الزر وإظهار حالة التحميل
                submitBtn.prop('disabled', true).text('جاري الحفظ...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            // إغلاق الموديل
                            $('#addSectionModal').modal('hide');

                            // إعادة تعيين النموذج
                            form[0].reset();

                            // إعادة رسم الجدول
                            $('#campaign-management-table').DataTable().ajax.reload(null,
                                false);

                            // إظهار رسالة نجاح
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'حدث خطأ غير متوقع.');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'حدث خطأ أثناء الحفظ.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // إظهار أخطاء التحقق
                            var errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }

                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // إعادة تفعيل الزر
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // =============================
            // تعديل قسم
            // =============================
            $('#editSectionModal form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.text();

                // تعطيل الزر وإظهار حالة التحميل
                submitBtn.prop('disabled', true).text('جاري الحفظ...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            // إغلاق الموديل
                            $('#editSectionModal').modal('hide');

                            // إعادة رسم الجدول
                            $('#campaign-management-table').DataTable().ajax.reload(null,
                                false);

                            // إظهار رسالة نجاح
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'حدث خطأ غير متوقع.');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'حدث خطأ أثناء التعديل.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }

                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // إعادة تفعيل الزر
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // =============================
            // إعادة تعيين الموديل عند الإغلاق
            // =============================
            $('#addSectionModal, #editSectionModal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').remove();
            });
        });
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var color = $(this).data('color');
            var editForm = $('#editSettingForm');

            var actionUrl = "{{ route($route . '.update', ':id') }}".replace(':id', id);

            editForm.attr('action', actionUrl);
            $('#modalSettingNameEdit').val(name);
            $('#modalSettingColorEdit').val(color);
            $('#editSectionModal').modal('show');
        });


        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            $.ajax({
                url: "{{ route($route . '.edit', ':id') }}".replace(':id', id),
                method: 'get',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.status === 'active') {
                            badge.removeClass('bg-danger').addClass('bg-success').text('نشط');
                        } else if (response.status === 'inactive') {
                            badge.removeClass('bg-success').addClass('bg-danger').text('غير نشط');
                        }
                        toastr.success('تم تغيير الحالة بنجاح.');
                    } else {
                        toastr.error('فشل في تغيير الحالة.');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء تغيير الحالة.');
                }
            });
        });
    </script>
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('content')

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
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    {{-- Create --}}
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                    <form class="row mb-0" id="" action="{{ route($route . '.store') }}" method="POST">
                        @csrf

                        <div class="col-12 mb-4">
                            <label class="form-label" for="name">
                                اسم القسم
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="اسم القسم" required />
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label" for="color">
                                لون القسم
                            </label>
                            <input type="color" name="color" class="form-control" required />
                        </div>
                        <div class="col-12 text-center demo-vertical-spacing">
                            <button type="submit" class="btn btn-primary me-4">إضافة</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Update --}}
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                    <form id="editSettingForm" class="row mb-0" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="col-12 mb-4">
                            <label class="form-label" for="modalSettingNameEdit">
                                اسم القسم
                            </label>
                            <input id="modalSettingNameEdit" type="text" name="name" class="form-control"
                                placeholder="اسم القسم" required />
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label" for="modalSettingColorEdit">
                                لون القسم
                            </label>
                            <input id="modalSettingColorEdit" type="color" name="color" class="form-control" required />
                        </div>
                        <div class="col-12 text-center demo-vertical-spacing">
                            <button type="submit" class="btn btn-primary me-4">إضافة</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">إلغاء</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
