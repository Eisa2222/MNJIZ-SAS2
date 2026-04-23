@extends('layouts.layoutMaster')

@section('title', 'تعديل المهمة')

@section('breadcrumb')
    <li><a href="#">مركز تنظيم الاعمال</a></li>
    <li><a href="{{ route('organization-center.tasks.index') }}">المهام</a></li>

    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل المهمة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل المهمة" data-page-url="{{ url()->current() }}"
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

                    <!-- تغيير route والطريقة لتحديث المهمة -->
                    <form id="form" action="{{ route('organization-center.tasks.update', $task->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label for="task_name" class="form-label">عنوان المهمة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control"
                                        value="{{ old('task_name', $task->task_name) }}" id="task_name" name="task_name"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <label for="due_date" class="form-label">تاريخ الاستحقاق
                                        <span class="text-danger">*</span>
                                    </label>
                                    <!-- تنسيق التاريخ للـ datetime-local input -->
                                    <input type="datetime-local"
                                        value="{{ old('due_date', \Carbon\Carbon::parse($task->due_date . ' ' . $task->due_time)->format('Y-m-d\TH:i')) }}"
                                        name="due_date" id="due_date" class="form-control" required>
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
                                                {{ old('task_field', $task->task_field?->value) == $field['id'] ? 'selected' : '' }}>
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
                                                {{ old('priority', $task->priority?->value) == 'high' ? 'checked' : '' }}
                                                required>
                                            <span
                                                style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                                            <span>مرتفعة</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                                id="priorityMedium" value="medium"
                                                {{ old('priority', $task->priority?->value) == 'medium' ? 'checked' : '' }}
                                                required>
                                            <span
                                                style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                                            <span>متوسطة</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <input class="form-check-input priority-checkbox" type="radio"
                                                name="priority" id="priorityLow" value="low"
                                                {{ old('priority', $task->priority?->value) == 'low' ? 'checked' : '' }}
                                                required>
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
                                        data-placeholder="اختر المشروع">
                                        <option></option>
                                        <!-- سيتم ملؤها بـ JavaScript -->
                                    </select>
                                </div>

                                <!-- دعاوى -->
                                <div class="col-md-6 hide-elements" id="lawsuit_container">
                                    <label for="lawsuit_id" class="form-label"> الدعوى
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="lawsuit_id" name="lawsuit_id" class="form-select select2" required
                                        data-placeholder="اختر الدعوى">
                                        <option></option>
                                        <!-- سيتم ملؤها بـ JavaScript -->
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
                                                {{ old('marketing_id', $task->marketing_id) == $channel->id ? 'selected' : '' }}>
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
                                                {{ old('detailed_marketing_channel_id', $task->detailed_marketing_channel_id) == $employee->id ? 'selected' : '' }}>
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
                                                {{ old('customer_id', $task->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                                {{ old('social_media_id', $task->social_media_id) == $social->id ? 'selected' : '' }}>
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
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="ti ti-user-plus me-1"></i>لنفسي
                                        </button>
                                    </div>
                                    <select id="assigned_user_id" name="assigned_user_ids[]" class="form-select select2"
                                        required multiple data-placeholder="اختر المكلفين">
                                        @foreach ($users as $user)
                                            @php
                                                $isSelected =
                                                    $task->assignedUsers->contains('id', $user->id) ||
                                                    collect(old('assigned_user_ids', []))->contains($user->id);
                                            @endphp
                                            <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}
                                                data-image="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}">
                                                {{ $user->employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <label for="taskDescription" class="form-label">وصف المهمة</label>
                                    <textarea class="form-control" id="taskDescription" name="description" rows="4">{{ old('description', $task->description) }}</textarea>
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
                                <div id="stepsContainer"
                                    style="{{ $task->steps->count() > 0 ? 'display: block;' : 'display: none;' }}">
                                    <!-- سيتم ملؤها بالخطوات الموجودة -->
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
                                <!-- عرض المرفقات الموجودة -->
                                @if ($task->attachments && $task->attachments->count() > 0)
                                    <div class="col-12 mb-3">
                                        <h6>المرفقات الحالية:</h6>
                                        <div class="existing-attachments">
                                            @foreach ($task->attachments as $attachment)
                                                <div
                                                    class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded border">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-file me-2"></i>
                                                        <span>{{ $attachment->original_name }}</span>
                                                        <small
                                                            class="text-muted ms-2">({{ number_format($attachment->file_size / 1024, 2) }}
                                                            KB)</small>
                                                    </div>
                                                    <div>
                                                        <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-primary me-1">عرض</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteAttachment({{ $attachment->id }})">حذف</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12 mb-3">
                                    <h6>إضافة مرفقات جديدة:</h6>
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
                                <button type="submit" class="btn btn-primary btn-submit">
                                    تحديث
                                </button>
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
            initializeAttachments();
            loadExistingSteps(); // إضافة دالة لتحميل الخطوات الموجودة

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
                hideAllMarketingContainers(true);

                // عرض الحقل المناسب حسب الاختيار
                if (selectedValue) {
                    showMarketingContainer(selectedValue);
                }
            });
        }

        // دالة إخفاء جميع حاويات التسويق
        function hideAllMarketingContainers(clearValues = true) {
            // إخفاء جميع الحاويات
            $('#detailedMarketingChannelContainer').hide();
            $('#clientsContainer').hide();
            $('#socialMediaContainer').hide();

            // إزالة الخاصية required من جميع الحقول
            $('#detailed_marketing_channel_id').removeAttr('required');
            $('#customer_id').removeAttr('required');
            $('#social_media_id').removeAttr('required');

            // مسح القيم فقط إذا طُلب ذلك
            if (clearValues) {
                $('#detailed_marketing_channel_id').val('').trigger('change');
                $('#customer_id').val('').trigger('change');
                $('#social_media_id').val('').trigger('change');
            }

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


            // القيم الحالية للمهمة
            const currentTaskField = '{{ old('task_field', $task->task_field?->value) }}';
            const currentProjectId = '{{ old('project_id', $task->project_id) }}';
            const currentLawsuitId = '{{ old('lawsuit_id', $task->lawsuit_id) }}';

            function resetDependentFields() {
                // تهيئة select2 للحقول المعتمدة
                initializeDependentSelect2();

                // إخفاء جميع الحاويات الرئيسية
                $('.hide-elements').hide();

                // إزالة خاصية required من الحقول الرئيسية
                projectSelect.prop('required', false);
                lawsuitSelect.prop('required', false);
                marketingSelect.prop('required', false);

                // مسح جميع القيم بدون شروط
                projectSelect.val('').trigger('change');
                lawsuitSelect.val('').trigger('change');
                marketingSelect.val('').trigger('change');

                // إخفاء ومسح حقول التسويق الفرعية
                hideAllMarketingContainers(true);
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
                                const selected = project.id == currentProjectId ? 'selected' : '';
                                projectSelect.append(
                                    `<option value="${project.id}" ${selected}>${project.project_name}</option>`
                                );
                            });
                        }
                        projectSelect.trigger('change');
                    },
                    error: function(xhr) {
                        console.error('خطأ في جلب المشاريع:', xhr);
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
                                const selected = lawsuit.id == currentLawsuitId ? 'selected' : '';
                                lawsuitSelect.append(
                                    `<option value="${lawsuit.id}" ${selected}>${lawsuit.name}</option>`
                                );
                            });
                        }
                        lawsuitSelect.trigger('change');
                    },
                    error: function(xhr) {
                        console.error('خطأ في جلب الدعاوى:', xhr);
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
                        // استعادة قناة التسويق المحفوظة إذا كانت موجودة
                        if (currentMarketingId && selectedValue === currentTaskField) {
                            setTimeout(function() {
                                marketingSelect.val(currentMarketingId).trigger('change');
                            }, 100);
                        }
                        break;
                }
            });

            resetDependentFields();
            if (currentTaskField) {
                taskFieldSelect.trigger('change');
            }
        }


        // دالة لتحميل الخطوات الموجودة
        // دالة لتحميل الخطوات الموجودة - النسخة المحدثة
        // دالة لتحميل الخطوات الموجودة - النسخة المحدثة مع الحماية
        function loadExistingSteps() {
            @if ($task->steps && $task->steps->count() > 0)
                let stepCounter = {{ $task->steps->count() }};
                const fvSteps = window.taskFormValidations[1];

                // تجميع بيانات الخطوات في مصفوفة أولاً
                const stepsData = [
                    @foreach ($task->steps as $index => $step)
                        {
                            stepIndex: {{ $index + 1 }},
                            stepId: {{ $step->id }},
                            stepName: `{{ addslashes($step->name) }}`,
                            needsApproval: {{ $step->needs_approval ? 'true' : 'false' }},
                            status: `{{ $step->status }}`,
                            stepOrder: {{ $step->step_order }},
                            assignedUsers: [
                                @foreach ($step->assignedUsers as $user)
                                    {
                                        id: {{ $user->id }},
                                        name: `{{ addslashes($user->employee->name) }}`,
                                        image: `{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}`
                                    },
                                @endforeach
                            ]
                        },
                    @endforeach
                ];

                // معالجة كل خطوة
                stepsData.forEach(function(stepData) {
                    const stepIndex = stepData.stepIndex;
                    const isEditable = stepData.status === 'pending';
                    const canDelete = stepData.status === 'pending';

                    $('#stepsContainer').append(`
                <div class="step-block mb-3 border p-3 rounded ${!isEditable ? 'bg-light-subtle' : ''}" data-index="${stepIndex}">
                    <input type="hidden" name="step_id[]" value="${stepData.stepId}">
                    <div class="step-header mt-3 text-primary text-center d-flex justify-content-between align-items-center">
                        <strong class="step-number">الخطوة ${stepIndex}</strong>
                    </div>
                    <div class="border-1 border-light border-dashed my-4"></div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">اسم الخطوة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control step-name ${!isEditable ? 'bg-light' : ''}"
                                name="steps[${stepIndex}][name]" value="${stepData.stepName}"
                                data-step="${stepIndex}" ${!isEditable ? 'readonly' : ''} />
                            ${!isEditable ? '<small class="text-muted">لا يمكن تعديل الخطوة بعد بدء العمل عليها</small>' : ''}
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">تحتاج لاعتماد؟</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                    name="steps[${stepIndex}][needs_approval]" value="1"
                                    id="approval_${stepIndex}" ${stepData.needsApproval ? 'checked' : ''}
                                    ${!isEditable ? 'disabled' : ''}>
                                <label class="form-check-label" for="approval_${stepIndex}">نعم</label>
                            </div>
                        </div>

                       <div class="col-12">
    <label class="form-label d-block">المكلفين بالخطوة <span class="text-danger">*</span></label>
    <select class="form-select select2-step step-users ${!isEditable ? 'bg-light' : ''}"
            ${!isEditable ? 'disabled' : ''} multiple
            ${isEditable ? `name="steps[${stepIndex}][assigned_user_ids][]"` : ''}></select>

    ${!isEditable ?
        // إضافة hidden inputs للخطوات غير القابلة للتعديل
        stepData.assignedUsers.map(user =>
            `<input type="hidden" name="steps[${stepIndex}][assigned_user_ids][]" value="${user.id}">`
        ).join('')
        : ''
    }

    ${!isEditable ? '<small class="text-muted">لا يمكن تغيير المكلفين بعد بدء العمل على الخطوة</small>' : ''}
</div>
                    </div>

                    <div class="text-end mt-2">
                        ${canDelete ?
                            `<button type="button" class="btn btn-danger btn-sm remove-step-btn">
                                                                                                                                                                                                                                        <i class="ti ti-trash me-1"></i>حذف
                                                                                                                                                                                                                                    </button>` :
                            `<span class="text-muted">
                                                                                                                                                                                                                                        <i class="ti ti-lock me-1"></i>
                                                                                                                                                                                                                                        لا يمكن حذف الخطوة الا اذا كانت قيد الانتظار
                                                                                                                                                                                                                                    </span>`
                        }
                    </div>
                </div>
            `);

                    // إضافة الخيارات للمستخدمين المكلفين بالخطوة
                    const stepSelect = $(`.step-block[data-index="${stepIndex}"] .step-users`);

                    // إضافة جميع المستخدمين
                    @foreach ($users as $user)
                        const isSelected{{ $user->id }} = stepData.assignedUsers.some(assignedUser =>
                            assignedUser.id === {{ $user->id }});
                        stepSelect.append(`
                    <option value="{{ $user->id }}" ${isSelected{{ $user->id }} ? 'selected' : ''}
                        data-image="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}">
                        {{ addslashes($user->employee->name) }}
                    </option>
                `);
                    @endforeach

                    // تهيئة select2
                    stepSelect.select2({
                        width: '100%',
                        dir: 'rtl',
                        allowClear: true,
                        placeholder: 'اختر المكلفين',
                    });

                    // إضافة validation للخطوات القابلة للتعديل فقط
                    if (isEditable) {
                        const stepNameField = `step_name_${stepIndex}`;
                        const stepUsersField = `step_users_${stepIndex}`;

                        fvSteps.addField(stepNameField, {
                            selector: `.step-block[data-index="${stepIndex}"] .step-name`,
                            validators: {
                                notEmpty: {
                                    message: 'اسم الخطوة مطلوب'
                                },
                                stringLength: {
                                    min: 3,
                                    max: 100,
                                    message: 'يجب أن يكون اسم الخطوة بين 3 و 100 حرف.'
                                }
                            }
                        });

                        fvSteps.addField(stepUsersField, {
                            selector: `.step-block[data-index="${stepIndex}"] .step-users`,
                            validators: {
                                notEmpty: {
                                    message: 'اختر موظفًا واحدًا على الأقل'
                                }
                            }
                        });

                        $(`.step-block[data-index="${stepIndex}"]`).data('fv-fields', [stepNameField,
                            stepUsersField
                        ]);
                    }
                });

                // تحديث العداد للخطوات الجديدة
                window.stepCounter = stepCounter;
            @endif
        }



        // تحديث دالة حذف الخطوة لتتضمن فحص الحالة
        $(document).off('click', '.remove-step-btn').on('click', '.remove-step-btn', function() {
            const $block = $(this).closest('.step-block');
            const stepIndex = $block.data('index');
            const fieldNames = $block.data('fv-fields') || [];
            const stepNumber = $block.find('.step-number').text();

            // فحص إضافي للتأكد من أن الخطوة قابلة للحذف
            const stepInput = $block.find('input.step-name');
            if (stepInput.prop('readonly')) {
                toastr.error('لا يمكن حذف هذه الخطوة لأنها ليست في حالة الانتظار');
                return;
            }

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
                    $block.fadeOut(300, function() {
                        fieldNames.forEach(name => {
                            if (window.taskFormValidations && window.taskFormValidations[
                                    1]) {
                                window.taskFormValidations[1].removeField(name);
                            }
                        });
                        $block.remove();
                        reorderSteps();

                        if ($('.step-block').length === 0) {
                            $('#stepsContainer').hide();
                            $('#addTaskStepBtn').html(
                                '<i class="ti ti-plus me-2"></i>إضافة خطوة جديدة');
                            window.stepCounter = 0;
                        }
                    });
                    toastr.success('تم حذف الخطوة بنجاح');
                }
            });
        });

        // تحديث دالة إعادة الترتيب لتتجنب الخطوات المحمية
        function reorderSteps() {
            const $editableSteps = $('.step-block').filter(function() {
                return !$(this).find('input.step-name').prop('readonly');
            });

            let counter = 1;
            $('.step-block').each(function(index) {
                const $stepBlock = $(this);
                const isEditable = !$stepBlock.find('input.step-name').prop('readonly');

                if (isEditable) {
                    $stepBlock.attr('data-index', counter);
                    $stepBlock.find('.step-number').text(`الخطوة ${counter}`);

                    // تحديث أسماء الحقول
                    $stepBlock.find('input, select').each(function() {
                        const $field = $(this);
                        const name = $field.attr('name');
                        if (name && name.includes('steps[')) {
                            const newName = name.replace(/steps\[\d+\]/, `steps[${counter}]`);
                            $field.attr('name', newName);
                        }
                    });

                    counter++;
                }
            });

            // تحديث العداد العام
            window.stepCounter = counter - 1;
        }


        // دالة محسنة لـ addslashes في JavaScript (إذا لم تكن متوفرة)
        function addslashes(str) {
            return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }

        // دالة لحذف المرفقات الموجودة
        // دالة لحذف المرفقات الموجودة
        function deleteAttachment(attachmentId) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا المرفق؟',
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
                    // إضافة loading state للزر
                    const deleteButton = $(`.btn-outline-danger[onclick="deleteAttachment(${attachmentId})"]`);
                    const originalText = deleteButton.html();
                    deleteButton.html('<i class="ti ti-loader-2 me-1"></i>جاري الحذف...').prop('disabled', true);

                    $.ajax({
                        url: `/organization-center/tasks/attachments/${attachmentId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // حذف المرفق من الواجهة
                                deleteButton.closest('.d-flex').fadeOut(400, function() {
                                    $(this).remove();

                                    // التحقق من وجود مرفقات أخرى
                                    const remainingAttachments = $('.existing-attachments')
                                        .find('.d-flex').length;

                                    if (remainingAttachments === 0) {
                                        // إخفاء قسم المرفقات الحالية إذا لم تعد هناك مرفقات
                                        $('.existing-attachments').parent().fadeOut(400,
                                            function() {
                                                $(this).remove();
                                            });
                                    }
                                });

                                toastr.success(response.message || 'تم حذف المرفق بنجاح');
                            } else {
                                // إرجاع الزر لحالته الأصلية في حالة الفشل
                                deleteButton.html(originalText).prop('disabled', false);
                                toastr.error(response.message || 'فشل في حذف المرفق');
                            }
                        },
                        error: function(xhr) {
                            // إرجاع الزر لحالته الأصلية في حالة الخطأ
                            deleteButton.html(originalText).prop('disabled', false);

                            let errorMessage = 'حدث خطأ أثناء حذف المرفق';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'المرفق غير موجود';
                            } else if (xhr.status === 403) {
                                errorMessage = 'لا تملك صلاحية حذف هذا المرفق';
                            } else if (xhr.status === 500) {
                                errorMessage = 'خطأ في الخادم، يرجى المحاولة مرة أخرى';
                            }

                            toastr.error(errorMessage);
                        }
                    });
                }
            });
        }


        // نسخة ثابتة من خيارات الموظفين بدون selected
        const rawPeopleOptions = $('#assigned_user_id option')
            .clone()
            .removeAttr('selected')
            .prop('selected', false);

        function initializeTaskSteps() {
            let stepCounter = window.stepCounter || {{ $task->steps->count() }};
            const fvSteps = window.taskFormValidations[1];

            $('#addTaskStepBtn').off('click').on('click', addNewStep);

            function addNewStep() {
                stepCounter++;

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
                            message: 'يجب أن يكون اسم الخطوة بين 3 و 100 حرف.'
                        }
                    }
                });

                fvSteps.addField(stepUsersField, {
                    selector: `.step-block[data-index="${stepCounter}"] .step-users`,
                    validators: {
                        notEmpty: {
                            message: 'اختر موظفًا واحدًا على الأقل'
                        }
                    }
                });

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
                                fieldNames.forEach(name => fvSteps.removeField(name));
                                $block.remove();
                                reorderSteps();

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
            const fileInput = document.getElementById('attachments');
            const filesList = document.getElementById('attachments-list');
            const clearBtn = document.getElementById('clear-attachments-btn');

            let selectedFiles = [];

            if (!fileInput || !filesList || !clearBtn) {
                console.warn('عناصر المرفقات غير موجودة');
                return;
            }

            fileInput.addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
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

            clearBtn.addEventListener('click', function() {
                selectedFiles = [];
                updateFilesList();
                updateFileInput();
                toastr.info('تم مسح جميع المرفقات الجديدة');
            });

            function removeFile(index) {
                if (index >= 0 && index < selectedFiles.length) {
                    const fileName = selectedFiles[index].name;
                    selectedFiles.splice(index, 1);
                    updateFilesList();
                    updateFileInput();
                    toastr.success(`تم حذف الملف: ${fileName}`);
                }
            }

            function updateFileInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(file => {
                    dt.items.add(file);
                });
                fileInput.files = dt.files;
            }

            function updateFilesList() {
                filesList.innerHTML = '';
                clearBtn.disabled = selectedFiles.length === 0;

                if (selectedFiles.length === 0) {
                    filesList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-file-upload fa-3x mb-3 d-block"></i>
                        <h6>لم يتم اختيار أي ملفات جديدة</h6>
                        <p class="mb-0">يمكنك اختيار عدة ملفات دفعة واحدة</p>
                    </div>
                `;
                    return;
                }

                const filesContainer = document.createElement('div');
                filesContainer.className = 'files-container';

                let totalSize = 0;

                selectedFiles.forEach((file, index) => {
                    totalSize += file.size;

                    const fileItem = document.createElement('div');
                    fileItem.className =
                        'd-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded border';

                    const fileIcon = getFileIcon(file.name);
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'd-flex align-items-center flex-grow-1';
                    fileInfo.innerHTML = `
                    <i class="${fileIcon} me-3 text-primary fs-4"></i>
                    <div>
                        <div class="fw-medium text-dark">${file.name}</div>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                    </div>
                `;

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

                const summary = document.createElement('div');
                summary.className = 'mt-3 p-3 bg-opacity-10 rounded border border-primary-subtle';
                summary.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-files me-2 text-primary fs-5"></i>
                            <strong class="text-primary">
                                إجمالي الملفات الجديدة: ${selectedFiles.length}
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

                document.querySelectorAll('.remove-file-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        removeFile(index);
                    });
                });
            }

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

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 بايت';
                const k = 1024;
                const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            updateFilesList();

            // إضافة drag and drop functionality
            const attachmentsContainer = document.querySelector('.attachments-container');
            if (attachmentsContainer) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    attachmentsContainer.addEventListener(eventName, unhighlight, false);
                });

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

            // if (!$("#marketing_id").hasClass("select2-hidden-accessible")) {
            //     $("#marketing_id").select2({
            //         ...selectOptions,
            //         placeholder: "اختر قناة التسويق"
            //     });
            // }
        }

        $(document).ready(function() {

            setTimeout(function() {
                const currentTaskField = '{{ $task->task_field?->value }}';
                const currentMarketingId = '{{ $task->marketing_id }}';

                // التحقق من أن المجال هو المبيعات وهناك قناة تسويق محفوظة
                if (currentTaskField === 'sales' && currentMarketingId) {
                    // إعادة تعيين قناة التسويق
                    $('#marketing_id').val(currentMarketingId).trigger('change');

                    // إظهار حقول التسويق الفرعية المناسبة
                    showMarketingContainer(currentMarketingId);

                    // استعادة القيم التفصيلية
                    const detailedMarketingValue = '{{ $task->detailed_marketing_channel_id }}';
                    const customerValue = '{{ $task->customer_id }}';
                    const socialMediaValue = '{{ $task->social_media_id }}';

                    setTimeout(function() {
                        if (detailedMarketingValue && detailedMarketingValue !== '') {
                            $('#detailed_marketing_channel_id').val(detailedMarketingValue).trigger(
                                'change');
                        }
                        if (customerValue && customerValue !== '') {
                            $('#customer_id').val(customerValue).trigger('change');
                        }
                        if (socialMediaValue && socialMediaValue !== '') {
                            $('#social_media_id').val(socialMediaValue).trigger('change');
                        }
                    }, 200);
                }
            }, 500); // زيادة الوقت لضمان اكتمال جميع العمليات
        });
    </script>
@endsection
