"use strict";

(function () {
    const select2 = $(".select2");

    const wizardValidation = document.querySelector("#wizard-validation");
    if (typeof wizardValidation !== "undefined" && wizardValidation !== null) {
        const wizardValidationForm = wizardValidation.querySelector(
            "#wizard-validation-form",
        );
        const wizardValidationFormStep1 = wizardValidationForm.querySelector(
            "#account-details-validation",
        );
        const wizardValidationFormStep2 = wizardValidationForm.querySelector(
            "#details-validation",
        );
        const wizardValidationNext = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-next"),
        );
        const wizardValidationPrev = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-prev"),
        );

        const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

        // إخفاء الخطوات التي لا يمتلك المستخدم صلاحية الوصول إليها
        if (!userCanAddProject) {
            // إخفاء الخطوة الأولى
            const step1Content = document.querySelector(
                "#account-details-validation",
            );
            if (step1Content) {
                step1Content.style.display = "none";
            }

            // إخفاء التنقل إلى الخطوة الأولى
            const step1Nav = wizardValidation.querySelector(
                '.step[data-target="#account-details-validation"]',
            );
            if (step1Nav) {
                step1Nav.style.display = "none";
            }
        }

        if (!userCanCompleteProject) {
            // إخفاء الخطوة الثانية
            const step2Content = document.querySelector("#details-validation");
            if (step2Content) {
                step2Content.style.display = "none";
            }

            // إخفاء التنقل إلى الخطوة الثانية
            const step2Nav = wizardValidation.querySelector(
                '.step[data-target="#details-validation"]',
            );
            if (step2Nav) {
                step2Nav.style.display = "none";
            }
        }

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
        });

        // إذا كان المستخدم لا يمتلك صلاحية الوصول إلى الخطوة الأولى، الانتقال إلى الخطوة الثانية
        if (!userCanAddProject && userCanCompleteProject) {
            validationStepper.to(2); // الانتقال إلى الخطوة الثانية (التعداد يبدأ من 1)
        }

        // تعريف متغيرات التحقق
        let FormValidation1 = null;
        let FormValidation2 = null;

        // تهيئة التحقق للخطوة الأولى إذا كان المستخدم يمتلك الصلاحية
        if (userCanAddProject) {
            FormValidation1 = FormValidation.formValidation(
                wizardValidationFormStep1,
                {
                    fields: {
                        project_name: {
                            validators: {
                                notEmpty: {
                                    message: "اسم المشروع مطلوب",
                                },
                                stringLength: {
                                    min: 3,
                                    max: 255,
                                    message:
                                        "يجب أن يكون اسم المشروع أكثر من 3 وأقل من 255 حرفًا",
                                },
                            },
                        },

                        contract_type: {
                            validators: {
                                notEmpty: {
                                    message: " نوع العقد مطلوب",
                                },
                            },
                        },

                        start_date: {
                            validators: {
                                notEmpty: {
                                    message: "تاريخ البدء مطلوب",
                                },
                                date: {
                                    format: "YYYY-MM-DD",
                                    message: "تاريخ البدء غير صالح",
                                },
                            },
                        },

                        primary_contract_id: {
                            validators: {
                                callback: {
                                    message: " العقد الرئيسي مطلوب",
                                    callback: function (input) {
                                        var contract_type = $("#contract_type").val();

                                        if (contract_type == "main_contract") {
                                            const primary_contract_id = input.value;

                                            if (primary_contract_id == "") {
                                                return false;
                                            } else {
                                                return true;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        exceptional_contract_id: {
                            validators: {
                                callback: {
                                    message: " العقد الإستثنائي مطلوب",
                                    callback: function (input) {
                                        var contract_type = $("#contract_type").val();

                                        if (contract_type == "exceptional_contract") {
                                            const exceptional_contract_id = input.value;

                                            if (exceptional_contract_id == "") {
                                                return false;
                                            } else {
                                                return true;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        project_type: {
                            validators: {
                                notEmpty: {
                                    message: " نوع المشروع مطلوب",
                                },
                            },
                        },

                        technical_manager_id: {
                            validators: {
                                notEmpty: {
                                    message: "مدير الشؤون الفنية مطلوب",
                                },
                            },
                        },
                        contractual_closure: {
                            validators: {
                                date: {
                                    format: "YYYY-MM-DD",
                                    message: "الإغلاق التعاقدي غير صالح",
                                },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            eleValidClass: "",
                            rowSelector: ".col-sm-6, .col-sm-12, .col-sm-4",
                        }),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                    },
                    init: (instance) => {
                        instance.on("plugins.message.placed", function (e) {
                            if (
                                e.element.parentElement.classList.contains(
                                    "input-group",
                                )
                            ) {
                                e.element.parentElement.insertAdjacentElement(
                                    "afterend",
                                    e.messageElement,
                                );
                            }
                        });
                    },
                },
            ).on("core.form.valid", function () {
                if (userCanAddProject && !userCanCompleteProject) {
                    // إذا كان المستخدم يمتلك صلاحية الخطوة الأولى فقط، أرسل النموذج
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    wizardValidationForm.submit();
                } else {
                    // الانتقال إلى الخطوة التالية
                    validationStepper.next();
                }
            });
        }

        // تهيئة التحقق للخطوة الثانية إذا كان المستخدم يمتلك الصلاحية
        if (userCanCompleteProject) {
            FormValidation2 = FormValidation.formValidation(
                wizardValidationFormStep2,
                {
                    fields: {
                        manager_user_id: {
                            validators: {
                                notEmpty: {
                                    message: "مدير المشروع مطلوب",
                                },
                                // callback: {
                                //     message: "مدير المشروع غير صالح",
                                //     callback: function (input) {
                                //         const value = input.value;
                                //         return (
                                //             value === "" ||
                                //             /^[0-9]+$/.test(value)
                                //         );
                                //     },
                                // },
                            },
                        },
                        // 'team_members[]': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: "فريق المشروع مطلوب",
                        //         },

                        //     },
                        // },
                        // claim_type: {
                        //     validators: {
                        //         notEmpty: {
                        //             message: "نوع المطالبة  مطلوبة.",
                        //         },
                        //     },
                        // },
                        total_claim: {
                            validators: {
                                numeric: {
                                    message:
                                        "إجمالي المطالبة يجب أن يكون رقمًا",
                                },
                                // notEmpty: {
                                //     message: 'القيمة الخاصة بالمطالبة المالية مطلوبة'
                                // },
                                // callback: {
                                //     message:
                                //         "القيمة الخاصة بالمطالبة المالية مطلوبة",
                                //     callback: function (input) {
                                //         const claim_type =
                                //             $("#claim_type").val();
                                //         const thisVal = input.value;
                                //         if (
                                //             claim_type == "financial" &&
                                //             thisVal == ""
                                //         ) {
                                //             return false;
                                //         }
                                //         return true;
                                //     },
                                // },
                            },
                        },
                        non_financial_claim: {
                            validators: {
                                // notEmpty: {
                                //     message: 'القيمة الخاصة بالمطالبة غير المالية مطلوبة'
                                // },
                                // callback: {
                                //     message:
                                //         "القيمة الخاصة بالمطالبة غير المالية مطلوبة",
                                //     callback: function (input) {
                                //         const claim_type =
                                //             $("#claim_type").val();
                                //         const thisVal = input.value;
                                //         if (
                                //             claim_type == "non_financial" &&
                                //             thisVal == ""
                                //         ) {
                                //             return false;
                                //         }
                                //         return true;
                                //     },
                                // },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            eleValidClass: "",
                            rowSelector: ".col-sm-6, .col-sm-12, .col-sm-4",
                        }),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                    },
                    init: (instance) => {
                        instance.on("plugins.message.placed", function (e) {
                            if (
                                e.element.parentElement.classList.contains(
                                    "input-group",
                                )
                            ) {
                                e.element.parentElement.insertAdjacentElement(
                                    "afterend",
                                    e.messageElement,
                                );
                            }
                        });
                    },
                },
            ).on("core.form.valid", function () {
                // إرسال النموذج عندما تكون الخطوة الثانية صالحة
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                wizardValidationForm.submit();
            });
        }

        // تهيئة Select2
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this
                    .select2({
                        placeholder: $this.data("placeholder") || "اختر خيارًا",
                        dropdownParent: $this.parent(),
                        language: "ar",
                    })
                    .on("change", function () {
                        var fieldName = $this.attr("name");
                        var currentIndex = validationStepper._currentIndex;

                        // إعادة التحقق من الحقل عند التغيير
                        if (currentIndex === 0 && FormValidation1) {
                            FormValidation1.revalidateField(fieldName);
                        } else if (currentIndex === 1 && FormValidation2) {
                            FormValidation2.revalidateField(fieldName);
                        }
                    });
            });
        }

        // أزرار التالي
        wizardValidationNext.forEach((item) => {
            item.addEventListener("click", () => {
                var currentIndex = validationStepper._currentIndex;

                if (currentIndex === 0) {
                    if (FormValidation1) {
                        if (userCanAddProject && !userCanCompleteProject) {
                            // إذا كان المستخدم يمتلك صلاحية الخطوة الأولى فقط
                            FormValidation1.validate().then(function (status) {
                                if (status === "Valid") {
                                    btnSubmit.setAttribute('disabled', 'disabled');
                                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                                    wizardValidationForm.submit();
                                }
                            });
                        } else {
                            // الانتقال إلى الخطوة التالية
                            FormValidation1.validate();
                        }
                    } else if (FormValidation2) {
                        // إذا لم يكن لدى المستخدم صلاحية الخطوة الأولى، وانتقل إلى الخطوة الثانية
                        // validationStepper.next();
                        FormValidation2.validate();
                    }
                } else if (currentIndex === 1 && FormValidation2) {
                    FormValidation2.validate();
                }
            });
        });

        // أزرار السابق
        wizardValidationPrev.forEach((item) => {
            item.addEventListener("click", () => {
                var currentIndex = validationStepper._currentIndex;

                if (currentIndex === 1) {
                    if (userCanAddProject) {
                        validationStepper.previous();
                    } else {
                        // لا يمكن العودة إلى الخطوة السابقة
                        return;
                    }
                }
            });
        });

        // تعديل نص زر "التالي" في الخطوة الأولى إذا كان المستخدم يمتلك صلاحية واحدة فقط
        if (userCanAddProject && !userCanCompleteProject) {
            const btnNextStep1 =
                wizardValidationFormStep1.querySelector(".btn-next");
            if (btnNextStep1) {
                btnNextStep1.textContent = "إرسال";
            }
        }

        // إخفاء أزرار "التالي" و"السابق" إذا كان المستخدم يمتلك خطوة واحدة فقط
        if (userCanAddProject && !userCanCompleteProject) {
            // إخفاء زر "السابق" في الخطوة الأولى
            const btnPrevStep1 =
                wizardValidationFormStep1.querySelector(".btn-prev");
            if (btnPrevStep1) {
                btnPrevStep1.style.display = "none";
            }
        }

        if (!userCanAddProject && userCanCompleteProject) {
            // إخفاء زر "السابق" في الخطوة الثانية
            const btnPrevStep2 =
                wizardValidationFormStep2.querySelector(".btn-prev");
            if (btnPrevStep2) {
                btnPrevStep2.style.display = "none";
            }
        }
    }
})();
