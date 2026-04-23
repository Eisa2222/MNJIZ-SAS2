"use strict";

// تعريف المتغيرات العامة للمرفقات
const deletedAttachments = new Set();

(function () {
    const select2 = $(".select2");

    // Wizard Validation
    const wizardValidation = document.querySelector("#wizard-validation");
    if (wizardValidation) {
        const wizardValidationForm =
            wizardValidation.querySelector("#employee-form");
        const steps = wizardValidationForm.querySelectorAll(".content");
        const btnNextList = wizardValidationForm.querySelectorAll(".btn-next");
        const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");
        const btnSubmit = wizardValidationForm.querySelector(".btn-submit");

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
        });

        // Form Validation Instances
        const formValidations = [];

        // Initialize validation for each step
        steps.forEach((step, index) => {
            const fv = FormValidation.formValidation(step, {
                fields: {
                    // الخطوة الأولى
                    ...(index === 0 && {
                        national_number: {
                            validators: {
                                notEmpty: {
                                    message: "الرقم الوظيفي مطلوب",
                                },
                                stringLength: {
                                    min: 3,
                                    max: 10,
                                    message:
                                        "الرقم الوظيفي يجب أن يكون بين 3 و10 أرقام.",
                                },
                            },
                        },

                        name: {
                            //  الاسم
                            validators: {
                                notEmpty: {
                                    message: "حقل الاسم مطلوب.",
                                },
                                stringLength: {
                                    min: 5,
                                    max: 50,
                                    message:
                                        "اسم الموظف  يجب أن يكون على الاقل 5 احرف و لا يتجازو 50 حرف.",
                                },
                            },
                        },
                        nickname: {
                            validators: {
                                notEmpty: { message: "حقل اللقب مطلوب." },
                                stringLength: {
                                    max: 20,
                                    message:
                                        "لا يجوز أن يتجاوز اللقب 20 حرفاً.",
                                },
                            },
                        },
                        id_number: {
                            // رقم الهوية
                            validators: {
                                notEmpty: {
                                    message: "رقم الهوية أو الإقامة مطلوب.",
                                },
                                stringLength: {
                                    min: 10,
                                    max: 10,
                                    message: "رقم الهوية يجب أن يكون 10 أرقام.",
                                },
                            },
                        },
                        gender: {
                            // النوع
                            validators: {
                                notEmpty: {
                                    message: "النوع مطلوب",
                                },
                            },
                        },
                        nationality: {
                            // الجنسية
                            validators: {
                                notEmpty: {
                                    message: "الجنسية مطلوبة",
                                },
                            },
                        },

                        qualification_degree: {
                            validators: {
                            },
                        },

                        knowledge_area: {
                            validators: {
                            },
                        },

                        personal_email: {
                            validators: {
                                emailAddress: {
                                    message: "يرجى إدخال بريد إلكتروني صالح.",
                                },
                            },
                        },
                        work_email: {
                            validators: {
                                notEmpty: {
                                    message: "بريد العمل مطلوب.",
                                },
                                emailAddress: {
                                    message: "يرجى إدخال بريد إلكتروني صالح.",
                                },
                            },
                        },
                        mobile: {
                            validators: {
                                notEmpty: {
                                    message: "رقم الهاتف مطلوب",
                                },
                                callback: {
                                    message: "رقم الجوال غير صحيح",
                                    callback: function (input) {
                                        // إذا كان الحقل فارغًا، لا تتحقق هنا لأن محقق notEmpty سيتولى ذلك
                                        if (input.value.trim() === "") {
                                            return true; // يعتبر التحقق ناجحًا هنا لتجنب ظهور رسالة "رقم الجوال غير صحيح"
                                        }

                                        if (window.iti) {
                                            return window.iti.isValidNumber();
                                        }

                                        return false;
                                    },
                                },
                            },
                        },
                        address: {
                            validators: {
                                stringLength: {
                                    min: 5,
                                    max: 255,
                                    message:
                                        " العنوان  يجب أن يكون على الاقل 5 احرف و لا يتجازو 255 حرف.",
                                },
                            },
                        },
                    }),
                    // الخطوة الثانية
                    ...(index === 1 && {
                        hr_status_id: {
                            validators: {
                                notEmpty: {
                                    message: "حالة الموظف مطلوبة.",
                                },
                            },
                        },
                        roles: {
                            validators: {
                                notEmpty: {
                                    message: "يرجى اختيار المسمى الوظيفي.",
                                },
                            },
                        },

                        license_type: {
                            validators: {
                                notEmpty: {
                                    message: "يرجى اختيار نوع الرخصة.",
                                },
                            },
                        },

                        law_license_number: {
                            validators: {
                                stringLength: {
                                    min: 5,
                                    max: 10,
                                    message:
                                        " رقم رخصة المحاماة يجب أن يكون بين 5 و10 أرقام.",
                                },
                                callback: {
                                    message: "رقم رخصة المحاماة مطلوب.",
                                    callback: function (input) {
                                        const license_type = wizardValidationForm.querySelector(
                                            '[name="license_type"]',
                                        ).value;

                                        if (license_type === 'lawyer') {
                                            if (input.value.trim() === '') {
                                                return false;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        law_license_end_date: {
                            validators: {
                                callback: {
                                    message: "تاريخ انتهاء رخصة المحاماة مطلوب",
                                    callback: function (input) {
                                        const license_type = wizardValidationForm.querySelector(
                                            '[name="license_type"]',
                                        ).value;

                                        if (license_type === 'lawyer') {
                                            if (input.value.trim() === '') {
                                                return false;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        training_number: {
                            validators: {
                                stringLength: {
                                    min: 5,
                                    max: 10,
                                    message:
                                        " رقم رخصة التدريب يجب أن يكون بين 5 و10 أرقام.",
                                },
                                callback: {
                                    message: "رقم رخصة التدريب مطلوب.",
                                    callback: function (input) {
                                        const license_type = wizardValidationForm.querySelector(
                                            '[name="license_type"]',
                                        ).value;

                                        if (license_type === 'trainee_lawyer') {
                                            if (input.value.trim() === '') {
                                                return false;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        training_end_date: {
                            validators: {
                                callback: {
                                    message: "تاريخ انتهاء رخصة التدريب مطلوب",
                                    callback: function (input) {
                                        const license_type = wizardValidationForm.querySelector(
                                            '[name="license_type"]',
                                        ).value;

                                        if (license_type === 'trainee_lawyer') {
                                            if (input.value.trim() === '') {
                                                return false;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        insurance_status: {
                            validators: {
                                notEmpty: {
                                    message: "حالة التأمينات مطلوبة.",
                                },
                            },
                        },

                        contract_type: {
                            validators: {
                                notEmpty: {
                                    message: "نوع العقد مطلوب.",
                                },
                            },
                        },

                        trial_period: {
                            validators: {
                                notEmpty: {
                                    message: "فترة التجربة مطلوبة.",
                                },
                            },
                        },

                        contract_start_date: {
                            validators: {
                                notEmpty: {
                                    message: "تاريخ بداية العقد مطلوب.",
                                },
                                date: {
                                    format: "YYYY-MM-DD",
                                    message: "يرجى إدخال تاريخ صالح.",
                                },
                            },
                        },

                        contract_end_date: {
                            validators: {
                                date: {
                                    format: "YYYY-MM-DD",
                                    message: "يرجى إدخال تاريخ صالح.",
                                },
                                callback: {
                                    message: "تاريخ نهاية العقد مطلوب ويجب أن يكون بعد تاريخ البداية.",
                                    callback: function (input) {
                                        const type = wizardValidationForm.querySelector(
                                            '[name="contract_type"]',
                                        ).value;
                                        const startDate = wizardValidationForm.querySelector(
                                            '[name="contract_start_date"]',
                                        ).value;

                                        // Only require contract_end_date if contract_type is 'specific'
                                        if (type === 'specific') {
                                            if (input.value.trim() === '') {
                                                return {
                                                    valid: false,
                                                    message: "تاريخ نهاية العقد مطلوب."
                                                };
                                            }

                                            if (startDate && input.value < startDate) {
                                                return {
                                                    valid: false,
                                                    message: "يجب أن يكون تاريخ نهاية العقد بعد تاريخ البداية."
                                                };
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        work_license_end_date: {
                            validators: {
                                callback: {
                                    message: " تاريخ نهاية رخصة العمل مطلوب",
                                    callback: function (input) {
                                        const nationality = wizardValidationForm.querySelector(
                                            '[name="nationality"]',
                                        ).value;

                                        if (nationality != 1) {
                                            if (input.value.trim() === '') {
                                                return false;
                                            }
                                        }

                                        return true;
                                    },
                                },
                            },
                        },

                        basic_salary: {
                            // الراتب الاساسي
                            validators: {
                                notEmpty: {
                                    message: "قيمة الراتب الاساسي مطلوبة ",
                                },
                                numeric: {
                                    message: "يرجى إدخال قيمة رقمية.",
                                },
                                between: {
                                    min: 0,
                                    max: 1000000,
                                    message:
                                        "يجب أن تكون قيمة الراتب بين 0 و1,000,000 ريال.",
                                },
                            },
                        },
                        transportation_allowance: {
                            // بدل النقل
                            validators: {
                                numeric: {
                                    message: "يرجى إدخال قيمة رقمية.",
                                },
                                between: {
                                    min: 0,
                                    max: 1000000,
                                    message:
                                        "يجب أن تكون قيمة بدل النقل بين 0 و1,000,000 ريال.",
                                },
                            },
                        },
                        housing_allowance: {
                            // بدل السكن
                            validators: {
                                numeric: {
                                    message: "يرجى إدخال قيمة رقمية.",
                                },
                                between: {
                                    min: 0,
                                    max: 1000000,
                                    message:
                                        "يجب أن تكون قيمة بدل السكن بين 0 و1,000,000 ريال.",
                                },
                            },
                        },
                        other_allowances: {
                            // بدلات أخرى
                            validators: {
                                numeric: {
                                    message: "يرجى إدخال قيمة رقمية.",
                                },
                                between: {
                                    min: 0,
                                    max: 1000000,
                                    message:
                                        "يجب أن تكون قيمة البدلات الأخرى بين 0 و1,000,000 ريال.",
                                },
                            },
                        },
                        bank_account_type: {
                            validators: {
                                notEmpty: {
                                    message: "نوع الحساب البنكي مطلوب.",
                                },
                            },
                        },
                        iban: {
                            validators: {
                                notEmpty: {
                                    message: "رقم الآيبان مطلوب.",
                                },
                                stringLength: {
                                    min: 24,
                                    max: 24,
                                    message:
                                        "يجب أن يكون الآيبان مكوناً من 24 حرفاً.",
                                },
                                regexp: {
                                    regexp: /^SA[0-9A-Z]{22}$/,
                                    message:
                                        "صيغة الآيبان غير صحيحة (مثال: SA0310000000000000000000).",
                                },
                            },
                        },
                    }),
                    // الخطوة الثالثة
                    ...(index === 2 &&
                    {
                        // تحقق من المرفقات إذا لزم الأمر
                    }),
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-3, .col-md-12",
                        eleValidClass: "",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
                // استبدل الجزء الخاص بإرسال النموذج
            }).on("core.form.valid", function () {
                var fullNumber = window.iti.getNumber();
                var phoneFullInput = document.getElementById("contact_number");
                if (phoneFullInput) {
                    phoneFullInput.value = fullNumber;
                }

                // الانتقال إلى الخطوة التالية
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    // تحديث قائمة المرفقات المحذوفة قبل إرسال النموذج
                    if (deletedAttachments.size > 0) {
                        $("#deleted-attachments").val(
                            Array.from(deletedAttachments).join(","),
                        );
                    }

                    // تعطيل زر الإرسال لمنع الإرسال المتكرر
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = 'جاري الإرسال...';

                    // التحقق من وجود النموذج قبل الإرسال
                    if (wizardValidationForm) {
                        console.log('إرسال النموذج...'); // للتشخيص

                        // إرسال النموذج يدوياً
                        const formData = new FormData(wizardValidationForm);

                        // إرسال عبر fetch أو submit عادي
                        fetch(wizardValidationForm.action || window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        }).then(response => {
                            if (response.ok) {
                                console.log('تم الإرسال بنجاح');
                                // إعادة توجيه أو عرض رسالة نجاح
                                window.location.href = response.url || '/success';
                            } else {
                                console.error('خطأ في الإرسال');
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = 'إرسال';
                            }
                        }).catch(error => {
                            console.error('خطأ:', error);
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = 'إرسال';
                        });
                    }
                }
            });

            formValidations.push(fv);
        });

        // Next buttons
        btnNextList.forEach((btn, index) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                formValidations[index].validate();
            });
        });

        // Previous buttons
        btnPrevList.forEach((btn, index) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                validationStepper.previous();
            });
        });

        // إضافة مرفقات إضافية
        let attachmentIndex = $(
            "#additional-attachments-container .row",
        ).length;
        $("#add-attachment").on("click", function () {
            const container = $("#additional-attachments-container");
            const attachmentHtml = `
                <div class="row mb-3" data-index="${attachmentIndex}">
                    <div class="col-md-5">
                        <input type="text"
                            name="additional_attachments[${attachmentIndex}][name]"
                            class="form-control"
                            placeholder="اسم المرفق"
                            required />
                    </div>
                    <div class="col-md-5">
                        <input type="file"
                            name="additional_attachments[${attachmentIndex}][file]"
                            class="form-control"
                            required />
                    </div>
                    <div class="col-md-2">
                        <button type="button"
                            class="btn btn-danger remove-attachment"
                            data-index="${attachmentIndex}">حذف
                        </button>
                    </div>
                </div>
            `;
            container.append(attachmentHtml);
            attachmentIndex++;
        });

        // حذف مرفق إضافي
        $(document).on("click", ".remove-attachment", function () {
            const attachmentRow = $(this).closest(".row");
            const attachmentId = attachmentRow.data("attachment-id");

            if (attachmentId !== undefined) {
                deletedAttachments.add(attachmentId.toString());
                $("#deleted-attachments").val(
                    Array.from(deletedAttachments).join(","),
                );
            }

            attachmentRow.fadeOut(300, function () {
                $(this).remove();

                // إعادة ترتيب المؤشرات للمرفقات المتبقية
                $("#additional-attachments-container .row").each(
                    function (index) {
                        $(this)
                            .find('input[name^="additional_attachments["]')
                            .each(function () {
                                const oldName = $(this).attr("name");
                                const newName = oldName.replace(
                                    /\[\d+\]/,
                                    `[${index}]`,
                                );
                                $(this).attr("name", newName);
                            });
                    },
                );
            });
        });

        // Initialize Select2
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this
                    .select2({
                        placeholder: $this.data("placeholder") || "اختر خيارًا",
                        dropdownParent: $this.parent(),
                        language: "ar",
                        allowClear: true,
                    })
                    .on("change", function () {
                        const fieldName = $this.attr("name");
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(
                            fieldName,
                        );
                    });
            });
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

    function toggleInsurancePercentage() {
        const checkbox = document.getElementById('has_insurance');
        const wrapper = document.getElementById('insurance_percentage_wrapper');
        const input = document.getElementById('insurance_percentage');
        const enableBtn = document.getElementById('enable_insurance_percentage');
        const originalValue = input.getAttribute('data-original');

        if (checkbox.checked) {
            wrapper.style.display = 'block';
            if (!input.value && originalValue) {
                input.value = originalValue;
            }
        } else {
            wrapper.style.display = 'none';
            input.value = '';
            input.disabled = true;
            enableBtn.style.display = 'inline-block';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleInsurancePercentage();

        document.getElementById('has_insurance').addEventListener('change', toggleInsurancePercentage);

        document.getElementById('enable_insurance_percentage').addEventListener('click', function () {
            const input = document.getElementById('insurance_percentage');
            input.disabled = false;
            input.focus();
        });

        document.getElementById('insurance_percentage').addEventListener('input', function () {
            if (this.value > 100) {
                this.value = 100;
            }
        });
    });
})();
