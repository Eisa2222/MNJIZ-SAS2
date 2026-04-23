"use strict";

(function () {
    const select2 = $(".select2");

    const wizardValidation = document.querySelector("#wizard-validation");
    if (!wizardValidation) return;
    const wizardValidationForm = wizardValidation.querySelector("#leave-balance-form");
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
                    employee_id: {
                        validators: {
                            notEmpty: {
                                message: "الرجاء اختيار الموظف",
                            },
                        },
                    },
                    year: {
                        validators: {
                            notEmpty: {
                                message: "الرجاء اختيار السنة",
                            },
                        },
                    },
                    total_days: {
                        validators: {
                            notEmpty: {
                                message: "إجمالي الأيام مطلوب",
                            },
                            numeric: {
                                message: "يجب أن يكون إجمالي الأيام رقمًا",
                                decimalSeparator: '.'
                            },
                            greaterThan: {
                                message: "يجب أن يكون إجمالي الأيام أكبر من أو يساوي 0",
                                min: 0,
                                inclusive: true
                            }
                        }
                    },
                    used_days: {
                        validators: {
                            notEmpty: {
                                message: "الأيام المستخدمة مطلوبة",
                            },
                            numeric: {
                                message: "يجب أن تكون الأيام المستخدمة رقمًا",
                                decimalSeparator: '.'
                            },
                            greaterThan: {
                                message: "يجب أن تكون الأيام المستخدمة أكبر من أو تساوي 0",
                                min: 0,
                                inclusive: true
                            }
                        }
                    }
                }),
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger({
                    event: {
                        total_days: 'change input',
                        used_days: 'change input'
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
            const totalDaysInput = step.querySelector('[name="total_days"]');
            const usedDaysInput = step.querySelector('[name="used_days"]');
            const remainingDaysOutput = step.querySelector('[name="remaining_days"]');

            /**
             * تنسيق عرض الأرقام: إظهار الكسور بدقة 4 خانات عشرية
             */
            const formatNumber = (number) => {
                const num = parseFloat(number);
                if (isNaN(num)) return '0';

                return Number.isInteger(num) ? num.toString() : parseFloat(num.toFixed(4)).toString();
            };

            /**
             * حساب الأيام المتبقية
             */
            const calculateRemainingDays = () => {
                const totalDays = parseFloat(totalDaysInput.value) || 0;
                const usedDays = parseFloat(usedDaysInput.value) || 0;

                const remainingDays = totalDays - usedDays;

                remainingDaysOutput.value = formatNumber(remainingDays);

                if (remainingDays < 0) {
                    remainingDaysOutput.classList.add('text-danger');
                } else {
                    remainingDaysOutput.classList.remove('text-danger');
                }
            };

            if (isEditForm) {
                const totalDaysValue = parseFloat(totalDaysInput.value) || 0;
                totalDaysInput.value = formatNumber(totalDaysValue);

                const usedDaysValue = parseFloat(usedDaysInput.value) || 0;
                usedDaysInput.value = formatNumber(usedDaysValue);

                const remainingDaysValue = parseFloat(remainingDaysOutput.value) || 0;
                remainingDaysOutput.value = formatNumber(remainingDaysValue);

                if (remainingDaysValue < 0) {
                    remainingDaysOutput.classList.add('text-danger');
                }
            }

            try {
                var tooltipElements = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipElements.map(function (tooltipEl) {
                    return new bootstrap.Tooltip(tooltipEl);
                });
            } catch (e) {
                console.error("خطأ في تهيئة التلميحات:", e);
            }

            totalDaysInput.addEventListener("change", calculateRemainingDays);
            totalDaysInput.addEventListener("input", calculateRemainingDays);
            usedDaysInput.addEventListener("change", calculateRemainingDays);
            usedDaysInput.addEventListener("input", calculateRemainingDays);

            window.addEventListener('load', () => {
                calculateRemainingDays();
            });

            calculateRemainingDays();

            if (remainingDaysOutput) {
                remainingDaysOutput.setAttribute('readonly', true);
            }

            if (window.jQuery && select2.length) {
                $('#employee_id, #year').on('select2:select', function () {
                    const fieldName = $(this).attr('name');
                    fv.revalidateField(fieldName);
                });
            }
        }
    });

    if (btnSubmit) {
        btnSubmit.addEventListener("click", function (e) {
            e.preventDefault();
            const currentIndex = validationStepper._currentIndex;
            formValidations[currentIndex].validate();
        });
    }

    btnPrevList.forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            validationStepper.previous();
        });
    });

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

        const totalDaysInput = document.querySelector('[name="total_days"]');
        const usedDaysInput = document.querySelector('[name="used_days"]');
        const remainingDaysOutput = document.querySelector('[name="remaining_days"]');

        if (totalDaysInput && usedDaysInput && remainingDaysOutput) {
            const totalDays = parseFloat(totalDaysInput.value) || 0;
            const usedDays = parseFloat(usedDaysInput.value) || 0;

            if (usedDays > totalDays) {
                Swal.fire({
                    title: 'تنبيه',
                    text: 'الأيام المستخدمة تتجاوز إجمالي الأيام، مما سيؤدي إلى رصيد متبقي بالسالب',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'متابعة التحديث',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const remainingDays = totalDays - usedDays;
                        const formattedRemainingDays = Number.isInteger(remainingDays) ?
                            remainingDays.toString() : parseFloat(remainingDays.toFixed(4)).toString();
                        remainingDaysOutput.value = formattedRemainingDays;

                        wizardValidationForm.submit();
                    }
                });
                event.preventDefault();
                return false;
            }

            const remainingDays = totalDays - usedDays;
            const formattedRemainingDays = Number.isInteger(remainingDays) ?
                remainingDays.toString() : parseFloat(remainingDays.toFixed(4)).toString();
            remainingDaysOutput.value = formattedRemainingDays;
        }
    });
})();
