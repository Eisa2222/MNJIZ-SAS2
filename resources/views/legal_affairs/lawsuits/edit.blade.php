@extends('layouts.layoutMaster')

@section('title', 'تعديل دعوى')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.lawsuits.index') }}"> الدعاوى</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات الدعوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الدعوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-wizard-validation-lawsuits.js'])
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                // Check if the element exists and has options
                if ($(this).length && $(this).find('option').length) {
                    $(this).select2({
                        placeholder: $(this).data('placeholder') || 'اختر خيارًا',
                        allowClear: true,
                        closeOnSelect: !$(this).prop('multiple'),
                        width: '100%',
                        language: 'ar',
                        dir: 'rtl',
                        escapeMarkup: function(markup) {
                            return markup;
                        },
                        templateResult: function(data) {
                            // Check if data exists and has an element property
                            if (!data.id) {
                                return data.text || '';
                            }

                            var content = data.element ? data.element.getAttribute(
                                'data-content') : null;
                            return content ? $('<span>' + content + '</span>') : data.text;
                        },
                        templateSelection: function(data) {
                            // Check if data exists and has an element property
                            if (!data.id) {
                                return data.text || '';
                            }

                            var content = data.element ? data.element.getAttribute(
                                'data-content') : null;
                            return content ? $('<span>' + content + '</span>') : data.text;
                        }
                    });
                } else {
                    console.warn('Select2 element or options missing');
                }
            });
        });
    </script>
@endsection

@section('content')
    <!-- Form Wizard -->
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- مراحل النموذج -->
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل الدعوى</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الإضافية</span>
                                <span class="bs-stepper-subtitle">تفاصيل إضافية عن الدعوى</span>
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
                    <form id="power-form" action="{{ route('legal-affairs.lawsuits.update', $lawsuit->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- تحديد طريقة HTTP للتحديث -->
                        <!-- المرحلة الأولى: المعلومات الأساسية -->
                        <div id="basic-info" class="content  dstepper-block">
                            <div class="row g-3">
                                <!-- حقول المعلومات الأساسية -->
                                <div class="col-md-6">
                                    <label for="name" class="form-label">اسم الدعوى</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $lawsuit->name) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="lawsuit_number" class="form-label">رقم الدعوى</label>
                                    <input type="number" id="lawsuit_number" name="lawsuit_number" class="form-control"
                                        value="{{ old('lawsuit_number', $lawsuit->lawsuit_number) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="plaintiff_id" class="form-label">المدعي</label>
                                    <select id="plaintiff_id" name="plaintiff_id[]" class="w-100 select2"
                                        data-style="btn-default" required multiple data-actions-box="true"
                                        data-live-search="true" data-placeholder="اختر المدعي" data-dropup-auto="false">
                                        <optgroup label="العملاء">
                                            @foreach ($mergedList->where('thisType', 'customer') as $customer)
                                                <option value="{{ $customer->id }}-customer"
                                                    data-content="<i class='ti ti-rosette-discount-check-filled text-success'></i> {{ $customer->name }}"
                                                    {{ in_array($customer->id . '-App\Models\OperationsCenter\Customer', old('plaintiff_id', $existingPlaintiffs)) ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="الخصوم">
                                            @foreach ($mergedList->where('thisType', 'opponent') as $opponent)
                                                <option value="{{ $opponent->id }}-opponent"
                                                    data-content="<i class='ti ti-rosette-discount-check-filled text-danger' title='خصم'></i> {{ $opponent->name }}"
                                                    {{ in_array($opponent->id . '-App\Models\LegalAffair\Opponent', old('plaintiff_id', $existingPlaintiffs)) ? 'selected' : '' }}>
                                                    {{ $opponent->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="defendant_id" class="form-label">المدعى عليه</label>
                                    <select id="defendant_id" name="defendant_id[]" class="w-100 select2"
                                        data-style="btn-default" required multiple data-actions-box="true"
                                        data-live-search="true" data-placeholder="اختر المدعى عليه"
                                        data-dropup-auto="false">
                                        <optgroup label="العملاء">
                                            @foreach ($mergedList->where('thisType', 'customer') as $customer)
                                                <option value="{{ $customer->id }}-customer"
                                                    data-content="<i class='ti ti-rosette-discount-check-filled text-success'></i> {{ $customer->name }}"
                                                    {{ in_array($customer->id . '-App\Models\OperationsCenter\Customer', old('defendant_id', $existingDefendants)) ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="الخصوم">
                                            @foreach ($mergedList->where('thisType', 'opponent') as $opponent)
                                                <option value="{{ $opponent->id }}-opponent"
                                                    data-content="<i class='ti ti-rosette-discount-check-filled text-danger'  title='خصم'></i> {{ $opponent->name }}"
                                                    {{ in_array($opponent->id . '-App\Models\LegalAffair\Opponent', old('defendant_id', $existingDefendants)) ? 'selected' : '' }}>
                                                    {{ $opponent->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                {{-- التصنيفات الرئيسية --}}
                                <div class="col-md-4">
                                    <label for="category_id" class="form-label">التصنيفات الرئيسية</label>
                                    <select id="category_id" name="category_id" class="form-select select2" required
                                        data-placeholder="اختر التصنيف الرئيسي">
                                        <option value=""></option>
                                        @foreach ($mainCategories as $mainCategory)
                                            <option value="{{ $mainCategory->id }}"
                                                {{ old('category_id', $lawsuit->category_id) == $mainCategory->id ? 'selected' : '' }}>
                                                {{ $mainCategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- التصنيفات الفرعية --}}
                                <div class="col-md-4">
                                    <label for="subcategory_id" class="form-label">التصنيفات الفرعية
                                        <span id="subcategories-count" class="badge bg-label-info ms-1"
                                            style="display: none;"></span>
                                        <span title="التصنيفات الفرعية تظهر بناءً على التصنيف الرئيسي المختار"
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <div class="position-relative">
                                        <select id="subcategory_id" name="subcategory_id" class="form-select select2"
                                            required data-placeholder="اختر التصنيف الفرعي">
                                            <option value=""></option>
                                            @foreach ($subcategories as $subcategory)
                                                <option value="{{ $subcategory->id }}"
                                                    {{ old('subcategory_id', $lawsuit->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                    {{ $subcategory->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Loader للتصنيفات الفرعية -->
                                        <div id="subcategories-loader"
                                            class="position-absolute top-50 end-0 translate-middle-y me-3"
                                            style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- نوع الدعوى --}}
                                <div class="col-md-4">
                                    <label for="lawsuit_type_id" class="form-label">نوع الدعوى
                                        <span id="lawsuit-types-count" class="badge bg-label-success ms-1"
                                            style="display: none;"></span>
                                        <span title="نوع الدعوى يظهر بناءً على التصنيف الفرعي المختار"
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <div class="position-relative">
                                        <select id="lawsuit_type_id" name="lawsuit_type_id" class="form-select select2"
                                            required data-placeholder="اختر نوع الدعوى">
                                            <option value=""></option>
                                            @foreach ($lawsuitTypes as $lawsuitType)
                                                <option value="{{ $lawsuitType->id }}"
                                                    {{ old('lawsuit_type_id', $lawsuit->lawsuit_type_id) == $lawsuitType->id ? 'selected' : '' }}>
                                                    {{ $lawsuitType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div id="lawsuit-types-loader"
                                            class="position-absolute top-50 end-0 translate-middle-y me-3"
                                            style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-success" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="power_of_attorney_id" class="form-label">الوكالة</label>
                                    <select id="power_of_attorney_id" name="power_of_attorney_id[]" class="w-100 select2"
                                        data-style="btn-default" required multiple data-actions-box="true"
                                        data-live-search="true" data-placeholder="اختر الوكالة" data-dropup-auto="false">
                                        @foreach ($power_of_attorneys as $power_of_attorney)
                                            <option value="{{ $power_of_attorney->id }}"
                                                {{ in_array($power_of_attorney->id, old('power_of_attorney_id', $lawsuit->powerOfAttorneys->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                {{ $power_of_attorney->power_name }} -
                                                {{ $power_of_attorney->power_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="project_id" class="form-label">المشروع</label>
                                    <span title="المشاريع التي تظهر هي المشاريع التي حالتها (جديد - في المسار) "
                                        style="color: var(--primary-color);">
                                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                    </span>
                                    <select id="project_id" name="project_id" class="form-select select2" required
                                        data-placeholder="اختر المشروع">
                                        <option value=""></option>
                                        @foreach ($projects as $single_project)
                                            <option value="{{ $single_project->id }}"
                                                {{ old('project_id', $lawsuit->project_id) == $single_project->id ? 'selected' : '' }}>
                                                {{ $single_project->project_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- فريق الدعوى -->
                                <div class="col-md-6">
                                    <label for="assigned_to" class="form-label">فريق الدعوى
                                        <span id="team-count" class="badge bg-label-success ms-1"
                                            style="display: none;"></span>
                                        <span title="فريق الدعوى يتم جلبه تلقائيًا من فريق المشروع المحدد"
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select id="assigned_to" name="assigned_to[]" class="w-100 select2"
                                        data-style="btn-default" required multiple data-actions-box="true"
                                        data-live-search="true" data-dropup-auto="false"
                                        data-placeholder="اختر فريق الدعوى">
                                    </select>
                                </div>

                                <script>
                                    window.assignedTeamMembers = @json($assignedTeamMembers->pluck('user_id')->toArray());
                                </script>

                                <div class="col-md-6">
                                    <label for="main_courts_id" class="form-label">المحكمة</label>
                                    <select id="main_courts_id" name="main_courts_id" class="form-select select2"
                                        required data-placeholder="اختر المحكمة">
                                        <option value=""></option>
                                        @foreach ($settings_main_courts as $court)
                                            <option value="{{ $court->id }}"
                                                {{ old('main_courts_id', $lawsuit->main_courts_id) == $court->id ? 'selected' : '' }}>
                                                {{ $court->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <div class="col-md-6">
                                <label for="entity_ranks_id" class="form-label">درجة الجهة</label>
                                <select id="entity_ranks_id" name="entity_ranks_id" class="form-select select2" required
                                    data-placeholder="اختر درجة الجهة">
                                    <option value=""></option>
                                    @foreach ($settings_entity_ranks as $ranks)
                                    <option value="{{ $ranks->id }}" {{ (old('entity_ranks_id', $lawsuit->
                                        entity_ranks_id) == $ranks->id) ? 'selected' : '' }}>{{ $ranks->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}

                                <div class="col-md-6">
                                    <label for="regions_id" class="form-label">المدينة</label>
                                    <select id="regions_id" name="regions_id" class="form-select select2" required
                                        data-placeholder="اختر المدينة">
                                        <option value=""></option>
                                        @foreach ($settings_regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('regions_id', $lawsuit->regions_id) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>
                        <!-- المرحلة الثانية: المعلومات الإضافية -->
                        <div id="additional-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-6 ">
                                    <label for="circle" class="form-label">الدائرة</label>
                                    <input type="text" id="circle" name="circle" class="form-control"
                                        value="{{ old('circle', $lawsuit->circle) }}" />
                                </div>

                                <div class="col-md-6 ">

                                </div>

                                <div class="col-md-6">
                                    <label for="lawsuit_subject" class="form-label">موضوع الدعوى</label>
                                    <textarea id="lawsuit_subject" name="lawsuit_subject" class="form-control" rows="4">{{ old('lawsuit_subject', $lawsuit->lawsuit_subject) }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="plaintiff_requests" class="form-label">طلبات المدعي</label>
                                    <textarea id="plaintiff_requests" name="plaintiff_requests" class="form-control" rows="4">{{ old('plaintiff_requests', $lawsuit->plaintiff_requests) }}</textarea>
                                </div>

                                <div class="col-md-12">
                                    <label for="lawsuit_proofs" class="form-label">أسانيد الدعوى</label>
                                    <textarea id="lawsuit_proofs" name="lawsuit_proofs" class="form-control" rows="4">{{ old('lawsuit_proofs', $lawsuit->lawsuit_proofs) }}</textarea>
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // عند اختيار التصنيف الرئيسي
            $('#category_id').on('change', function() {
                const categoryId = $(this).val();
                const subcategorySelect = $('#subcategory_id');
                const lawsuitTypeSelect = $('#lawsuit_type_id');
                const subcategoriesLoader = $('#subcategories-loader');
                const subcategoriesCount = $('#subcategories-count');
                const lawsuitTypesCount = $('#lawsuit-types-count');

                // تنظيف فوري للحقول
                subcategorySelect.empty().append('<option value="">جاري التحميل...</option>');
                lawsuitTypeSelect.empty().append('<option value="">اختر التصنيف الفرعي أولاً</option>');
                lawsuitTypeSelect.prop('disabled', true);
                subcategoriesCount.hide();
                lawsuitTypesCount.hide();

                if (categoryId) {
                    // إظهار loader
                    subcategoriesLoader.show();
                    subcategorySelect.prop('disabled', true);

                    $.ajax({
                        url: '/employees/legal-affairs/lawsuits/get-subcategories/' +
                            categoryId,
                        type: 'GET',
                        success: function(data) {
                            // إخفاء loader
                            subcategoriesLoader.hide();

                            subcategorySelect.empty().append(
                                '<option value="">اختر التصنيف الفرعي</option>');

                            if (data && data.length > 0) {
                                $.each(data, function(key, value) {
                                    subcategorySelect.append('<option value="' + value
                                        .id + '">' + value.name + '</option>');
                                });
                                subcategorySelect.prop('disabled', false);

                                // إظهار عدد التصنيفات الفرعية
                                subcategoriesCount.text(`${data.length} تصنيف فرعي`)
                                    .removeClass('bg-label-warning bg-label-danger')
                                    .addClass('bg-label-info')
                                    .show();


                            } else {
                                subcategorySelect.append(
                                    '<option value="">لا توجد تصنيفات فرعية متاحة</option>');
                                subcategoriesCount.text('0 تصنيف فرعي')
                                    .removeClass('bg-label-info bg-label-danger')
                                    .addClass('bg-label-warning')
                                    .show();

                            }

                            // تحديث Select2
                            subcategorySelect.select2({
                                placeholder: 'اختر التصنيف الفرعي',
                                allowClear: true,
                                width: '100%',
                                language: 'ar',
                                dir: 'rtl'
                            });
                        },
                        error: function(xhr, status, error) {
                            // إخفاء loader
                            subcategoriesLoader.hide();

                            subcategorySelect.empty().append(
                                '<option value="">خطأ في تحميل البيانات</option>');
                            subcategoriesCount.text('خطأ')
                                .removeClass('bg-label-info bg-label-warning')
                                .addClass('bg-label-danger')
                                .show();

                        }
                    });
                } else {
                    // إذا لم يتم اختيار تصنيف رئيسي
                    subcategorySelect.empty().append(
                        '<option value="">اختر التصنيف الرئيسي أولاً</option>');
                    subcategorySelect.prop('disabled', true);
                    subcategoriesCount.hide();
                }
            });

            // عند اختيار التصنيف الفرعي
            $('#subcategory_id').on('change', function() {
                const subcategoryId = $(this).val();
                const lawsuitTypeSelect = $('#lawsuit_type_id');
                const lawsuitTypesLoader = $('#lawsuit-types-loader');
                const lawsuitTypesCount = $('#lawsuit-types-count');

                if (subcategoryId) {
                    // إظهار loader
                    lawsuitTypesLoader.show();
                    lawsuitTypeSelect.empty().append('<option value="">جاري التحميل...</option>');
                    lawsuitTypeSelect.prop('disabled', true);

                    $.ajax({
                        url: '/employees/legal-affairs/lawsuits/get-lawsuit-types/' +
                            subcategoryId,
                        type: 'GET',
                        success: function(data) {
                            // إخفاء loader
                            lawsuitTypesLoader.hide();

                            lawsuitTypeSelect.empty().append(
                                '<option value="">اختر نوع الدعوى</option>');

                            if (data && data.length > 0) {
                                $.each(data, function(key, value) {
                                    lawsuitTypeSelect.append('<option value="' + value
                                        .id + '">' + value.name + '</option>');
                                });
                                lawsuitTypeSelect.prop('disabled', false);

                                // إظهار عدد أنواع الدعاوى
                                lawsuitTypesCount.text(`${data.length} نوع دعوى`)
                                    .removeClass('bg-label-warning bg-label-danger')
                                    .addClass('bg-label-success')
                                    .show();


                            } else {
                                lawsuitTypeSelect.append(
                                    '<option value="">لا توجد أنواع دعاوى متاحة</option>');
                                lawsuitTypesCount.text('0 نوع دعوى')
                                    .removeClass('bg-label-success bg-label-danger')
                                    .addClass('bg-label-warning')
                                    .show();

                                toastr.warning('لا توجد أنواع دعاوى متاحة');
                            }

                            // تحديث Select2
                            lawsuitTypeSelect.select2({
                                placeholder: 'اختر نوع الدعوى',
                                allowClear: true,
                                width: '100%',
                                language: 'ar',
                                dir: 'rtl'
                            });
                        },
                        error: function(xhr, status, error) {
                            // إخفاء loader
                            lawsuitTypesLoader.hide();

                            lawsuitTypeSelect.empty().append(
                                '<option value="">خطأ في تحميل البيانات</option>');
                            lawsuitTypesCount.text('خطأ')
                                .removeClass('bg-label-success bg-label-warning')
                                .addClass('bg-label-danger')
                                .show();

                            toastr.error('حدث خطأ أثناء تحميل أنواع الدعاوى');
                        }
                    });

                } else {
                    // إذا لم يتم اختيار تصنيف فرعي
                    lawsuitTypeSelect.empty().append('<option value="">اختر التصنيف الفرعي أولاً</option>');
                    lawsuitTypeSelect.prop('disabled', true);
                    lawsuitTypesCount.hide();
                }
            });

            // التأكد من تنظيف الحقول عند تحميل الصفحة
            $(window).on('load', function() {
                if (!$('#category_id').val()) {
                    $('#subcategory_id').prop('disabled', true);
                    $('#lawsuit_type_id').prop('disabled', true);
                }
            });

            // تحميل التصنيفات الفرعية وأنواع الدعاوى الحالية عند تحميل الصفحة
            var selectedCategoryId = "{{ $case->category_id ?? '' }}";
            if (selectedCategoryId) {
                $('#category_id').val(selectedCategoryId).trigger('change');
            }
        });
    </script>

@endsection
