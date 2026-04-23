@extends('layouts.layoutMaster', ['excludeJquery' => true])

@section('title', 'تعديل النموذج')

@section('breadcrumb')
    <li><a href="{{ route('settings-templates.index') }}">النماذج</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل النموذج</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل النموذج" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script src="https://cdn.tiny.cloud/1/nvyqwuwt7fk7h1bck3spzsrvislolre7pukg4a8wxla9xiu4/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            language: 'ar',
            directionality: 'rtl',
            selector: 'textarea',
            plugins: [
                'lists',
                'table',
                'link',
                'image',
                'media',
                'code'
            ],

            font_family_formats: [
                'Default=;', // هذا الخيار يزيل inline font-family
                'System UI=system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI","Helvetica Neue",Arial,sans-serif',
                'Arial=arial,helvetica,sans-serif',
                'Courier New=courier new,courier'
            ].join(';'),
            content_style: 'body{font-family: system-ui, sans-serif;}',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',

            setup: function(editor) {

                // عند تهيئة المحرر، قم بتعيين المحتوى القديم وحدّث الحقل المخفي
                editor.on('init', function(e) {
                    editor.setContent(`{!! old('content', $template->content) !!}`);
                    $('#content').val(editor.getContent());
                });
                // تحديث الحقل المخفي عند كل تغيير
                editor.on('change keyup', function(e) {
                    $('#content').val(editor.getContent());
                });
            }
        });
    </script>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تعديل تفاصيل النموذج</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الإضافية</span>
                                <span class="bs-stepper-subtitle">تعديل تفاصيل إضافية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#preview-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">عرض النموذج</span>
                                <span class="bs-stepper-subtitle">معاينة النموذج</span>
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
                    <form id="template-form" action="{{ route('settings-templates.update', $template->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- الخطوة الأولى: المعلومات الأساسية -->
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-6">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">اسم النموذج</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $template->name) }}" required />
                                </div>
                                <div class="col-md-6">
                                    <label for="template_type" class="form-label">نوع النموذج</label>
                                    <select id="template_type" disabled name="template_type" class="form-select select2"
                                        required>
                                        <option value="" disabled>اختر النوع المناسب</option>
                                        <option value="offers"
                                            {{ old('template_type', $template->template_type) == 'offers' ? 'selected' : '' }}>
                                            نموذج خاص للعروض</option>

                                        <option value="contracts"
                                            {{ old('template_type', $template->template_type) == 'contracts' ? 'selected' : '' }}>
                                            نموذج خاص للعقود</option>

                                        <option value="salary_definition"
                                            {{ old('template_type', $template->template_type) == 'salary_definition' ? 'selected' : '' }}>
                                            نموذج خاص بتعريف الراتب
                                        </option>

                                        <option value="salary_fixation"
                                            {{ old('template_type', $template->template_type) == 'salary_fixation' ? 'selected' : '' }}>
                                            نموذج خاص بتثبيت الراتب
                                        </option>

                                        <option value="training_certificate"
                                            {{ old('template_type', $template->template_type) == 'training_certificate' ? 'selected' : '' }}>
                                            نموذج خاص  بإفادة التدريب
                                        </option>

                                        <option value="clearance_certificates"
                                            {{ old('template_type', $template->template_type) == 'clearance_certificates' ? 'selected' : '' }}>
                                            نموذج خاص بإخلاء الطرف
                                        </option>

                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>
                        <!-- الخطوة الثانية: المعلومات الإضافية -->
                        <div id="additional-info" class="content">
                            <div class="row g-6">
                                <div class="mb-5 col-12">
                                    <label class="form-label">المتغيرات</label>
                                    <div class="d-flex flex-wrap gap-2" id="variables-container">
                                        <!-- المتغيرات سيتم تحميلها هنا -->
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="content">النموذج</label>
                                    <!-- سيتم تهيئة المحرر باستخدام TinyMCE -->
                                    <textarea id="editor-container" name="editor_content">{!! old('content', $template->content) !!}</textarea>
                                    <input type="hidden" name="content" id="content">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>
                        <!-- الخطوة الثالثة: المعاينة -->
                        <div id="preview-info" class="content">
                            <div class="row">
                                <div class="col-12">
                                    <!-- صورة الهيدر -->
                                    <div class="d-flex justify-content-between">
                                        @if (App\Helpers\SettingsHelper::get('horizontal_header_image'))
                                            <img src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_header_image')) }}"
                                                alt="Header Image" class="img-fluid d-block mb-3"
                                                style="max-height: 100px; object-fit: cover;">
                                        @else
                                            <p class="text-danger">يرجي اختيار صورة للترويسة من الاعدادات الخاصة بالطباعة
                                            </p>
                                        @endif
                                    </div>
                                    <!-- منطقة المعاينة -->
                                    <div id="template-preview" class="p-3" style="min-height: 300px; ">
                                        <!-- سيتم عرض المحتوى هنا -->
                                    </div>
                                    <!-- صورة الفوتر -->
                                    @if (App\Helpers\SettingsHelper::get('horizontal_footer_image'))
                                        <img src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_footer_image')) }}"
                                            alt="Footer Image" class="img-fluid d-block w-100 mt-3"
                                            style="max-height: 150px; object-fit: cover;">
                                    @else
                                        <p class="text-danger">يرجي اختيار صورة للفوتر من الاعدادات الخاصة بالطباعة</p>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">تعديل النموذج</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-wizard-validation-template.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const templatesTypeSelect = $('#template_type');
            const variablesContainer = $('#variables-container');
            const oldType = "{{ old('template_type', $template->template_type) }}";

            // تهيئة Select2
            templatesTypeSelect.select2({
                placeholder: "اختر النوع المناسب",
                allowClear: true
            });

            // دالة لجلب المتغيرات باستخدام AJAX
            function fetchVariables(type) {
                return $.ajax({
                    url: "{{ route('templates.getVariables') }}",
                    type: 'GET',
                    data: {
                        type: type
                    },
                    dataType: 'json',
                });
            }

            // تحميل المتغيرات
            function loadVariables(type) {
                variablesContainer.html('<p>جارٍ التحميل...</p>');
                fetchVariables(type)
                    .done(function(data) {
                        variablesContainer.empty();
                        if (data.length > 0) {
                            data.forEach(function(variable) {
                                const button = $('<button>')
                                    .attr('type', 'button')
                                    .addClass('btn btn-outline-secondary variable-button')
                                    .attr('data-placeholder', variable.placeholder)
                                    .text(variable.name);
                                variablesContainer.append(button);
                            });
                        } else {
                            variablesContainer.html('<p>لا توجد متغيرات لهذا النوع.</p>');
                        }
                    })
                    .fail(function() {
                        variablesContainer.html('<p>حدث خطأ أثناء تحميل المتغيرات.</p>');
                    });
            }

            // تحديث المعاينة عند الانتقال للخطوة الثالثة
            $('.btn-next').on('click', function() {
                const currentStep = $('.bs-stepper-header .step.active').data('target');
                if (currentStep === '#additional-info') {
                    const templateContent = tinymce.activeEditor.getContent();
                    $('#template-preview').html(templateContent || '<p>لا توجد بيانات للعرض.</p>');
                }
            });

            // حدث عند تغيير نوع النموذج
            templatesTypeSelect.on('select2:select', function(e) {
                const selectedType = $(this).val();
                if (selectedType) {
                    // تفريغ محتوى المحرر عند تغيير النوع
                    tinymce.activeEditor.setContent('');
                    loadVariables(selectedType);
                } else {
                    variablesContainer.empty();
                }
            });

            // تحميل المتغيرات بناءً على القيمة القديمة
            if (oldType) {
                templatesTypeSelect.val(oldType).trigger('change');
                loadVariables(oldType);
            }

            // إدراج المتغيرات في TinyMCE عند النقر على زر المتغير
            variablesContainer.on('click', 'button.variable-button', function() {
                const placeholder = $(this).data('placeholder');
                tinymce.activeEditor.execCommand('mceInsertContent', false, placeholder);
            });

            // عرض المعاينة عند الانتقال للخطوة الثالثة عبر حدث bs-stepper
            document.querySelector('#wizard-validation').addEventListener('shown.bs-stepper', function(event) {
                if (event.detail.indexStep === 2) {
                    const templateContent = tinymce.activeEditor.getContent();
                    $('#template-preview').html(templateContent || '<p>لا توجد بيانات للعرض.</p>');
                }
            });

            // التعامل مع إرسال النموذج
            $('#template-form').on('submit', function(e) {
                $('#content').val(tinymce.activeEditor.getContent());
                // إذا كنت ترغب في منع الإرسال للتجربة، قم بإلغاء تعليق السطر التالي:
                // e.preventDefault();
            });
        });
    </script>
@endsection
