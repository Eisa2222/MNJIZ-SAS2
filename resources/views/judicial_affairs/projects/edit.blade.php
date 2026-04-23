@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات المشروع ')

@section('breadcrumb')
    <li><a href="{{ route('projects.index') }}"> المشاريع</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات المشروع </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات المشروع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
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

            // ---------------------------
            // 1) منطق العقد الرئيسي والفرعية
            // ---------------------------
            const $primaryContract = $('#primary_contract_id');
            const $secondaryContracts = $('#secondary_contract_ids');
            const $startDateField = $('#start_date');
            const $closureDateField = $('#contractual_closure');
            const $attachmentsContainer = $('#attachments_container');

            // دالة جلب تفاصيل العقد الرئيسي
            function loadContractDetails(contractId) {
                if (contractId) {
                    $.ajax({
                        url: '/employees/contracts/' + contractId +
                            '/detils', // عدِّل المسار وفقًا لراوت المشروع
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $startDateField.val(data.contract_start_date);
                            $closureDateField.val(data.expected_closure_date);
                            $attachmentsContainer.empty();

                            let attachments = data.attachments || [];
                            // Flatten if necessary
                            if (Array.isArray(attachments.flat)) {
                                attachments = attachments.flat();
                            } else {
                                attachments = [].concat.apply([], attachments);
                            }

                            if (attachments.length > 0) {
                                attachments.forEach(function(attachment) {
                                    if (attachment.file_url) {
                                        let attachmentUrl = attachment.file_url;
                                        let attachmentName = attachment.name || 'ملف';

                                        let attachmentDiv = $('<div>').addClass('mb-3 row g-3');
                                        let attachmentTitle = $('<span>').text(attachmentName)
                                            .addClass('me-3 col-12 col-md-3');
                                        let viewLink = $('<a>')
                                            .attr('href', attachmentUrl)
                                            .attr('target', '_blank')
                                            .addClass(
                                                'col-5 col-md-4 col-lg-1 btn btn-sm btn-outline-secondary me-2'
                                            )
                                            .html('<i class="ti ti-eye me-1"></i> عرض');

                                        attachmentDiv.append(attachmentTitle, viewLink);
                                        $attachmentsContainer.append(attachmentDiv);
                                    }
                                });
                            } else {
                                $attachmentsContainer.append('<p>لا توجد مرفقات لهذا العقد.</p>');
                            }
                        },
                        error: function() {
                            $startDateField.val('');
                            $closureDateField.val('');
                            $attachmentsContainer.empty().append(
                                '<p>حدث خطأ أثناء جلب تفاصيل العقد.</p>');
                        }
                    });
                } else {
                    // إذا أزال الاختيار
                    $startDateField.val('');
                    $closureDateField.val('');
                    $attachmentsContainer.empty();
                }
            }

            // عند تغيير العقد الرئيسي
            $primaryContract.on('change', function() {
                loadContractDetails($(this).val());
                updateContractLists(); // لو ترغب بتعطيل اختياره من قائمة العقود الفرعية
            });

            // عند تغيير العقود الفرعية
            $secondaryContracts.on('change', function() {
                updateContractLists();
            });

            // منطق منع تكرار نفس العقد في الرئيسي والفرعي
            function updateContractLists() {
                let primaryVal = $primaryContract.val();
                let secondaryVal = $secondaryContracts.val() || [];

                // تفعيل كل الخيارات
                $secondaryContracts.find('option').prop('disabled', false);
                $primaryContract.find('option').prop('disabled', false);

                // لو يوجد عقد رئيسي
                if (primaryVal) {
                    // تعطيل نفس العقد في العقود الفرعية
                    $secondaryContracts.find('option[value="' + primaryVal + '"]').prop('disabled', true);

                    // لو كان مختارًا ضمن العقود الفرعية، نزيله
                    if (secondaryVal.includes(primaryVal)) {
                        secondaryVal = secondaryVal.filter(id => id !== primaryVal);
                    }
                }

                // تعطيل أي عقد فرعي مختار من قائمة العقد الرئيسي
                secondaryVal.forEach(function(secId) {
                    $primaryContract.find('option[value="' + secId + '"]').prop('disabled', true);
                });

                $secondaryContracts.val(secondaryVal).trigger('change.select2');
            }

            // تحميل التفاصيل الأولية (عند فتح الصفحة)
            let initialPrimaryContract = $primaryContract.val();
            if (initialPrimaryContract) {
                loadContractDetails(initialPrimaryContract);
            }
            updateContractLists();




            // ---------------------------
            // 2) منع اختيار مدير المشروع ضمن فريق المشروع
            // ---------------------------
            var $managerSelect = $('#manager_user_id');
            var $teamSelect = $('#team_members');

            function updateTeamList() {
                var managerId = $managerSelect.val();
                var selectedTeam = $teamSelect.val() || [];

                // إزالة المدير من القائمة المختارة للفريق إذا كان مختاراً مسبقاً
                $teamSelect.find('option').each(function() {
                    var teamUserId = $(this).data('user_id');
                    if (managerId && teamUserId == managerId) {
                        // تحقق إن كان هذا العضو ضمن المختارين
                        if (selectedTeam.includes($(this).val())) {
                            // إزالة المدير من القيم المختارة
                            selectedTeam = selectedTeam.filter((v) => v != $(this).val());
                        }
                    }
                });

                // إعادة تعيين تمكين/تعطيل الخيارات
                $teamSelect.find('option').prop('disabled', false);
                if (managerId) {
                    // تعطيل خيار مدير المشروع في الفريق
                    $teamSelect.find('option[data-user_id="' + managerId + '"]').prop('disabled', true);
                }

                // تحديث القيم المختارة بعد التعديل
                $teamSelect.val(selectedTeam).trigger('change.select2');
            }

            $managerSelect.on('change', function() {
                updateTeamList();
            });
            // استدعاء التحديث عند تحميل الصفحة
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
                    <form id="wizard-validation-form" action="{{ route('projects.update', $project->id) }}" method="POST"
                        onSubmit="return false" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @can('إضافة مشروع')
                            <div id="account-details-validation" class="content">
                                <div class="row g-6">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="project_name">اسم المشروع</label>
                                        <input type="text" name="project_name" id="project_name" class="form-control"
                                            placeholder="ادخل اسم المشروع"
                                            value="{{ old('project_name', $project->project_name) }}" />
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="project_type">نوع المشروع</label>
                                        <select class="select2 form-select" id="project_type" name="project_type"
                                            data-placeholder="اختر نوع المشروع">
                                            <option value="">اختر نوع المشروع</option>
                                            <option value="consulting"
                                                {{ old('project_type', $project->project_type) == 'consulting' ? 'selected' : '' }}>
                                                استشاري
                                            </option>
                                            <option value="legal"
                                                {{ old('project_type', $project->project_type) == 'legal' ? 'selected' : '' }}>
                                                قضائي
                                            </option>
                                            <option value="consulting_legal"
                                                {{ old('project_type', $project->project_type) == 'consulting_legal' ? 'selected' : '' }}>
                                                استشاري قضائي
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
                                                    {{ old('contract_type', $project->contract_type ?? '') === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- العقد الرئيسي --}}
                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="primary_contract_id">العقد الرئيسي
                                            <span title="يظهر هنا العقود الرئيسية المعتمدة فقط."
                                                style="color: var(--primary-color);">
                                                <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                            </span>
                                        </label>
                                        <select class="select2 form-select" id="primary_contract_id" name="primary_contract_id"
                                            data-placeholder="اختر العقد الرئيسي">
                                            <option value="">اختر العقد الرئيسي</option>
                                            @foreach ($contracts as $c)
                                                <option value="{{ $c->id }}"
                                                    {{ old('primary_contract_id', $primaryContractId ?? '') == $c->id ? 'selected' : '' }}>
                                                    {{ $c->contract_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- العقود الفرعية (متعددة) --}}
                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="secondary_contract_ids">العقود الفرعية</label>
                                        <select multiple class="select2 form-select" id="secondary_contract_ids"
                                            name="secondary_contract_ids[]" data-placeholder="اختر العقود الفرعية">
                                            @foreach ($contracts as $c)
                                                <option value="{{ $c->id }}"
                                                    @if (is_array(old('secondary_contract_ids', $secondaryContractsIds ?? [])) &&
                                                            in_array($c->id, old('secondary_contract_ids', $secondaryContractsIds ?? []))) selected @endif>
                                                    {{ $c->contract_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label" for="start_date">تاريخ البدء</label>
                                        <input type="text" value="{{ old('start_date', $project->start_date) }}"
                                            placeholder="تاريخ البدء" disabled id="start_date" class="form-control" />
                                    </div>

                                    <div class="col-sm-6 main_contract_fields">
                                        <label class="form-label">الإغلاق التعاقدي</label>
                                        <input type="text"
                                            value="{{ old('contractual_closure', $project->contractual_closure) }}"
                                            placeholder="تاريخ الاغلاق التعاقدي" disabled id="contractual_closure"
                                            class="form-control" />
                                    </div>

                                    <div class="col-sm-6 exceptional_contract_fields">
                                        <label class="form-label" for="exceptional_contract_id">العقد الاستثنائي</label>
                                        <select class="select2 form-select" id="exceptional_contract_id"
                                            name="exceptional_contract_id" data-placeholder="اختر العقد الاستثنائي">
                                            <option value=""></option>
                                            @foreach ($exceptionalContracts as $ec)
                                                <option value="{{ $ec->id }}"
                                                    {{ old('exceptional_contract_id', $project->exceptional_contract_id ?? '') == $ec->id ? 'selected' : '' }}>
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
                                                    {{ old('technical_manager_id', $project->technical_manager_id) == $technical_manager['user_id'] ? 'selected' : '' }}>
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
                                                    data-user_id="{{ $employee->user_id }}"
                                                    {{ old('manager_user_id', $project->manager_user_id) == $employee->user_id ? 'selected' : '' }}>
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
                                                    {{ in_array($employee->user_id, old('team_members', $project->teamMembers->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="financial_claim">إجمالي المطالبة المالية</label>
                                        <input type="number" step="0.01" name="financial_claim" id="financial_claim"
                                            class="form-control" placeholder="ادخل إجمالي المطالبة المالية"
                                            value="{{ old('financial_claim', $project->financial_claim) }}" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="non_financial_claim">المطالبة غير المالية</label>
                                        <input type="text" name="non_financial_claim" id="non_financial_claim"
                                            class="form-control" placeholder="ادخل وصف المطالبة غير المالية"
                                            value="{{ old('non_financial_claim', $project->non_financial_claim) }}" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="other_claim">مطالبة اخرى</label>
                                        <input type="text" name="other_claim" id="other_claim" class="form-control"
                                            placeholder="ادخل وصف المطالبة الاخرى"
                                            value="{{ old('other_claim', $project->other_claim) }}" />
                                    </div>

                                    <div class="col-sm-12">
                                        <label class="form-label" for="description">وصف المشروع</label>
                                        <textarea name="description" id="description" class="form-control" rows="4" placeholder="ادخل وصف المشروع">{{ old('description', $project->description) }}</textarea>
                                    </div>

                                    <div class="col-sm-12">
                                        <label class="form-label" for="scope_of_work">نطاق العمل</label>
                                        <textarea name="scope_of_work" id="scope_of_work" class="form-control" rows="4"
                                            placeholder="ادخل نطاق العمل">{{ old('scope_of_work', $project->scope_of_work) }}</textarea>
                                    </div>

                                    @can('مشاهدة مرفق العقد')
                                        <div class="mb-3">
                                            <label for="attachments" class="form-label mb-3">مرفقات العقد</label>
                                            <div id="attachments_container">
                                                <!-- سيتم جلب المرفقات عند اختيار العقد -->
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

    <script>
        $(document).ready(function() {
            function loadContractDetails(contractId) {
                if (contractId) {
                    $.ajax({
                        url: '/employees/contracts/' + contractId + '/detils',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#start_date').val(data.contract_start_date);
                            $('#contractual_closure').val(data.expected_closure_date);
                            $('#attachments_container').empty();

                            var attachments = data.attachments.flat ? data.attachments.flat() : []
                                .concat.apply([], data.attachments || []);
                            if (attachments && attachments.length > 0) {
                                attachments.forEach(function(attachment) {
                                    if (attachment.file_url) {
                                        var attachmentUrl = attachment.file_url;
                                        var attachmentName = attachment.name || 'ملف';

                                        var attachmentDiv = $('<div>').addClass('mb-3 row g-3');
                                        var attachmentTitle = $('<span>').text(attachmentName)
                                            .addClass('me-3 col-12 col-md-3 ');
                                        var viewLink = $('<a>')
                                            .attr('href', attachmentUrl)
                                            .attr('target', '_blank')
                                            .addClass(
                                                'col-5 col-md-4 col-lg-1 btn btn-sm btn-outline-secondary me-2'
                                            )
                                            .html('<i class="ti ti-eye me-1"></i> عرض');

                                        attachmentDiv.append(attachmentTitle, viewLink);
                                        $('#attachments_container').append(attachmentDiv);
                                    }
                                });
                            } else {
                                $('#attachments_container').append('<p>لا توجد مرفقات لهذا العقد.</p>');
                            }
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
            }



        });
    </script>
@endsection
