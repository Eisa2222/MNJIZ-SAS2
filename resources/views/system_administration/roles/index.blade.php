@extends('layouts.layoutMaster')

@section('title', 'الصلاحيات الوظيفية')

@section('breadcrumb')
    <li><a href="#">إدارة النظام</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الصلاحيات الوظيفية</a>
        <i class="ti ti-star favorite-icon" data-page-name="الصلاحيات الوظيفية" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/hr/advances/advances.js'])
    <script>
        $('#roles-table').on('click', '.edit-role-link', function() {
            var employeeId = $(this).data('employee-id');

            if (!employeeId) {
                toastr.error('معرف الموظف غير موجود.');
                return;
            }

            $('#editRoleModal').modal('show');
            $('#editRoleModal').find('#employee-id').val(employeeId);

            // جلب الأدوار والحالة المتاحة للموظف
            $.ajax({
                url: '{{ route('roles.getEmployeeRoles', ':employeeId') }}'.replace(
                    ':employeeId', employeeId),
                method: 'GET',
                success: function(data) {
                    // تحقق من وجود خطأ في الاستجابة
                    if (data.error) {
                        toastr.error(data.error);
                        return;
                    }

                    var roleSelect = $('#editRoleModal').find('#employee-role');
                    roleSelect.empty();

                    roleSelect.append('<option value=""></option>');
                    $.each(data.roles, function(index, role) {
                        var isSelected = data.assigned_role === role.id ?
                            'selected' : '';
                        roleSelect.append('<option value="' + role.id + '" ' +
                            isSelected + '>' + role.name + '</option>');
                    });

                    // تهيئة Select2 للمودال
                    roleSelect.select2({
                        dropdownParent: $('#editRoleModal'),
                        placeholder: 'اختر الدور',
                        allowClear: true,
                        width: '100%',
                        language: 'ar',
                        dir: 'rtl'
                    });

                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    toastr.error('فشل في جلب الأدوار');
                }
            });
        });

        $('#updateRoleForm').submit(function(e) {
            e.preventDefault();
            var employeeId = $('#employee-id').val();
            var role = $('#employee-role').val();

            $.ajax({
                url: '{{ route('roles.updateEmployeeRoles', ':employeeId') }}'.replace(
                    ':employeeId', employeeId),
                method: 'POST',
                data: {
                    role: role,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editRoleModal').modal('hide');
                        $('#roles-table').DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    toastr.error('فشل في تحديث الدور');
                }
            });
        });


        $(document).on('click', '[data-bs-target="#managePermissionsModal"]', function(e) {
            console.log('Opening managePermissionsModal');
            e.preventDefault();

            var userId = $(this).data('user-id');

            if (!userId) {
                toastr.error('معرف المستخدم غير موجود.');
                return;
            }

            $('#manage-user-id').val(userId);

            // تفريغ القائمة
            $('#permissions-container').empty();

            // عرض loader أثناء التحميل
            $('#permissions-container').html(
                '<div class="text-center"><div class="spinner-border" role="status"></div></div>');

            // فتح المودال
            $('#managePermissionsModal').modal('show');

            // جلب الصلاحيات للمستخدم
            $.ajax({
                url: '/employees/roles/get-user-permissions/' + userId,
                method: 'GET',
                success: function(data) {
                    console.log('Received data:', data); // للتحقق من البيانات المستلمة

                    // جميع الصلاحيات في النظام مقسمة حسب الأقسام
                    var allPermissions = @json($permissions);

                    // الصلاحيات الممنوحة من الدور
                    var rolePermissions = data.role_permissions;

                    // الصلاحيات الفردية المضافة
                    var directPermissions = data.direct_permissions;

                    // الصلاحيات المنزوعة
                    var deniedPermissions = data.denied_permissions;

                    // تعبئة الأقسام
                    var containerHtml = '';

                    // تحويل allPermissions إلى مصفوفة من الأقسام
                    var sectionsArray = [];
                    for (var sectionName in allPermissions) {
                        if (allPermissions.hasOwnProperty(sectionName)) {
                            sectionsArray.push({
                                sectionName: sectionName,
                                permissions: allPermissions[sectionName]
                            });
                        }
                    }

                    // ترتيب الأقسام في صفوف مع كل قسمين في صف واحد
                    for (var i = 0; i < sectionsArray.length; i += 2) {
                        containerHtml += '<div class="row">';

                        for (var j = i; j < i + 2 && j < sectionsArray.length; j++) {
                            var section = sectionsArray[j];
                            var sectionId = 'section-' + j;

                            containerHtml += `
                        <div class="col-md-6 permissions-column">
                            <div class="accordion permissions-accordion" id="accordion-${sectionId}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-${sectionId}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${sectionId}" aria-expanded="false" aria-controls="collapse-${sectionId}">
                                            ${section.sectionName}
                                        </button>
                                    </h2>
                                    <div id="collapse-${sectionId}" class="accordion-collapse collapse" aria-labelledby="heading-${sectionId}" data-bs-parent="#permissions-container">
                                        <div class="accordion-body">
                    `;

                            var permissions = section.permissions;
                            permissions.forEach(function(permission) {
                                var permissionName = permission.name;

                                // تحديد حالة الـ Checkbox للصلاحية
                                var isGrantedByRole = rolePermissions.includes(permissionName);
                                var isDirectlyGranted = directPermissions.includes(
                                    permissionName);
                                var isDenied = deniedPermissions.includes(permissionName);

                                if (isGrantedByRole) {
                                    // الصلاحية ممنوحة من الدور
                                    containerHtml += `
                                <div class="permission-item d-flex justify-content-between mt-5">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked disabled>
                                        <label class="form-check-label">${permissionName}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input deny-permission-checkbox" type="checkbox" name="denied_permissions[]" value="${permissionName}" ${isDenied ? 'checked' : ''}>
                                        <label class="form-check-label text-danger">نزع</label>
                                    </div>
                                </div>
                            `;
                                } else {
                                    // الصلاحيات الأخرى التي يمكن إضافتها
                                    var isChecked = isDirectlyGranted ? 'checked' : '';
                                    containerHtml += `
                                <div class="permission-item">
                                    <div class="form-check">
                                        <input class="form-check-input additional-permission-checkbox" type="checkbox" name="permissions[]" value="${permissionName}" ${isChecked}>
                                        <label class="form-check-label">${permissionName}</label>
                                    </div>
                                </div>
                            `;
                                }
                            });

                            containerHtml += `
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        }

                        containerHtml += '</div>';
                    }

                    $('#permissions-container').html(containerHtml);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    toastr.error('فشل في جلب الصلاحيات.');
                    $('#permissions-container').html(
                        '<div class="alert alert-danger">فشل في تحميل الصلاحيات</div>');
                }
            });
        });

        // عند إرسال النموذج
        $('#managePermissionsForm').submit(function(e) {
            e.preventDefault();
            var userId = $('#manage-user-id').val();
            var permissions = $('input[name="permissions[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            var deniedPermissions = $('input[name="denied_permissions[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            $.ajax({
                url: '/employees/roles/update-user-permissions/' + userId,
                method: 'POST',
                data: {
                    permissions: permissions,
                    denied_permissions: deniedPermissions,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#managePermissionsModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('فشل في تحديث صلاحيات المستخدم');
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
    <div class="row g-4 mb-5">
        @foreach ($roles as $role)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-normal mb-0 text-body">إجمالي {{ $role->users->count() }} مستخدمين</h6>
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @foreach ($role->users->take(5) as $user)
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        title="{{ $user->employee->nickname ?? $user->name }}" class="avatar pull-up">
                                        <img class="rounded-circle"
                                            src="{{ $user->image ? asset('storage/' . $user->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                            alt="Avatar" width="40" height="40">
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="role-heading">
                                <h5 class="mb-1">{{ $role->name }}</h5>
                                @can('تعديل دور')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="role-edit-modal">
                                        <span>تعديل الدور</span>
                                    </a>
                                @endcan
                            </div>

                            @can('حذف دور')
                                <a href="javascript:void(0);" onclick="confirmDelete({{ $role->id }})">
                                    <i class="ti ti-trash ti-md text-danger"></i>
                                </a>
                                <form id="delete-form-{{ $role->id }}" action="{{ route('roles.destroy', $role->id) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- بطاقة إضافة دور جديد -->
        @can('إضافة دور')
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 w-100">
                    <div class="row h-100">
                        <div class="col-sm-5 d-flex align-items-center justify-content-center p-3">
                            <div class="d-flex align-items-center h-100 justify-content-center mt-sm-0 mt-4">
                                <img src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                    class="img-fluid mt-sm-4 mt-md-0" alt="add-new-roles" width="83">
                            </div>
                        </div>
                        <div class="col-sm-7 d-flex align-items-center justify-content-center">
                            <div class="card-body text-sm-end text-center ps-sm-0">
                                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> إضافة دور جديد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
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
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="updateRoleForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل دور الموظف </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="employee-id" name="employee_id">

                        <div class="mb-3">
                            <label for="employee-role" class="form-label">
                                الدور
                                <span data-toggle="tooltip" data-placement="top"
                                    title="عند تحديث دور المستخدم، سيتم حذف جميع الصلاحيات المضافة فردياً والصلاحيات المنزوعة، وسيتم الاعتماد على صلاحيات الدور الجديد."
                                    style="color: var(--primary-color);">
                                    <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                </span>
                            </label>
                            <select id="employee-role" name="role" class="form-control select2" required>
                                <option value=""></option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تحديث البيانات</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="managePermissionsModal" tabindex="-1" aria-labelledby="managePermissionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="managePermissionsForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إدارة صلاحيات المستخدم</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="manage-user-id" name="user_id">

                        <!-- قسم الصلاحيات -->
                        <div id="permissions-container" class="permissions-container">
                            <!-- سيتم تعبئته بواسطة JavaScript -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
