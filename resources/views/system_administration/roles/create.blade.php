@extends('layouts.layoutMaster')

@section('title', 'إضافة دور')

@section('breadcrumb')
    <li><a href="#">إدارة النظام</a></li>
    <li><a href="{{ route('roles.index') }}"> الصلاحيات الوظيفية </a></li>

    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة دور</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة دور" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/system_administration/roles/main.css', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل checkbox لتحديد الكل العام
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('#permissions-treeview .permission-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);

                // تحديث حالة "تحديد الكل" لكل قسم بناءً على حالة الكل العام
                const sectionSelectAllCheckboxes = document.querySelectorAll('.section-select-all');
                sectionSelectAllCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    checkbox.indeterminate = false;
                });
            });

            // تفعيل اختيار كل العناصر تحت فئة معينة (كل قسم)
            const sectionSelectAllCheckboxes = document.querySelectorAll('.section-select-all');
            sectionSelectAllCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const sectionIndex = this.getAttribute('data-section-index');
                    const childCheckboxes = document.querySelectorAll(
                        `#collapse-${sectionIndex} .permission-checkbox`);
                    childCheckboxes.forEach(child => child.checked = this.checked);

                    // تحديث حالة checkbox العام إذا تم تحديد أو إلغاء تحديد جميع الصلاحيات
                    updateGlobalSelectAll();
                });
            });

            // تفعيل اختيار كل الصلاحيات في قسم عند تغيير أي صلاحية داخل القسم
            const individualSectionCheckboxes = document.querySelectorAll('.permission-checkbox');
            individualSectionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const section = this.closest('.permissions-column');
                    const sectionIndex = this.closest('.accordion').getAttribute('id').split('-')[
                        1];
                    const allSectionCheckboxes = document.querySelectorAll(
                        `#collapse-${sectionIndex} .permission-checkbox`);
                    const sectionSelectAll = document.querySelector(
                        `.section-select-all[data-section-index="${sectionIndex}"]`);

                    const allChecked = Array.from(allSectionCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(allSectionCheckboxes).some(cb => cb.checked);

                    if (allChecked) {
                        sectionSelectAll.checked = true;
                        sectionSelectAll.indeterminate = false;
                    } else if (someChecked) {
                        sectionSelectAll.checked = false;
                        sectionSelectAll.indeterminate = true;
                    } else {
                        sectionSelectAll.checked = false;
                        sectionSelectAll.indeterminate = false;
                    }

                    // تحديث حالة checkbox العام
                    updateGlobalSelectAll();
                });
            });

            // تحديث حالة checkbox العام بناءً على حالة الصلاحيات
            function updateGlobalSelectAll() {
                const allCheckboxes = document.querySelectorAll('#permissions-treeview .permission-checkbox');
                const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(allCheckboxes).some(cb => cb.checked);

                const globalSelectAll = document.getElementById('select-all');
                if (allChecked) {
                    globalSelectAll.checked = true;
                    globalSelectAll.indeterminate = false;
                } else if (someChecked) {
                    globalSelectAll.checked = false;
                    globalSelectAll.indeterminate = true;
                } else {
                    globalSelectAll.checked = false;
                    globalSelectAll.indeterminate = false;
                }
            }

            // التحقق من حالة تحديد الكل عند تحميل الصفحة
            updateGlobalSelectAll();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-lock text-warning me-2"></i>
                        إضافة دور جديد
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم الدور</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="permissions" class="form-label">الصلاحيات</label>
                            <div id="permissions-treeview" class="treeview">
                                <!-- خيار تحديد الكل العام -->
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                    <label class="form-check-label" for="select-all">تحديد الكل</label>
                                </div>
                                <div class="permissions-container">
                                    @foreach ($permissions as $section => $perms)
                                        <div class="permissions-column">
                                            <div class="accordion permissions-accordion" id="accordion-{{ $loop->index }}">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse-{{ $loop->index }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse-{{ $loop->index }}">
                                                            {{ $section }}
                                                        </button>
                                                    </h2>
                                                    <div id="collapse-{{ $loop->index }}"
                                                        class="accordion-collapse collapse"
                                                        aria-labelledby="heading-{{ $loop->index }}"
                                                        data-bs-parent="#accordion-{{ $loop->index }}">
                                                        <div class="accordion-body">
                                                            <!-- إضافة خط فاصل بين القسم والصلاحيات -->
                                                            <div class="section-divider"></div>
                                                            <!-- إضافة وصف مختصر لكل قسم -->
                                                            <div class="section-description">
                                                                {{ __('تحديد الصلاحيات الخاصة بـ') . $section }}
                                                            </div>
                                                            <!-- خيار تحديد الكل داخل القسم -->
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox"
                                                                    class="form-check-input section-select-all"
                                                                    id="section-select-all-{{ $loop->index }}"
                                                                    data-section-index="{{ $loop->index }}">
                                                                <label class="form-check-label"
                                                                    for="section-select-all-{{ $loop->index }}">تحديد الكل
                                                                    داخل القسم</label>
                                                            </div>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach ($perms as $permission)
                                                                    @if ($permission)
                                                                        <li class="permission-item">
                                                                            <input type="checkbox" name="permissions[]"
                                                                                value="{{ $permission->name }}"
                                                                                class="form-check-input permission-checkbox"
                                                                                id="permission-{{ $permission->id }}">
                                                                            <label class="form-check-label"
                                                                                for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">إضافة</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
