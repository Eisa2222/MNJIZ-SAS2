/**
 * Form Wizard
 */

"use strict";

(function () {
    const select2 = $(".select2"),
        selectPicker = $(".selectpicker");

    // وظائف مسح الحقول - تم تحديثها لتمسح فقط الحقول الخاصة بكل نوع
    function clearIndividualFields() {
        // مسح فقط الحقول الخاصة بالفرد (دون مسح الاسم)
        $("#civil_registry_number").val("");
        $("#nationality_id").val(null).trigger("change");
        $("#status_id").val(null).trigger("change");
    }

    function clearCompanyFields() {
        // مسح الحقول الخاصة بالمؤسسة
        $("#commercial_registration_number").val("");
        $("#unified_number").val("");
        $("#sector_id").val(null).trigger("change"); // إضافة مسح حقل القطاع
        $("#authorizations-wrapper").empty();
    }

    // تهيئة حالة حقل القطاع عند تحميل الصفحة
    $(document).ready(function () {
        if ($('input[name="customer_type"]:checked').val() === "individual") {
            $("#sector_id").closest(".col-sm-6").hide();
        }
    });

    // Wizard Validation
    const wizardValidation = document.querySelector("#wizard-validation");
    if (typeof wizardValidation !== undefined && wizardValidation !== null) {
        // Wizard form
        const wizardValidationForm = wizardValidation.querySelector(
            "#wizard-validation-form",
        );
        const wizardValidationFormStep1 = wizardValidationForm.querySelector(
            "#account-details-validation",
        );
        const wizardValidationFormStep2 = wizardValidationForm.querySelector(
            "#personal-info-validation",
        );
        const wizardValidationFormStep3 = wizardValidationForm.querySelector(
            "#social-links-validation",
        );
        const wizardValidationNext = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-next"),
        );
        const wizardValidationPrev = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-prev"),
        );

        const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
        });

        // Account details
        const FormValidation1 = FormValidation.formValidation(
            wizardValidationFormStep1,
            {
                fields: {
                    name: {
                        validators: {
                            notEmpty: {
                                message: "اسم العميل مطلوب",
                            },
                            stringLength: {
                                min: 2,
                                max: 255,
                                message:
                                    "يجب أن يكون الاسم أكثر من 2 وأقل من 255 حرفًا",
                            },
                        },
                    },

                    commercial_registration_number: {
                        validators: {
                            stringLength: {
                                min: 10,
                                max: 10,
                                message: "رقم السجل التجاري يجب أن يكون 10 أرقام.",
                            },
                        },
                    },

                    unified_number: {
                        validators: {
                            stringLength: {
                                min: 9,
                                max: 10,

                                message: "الرقم الموحد  يجب أن يكون 9 أو 10 أرقام.",
                            },
                        },
                    },
                    title: {
                        validators: {
                            stringLength: {
                                min: 2,
                                max: 255,
                                message:
                                    "يجب أن تكون الكنية أكثر من 2 وأقل من 255 حرفًا",
                            },
                        },
                    },
                    status_id: {
                        validators: {
                            callback: {
                                message: "حالة العميل مطلوبة",
                                callback: function (input) {
                                    if (
                                        $(
                                            'input[name="customer_type"]:checked',
                                        ).val() === "individual"
                                    ) {
                                        return input.value.trim() !== "";
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                    department_id: {
                        validators: {
                            notEmpty: {
                                message: "قسم العميل مطلوب",
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: "",
                        rowSelector: ".col-sm-6, .col-sm-12",
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

                    // معالج تغيير نوع العميل
                    $('input[name="customer_type"]').on("change", function () {
                        const customerType = $(this).val();

                        if (customerType === "company") {
                            // إخفاء حقول الفرد
                            $(
                                "#title-container, #nationality-container, #status-container, #civil-registry-field",
                            ).hide();
                            clearIndividualFields();

                            // إظهار حقول المؤسسة
                            $(
                                "#commercial-registration-container, #unified-number-container, #authorizations-container",
                            ).show();
                            $("#sector_id").closest(".col-sm-6").show();

                            // تحديث التحقق
                            instance.disableValidator("status_id");
                            instance.disableValidator("nationality_id");
                            instance.enableValidator(
                                "commercial_registration_number",
                            );
                            instance.enableValidator("unified_number");
                        } else {
                            // إخفاء حقول المؤسسة
                            $(
                                "#commercial-registration-container, #unified-number-container, #authorizations-container",
                            ).hide();
                            $("#sector_id").closest(".col-sm-6").hide();
                            clearCompanyFields();

                            // إظهار حقول الفرد
                            $(
                                "#title-container, #nationality-container, #status-container, #civil-registry-field",
                            ).show();

                            // تحديث التحقق
                            instance.enableValidator("status_id");
                            instance.enableValidator("nationality_id");
                            instance.disableValidator(
                                "commercial_registration_number",
                            );
                            instance.disableValidator("unified_number");
                        }

                        // تحديث التحقق للحقول المتأثرة فقط
                        instance.revalidateField("status_id");
                        instance.revalidateField("nationality_id");
                        instance.revalidateField(
                            "commercial_registration_number",
                        );
                        instance.revalidateField("unified_number");
                    });
                },
            },
        ).on("core.form.valid", function () {
            validationStepper.next();
        });

        // Personal info
        const FormValidation2 = FormValidation.formValidation(
            wizardValidationFormStep2,
            {
                fields: {
                    contact_number: {
                        validators: {
                            callback: {
                                message: "رقم الجوال غير صحيح",
                                callback: function (input) {
                                    // التحقق من نوع العميل
                                    const customerType = $('input[name="customer_type"]:checked').val();

                                    // إذا كان نوع العميل ليس "individual"، نعتبر الحقل صحيحاً
                                    if (customerType !== "individual") {
                                        return true;
                                    }

                                    // إذا كان الحقل فارغاً ونوع العميل "individual"
                                    if (input.value.trim() === "") {
                                        return {
                                            valid: false,
                                            message: "رقم الهاتف مطلوب"
                                        };
                                    }

                                    // التحقق من صحة رقم الهاتف باستخدام intl-tel-input
                                    if (window.iti) {
                                        return window.iti.isValidNumber();
                                    }

                                    return false;
                                }
                            }
                        }
                    },
                    email: {
                        validators: {
                            emailAddress: {
                                message: "يرجى إدخال بريد إلكتروني صالح.",
                            },
                        },
                    },
                    address: {
                        validators: {
                            callback: {
                                message: "المدينة مطلوبة",
                                callback: function (input) {
                                    // التحقق من نوع العميل
                                    const customerType = $('input[name="customer_type"]:checked').val();

                                    // إذا كان نوع العميل ليس "individual"، نعتبر الحقل صحيحاً
                                    if (customerType === "company") {
                                        return true;
                                    }

                                    // إذا كان نوع العميل "individual"، نتحقق من أن الحقل غير فارغ
                                    return input.value.trim() !== "";
                                }
                            }
                        }
                    },
                    civil_registry_number: {
                        validators: {
                            stringLength: {
                                min: 10,
                                max: 10,
                                message: "رقم السجل المدني يجب أن يكون 10 أرقام.",
                            },
                            callback: {
                                message: "السجل المدني مطلوب",
                                callback: function (input) {
                                    if (
                                        $(
                                            'input[name="customer_type"]:checked',
                                        ).val() === "individual"
                                    ) {
                                        return input.value.trim() !== "";
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: "",
                        rowSelector: ".col-sm-6, .col-sm-12",
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

                    $('input[name="customer_type"]').on("change", function () {
                        if ($(this).val() === "company") {
                            instance.disableValidator("civil_registry_number");
                        } else {
                            instance.enableValidator("civil_registry_number");
                        }

                        // إعادة التحقق من السجل المدني فقط
                        instance.revalidateField("civil_registry_number");
                    });
                },
            },
        ).on("core.form.valid", function () {
            var fullNumber = window.iti.getNumber();
            var phoneFullInput = document.getElementById("contact_number");
            if (phoneFullInput) {
                phoneFullInput.value = fullNumber;
            }

            validationStepper.next();
        });

        // Social links validation
        const FormValidation3 = FormValidation.formValidation(
            wizardValidationFormStep3,
            {
                fields: {
                    relationship_manager_id: {
                        validators: {
                            notEmpty: {
                                message: "مسؤول العلاقات مطلوب",
                            },
                        },
                    },
                    detailed_marketing_channel_id: {
                        validators: {
                            callback: {
                                message: "قناة التسويق التفصيلية مطلوبة",
                                callback: function (input) {
                                    var marketingChannelVal =
                                        $("#marketing_channel_id").val();
                                    return (
                                        marketingChannelVal != "2" ||
                                        input.value !== ""
                                    );
                                },
                            },
                        },
                    },
                    parent_customer_id: {
                        validators: {
                            callback: {
                                message: "العميل مطلوب",
                                callback: function (input) {
                                    var marketingChannelVal =
                                        $("#marketing_channel_id").val();
                                    return (
                                        marketingChannelVal != "3" ||
                                        input.value !== ""
                                    );
                                },
                            },
                        },
                    },
                    social_media_id: {
                        validators: {
                            callback: {
                                message: "موقع التواصل الاجتماعي مطلوب",
                                callback: function (input) {
                                    var marketingChannelVal =
                                        $("#marketing_channel_id").val();
                                    return (
                                        marketingChannelVal != "6" ||
                                        input.value !== ""
                                    );
                                },
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: "",
                        rowSelector: ".col-sm-6, .col-sm-12",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            btnSubmit.setAttribute('disabled', 'disabled');
            btnSubmit.dataset.oldText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

            wizardValidationForm.submit();
        });

        // معالجة تغيير قناة التسويق
        $("#marketing_channel_id").on("change", function () {
            let selectedValue = $(this).val();

            // إخفاء جميع الحقول أولاً
            $(
                "#detailedMarketingChannelContainer, #clientsContainer, #socialMediaContainer",
            ).hide();
            $(
                "#detailed_marketing_channel_id, #parent_customer_id, #social_media_id",
            ).removeAttr("required");

            // مسح القيم السابقة
            $("#detailed_marketing_channel_id, #parent_customer_id, #social_media_id")
                .val(null)
                .trigger("change");

            // إظهار الحقل المناسب
            if (selectedValue == "2") {
                $("#detailedMarketingChannelContainer").show();
                $("#detailed_marketing_channel_id").attr("required", "required");
            } else if (selectedValue == "3") {
                $("#clientsContainer").show();
                $("#parent_customer_id").attr("required", "required");
            } else if (selectedValue == "6") {
                $("#socialMediaContainer").show();
                $("#social_media_id").attr("required", "required");
            }

            // إعادة التحقق من الحقول المتأثرة
            FormValidation3.revalidateField("detailed_marketing_channel_id");
            FormValidation3.revalidateField("parent_customer_id");
            FormValidation3.revalidateField("social_media_id");
        });

        // معالجة أزرار التالي والسابق
        wizardValidationNext.forEach((item) => {
            item.addEventListener("click", (event) => {
                switch (validationStepper._currentIndex) {
                    case 0:
                        FormValidation1.validate();
                        break;
                    case 1:
                        FormValidation2.validate();
                        break;
                    case 2:
                        FormValidation3.validate();
                        break;
                }
            });
        });

        wizardValidationPrev.forEach((item) => {
            item.addEventListener("click", (event) => {
                switch (validationStepper._currentIndex) {
                    case 2:
                        validationStepper.previous();
                        break;
                    case 1:
                        validationStepper.previous();
                        break;
                    case 0:
                    default:
                        break;
                }
            });
        });

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
                        switch (validationStepper._currentIndex) {
                            case 0:
                                FormValidation1.revalidateField(
                                    $this.attr("name"),
                                );
                                break;
                            case 1:
                                FormValidation2.revalidateField(
                                    $this.attr("name"),
                                );
                                break;
                            case 2:
                                FormValidation3.revalidateField(
                                    $this.attr("name"),
                                );
                                break;
                            default:
                                break;
                        }
                    });
            });
        }

        // تحديث قيم select2 عند تحميل الصفحة إذا كانت هناك قيم محفوظة
        if ($('input[name="customer_type"]:checked').val() === "company") {
            $(
                "#title-container, #nationality-container, #status-container, #civil-registry-field",
            ).hide();
            $(
                "#commercial-registration-container, #unified-number-container, #authorizations-container",
            ).show();
            $("#sector_id").closest(".col-sm-6").show();

            FormValidation1.disableValidator("status_id");
            FormValidation1.disableValidator("nationality_id");
            FormValidation1.enableValidator("commercial_registration_number");
            FormValidation1.enableValidator("unified_number");
            FormValidation2.disableValidator("civil_registry_number");
        } else {
            $(
                "#commercial-registration-container, #unified-number-container, #authorizations-container",
            ).hide();
            $(
                "#title-container, #nationality-container, #status-container, #civil-registry-field",
            ).show();
            $("#sector_id").closest(".col-sm-6").hide();

            FormValidation1.enableValidator("status_id");
            FormValidation1.enableValidator("nationality_id");
            FormValidation1.disableValidator("commercial_registration_number");
            FormValidation1.disableValidator("unified_number");
            FormValidation2.enableValidator("civil_registry_number");
        }

        // تهيئة قيم قناة التسويق المحفوظة
        let savedMarketingChannel = $("#marketing_channel_id").val();
        if (savedMarketingChannel) {
            if (savedMarketingChannel == "2") {
                $("#detailedMarketingChannelContainer").show();
                $("#detailed_marketing_channel_id").attr("required", "required");
            } else if (savedMarketingChannel == "3") {
                $("#clientsContainer").show();
                $("#parent_customer_id").attr("required", "required");
            } else if (savedMarketingChannel == "6") {
                $("#socialMediaContainer").show();
                $("#social_media_id").attr("required", "required");
            }
        }
    }

    document.querySelectorAll("input.numeric-only").forEach((input) => {
        input.addEventListener("input", () => {
            // إزالة كل ما هو ليس رقم
            let sanitized = input.value.replace(/\D/g, "");
            // إذا كان هناك maxlength، نقص القيمة لطوله
            const max = input.getAttribute("maxlength");
            if (max) {
                sanitized = sanitized.slice(0, max);
            }

            input.value = sanitized;
        });
    });
})();
