"use strict";

(function () {
    const select2 = $(".select2");

    // تحميل أيام العطلة الأسبوعية من إعدادات النظام
    // سنضيف هذا كحقل hidden في الصفحة
    let weeklyDaysOff = [];
    try {
        const weeklyDaysOffInput = document.getElementById('weekly_days_off');
        if (weeklyDaysOffInput && weeklyDaysOffInput.value) {
            weeklyDaysOff = JSON.parse(weeklyDaysOffInput.value);
        }
    } catch (e) {
        console.error("خطأ في تحميل أيام العطلة الأسبوعية:", e);
        // استخدام قيم افتراضية - الجمعة والسبت كأيام عطلة نموذجية
        weeklyDaysOff = ["friday", "saturday"];
    }

    const wizardValidation = document.querySelector("#wizard-validation");
    if (!wizardValidation) return;
    const wizardValidationForm = wizardValidation.querySelector("#leave-request-form");
    const isEditForm = window.location.pathname.includes('/edit') || window.location.href.includes('/update');
    const steps = wizardValidationForm.querySelectorAll(".content");
    const btnSubmit = wizardValidationForm.querySelector(".btn-submit");
    const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const fv = FormValidation.formValidation(step, {
            fields: {
                ...(index === 0 && {
                    leave_type_id: {
                        validators: {
                            notEmpty: {
                                message: "الرجاء اختيار نوع الإجازة",
                            },
                        },
                    },
                    start_date: {
                        validators: {
                            notEmpty: {
                                message: "تاريخ البداية مطلوب",
                            },
                            date: {
                                enabled: true,
                                format: "YYYY-MM-DD",
                                message: "صيغة التاريخ غير صحيحة",
                            },
                        },
                    },
                    end_date: {
                        validators: {
                            callback: {
                                message: "تاريخ النهاية مطلوب ويجب أن يكون بعد أو يساوي تاريخ البداية",
                                callback: function (input) {
                                    const leaveTypeSelect = step.querySelector("#leave_type_id");
                                    const isHalfDay = leaveTypeSelect.selectedOptions[0]?.dataset.leaveUnitType === "half_day";

                                    if (isHalfDay) {
                                        // Skip validation for half-day leaves
                                        return true;
                                    }

                                    const start = step.querySelector('[name="start_date"]').value;
                                    if (!input.value.trim() || !start) {
                                        return false;
                                    }

                                    return input.value >= start;
                                },
                            },
                            date: {
                                enabled: true,
                                format: "YYYY-MM-DD",
                                message: "صيغة التاريخ غير صحيحة",
                            },
                        },
                    },
                }),
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger({
                    event: {
                        start_date: 'change',
                        end_date: 'change'
                    }
                }),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: ".col-md-6, .col-md-12",
                    eleValidClass: "",
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton(),
            },
        }).on("core.form.valid", function () {
            if (index === steps.length - 1) {
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                wizardValidationForm.submit();
            } else {
                validationStepper.next();
            }
        });

        formValidations.push(fv);

        if (index === 0) {
            const leaveTypeSelect = step.querySelector("#leave_type_id");
            const startDateInput = step.querySelector('[name="start_date"]');
            const endDateInput = step.querySelector('[name="end_date"]');
            const daysOutputField = step.querySelector('[name="calculated_days"]');

            // إضافة خاصية لتتبع ما إذا كان نوع الإجازة يحتسب أيام العطلة الأسبوعية
            const leaveTypesInfo = {};

            // تحميل بيانات أنواع الإجازات من خلال data attributes
            const leaveTypeOptions = leaveTypeSelect.querySelectorAll('option');
            leaveTypeOptions.forEach(option => {
                if (option.value) {
                    // نحتاج إضافة data-count-weekends إلى options في الصفحة
                    leaveTypesInfo[option.value] = {
                        countWeekends: option.dataset.countWeekends === "1" || option.dataset.countWeekends === "true",
                        leaveUnitType: option.dataset.leaveUnitType
                    };
                }
            });

            const initialLeaveType = leaveTypeSelect.value;

            const endDateRow = endDateInput.closest('.col-md-6');
            const daysOutputRow = daysOutputField?.closest('.col-md-6');

            const secondRow = endDateRow.closest('.row');

            const toggleField = (element, enable, readOnly = false) => {
                if (!element) return;

                if (enable) {
                    element.removeAttribute('disabled');

                    if (readOnly) {
                        element.setAttribute('readonly', 'readonly');
                        element.removeAttribute('required');
                    } else {
                        element.removeAttribute('readonly');
                        if (element !== daysOutputField) {
                            element.setAttribute('required', 'required');
                        }
                    }
                } else {
                    element.setAttribute('disabled', 'disabled');
                    element.removeAttribute('required');
                    element.removeAttribute('readonly');
                    element.value = '';
                }
            };

            /**
             * تحقق ما إذا كان اليوم هو يوم عطلة أسبوعية
             * @param {Date} date - كائن Date لليوم المراد التحقق منه
             * @returns {boolean} - true إذا كان يوم عطلة، false خلاف ذلك
             */
            const isWeekendDay = (date) => {
                // تحويل اليوم إلى اسم اليوم المطابق للقيم في weeklyDaysOff
                const dayNames = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
                const dayName = dayNames[date.getDay()];
                return weeklyDaysOff.includes(dayName);
            };

            /**
             * Calculate days for regular leaves - with weekend calculation support
             */
            const calculateDays = () => {
                if (isHalfDayLeaveSelected()) return;

                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (!isNaN(startDate) && !isNaN(endDate) && endDate >= startDate) {
                    // الحصول على حالة احتساب أيام العطلة لنوع الإجازة المحدد
                    const selectedLeaveTypeId = leaveTypeSelect.value;
                    const countWeekends = leaveTypesInfo[selectedLeaveTypeId]?.countWeekends ?? true;

                    // إذا كنا نحتسب العطل، أو كان اليوم نفسه
                    if (countWeekends || startDate.getTime() === endDate.getTime()) {
                        // الطريقة القديمة: احتساب جميع الأيام
                        const daysDiff = Math.floor((endDate - startDate) / 86400000) + 1;
                        daysOutputField.value = daysDiff;
                    } else {
                        // الطريقة الجديدة: استبعاد أيام العطلة
                        let dayCount = 0;
                        const currentDate = new Date(startDate);

                        // حلقة لعد الأيام مع استبعاد أيام العطلة
                        while (currentDate <= endDate) {
                            if (!isWeekendDay(currentDate)) {
                                dayCount++;
                            }

                            // الانتقال لليوم التالي
                            currentDate.setDate(currentDate.getDate() + 1);
                        }

                        daysOutputField.value = dayCount;
                    }
                } else {
                    daysOutputField.value = '';
                }

                // تحديث التلميح بعد حساب الأيام
                updateDaysTooltip();
            };

            /**
             * Update half-day fields
             */
            const updateHalfDayFields = () => {
                if (isHalfDayLeaveSelected() && startDateInput.value) {
                    endDateInput.value = startDateInput.value;

                    daysOutputField.value = '0.5';
                }
            };

            /**
             * Check if half-day leave is selected
             */
            const isHalfDayLeaveSelected = () => {
                return leaveTypeSelect.selectedOptions[0]?.dataset.leaveUnitType === 'half_day';
            };

            /**
             * تحديث تلميح عدد الأيام حسب إعداد count_weekends
             */
            const updateDaysTooltip = () => {
                const selectedLeaveTypeId = leaveTypeSelect.value;
                const tooltipElement = document.getElementById('days-tooltip');

                if (!tooltipElement || !selectedLeaveTypeId) return;

                const countWeekends = leaveTypesInfo[selectedLeaveTypeId]?.countWeekends ?? true;
                const tooltipText = countWeekends
                    ? "يتم احتساب أيام العطلة الأسبوعية ضمن أيام الإجازة"
                    : "لا يتم احتساب أيام العطلة الأسبوعية ضمن أيام الإجازة";

                // تحديث نص التلميح
                tooltipElement.setAttribute('title', tooltipText);

                // إعادة تهيئة التلميح في Bootstrap إذا كان موجودًا
                try {
                    var tooltip = bootstrap.Tooltip.getInstance(tooltipElement);
                    if (tooltip) {
                        tooltip.dispose();
                    }

                    new bootstrap.Tooltip(tooltipElement);
                } catch (e) {
                    console.error("خطأ في تهيئة التلميح:", e);
                }
            };

            /**
             * Reset all event listeners
             */
            const resetEventListeners = () => {
                startDateInput.removeEventListener('change', updateHalfDayFields);
                startDateInput.removeEventListener('change', calculateDays);
                endDateInput.removeEventListener('change', calculateDays);
            };

            /**
             * Clear field values - used in edit form
             */
            const clearFieldValues = (isTypeChange = false) => {
                if (isTypeChange) {
                    startDateInput.value = '';
                    endDateInput.value = '';
                    daysOutputField.value = '';
                }
            };

            /**
             * Toggle end date validators based on leave type
             */
            const toggleEndDateValidators = () => {
                const isHalfDay = isHalfDayLeaveSelected();

                if (isHalfDay) {
                    fv.disableValidator("end_date", "notEmpty");
                    fv.disableValidator("end_date", "callback");
                    fv.disableValidator("end_date", "date");

                    // If start date is set, copy to end date
                    if (startDateInput.value) {
                        endDateInput.value = startDateInput.value;
                        daysOutputField.value = '0.5';
                    }

                    fv.resetField("end_date");

                    secondRow.style.display = 'none';
                } else {
                    secondRow.style.display = 'flex';

                    fv.enableValidator("end_date", "notEmpty");
                    fv.enableValidator("end_date", "callback");
                    fv.enableValidator("end_date", "date");

                    calculateDays();

                    if (startDateInput.value && endDateInput.value) {
                        fv.revalidateField("end_date");
                    }
                }
            };

            /**
             * Set field state based on leave type
             */
            const toggleFields = (isTypeChange = false) => {
                // Determine leave type
                const isHalfDay = isHalfDayLeaveSelected();

                if (isEditForm && isTypeChange) {
                    clearFieldValues(isTypeChange);
                } else if (!isEditForm) {
                    startDateInput.value = '';
                    endDateInput.value = '';
                    daysOutputField.value = '';
                }

                resetEventListeners();

                toggleField(startDateInput, true, false);

                if (isHalfDay) {
                    toggleField(endDateInput, true, true); // Read-only
                    toggleField(daysOutputField, true, true); // Read-only

                    // Hide second row
                    secondRow.style.display = 'none';

                    // Add event listener to update half-day fields
                    startDateInput.addEventListener('change', updateHalfDayFields);

                    if (isEditForm && startDateInput.value) {
                        endDateInput.value = startDateInput.value;
                        daysOutputField.value = '0.5';
                    }
                } else {
                    // For regular leaves
                    toggleField(endDateInput, true, false); // Fully enabled
                    toggleField(daysOutputField, true, true); // Read-only

                    secondRow.style.display = 'flex';

                    startDateInput.addEventListener('change', calculateDays);
                    endDateInput.addEventListener('change', calculateDays);

                    if (isEditForm && startDateInput.value && endDateInput.value) {
                        calculateDays();
                    }
                }

                // تحديث التلميح عند تغيير نوع الإجازة
                updateDaysTooltip();
            };

            // Initialize without showing error messages
            fv.disableValidator("end_date", "notEmpty");

            leaveTypeSelect.addEventListener("change", () => {
                toggleFields(true);

                // Also update validation state
                toggleEndDateValidators();

                // تحديث التلميح
                updateDaysTooltip();
            });

            // When start date changes
            startDateInput.addEventListener("change", () => {
                const isHalfDay = isHalfDayLeaveSelected();

                if (isHalfDay && startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                    daysOutputField.value = '0.5';
                    fv.resetField("end_date");
                } else {
                    fv.revalidateField("end_date");
                    calculateDays();
                }
            });

            // Add event listener to revalidate end date on change
            endDateInput.addEventListener("change", () => {
                fv.revalidateField("end_date");
                calculateDays();
            });

            // Initial validation on page load
            window.addEventListener('load', () => {
                toggleEndDateValidators();

                // In edit form, calculate initial days (if not half-day)
                if (isEditForm && !isHalfDayLeaveSelected() && startDateInput.value && endDateInput.value) {
                    calculateDays();
                }

                // تحديث التلميح عند تحميل الصفحة
                updateDaysTooltip();
            });

            // Support Select2 for leave type change
            if (window.jQuery && select2.length) {
                $(leaveTypeSelect).on("select2:select", function () {
                    toggleFields(true);
                    toggleEndDateValidators();

                    // تحديث التلميح
                    updateDaysTooltip();
                });
            }

            if (isEditForm) {
                toggleFields(false);
            }
        }
    });

    if (btnSubmit) {
        btnSubmit.addEventListener("click", function (e) {
            e.preventDefault();
            const currentIndex = validationStepper._currentIndex;

            const leaveTypeSelect = document.querySelector("#leave_type_id");
            const startDateInput = document.querySelector('[name="start_date"]');
            const endDateInput = document.querySelector('[name="end_date"]');
            const daysOutputField = document.querySelector('[name="calculated_days"]');

            if (leaveTypeSelect && startDateInput && endDateInput) {
                const isHalfDay = leaveTypeSelect.selectedOptions[0]?.dataset.leaveUnitType === "half_day";

                if (isHalfDay && startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                    daysOutputField.value = '0.5';
                    formValidations[currentIndex].disableValidator("end_date", "notEmpty");
                    formValidations[currentIndex].disableValidator("end_date", "callback");
                } else {
                    formValidations[currentIndex].enableValidator("end_date", "notEmpty");
                    formValidations[currentIndex].enableValidator("end_date", "callback");
                }
            }

            formValidations[currentIndex].validate();
        });
    }

    btnPrevList.forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            validationStepper.previous();
        });
    });

    $(document).on('click', '.toggle-removal', function () {
        const id = $(this).data('attachment-id');
        const chk = $(`#remove_attachment_${id}`).prop('checked', function (_, v) {
            return !v;
        });
        $(this).toggleClass('btn-danger btn-outline-danger');
        $(this).closest('tr').toggleClass('table-danger', chk.prop('checked'));
    });

    // Add additional attachments
    let attachmentIndex = 0;
    $("#add-attachment").on("click", function () {
        const container = $("#additional-attachments-container");
        const attachmentHtml = `
        <div class="row mb-3 attachment-row" data-index="${attachmentIndex}">
            <div class="col-md-6">
                <label class="form-label">اسم المرفق</label>
                <input type="text"
                       name="additional_attachments[${attachmentIndex}][name]"
                       class="form-control"
                       placeholder="اسم المرفق"
                       required />
            </div>
            <div class="col-md-5">
                <label class="form-label">الملف</label>
                <input type="file"
                       name="additional_attachments[${attachmentIndex}][file]"
                       class="form-control"
                       required />
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-attachment">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
        `;
        container.append(attachmentHtml);
        attachmentIndex++;
    });

    // Remove attachment row
    $(document).on("click", ".remove-attachment", function () {
        $(this).closest(".attachment-row").remove();
    });

    // Initialize Select2 and revalidate on change
    if (select2.length) {
        select2.each(function () {
            const $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this
                .select2({
                    placeholder: $this.data("placeholder") || "اختر خيارًا",
                    dropdownParent: $this.parent(),
                    language: "ar",
                })
                .on("change", function () {
                    const fieldName = $this.attr("name");
                    const currentIndex = validationStepper._currentIndex;
                    formValidations[currentIndex].revalidateField(fieldName);
                });
        });
    }

    wizardValidationForm.addEventListener('submit', function (event) {
        document.querySelectorAll('input[disabled]').forEach(field => {
            field.removeAttribute('required');
        });

        const leaveTypeSelect = document.querySelector("#leave_type_id");
        const startDateInput = document.querySelector('[name="start_date"]');
        const endDateInput = document.querySelector('[name="end_date"]');
        const daysOutputField = document.querySelector('[name="calculated_days"]');

        if (leaveTypeSelect && startDateInput && endDateInput) {
            const isHalfDay = leaveTypeSelect.selectedOptions[0]?.dataset.leaveUnitType === 'half_day';
            if (isHalfDay && startDateInput.value) {
                endDateInput.value = startDateInput.value;
                daysOutputField.value = '0.5';
            }
        }
    });
})();
