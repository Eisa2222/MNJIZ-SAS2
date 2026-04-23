<div class="tab-pane fade" id="human_resources">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-users text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات الموارد البشرية</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="human_resources" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" id="active_tab" value="#human_resources">

                <!-- بداية قسم إعدادات الرواتب والحسومات -->
                <div class="row">
                    <!-- حقل عدد ساعات العمل اليومية -->
                    <div class="mb-4 col-md-6">
                        <label for="daily_working_hours" class="form-label">عدد ساعات العمل اليومية</label>
                        <input type="number" name="daily_working_hours" id="daily_working_hours" class="form-control"
                            min="1" max="24" step="0.5"
                            value="{{ old('daily_working_hours', $settings->daily_working_hours) }}">
                        @error('daily_working_hours')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- يوم صرف المرتب الشهري -->
                    <div class="mb-4 col-md-6">
                        <label for="payroll_disbursement_day" class="form-label">يوم صرف المرتب الشهري</label>
                        <input type="number" name="payroll_disbursement_day" id="payroll_disbursement_day" required
                            class="form-control" min="1" max="28"
                            value="{{ old('payroll_disbursement_day', $settings->payroll_disbursement_day) }}">
                        @error('payroll_disbursement_day')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>



                <!-- بداية قسم إعدادات الحضور والانصراف -->
                <div class="row">
                    <!-- إضافة حقل مواعيد العمل -->
                    <div class="mb-4 col-md-6">
                        <label for="work_start_time" class="form-label">وقت بداية العمل</label>
                        <input type="time" name="work_start_time" id="work_start_time" class="form-control"
                            value="{{ old('work_start_time', $settings->work_start_time) }}" required>
                        @error('work_start_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="work_end_time" class="form-label">وقت نهاية العمل</label>
                        <input type="time" name="work_end_time" id="work_end_time" class="form-control"
                            value="{{ old('work_end_time', $settings->work_end_time) }}" required>
                        @error('work_end_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <!-- وقت بداية تسجيل الحضور -->
                    <div class="mb-4 col-md-6">
                        <label for="attendance_start_time" class="form-label">وقت بداية تسجيل الحضور</label>
                        <input type="time" name="attendance_start_time" id="attendance_start_time"
                            class="form-control"
                            value="{{ old('attendance_start_time', $settings->attendance_start_time) }}">
                        @error('attendance_start_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- وقت نهاية تسجيل الحضور -->
                    <div class="mb-4 col-md-6">
                        <label for="attendance_end_time" class="form-label">وقت نهاية تسجيل الحضور</label>
                        <input type="time" name="attendance_end_time" id="attendance_end_time" class="form-control"
                            value="{{ old('attendance_end_time', $settings->attendance_end_time) }}">
                        @error('attendance_end_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- وقت بداية تسجيل الانصراف -->
                    <div class="mb-4 col-md-6">
                        <label for="departure_start_time" class="form-label">وقت بداية تسجيل الانصراف</label>
                        <input type="time" name="departure_start_time" id="departure_start_time" class="form-control"
                            value="{{ old('departure_start_time', $settings->departure_start_time) }}">
                        @error('departure_start_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- قفل نظام الحضور والانصراف -->
                    <div class="mb-4 col-md-6">
                        <label class="form-label d-block">قفل نظام الحضور والانصراف</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="attendance_system_locked"
                                name="attendance_system_locked"
                                {{ old('attendance_system_locked', $settings->attendance_system_locked) ? 'checked' : '' }}>
                            <label class="form-check-label samll" for="attendance_system_locked">
                                قفل نظام الحضور والانصراف
                            </label>

                        </div>
                        <small class="form-text text-muted d-block">
                            عند تفعيل هذا الخيار، سيتم تعطيل إمكانية تسجيل الحضور والانصراف للموظفين.
                        </small>
                        @error('attendance_system_locked')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>


                    <div class="mb-4 col-md-6">
                        <label for="insurance_percentage" class="form-label">نسبة التأمينات 
                        </label>
                        <input type="number" name="insurance_percentage" id="insurance_percentage"
                            class="form-control" step="0.01" min="0" max="100"
                            value="{{ old('insurance_percentage', number_format($settings->insurance_percentage)) }}"
                            >
                        @error('insurance_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    <!-- خيار تفعيل حسم التأمينات -->
                    <div class="mb-4 col-md-6">
                        <label class="form-label d-block"> حسم التأمينات</label>
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="radio" name="insurance_deduction"
                                id="insurance_deduction_yes" value="1"
                                {{ old('insurance_deduction', $settings->insurance_deduction) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="insurance_deduction_yes">نعم</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="insurance_deduction"
                                id="insurance_deduction_no" value="0"
                                {{ old('insurance_deduction', $settings->insurance_deduction) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="insurance_deduction_no">لا</label>
                        </div>
                        <small class="form-text text-muted d-block">
                            عند تفعيل هذا الخيار، سيتم خصم قيمة التأمينات تلقائيًا من مرتبات الموظفين  .
                        </small>
                        @error('insurance_deduction')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- حقل أيام العطلة الأسبوعية -->
                <div class="row">
                    <div class="mb-4 col-md-12">
                        <label class="form-label">أيام العطلة الأسبوعية <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($weekDays as $dayValue => $dayName)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="day_{{ $dayValue }}"
                                        name="weekly_days_off[]" value="{{ $dayValue }}"
                                        {{ in_array($dayValue, $weeklyDaysOff) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="day_{{ $dayValue }}">{{ $dayName }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('weekly_days_off')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
