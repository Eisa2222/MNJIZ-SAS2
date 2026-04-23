@extends('layouts.layoutMaster')

@inject('project', 'App\Models\judicial_affairs\Project')

@section('title', 'إضافة مشروع جديد')

@section('breadcrumb')
    <li><a href="{{ route('projects.index') }}"> المشاريع</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إضافة مشروع جديد </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة مشروع جديد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/shepherd/shepherd.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/shepherd/shepherd.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite('resources/assets/js/tour_question.js')

    <script>
        var userCanAddProject = false;
        var userCanCompleteProject = false;

        @can('إضافة مشروع')
            userCanAddProject = true;
        @endcan

        @can('الإعتماد الفني للمشاريع')
            userCanCompleteProject = true;
        @endcan
    </script>

    @vite(['resources/assets/js/project-validation.js'])

    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: $(this).data('placeholder') || 'اختر خيارًا',
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
            });

            // جلب بيانات العقد عند اختياره
            $('#primary_contract_id').on('change', function() {
                var contractId = $(this).val();

                if (contractId) {
                    $.ajax({
                        url: '/employees/contracts/' + contractId + '/detils',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#start_date').val(data.contract_start_date);
                            $('#contractual_closure').val(data.expected_closure_date);
                            $('#attachments_container').empty();

                            @can('مشاهدة مرفق العقد')
                                var attachments = data.attachments.flat ? data.attachments
                                    .flat() : [].concat.apply([], data.attachments || []);
                                if (attachments && attachments.length > 0) {
                                    attachments.forEach(function(attachment) {
                                        if (attachment.file_url) {
                                            var attachmentUrl = attachment.file_url;
                                            var attachmentName = attachment.name ||
                                                'ملف';

                                            var attachmentDiv = $('<div>').addClass(
                                                'mb-3 row g-3');
                                            var attachmentTitle = $('<span>').text(
                                                attachmentName).addClass(
                                                'me-3 col-12 col-md-3');
                                            var viewLink = $('<a>')
                                                .attr('href', attachmentUrl)
                                                .attr('target', '_blank')
                                                .addClass(
                                                    'col-5 col-md-4 col-lg-1 btn btn-sm btn-outline-secondary me-2'
                                                )
                                                .html(
                                                    '<i class="ti ti-eye me-1"></i> عرض'
                                                );

                                            attachmentDiv.append(attachmentTitle,
                                                viewLink);
                                            $('#attachments_container').append(
                                                attachmentDiv);
                                        }
                                    });
                                } else {
                                    $('#attachments_container').append(
                                        '<p>لا توجد مرفقات لهذا العقد.</p>');
                                }
                            @endcan
                        },
                        error: function() {
                            $('#start_date').val('');
                            $('#contractual_closure').val('');
                            $('#attachments_container').empty().append(
                                '<p>حدث خطأ أثناء جلب تفاصيل العقد.</p>');
                        }
                    });
                } else {
                    $('#start_date').val('');
                    $('#contractual_closure').val('');
                    $('#attachments_container').empty();
                }
            });


            $(document).ready(function() {
                var $primaryContract = $('#primary_contract_id');
                var $secondaryContracts = $('#secondary_contract_ids');
                var $startDateField = $('#primary_start_date');
                var $closureDateField = $('#primary_closure_date');

                // دالة لتحديث قوائم العقود
                function updateContractLists() {
                    var primaryId = $primaryContract.val();
                    var selectedSecondary = $secondaryContracts.val() || [];

                    // 1) تفعيل كل خيارات العقود الفرعية
                    $secondaryContracts.find('option').prop('disabled', false);
                    // 2) تفعيل كل خيارات العقد الرئيسي
                    $primaryContract.find('option').prop('disabled', false);

                    // إذا وُجد عقد رئيسي
                    if (primaryId) {
                        // إذا كان العقد الرئيسي مختارًا أيضًا في العقود الفرعية، نزيله
                        if (selectedSecondary.includes(primaryId)) {
                            selectedSecondary = selectedSecondary.filter(id => id !== primaryId);
                        }
                        // تعطيل العقد الرئيسي في قائمة العقود الفرعية
                        $secondaryContracts.find('option[value="' + primaryId + '"]').prop('disabled',
                            true);
                    }

                    // أي عقود فرعية مختارة يتم تعطيلها في قائمة العقد الرئيسي
                    selectedSecondary.forEach(function(secondaryId) {
                        $primaryContract.find('option[value="' + secondaryId + '"]').prop(
                            'disabled', true);
                    });

                    // تحديث القيم المختارة بعد التعديلات
                    $secondaryContracts.val(selectedSecondary).trigger('change.select2');
                }

                // عند تغيير العقد الرئيسي
                $primaryContract.on('change', function() {
                    var contractId = $(this).val();

                    // جلب تاريخ البدء والإغلاق من السيرفر
                    if (contractId) {
                        $.ajax({
                            url: '/employees/contracts/' + contractId +
                                '/detils', // عدِّل المسار وفقًا لراوت المشروع
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                // مثلاً:
                                // data = {
                                //   contract_start_date: "2025-01-01",
                                //   expected_closure_date: "2025-12-31",
                                //   ...
                                // }
                                $startDateField.val(data.contract_start_date);
                                $closureDateField.val(data.expected_closure_date);
                            },
                            error: function() {
                                $startDateField.val('');
                                $closureDateField.val('');
                            }
                        });
                    } else {
                        // لو أزال الاختيار
                        $startDateField.val('');
                        $closureDateField.val('');
                    }

                    updateContractLists();
                });

                // عند تغيير العقود الفرعية
                $secondaryContracts.on('change', function() {
                    updateContractLists();
                });

                // استدعاء دالة التحديث عند أول تحميل للصفحة
                updateContractLists();
            });

            // عند تغيير مدير المشروع، نقوم بمنع هذا المستخدم من الظهور في فريق المشروع
            var $managerSelect = $('#manager_user_id');
            var $teamSelect = $('#team_members');

            function updateTeamList() {
                var managerId = $managerSelect.val();
                var selectedTeam = $teamSelect.val() || [];

                // تمكين جميع الخيارات أولاً
                $teamSelect.find('option').prop('disabled', false);

                if (managerId) {
                    // إذا كان المدير المختار ضمن الفريق، إزالته من القيم المختارة
                    $teamSelect.find('option').each(function() {
                        var teamUserId = $(this).data('user_id');
                        if (teamUserId == managerId) {
                            // إذا كان المدير مختار في الفريق
                            if (selectedTeam.includes($(this).val())) {
                                // إزالة المدير من القيم المختارة
                                selectedTeam = selectedTeam.filter((v) => v != $(this).val());
                            }
                            // تعطيل خيار المدير
                            $(this).prop('disabled', true);
                        }
                    });
                }

                // إعادة تعيين القيم المختارة بعد التعديل
                $teamSelect.val(selectedTeam).trigger('change.select2');
            }

            $managerSelect.on('change', function() {
                updateTeamList();
            });

            // استدعاء التحديث عند تحميل الصفحة إذا لزم الأمر
            updateTeamList();

            // منع إدخال قيم سالبة أو غير رقمية في المطالبة المالية
            $('#financial_claim').on('input', function() {
                let value = parseFloat(this.value);
                if (isNaN(value) || value < 0) {
                    this.value = '';
                }
            });
        });


        $(document).ready(function() {
            const mainFields = $('.main_contract_fields');
            const exceptionalFields = $('.exceptional_contract_fields');

            function toggleContractSections() {
                const type = $('#contract_type').val();

                if (type === '{{ $project::TYPE_CONTRACT_MAIN }}') {
                    // عرض حقول الرئيسي وإخفاء الاستثنائي
                    mainFields.show();
                    exceptionalFields.hide();

                    // مسح قيم الاستثنائي
                    $('#exceptional_contract_id')
                        .val(null)
                        .trigger('change.select2');

                } else if (type === '{{ $project::TYPE_CONTRACT_EXCEPTIONAL }}') {
                    // عرض حقول الاستثنائي وإخفاء الرئيسي
                    mainFields.hide();
                    exceptionalFields.show();

                    // مسح قيم الرئيسي
                    $('#primary_contract_id')
                        .val(null)
                        .trigger('change.select2');
                    $('#secondary_contract_ids')
                        .val([])
                        .trigger('change.select2');
                    $('#start_date').val('');
                    $('#contractual_closure').val('');

                } else {
                    // لم يختَر أي نوع → إخفاء الكل ومسح الكل
                    mainFields.hide();
                    exceptionalFields.hide();

                    $('#primary_contract_id').val(null).trigger('change.select2');
                    $('#secondary_contract_ids').val([]).trigger('change.select2');
                    $('#start_date').val('');
                    $('#contractual_closure').val('');
                    $('#exceptional_contract_id').val(null).trigger('change.select2');
                }

                // أعِد تهيئة Select2 للحقل الظاهر فقط
                $('.select2:visible').select2({
                    width: '100%',
                    dir: 'rtl',
                    language: 'ar'
                });
            }

            // استمع للتغيير
            $('#contract_type').on('change', toggleContractSections);

            // نفّذ مرة عند التحميل لعرض القيمة القديمة
            toggleContractSections();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-6">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    @can('إضافة مشروع')
                        <!-- الخطوة الأولى -->
                        <div class="step" data-target="#account-details-validation">
                            <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">1</span>
                                <span class="bs-stepper-label mt-1">
                                    <span class="bs-stepper-title">معلومات المشروع</span>
                                    <span class="bs-stepper-subtitle">إدخال معلومات المشروع الأساسية</span>
                                </span>
                            </button>
                        </div>
                        <div class="line"><i class="ti ti-chevron-right"></i></div>
                    @endcan
                    @can('الإعتماد الفني للمشاريع')
                        <!-- الخطوة الثانية -->
                        <div class="step" data-target="#details-validation">
                            <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">2</span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-title">الإعتماد الفني للمشاريع</span>
                                    <span class="bs-stepper-subtitle">تفاصيل الإعتماد الفني للمشاريع</span>
                                </span>
                            </button>
                        </div>
                    @endcan
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger my-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="wizard-validation-form" action="{{ route('projects.store') }}" method="POST"
                        onSubmit="return false" enctype="multipart/form-data">
                        @csrf

                        @can('إضافة مشروع')
                            <div id="account-details-validation" class="content">
                                <div class="row g-6">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="project_name">اسم المشروع</label>
                                        <input type="text" name="project_name" id="project_name" class="form-control"
                                            placeholder="ادخل اسم المشروع" value="{{ old('project_name') }}" />
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="project_type">نوع المشروع</label>
                                        <select class="select2 form-select" id="project_type" name="project_type"
                                            data-placeholder="اختر نوع المشروع">
                                            <option value="">اختر نوع المشروع</option>
                                            <option value="consulting"
                                                {{ old('project_type') == 'consulting' ? 'selected' : '' }}>استشاري</option>
                                            <option value="legal" {{ old('project_type') == 'legal' ? 'selected' : '' }}>قضائي
                                            </option>
                                            <option value="consulting_legal"
                                                {{ old('project_type') == 'consulting_legal' ? 'selected' : '' }}>استشاري قضائي
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="contract_type">نوع العقد</label>
                                        <select class="select2 form-select" id="contract_type" name="contract_type"
                                            data-placeholder="اختر نوع العقد">
                                            <option value=""></option>
                                            @foreach ($typeOptions as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('contract_type', $contract->contract_type ?? '') === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="primary_contract_id">العقد الرئيسي
                                            <span title="يتم عرض العقود الرئيسية المعتمدة فقط."
                                                style="color: var(--primary-color);">
                                                <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                            </span>
                                        </label>
                                        <select class="select2 form-select" id="primary_contract_id" name="primary_contract_id"
                                            data-placeholder="اختر العقد الرئيسي">
                                            <option value="">اختر العقد الرئيسي</option>
                                            @foreach ($contracts as $contractItem)
                                                <option value="{{ $contractItem->id }}"
                                                    {{ old('primary_contract_id') == $contractItem->id ? 'selected' : '' }}>
                                                    {{ $contractItem->contract_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="secondary_contract_ids">العقود الفرعية
                                            <span title="يمكنك اختيار أكثر من عقد فرعي" style="color: var(--primary-color);">
                                                <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                            </span>
                                        </label>
                                        <select multiple class="select2 form-select" id="secondary_contract_ids"
                                            name="secondary_contract_ids[]" data-placeholder="اختر العقود الفرعية">
                                            @foreach ($contracts as $contractItem)
                                                <option value="{{ $contractItem->id }}"
                                                    {{ collect(old('secondary_contract_ids'))->contains($contractItem->id) ? 'selected' : '' }}>
                                                    {{ $contractItem->contract_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="start_date">تاريخ البدء</label>
                                        <span title="يتم جلب التاريخ من العقد" style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                        <input type="text" name="" placeholder="تاريخ البدء" disabled
                                            id="start_date" class="form-control" />
                                    </div>

                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="contractual_closure">الإغلاق التعاقدي</label>
                                        <span title="يتم جلب التاريخ من العقد" style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                        <input type="text" name="" placeholder="تاريخ الاغلاق التعاقدي" disabled
                                            id="contractual_closure" class="form-control" />
                                    </div>


                                    <div class="col-sm-6 exceptional_contract_fields">
                                        <label class="form-label" for="exceptional_contract_id">العقد الاستثنائي</label>
                                        <select class="select2 form-select" id="exceptional_contract_id"
                                            name="exceptional_contract_id" data-placeholder="اختر العقد الاستثنائي">
                                            <option value=""></option>
                                            @foreach ($exceptionalContracts as $ec)
                                                <option value="{{ $ec->id }}"
                                                    {{ old('exceptional_contract_id', $contract->exceptional_contract_id ?? '') == $ec->id ? 'selected' : '' }}>
                                                    {{ $ec->contract_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>



                                    <div class="col-sm-6">
                                        <label class="form-label" for="technical_manager_id">مدير الشؤون الفنية
                                            <span title="هنا يظهر الموظفين الذين لديهم صلاحيات الإعتماد الفني للمشاريع"
                                                style="color: var(--primary-color);">
                                                <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                            </span>
                                        </label>
                                        <select class="select2 form-select" id="technical_manager_id"
                                            name="technical_manager_id" data-placeholder="اختر مدير الشؤون الفنية">
                                            <option value="">اختر مدير الشؤون الفنية</option>

                                            @foreach ($technical_manager_id as $technical_manager)
                                                <option value="{{ $technical_manager['user_id'] }}"
                                                    {{ old('technical_manager_id') == $technical_manager['user_id'] ? 'selected' : '' }}>
                                                    {{ $technical_manager['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button type="button" class="btn btn-label-secondary btn-prev" disabled>
                                            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                        </button>


                                        @cannot('الإعتماد الفني للمشاريع')
                                            <button type="button" class="btn btn-primary btn-next btn-submit">
                                                <span class="align-middle d-sm-inline-block d-none me-sm-2">إرسال</span>
                                                <i class="ti ti-arrow-right ti-xs"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-primary btn-next">
                                                <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                                <i class="ti ti-arrow-right ti-xs"></i>
                                            </button>
                                        @endcannot






                                    </div>
                                </div>
                            </div>
                        @endcan

                        @can('الإعتماد الفني للمشاريع')
                            <div id="details-validation" class="content">
                                <div class="row g-6">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="manager_user_id">مدير المشروع</label>
                                        <select class="select2 form-select" id="manager_user_id" name="manager_user_id"
                                            data-placeholder="اختر مدير المشروع">
                                            <option value="">اختر مدير المشروع</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->user_id }}"
                                                    {{ old('manager_user_id') == $employee->user_id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="team_members">فريق المشروع</label>
                                        <select multiple id="team_members" name="team_members[]" class="select2 form-select"
                                            data-placeholder="اختر فريق المشروع">
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->user_id }}"
                                                    data-user_id="{{ $employee->user_id }}"
                                                    {{ collect(old('team_members'))->contains($employee->user_id) ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="financial_claim">إجمالي المطالبة المالية</label>
                                        <input type="number" min="0" step="0.01" name="financial_claim"
                                            id="financial_claim" class="form-control"
                                            placeholder="ادخل إجمالي المطالبة المالية"
                                            value="{{ old('financial_claim') }}" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="non_financial_claim">المطالبة غير المالية</label>
                                        <input type="text" name="non_financial_claim" id="non_financial_claim"
                                            class="form-control" placeholder="ادخل وصف المطالبة غير المالية"
                                            value="{{ old('non_financial_claim') }}" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="other_claim">مطالبة اخرى</label>
                                        <input type="text" name="other_claim" id="other_claim" class="form-control"
                                            placeholder="ادخل وصف المطالبة الاخرى" value="{{ old('other_claim') }}" />
                                    </div>

                                    <div class="col-sm-12">
                                        <label class="form-label" for="description">وصف المشروع</label>
                                        <textarea name="description" id="description" class="form-control" rows="4" placeholder="ادخل وصف المشروع">{{ old('description') }}</textarea>
                                    </div>

                                    <div class="col-sm-12">
                                        <label class="form-label" for="scope_of_work">نطاق العمل</label>
                                        <textarea name="scope_of_work" id="scope_of_work" class="form-control" rows="4"
                                            placeholder="ادخل نطاق العمل">{{ old('scope_of_work') }}</textarea>
                                    </div>

                                    @can('مشاهدة مرفق العقد')
                                        <div class="mb-3">
                                            <label for="attachments" class="form-label mb-3">مرفقات العقد</label>
                                            <div id="attachments_container">
                                                <!-- يتم جلب المرفقات عند اختيار العقد -->
                                            </div>
                                        </div>
                                    @endcan

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-label-secondary btn-prev">
                                            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                        </button>
                                        <button class="btn btn-primary btn-next btn-submit">حفظ</button>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
