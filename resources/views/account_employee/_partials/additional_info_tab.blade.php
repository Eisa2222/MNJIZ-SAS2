<div class="tab-pane fade" id="additional_info_tab">

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="card-title mb-0">
                <i class="ti ti-info-circle text-warning me-2"></i>
                نبذة تعريفية
            </h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>

        <div class="card-body">
            <p>{{ $employee->bio ?? 'لا توجد نبذة تعريفية' }}</p>
        </div>
    </div>

    <div class="row g-3 d-flex align-items-stretch">
        <div class="col-xl-8">
            <!-- معلومات عن الموظف -->
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        البيانات التفصيلة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body row">
                    <div class="col-6">
                        <small class="card-text text-primary fw-bold">معلومات الموظف</small>
                        <ul class="list-unstyled my-3 py-1">

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-id text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->national_number }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-user-circle text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->getRawOriginal('name') }}
                                    {{ $employee->nickname ? $employee->nickname : '' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-align-box-top-center text-primary"></i>
                                <small
                                    class="mx-2 fw-semibold">{{ $employee->gender == 'male' ? 'ذكر' : 'أنثى' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-calendar text-primary"></i>
                                <small
                                    class="mx-2 fw-semibold">{{ $employee->birth_date?->format('Y-m-d') ?? 'تاريخ الميلاد غير محدد' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-id-badge text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->job_title ?? 'غير متوفر' }}</small>
                            </li>
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-world text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->country->name ?? 'غير متوفر' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-device-mobile text-primary"></i>
                                <small class="mx-2 fw-semibold"
                                    dir="ltr">{{ $employee->mobile ?? 'غير متوفر' }}</small>
                            </li>
                        </ul>
                    </div>

                    <div class="col-6">

                        <small class="card-text text-primary fw-bold">التواصل</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-at text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->work_email ?? 'غير محدد' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-phone-call text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->mobile ?? 'غير متوفر' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-mail-opened text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->personal_email ?? 'غير متوفر' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-brand-google-maps text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->address ?? 'غير محدد' }}</small>
                            </li>

                        </ul>
                    </div>

                </div>

            </div>

            {{-- البيانات الاساسية  --}}
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-briefcase text-warning me-2"></i>
                        المعلومات الوظيفية
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body row">
                    <div class="col-6">
                        <small class="card-text text-primary fw-bold">معلومات وظيفية</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center small">
                                <p>رقم الهوية / الإقامة</p>
                                <p class="mx-2 fw-semibold">{{ $employee->id_number ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> حالة الموظف</p>
                                <p class="mx-2 fw-semibold">{{ $employee->hrStatus?->name ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p>المسمى الوظيفي</p>
                                <p class="mx-2 fw-semibold">{{ $employee->job_title ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> درجة المؤهل</p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->qualification_degree?->label() ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> الجانب المعرفي</p>
                                <p class="mx-2 fw-semibold">{{ $employee->knowledge_area?->label() ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> نوع العقد</p>
                                <p class="mx-2 fw-semibold">{{ $employee->contract_type?->label() ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> فترة التجربة</p>
                                <p class="mx-2 fw-semibold">{{ $employee->trial_period?->label() ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p> تاريخ بدء العقد</p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->contract_start_date?->format('Y-m-d') ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p>تاريخ نهاية العقد</p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->contract_end_date?->format('Y-m-d') ?? 'غير محدد' }}</p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p>حالة التأمينات</p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->insurance_status?->label() ?? 'غير محدد' }}</p>
                            </li>
                        </ul>
                    </div>

                    <div class="col-6">
                        <small class="card-text text-primary fw-bold">الحساب البنكي</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center small">
                                <p>
                                    <i class="ti ti-building-bank  text-primary"></i>
                                </p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->bank_name?->name ?? 'غير محدد' }}
                                </p>
                            </li>

                            <li class="d-flex align-items-center small">
                                <p>
                                    <i class="ti ti-brand-cashapp  text-primary"></i>
                                </p>
                                <p class="mx-2 fw-semibold">
                                    {{ $employee->iban ?? 'غير متوفر' }}
                                </p>
                            </li>

                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header  py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-id text-warning me-2"></i>
                        التراخيص
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body pt-3 small ">
                    <ul class="list-unstyled">

                        @if ($employee->license_type?->value == 'lawyer')
                            <li class="mb-3">رقم رخصة المحاماة
                                <strong>
                                    {{ $employee->law_license_number ?? 'غير محدد' }}
                                </strong>
                            </li>
                            <li class="mb-3">انتهاء رخصة المحاماة
                                <strong>
                                    {{ $employee->law_license_end_date?->format('d-m-Y') ?? 'غير محدد' }}
                                </strong>
                            </li>
                        @endif

                        @if ($employee->license_type?->value == 'trainee_lawyer')
                            <li class="mb-3">رقم رخصة التدريب
                                <strong>
                                    {{ $employee->training_number ?? 'غير محدد' }}
                                </strong>
                            </li>
                            <li class="mb-3">انتهاء رخصة التدريب
                                <strong>
                                    {{ $employee->training_end_date?->format('d-m-Y') ?? 'غير محدد' }}
                                </strong>
                            </li>
                        @endif

                        <li class="mb-3">انتهاء رخصة العمل
                            <strong>
                                {{ $employee->work_license_end_date?->format('d-m-Y') ?? 'لا يوجد' }}
                            </strong>
                        </li>
                    </ul>

                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header  py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-currency-dollar text-warning me-2"></i>
                        الرواتب و البدلات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body row pt-3 small fw-bold">
                    <div class="col-md-6">
                        <li class="mb-3">الراتب الأساسي {{ $employee->basic_salary ?? 0 }}
                            <span class="icon-saudi_riyal mx-1"></span>
                        </li>
                        <li class="mb-3">بدل النقل
                            {{ $employee->transportation_allowance ?? 0 }}
                            <span class="icon-saudi_riyal mx-1"></span>
                        </li>
                    </div>
                    <div class="col-md-6">
                        <li class="mb-3">بدل السكن
                            {{ $employee->housing_allowance ?? 0 }}
                            <span class="icon-saudi_riyal mx-1"></span>
                        </li>
                        <li class="mb-3">بدلات أخرى
                            {{ $employee->other_allowances ?? 0 }}
                            <span class="icon-saudi_riyal mx-1"></span>
                        </li>
                    </div>

                </div>
            </div>

            {{-- المرفقات  --}}
            <div class="card mb-3">
                <div class="card-header  py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-paperclip text-warning me-2"></i>
                        المرفقات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body pt-3 small fw-bold">
                    @include('account_employee._partials.attachments-display')

                </div>
            </div>
        </div>
    </div>

</div>
