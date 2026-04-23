"use strict";

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

                        birth_date: {
                            validators: {
                                notEmpty: { message: "تاريخ الميلاد مطلوب" },
                            },
                        },

                        qualification_degree: {
                            validators: {
                                notEmpty: { message: "درجة المؤهل مطلوبة" },
                            },
                        },

                        personal_email: {
                            validators: {
                                notEmpty: {
                                    message: "البريد الإلكتروني الشخصي  مطلوب.",
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
                                notEmpty: {
                                    message: "العنوان  مطلوب",
                                },
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
                    }),
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-4, .col-md-12",
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
                                // إعادة توجيه أو عرض رسالة نجاح
                                window.location.href = response.url || '/success';
                            } else {
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = 'إرسال';
                            }
                        }).catch(error => {
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
})();
