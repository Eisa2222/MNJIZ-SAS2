@extends('layouts.layoutMaster')

@section('title', 'إضافة مهمة جديدة')

@section('breadcrumb')
    <li><a href="#">مركز تنظيم الاعمال</a></li>
    <li><a href="{{ route('organization-center.tasks.index') }}">المهام</a></li>

    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة مهمة جديدة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة مهمة جديدة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')

    @vite(['resources/assets/js/organization-center/tasks/task-wizard-validation.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل المهمة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-2">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">الخطوات الإضافية</span>
                                <span class="bs-stepper-subtitle">خطوات المهمة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-3">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المرفقات</span>
                                <span class="bs-stepper-subtitle">مرفقات المهمة</span>
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
                    <form id="form" action="{{ route('organization-center.tasks.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content  dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label for="task_name" class="form-label">عنوان المهمة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" value="{{ old('task_name') }}" id="task_name"
                                        name="task_name" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="due_date" class="form-label">تاريخ الاستحقاق
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" value="{{ old('due_date') }}" name="due_date"
                                        id="due_date" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="task_field" class="form-label">
                                        مجال المهمة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="task_field" name="task_field" class="form-select select2"
                                        data-placeholder="اختر مجال المهمة" required>
                                        <option value=""></option>
                                        @foreach ($taskFieldOptions as $field)
                                            <option value="{{ $field['id'] }}"
                                                {{ old('task_field') == $field['id'] ? 'selected' : '' }}>
                                                {{ $field['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">أولوية المهمة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-start mt-3" style="gap: 50px;">
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                                id="priorityHigh" value="high"
                                                {{ old('priority') == 'high' ? 'checked' : '' }} required>
                                            <span
                                                style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                                            <span>مرتفعة</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                                id="priorityMedium" value="medium"
                                                {{ old('priority') == 'medium' ? 'checked' : '' }} required>
                                            <span
                                                style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                                            <span>متوسطة</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <input class="form-check-input priority-checkbox" type="radio"
                                                name="priority" id="priorityLow" value="low"
                                                {{ old('priority') == 'low' ? 'checked' : '' }} required>
                                            <span
                                                style="background-color: green; width: 15px; height: 15px; border-radius: 50%;"></span>
                                            <span>منخفضة</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- مشاريع -->
                                <div class="col-md-6 hide-elements" id="project_container">
                                    <label for="project_id" class="form-label"> المشروع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="project_id" name="project_id" class="form-select select2" required
                                        data-placeholder="اختر  المشروع ">
                                        <option></option>
                                    </select>
                                </div>

                                <!-- دعاوى -->
                                <div class="col-md-6 hide-elements" id="lawsuit_container">
                                    <label for="lawsuit_id" class="form-label"> الدعوى
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="lawsuit_id" name="lawsuit_id" class="form-select select2" required
                                        data-placeholder="اختر  الدعوى ">
                                        <option></option>
                                    </select>
                                </div>


                                <!-- قنوات التسويق -->
                                <div class="col-md-6 hide-elements" id="marketing_container">
                                    <label class="form-label" for="marketing_id">قناة التسويق</label>
                                    <select class="select2 form-select" id="marketing_id" name="marketing_id"
                                        data-placeholder="اختر قناة التسويق">
                                        <option value="">اختر قناة التسويق</option>
                                        @foreach ($marketingChannels as $channel)
                                            <option value="{{ $channel->id }}"
                                                {{ old('marketing_id') == $channel->id ? 'selected' : '' }}>
                                                {{ $channel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- قناة التسويق التفصيلية (حقل الموارد البشرية) -->
                                <div class="col-md-6" id="detailedMarketingChannelContainer" style="display: none;">
                                    <label class="form-label" for="detailed_marketing_channel_id">قناة التسويق
                                        التفصيلية</label>
                                    <select class="select2 form-select" id="detailed_marketing_channel_id"
                                        name="detailed_marketing_channel_id"
                                        data-placeholder="اختر قناة التسويق التفصيلية">
                                        <option value="">اختر قناة التسويق التفصيلية</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('detailed_marketing_channel_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->nickname ?? $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="detailedMarketingChannelError" style="color:red; display:none;">يرجى ملء حقل
                                        قناة التسويق التفصيلية</div>
                                </div>
                                <!-- قائمة العملاء -->
                                <div class="col-md-6" id="clientsContainer" style="display: none;">
                                    <label class="form-label" for="customer_id">العملاء</label>
                                    <select class="select2 form-select" id="customer_id" name="customer_id"
                                        data-placeholder="اختر العميل">
                                        <option value="">اختر العميل</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- قائمة مواقع التواصل الاجتماعي -->
                                <div class="col-md-6" id="socialMediaContainer" style="display: none;">
                                    <label class="form-label" for="social_media_id">مواقع التواصل الاجتماعي</label>
                                    <select class="select2 form-select" id="social_media_id" name="social_media_id"
                                        data-placeholder="اختر موقع التواصل الاجتماعي">
                                        <option value="">اختر موقع التواصل الاجتماعي</option>
                                        @foreach ($socials as $social)
                                            <option value="{{ $social->id }}"
                                                {{ old('social_media_id') == $social->id ? 'selected' : '' }}>
                                                {{ $social->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-12 mt-5">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="assigned_user_id" class="form-label"> المكلفين بالمهمة
                                            <span class="text-danger">*</span>
                                        </label>
                                        <button type="button" id="assign-myself-btn-create"
                                            class="btn btn-outline-secondary btn-sm" data-step-index="${stepCounter}">
                                            <i class="ti ti-user-plus me-1"></i>لنفسي
                                        </button>

                                    </div>
                                    <select id="assigned_user_id" name="assigned_user_ids[]" class="form-select select2"
                                        required multiple data-placeholder="اختر المكلفين">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ collect(old('assigned_user_ids'))->contains($user->id) ? 'selected' : '' }}
                                                data-image="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}">
                                                {{ $user->employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <label for="taskDescription" class="form-label">وصف المهمة</label>
                                    <textarea class="form-control" id="taskDescription" name="description" rows="4">{{ old('description') }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="additional-info-2" class="content">
                            <div class="row g-6">
                                <!-- حاوية الخطوات -->
                                <div id="stepsContainer" style="display: none;">
                                </div>

                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-primary btn-sm" id="addTaskStepBtn">
                                        <i class="ti ti-plus me-2"></i>إضافة خطوة جديدة
                                    </button>
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

                        <div id="additional-info-3" class="content">
                            <div class="row g-6">
                                <div class="col-12 mb-3">
                                    <div class="attachments-container">
                                        <!-- القائمة التي ستظهر فيها أسماء الملفات المضافة -->
                                        <div id="attachments-list" class="mb-2 border-bottom pb-2"></div>

                                        <!-- زر إضافة مرفق جديد بطريقة أبسط -->
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="attachments"
                                                name="attachments[]" multiple>
                                            <button type="button" class="btn btn-danger" id="clear-attachments-btn">
                                                حذف الكل
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {
            // معالجة زر "لنفسي"
            $('#assign-myself-btn-create').click(function() {
                var currentUserId = '{{ auth()->id() }}';
                var select = $('#assigned_user_id');
                var currentValues = select.val() || [];

                if (currentValues.includes(currentUserId)) {
                    toastr.info('أنت بالفعل مضاف بهذه المهمة');
                    return;
                }

                currentValues.push(currentUserId);
                select.val(currentValues).trigger('change');
                toastr.success('تم إضافتك للمهمة');
            });

            initializeMarketingChannels();

            handleTaskFieldDependency();
            initializeTaskSteps();
            initializeAttachments(); // تهيئة المرفقات

        });

        // دالة تهيئة قنوات التسويق
        function initializeMarketingChannels() {
            const marketingChannelSelect = $('#marketing_id');

            // إخفاء جميع الحقول المشروطة في البداية
            hideAllMarketingContainers();

            // تحقق من القيمة المحفوظة عند تحميل الصفحة
            const savedValue = marketingChannelSelect.val();
            if (savedValue) {
                showMarketingContainer(savedValue);
            }

            // معالجة تغيير قناة التسويق
            marketingChannelSelect.off('change.marketing').on('change.marketing', function() {
                const selectedValue = $(this).val();

                // إخفاء جميع الحقول أولاً
                hideAllMarketingContainers();

                // عرض الحقل المناسب حسب الاختيار
                if (selectedValue) {
                    showMarketingContainer(selectedValue);
                }
            });
        }

        // دالة إخفاء جميع حاويات التسويق
        function hideAllMarketingContainers() {
            // إخفاء جميع الحاويات
            $('#detailedMarketingChannelContainer').hide();
            $('#clientsContainer').hide();
            $('#socialMediaContainer').hide();

            // إزالة الخاصية required من جميع الحقول
            $('#detailed_marketing_channel_id').removeAttr('required');
            $('#customer_id').removeAttr('required');
            $('#social_media_id').removeAttr('required');

            // مسح القيم المختارة
            $('#detailed_marketing_channel_id').val('').trigger('change');
            $('#customer_id').val('').trigger('change');
            $('#social_media_id').val('').trigger('change');

            // إخفاء رسائل الخطأ
            $('#detailedMarketingChannelError').hide();
        }

        // دالة عرض الحاوية المناسبة
        function showMarketingContainer(channelId) {

            switch (channelId) {
                case '2': // الموارد البشرية
                    $('#detailedMarketingChannelContainer').show();
                    $('#detailed_marketing_channel_id').attr('required', 'required');
                    break;

                case '3': // العملاء
                    $('#clientsContainer').show();
                    $('#customer_id').attr('required', 'required');
                    break;

                case '6': // مواقع التواصل الاجتماعي
                    $('#socialMediaContainer').show();
                    $('#social_media_id').attr('required', 'required');
                    break;

                default:
                    break;
            }
        }

        function handleTaskFieldDependency() {
            const taskFieldSelect = $('#task_field');
            const projectContainer = $('#project_container');
            const lawsuitContainer = $('#lawsuit_container');
            const marketingContainer = $('#marketing_container');

            const projectSelect = $('#project_id');
            const lawsuitSelect = $('#lawsuit_id');
            const marketingSelect = $('#marketing_id');

            function resetDependentFields() {
                $('.hide-elements').hide();
                projectSelect.prop('required', false);
                lawsuitSelect.prop('required', false);
                marketingSelect.prop('required', false);

                projectSelect.val('').trigger('change');
                lawsuitSelect.val('').trigger('change');
                marketingSelect.val('').trigger('change');
            }

            function fetchProjects() {
                if (projectSelect.find('option').length > 1) {
                    return;
                }

                $.ajax({
                    url: "{{ route('get.projects.json') }}",
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        projectSelect.empty().append('<option value="">اختر المشروع</option>');
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(project) {
                                projectSelect.append(
                                    `<option value="${project.id}">${project.project_name}</option>`
                                );
                            });
                        } else {
                            toastr.warning('لا توجد مشاريع متاحة');
                        }
                        projectSelect.trigger('change');
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء جلب المشاريع');
                    }
                });
            }

            function fetchLawsuits() {
                if (lawsuitSelect.find('option').length > 1) {
                    return;
                }

                $.ajax({
                    url: "{{ route('get.lawsuits.json') }}",
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        lawsuitSelect.empty().append('<option value="">اختر الدعوى</option>');
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(lawsuit) {
                                lawsuitSelect.append(
                                    `<option value="${lawsuit.id}">${lawsuit.name}</option>`
                                );
                            });
                        } else {
                            toastr.warning('لا توجد دعاوى متاحة');
                        }
                        lawsuitSelect.trigger('change');
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء جلب الدعاوى');
                    }
                });
            }

            taskFieldSelect.on('change', function() {
                const selectedValue = $(this).val();
                resetDependentFields();

                switch (selectedValue) {
                    case 'projects':
                        projectContainer.show();
                        projectSelect.prop('required', true);
                        fetchProjects();
                        break;
                    case 'lawsuits':
                        lawsuitContainer.show();
                        lawsuitSelect.prop('required', true);
                        fetchLawsuits();
                        break;
                    case 'sales':
                        marketingContainer.show();
                        marketingSelect.prop('required', true);
                        // fetchLawsuits();
                        break;
                }
            });

            resetDependentFields();
            if (taskFieldSelect.val()) {
                taskFieldSelect.trigger('change');
            }
        }

        // نسخة ثابتة من خيارات الموظفين بدون selected
        const rawPeopleOptions = $('#assigned_user_id option')
            .clone()
            .removeAttr('selected')
            .prop('selected', false);

        function initializeTaskSteps() {
            let stepCounter = 0;
            const fvSteps = window.taskFormValidations[1]; // مثيل FormValidation للخطوة-2

            /* زر إنشاء خطوة */
            $('#addTaskStepBtn').off('click').on('click', addNewStep);

            /* =============== إنشاء خطوة =============== */
            function addNewStep() {
                stepCounter++;

                /* HTML الخطوة الجديدة */
                $('#stepsContainer').show().append(`
            <div class="step-block mb-3 border p-3 rounded" data-index="${stepCounter}">
                <div class="step-header mt-3 text-primary text-center">
                    <strong class="step-number">الخطوة ${stepCounter}</strong>
                </div>
                <div class="border-1 border-light border-dashed my-4"></div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">اسم الخطوة <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control step-name"
                              name="steps[${stepCounter}][name]"
                               data-step="${stepCounter}"  />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">تحتاج لاعتماد؟</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                  name="steps[${stepCounter}][needs_approval]"
                                   value="1"
                                   id="approval_${stepCounter}">
                            <label class="form-check-label" for="approval_${stepCounter}">نعم</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">المكلفين بالخطوة <span class="text-danger">*</span></label>
                        <select class="form-select select2-step step-users"
                                name="steps[${stepCounter}][assigned_user_ids][]"
                                multiple></select>
                    </div>
                </div>

                <div class="text-end mt-2">
                    <button type="button" class="btn btn-danger btn-sm remove-step-btn">
                        <i class="ti ti-trash me-1"></i>حذف
                    </button>
                </div>
            </div>`);

                const $block = $(`.step-block[data-index="${stepCounter}"]`);
                const $select = $block.find('.step-users');
                $select.append(rawPeopleOptions.clone())
                    .select2({
                        width: '100%',
                        dir: 'rtl',
                        allowClear: true,

                        placeholder: 'اختر المكلفين'
                    });

                // عند اختيار أو حذف موظّف أعِد التحقّق فورًا
                $select.on('select2:select select2:unselect', function() {
                    fvSteps.revalidateField(stepUsersField);
                });


                /* >>>  إضافة التحقق الديناميكى  <<< */
                const stepNameField = `step_name_${stepCounter}`;
                const stepUsersField = `step_users_${stepCounter}`;

                fvSteps.addField(stepNameField, {
                    selector: `.step-block[data-index="${stepCounter}"] .step-name`,
                    validators: {
                        notEmpty: {
                            message: 'اسم الخطوة مطلوب'
                        },
                        stringLength: {
                            min: 3,
                            max: 100,
                            message: 'يجب أن يكون اسم الخطوة بين 3 و 150 حرف.'
                        }
                    }
                });

                /* 2) المكلَّفون بالخطوة ـ مطلوب */
                fvSteps.addField(stepUsersField, {
                    selector: `.step-block[data-index="${stepCounter}"] .step-users`,
                    validators: {
                        notEmpty: {
                            message: 'اختر موظفًا واحدًا على الأقل'
                        }
                    }
                });


                /* تخزين الأسماء داخل العنصر لتسهيل الإزالة لاحقًا */
                $block.data('fv-fields', [stepNameField, stepUsersField]);

                toastr.success(`أُضيفت الخطوة ${stepCounter}`);
            }

            /* =============== حذف خطوة =============== */
            $(document).off('click', '.remove-step-btn')
                .on('click', '.remove-step-btn', function() {
                    const $block = $(this).closest('.step-block');
                    const fieldNames = $block.data('fv-fields') || [];

                    const stepBlock = $(this).closest('.step-block');

                    const stepNumber = stepBlock.find('.step-number').text();


                    Swal.fire({
                        title: 'تأكيد الحذف',
                        text: `هل أنت متأكد من حذف ${stepNumber}؟`,
                        icon: 'warning',
                        showCancelButton: true,
                        showConfirmButton: true,
                        showDenyButton: false,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-popup',
                            title: 'custom-title',
                            text: 'custom-text',
                            confirmButton: 'btn btn-success custom-confirm',
                            cancelButton: 'btn btn-danger custom-cancel'
                        },
                        confirmButtonText: 'تأكيد',
                        cancelButtonText: 'إلغاء',
                        reverseButtons: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            stepBlock.fadeOut(300, function() {

                                // إزالة حقول التحقق
                                fieldNames.forEach(name => fvSteps.removeField(name));

                                $block.remove();
                                reorderSteps();

                                // إذا لم تعد هناك خطوات
                                if ($('.step-block').length === 0) {
                                    $('#stepsContainer').hide();
                                    $('#addTaskStepBtn').html(
                                        '<i class="ti ti-plus me-2"></i>إضافة خطوة جديدة');
                                    stepCounter = 0;
                                }
                            });
                            toastr.success('تم حذف الخطوة بنجاح');
                        }
                    });
                });

            /* =============== إعادة الترقيم (اختياري) =============== */
            function reorderSteps() {
                $('.step-block').each((i, el) => {
                    const idx = i + 1;
                    $(el).attr('data-index', idx)
                        .find('.step-number').text(`الخطوة ${idx}`);
                });
            }
        }



        // دالة تهيئة المرفقات - الخطوة الثالثة
        function initializeAttachments() {
            // الحصول على عناصر واجهة المستخدم
            const fileInput = document.getElementById('attachments');
            const filesList = document.getElementById('attachments-list');
            const clearBtn = document.getElementById('clear-attachments-btn');

            // مصفوفة لتخزين الملفات المختارة
            let selectedFiles = [];

            // التحقق من وجود العناصر
            if (!fileInput || !filesList || !clearBtn) {
                return;
            }

            // تحديث قائمة الملفات عند اختيار ملفات جديدة
            fileInput.addEventListener('change', function(e) {
                // إضافة الملفات الجديدة إلى المصفوفة
                const newFiles = Array.from(e.target.files);

                // تجنب المرفقات المكررة
                newFiles.forEach(file => {
                    const exists = selectedFiles.some(existingFile =>
                        existingFile.name === file.name &&
                        existingFile.size === file.size
                    );

                    if (!exists) {
                        selectedFiles.push(file);
                    }
                });

                updateFilesList();
                updateFileInput();
            });

            // زر مسح جميع الملفات
            clearBtn.addEventListener('click', function() {
                selectedFiles = [];
                updateFilesList();
                updateFileInput();
                toastr.info('تم مسح جميع المرفقات');
            });

            // دالة حذف ملف واحد
            function removeFile(index) {
                if (index >= 0 && index < selectedFiles.length) {
                    const fileName = selectedFiles[index].name;
                    selectedFiles.splice(index, 1);
                    updateFilesList();
                    updateFileInput();
                    toastr.success(`تم حذف الملف: ${fileName}`);
                }
            }

            // دالة تحديث input الملفات
            function updateFileInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(file => {
                    dt.items.add(file);
                });
                fileInput.files = dt.files;
            }

            // دالة تحديث قائمة الملفات المعروضة
            function updateFilesList() {
                // مسح القائمة الحالية
                filesList.innerHTML = '';

                // تفعيل/تعطيل زر الحذف
                clearBtn.disabled = selectedFiles.length === 0;
                // إذا لم يتم اختيار ملفات
                if (selectedFiles.length === 0) {
                    filesList.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="ti ti-file-upload fa-3x mb-3 d-block"></i>
                    <h6>لم يتم اختيار أي ملفات</h6>
                    <p class="mb-0">يمكنك اختيار عدة ملفات دفعة واحدة أو سحبها وإفلاتها هنا</p>
                </div>
            `;
                    return;
                }

                // إنشاء حاوية للملفات
                const filesContainer = document.createElement('div');
                filesContainer.className = 'files-container';

                let totalSize = 0;

                selectedFiles.forEach((file, index) => {
                    totalSize += file.size;

                    // إنشاء عنصر لكل ملف
                    const fileItem = document.createElement('div');
                    fileItem.className =
                        'd-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded border';

                    // أيقونة الملف حسب النوع
                    const fileIcon = getFileIcon(file.name);

                    // اسم الملف وحجمه
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'd-flex align-items-center flex-grow-1';
                    fileInfo.innerHTML = `
                <i class="${fileIcon} me-3 text-primary fs-4"></i>
                <div>
                    <div class="fw-medium text-dark">${file.name}</div>
                    <small class="text-muted">${formatFileSize(file.size)}</small>
                </div>
            `;

                    // أزرار العمليات
                    const fileActions = document.createElement('div');
                    fileActions.className = 'd-flex align-items-center gap-2';
                    fileActions.innerHTML = `
                <span class="badge bg-success-subtle text-success border border-success-subtle">جديد</span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-file-btn"
                        data-index="${index}" title="حذف الملف">
                    <i class="ti ti-trash fs-6"></i>
                </button>
            `;

                    fileItem.appendChild(fileInfo);
                    fileItem.appendChild(fileActions);
                    filesContainer.appendChild(fileItem);
                });

                filesList.appendChild(filesContainer);

                // إضافة ملخص الملفات
                const summary = document.createElement('div');
                summary.className = 'mt-3 p-3 bg-opacity-10 rounded border border-primary-subtle';
                summary.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-files me-2 text-primary fs-5"></i>
                        <strong class="text-primary">
                            إجمالي الملفات: ${selectedFiles.length}
                        </strong>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex align-items-center justify-content-md-end">
                        <i class="ti ti-database me-2 text-primary fs-5"></i>
                        <strong class="text-primary">
                            الحجم الإجمالي: ${formatFileSize(totalSize)}
                        </strong>
                    </div>
                </div>
            </div>
        `;
                filesList.appendChild(summary);

                // ربط أحداث أزرار الحذف
                document.querySelectorAll('.remove-file-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        removeFile(index);
                    });
                });
            }

            // دالة للحصول على أيقونة الملف حسب النوع
            function getFileIcon(filename) {
                const extension = filename.split('.').pop().toLowerCase();
                const iconMap = {
                    'pdf': 'ti ti-file-type-pdf text-danger',
                    'doc': 'ti ti-file-type-doc text-primary',
                    'docx': 'ti ti-file-type-docx text-primary',
                    'xls': 'ti ti-file-type-xls text-success',
                    'xlsx': 'ti ti-file-type-xlsx text-success',
                    'ppt': 'ti ti-file-type-ppt text-warning',
                    'pptx': 'ti ti-file-type-pptx text-warning',
                    'jpg': 'ti ti-photo text-info',
                    'jpeg': 'ti ti-photo text-info',
                    'png': 'ti ti-photo text-info',
                    'gif': 'ti ti-photo text-info',
                    'zip': 'ti ti-file-zip text-secondary',
                    'rar': 'ti ti-file-zip text-secondary',
                    'txt': 'ti ti-file-text text-muted',
                    'default': 'ti ti-file text-muted'
                };
                return iconMap[extension] || iconMap['default'];
            }

            // دالة لتنسيق حجم الملف
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 بايت';
                const k = 1024;
                const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // تهيئة القائمة عند تحميل الصفحة
            updateFilesList();

            // إضافة drag and drop functionality
            const attachmentsContainer = document.querySelector('.attachments-container');
            if (attachmentsContainer) {
                // منع السلوك الافتراضي للمتصفح
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                // تأثيرات بصرية للـ drag and drop
                ['dragenter', 'dragover'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, unhighlight, false);
                });

                // معالجة الـ drop
                attachmentsContainer.addEventListener('drop', handleDrop, false);

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                function highlight(e) {
                    attachmentsContainer.classList.add('drag-over');
                }

                function unhighlight(e) {
                    attachmentsContainer.classList.remove('drag-over');
                }

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = Array.from(dt.files);

                    if (files.length > 0) {
                        // إضافة الملفات الجديدة
                        files.forEach(file => {
                            const exists = selectedFiles.some(existingFile =>
                                existingFile.name === file.name &&
                                existingFile.size === file.size
                            );

                            if (!exists) {
                                selectedFiles.push(file);
                            }
                        });

                        updateFilesList();
                        updateFileInput();
                    }
                }
            }
        }


        function initializeDependentSelect2() {
            const selectOptions = {
                placeholder: "اختر من القائمة",
                allowClear: true,
                width: "100%",
                language: "ar",
                dir: "rtl"
            };

            if (!$("#project_id").hasClass("select2-hidden-accessible")) {
                $("#project_id").select2({
                    ...selectOptions,
                    placeholder: "اختر المشروع"
                });
            }

            if (!$("#lawsuit_id").hasClass("select2-hidden-accessible")) {
                $("#lawsuit_id").select2({
                    ...selectOptions,
                    placeholder: "اختر الدعوى"
                });
            }
        }

        $(document).ready(function() {
            setTimeout(function() {
                initializeDependentSelect2();
            }, 100);
        });
    </script>
@endsection
