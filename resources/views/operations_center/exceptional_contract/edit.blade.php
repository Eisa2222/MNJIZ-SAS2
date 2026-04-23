@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات العقد الإستثنائي')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.exceptional-contracts.index') }}"> العقود اللإستثنائية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات العقد الإستثنائي</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات العقد الإستثنائي"
            data-page-url="{{ url()->current() }}" onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
@section('page-script')
    @vite(['resources/assets/js/validation-exceptional-contract.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تعريف مصفوفة تحتوي على معرفات المحررات
            var editors = [{
                    editorId: 'editor-scope-of-work',
                    hiddenInputId: 'scope_of_work',
                    initialContent: @json(old('scope_of_work', $exceptional_contract->scope_of_work))
                },
                {
                    editorId: 'editor-reasons',
                    hiddenInputId: 'reasons',
                    initialContent: @json(old('reasons', $exceptional_contract->reasons))
                },
                {
                    editorId: 'editor-equivalent',
                    hiddenInputId: 'equivalent',
                    initialContent: @json(old('equivalent', $exceptional_contract->equivalent))
                },
            ];

            // تخزين كائنات المحررات
            var quillEditors = {};

            // تهيئة كل محرر Quill مع تعيين المحتوى الأولي
            editors.forEach(function(item) {
                quillEditors[item.editorId] = new Quill('#' + item.editorId, {
                    theme: 'snow',
                    placeholder: 'اكتب هنا...',
                    modules: {
                        toolbar: [
                            // أدوات العناوين
                            [{
                                header: [1, 2, 3, 4, 5, 6, false]
                            }],

                            // أدوات التنسيق النصي
                            ['bold', 'italic', 'underline', 'strike'],

                            // أدوات النصوص
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }],
                            [{
                                script: 'sub'
                            }, {
                                script: 'super'
                            }], // نص علوي وسفلي
                            [{
                                indent: '-1'
                            }, {
                                indent: '+1'
                            }], // زيادة ونقصان المسافة البادئة
                            [{
                                direction: 'rtl'
                            }], // اتجاه النص

                            // خيارات الخطوط والمحاذاة
                            [{
                                size: ['small', false, 'large', 'huge']
                            }], // أحجام الخط
                            [{
                                header: [1, 2, 3, 4, 5, 6, false]
                            }], // العناوين
                            [{
                                align: []
                            }], // المحاذاة

                            // أدوات اللون والخلفية
                            [{
                                color: []
                            }, {
                                background: []
                            }], // الألوان

                            // الوسائط المتعددة
                            ['link', 'image', 'video'],

                            // أدوات الحذف والإدراج
                            ['clean'], // إزالة التنسيقات
                        ]
                    }
                });

                // تعيين المحتوى المبدئي إلى محرر Quill
                if (item.initialContent) {
                    quillEditors[item.editorId].clipboard.dangerouslyPasteHTML(item.initialContent);
                }

                // تعيين المحتوى المبدئي إلى الحقل المخفي عند تحميل الصفحة
                var initialContent = quillEditors[item.editorId].root.innerHTML;
                document.getElementById(item.hiddenInputId).value = initialContent;

                // تحديث الحقل المخفي عند تغيير المحتوى
                quillEditors[item.editorId].on('text-change', function() {
                    var content = quillEditors[item.editorId].root.innerHTML;
                    document.getElementById(item.hiddenInputId).value = content;
                });
            });

            // تحديث الحقول المخفية قبل إرسال النموذج كخطوة احتياطية
            var form = document.getElementById('power-form');
            form.addEventListener('submit', function() {
                editors.forEach(function(item) {
                    var content = quillEditors[item.editorId].root.innerHTML;
                    document.getElementById(item.hiddenInputId).value = content;
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2" data-mode="edit">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">تعديل بيانات العقد الإستثنائي</span>
                                <span class="bs-stepper-subtitle">تفاصيل العقد الإستثنائي</span>
                            </span>
                        </button>
                    </div>

                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="form"
                        action="{{ route('operations-center.exceptional-contracts.update', $exceptional_contract->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content  dstepper-block">

                            <div class="row  g-3">

                                <div class="col-md-6">
                                    <label for="contract_name" class="form-label">اسم العقد الإستثنائي</label>
                                    <input type="text" id="contract_name" name="contract_name" class="form-control"
                                        value="{{ old('contract_name', $exceptional_contract->contract_name) }}"
                                        placeholder="اسم العقد الإستثنائي" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="project_name" class="form-label">اسم المشروع</label>
                                    <input type="text" id="project_name" name="project_name" class="form-control"
                                        value="{{ old('project_name', $exceptional_contract->project_name) }}"
                                        placeholder="اسم المشروع" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">المكلف</label>
                                    <select id="employee_id" name="employee_id" class="form-select select2" required
                                        data-placeholder="اختر المكلف">
                                        <option value=""></option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('employee_id', $exceptional_contract->employee_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">العميل</label>
                                    <select id="customer_id" name="customer_id" class="form-select select2" required
                                        data-placeholder="اختر العميل">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id', $exceptional_contract->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="reasons"> الأسباب</label>
                                    <div id="editor-reasons" style="height: 300px;">
                                        {!! old('reasons') !!}
                                    </div>
                                    <input type="hidden" name="reasons" id="reasons">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="scope_of_work">نطاق العمل</label>
                                    <div id="editor-scope-of-work" style="height: 300px;">
                                        {!! old('scope_of_work') !!}
                                    </div>
                                    <input type="hidden" name="scope_of_work" id="scope_of_work">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="equivalent">المقابل </label>
                                    <div id="editor-equivalent" style="height: 300px;">
                                        {!! old('equivalent') !!}
                                    </div>
                                    <input type="hidden" name="equivalent" id="equivalent">
                                </div>


                            </div>
                            <div class="d-flex justify-content-end mt-4 ">
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
