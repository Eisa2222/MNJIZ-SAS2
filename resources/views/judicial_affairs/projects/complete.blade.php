@extends('layouts.layoutMaster')

@section('title', 'اكمال بيانات المشروع')

@section('breadcrumb')
    <li><a href="{{ route('projects.index') }}"> المشاريع</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">استكمال بيانات المشروع </a>
        <i class="ti ti-star favorite-icon" data-page-name="استكمال بيانات المشروع" data-page-url="{{ url()->current() }}"
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

@section('page-script')
    @vite(['resources/assets/js/form-wizard-numbered.js', 'resources/assets/js/project-complete-validation.js'])

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
                            // إذا كان هذا العضو (المدير) مختاراً في الفريق
                            if (selectedTeam.includes($(this).val())) {
                                selectedTeam = selectedTeam.filter((v) => v != $(this).val());
                            }
                            // تعطيل خيار المدير في الفريق
                            $(this).prop('disabled', true);
                        }
                    });
                }

                // إعادة تعيين القيم المختارة بعد التعديلات
                $teamSelect.val(selectedTeam).trigger('change.select2');
            }

            // عند تغيير المدير
            $managerSelect.on('change', function() {
                updateTeamList();
            });

            // استدعاء التحديث عند تحميل الصفحة
            updateTeamList();

            // منع إدخال قيم سالبة في إجمالي المطالبة المالية
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
    <div class="row g-3">
        <div class="col-12 mb-6">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#account-details-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات المشروع</span>
                                <span class="bs-stepper-subtitle"> معلومات المشروع الأساسية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#details-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">الإعتماد الفني للمشاريع</span>
                                <span class="bs-stepper-subtitle"> تفاصيل الإعتماد الفني للمشاريع</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="wizard-validation-form" action="{{ route('projects.complete.update', $project->id) }}"
                        onSubmit="return false" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="account-details-validation" class="content">
                            <div class="row g-6">
                                <div class="col-sm-6">
                                    <label class="form-label">اسم المشروع</label>
                                    <input disabled class="form-control" value="{{ $project->project_name }}" />
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label" for="">نوع المشروع</label>
                                    <input type="text" id="" class="form-control"
                                        value="@if ($project->project_type == 'consulting') استشاري @elseif($project->project_type == 'legal') قضائي @elseif($project->project_type == 'consulting_legal') استشاري قضائي @else غير معروف @endif"
                                        disabled>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label" for="contract_type">نوع العقد</label>
                                    <select disabled class="select2 form-select" id="contract_type" name="contract_type"
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
                                    <select disabled class="select2 form-select" data-placeholder="لا يوجد عقد رئيسي">
                                        <option value="">اختر العقد الرئيسي</option>
                                        @foreach ($contracts as $c)
                                            <option value="{{ $c->id }}"
                                                {{ old('primary_contract_id', $primaryContract->id ?? '') == $c->id ? 'selected' : '' }}>
                                                {{ $c->contract_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- العقود الفرعية (متعددة) --}}
                                <div class="col-sm-6 main_contract_fields">
                                    <label class="form-label" for="secondary_contract_ids">العقود الفرعية</label>
                                    <select disabled multiple class="select2 form-select"
                                        data-placeholder="لا يوجد عقود فرعية">
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
                                    <input disabled type="text" value="{{ old('start_date', $project->start_date) }}"
                                        placeholder="تاريخ البدء" class="form-control" />
                                </div>

                                <div class="col-sm-6 main_contract_fields">
                                    <label class="form-label">الإغلاق التعاقدي</label>
                                    <input disabled value="{{ old('contractual_closure', $project->contractual_closure) }}"
                                        id="contractual_closure" class="form-control" />
                                </div>

                                <div class="col-sm-6 exceptional_contract_fields">
                                    <label class="form-label" for="exceptional_contract_id">العقد الاستثنائي</label>
                                    <select disabled class="select2 form-select" id="exceptional_contract_id">
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
                                    </label>
                                    <input value="{{ $project->technical_manager->employee->name }}" disabled
                                        class="form-control" />

                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button type="button" class="btn btn-label-secondary btn-prev" disabled>
                                        <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                        <i class="ti ti-arrow-right ti-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

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

                                <div class="mb-3">
                                    <label for="attachments" class="form-label mb-3">مرفقات العقد</label>
                                    <div id="attachments_container">
                                        <!-- سيتم إضافة روابط المرفقات هنا بواسطة JavaScript -->
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-label-secondary btn-prev">
                                        <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                    </button>
                                    <button class="btn btn-primary btn-next btn-submit">حفظ</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script></script>
@endsection
