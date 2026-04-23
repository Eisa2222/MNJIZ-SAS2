/**
 * Form Wizard Validation for Opponents
 */

"use strict";

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        const select2 = $(".select2");

        // المعرّف الرئيسي للـ Wizard
        const wizardValidation = document.querySelector("#wizard-validation");
        if (wizardValidation) {
            const wizardValidationForm =
                wizardValidation.querySelector("#power-form");
            const steps = wizardValidationForm.querySelectorAll(".content");
            const btnNextList =
                wizardValidationForm.querySelectorAll(".btn-next");
            const btnPrevList =
                wizardValidationForm.querySelectorAll(".btn-prev");
            const btnSubmit = wizardValidationForm.querySelector(".btn-submit");

            // إنشاء الـ Stepper
            const validationStepper = new Stepper(wizardValidation, {
                linear: true,
                animation: true,
            });

            // مصفوفة ستحتوي على كائنات التحقق لكل خطوة
            const formValidations = [];

            // ---------------------------------------------------------------------
            // دالة مساعدة للتأكد من نوع الخصم
            // ---------------------------------------------------------------------
            function getOpponentType() {
                const checkedRadio = document.querySelector(
                    'input[name="type"]:checked',
                );
                return checkedRadio ? checkedRadio.value : null;
            }

            // ---------------------------------------------------------------------
            // تهيئة التحقق لكل خطوة
            // ---------------------------------------------------------------------
            steps.forEach((step, index) => {
                let validators = {};

                // ---------------------- الخطوة الأولى (معلومات الخصم) ----------------------
                if (index === 0) {
                    validators = {
                        // الاسم
                        name: {
                            validators: {
                                notEmpty: {
                                    message: "الاسم مطلوب.",
                                },
                                stringLength: {
                                    min: 3,
                                    max: 150,
                                    message:
                                        "يجب أن يكون الاسم بين 3 و 150 حرف.",
                                },
                            },
                        },
                        // رقم الاتصال (جوال)
                        contact_number: {
                            validators: {
                                // notEmpty: {
                                //     message: 'رقم الجوال مطلوب'
                                // },
                                callback: {
                                    message: "رقم الجوال غير صحيح",
                                    callback: function (input) {
                                        // إذا كان الحقل فارغًا، سيقوم notEmpty بإظهار الخطأ المناسب
                                        if (input.value.trim() === "") {
                                            return true;
                                        }

                                        // التحقق من صلاحية الرقم باستخدام IntlTelInput (حسب الكود الذي أضفته سابقًا)
                                        if (window.iti) {
                                            return window.iti.isValidNumber();
                                        }

                                        return false;
                                    },
                                },
                            },
                        },
                        // البريد الإلكتروني
                        // email: {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'البريد الإلكتروني مطلوب'
                        //         },
                        //         email: {
                        //             message: 'البريد الإلكتروني غير صالح'
                        //         }
                        //     }
                        // },
                        // المدينة
                        // settings_region_id: {
                        //     validators: {
                        //         callback: {
                        //             callback: function (input) {
                        //                 const type = getOpponentType();
                        //                 // إذا كان النوع "مؤسسة" نتحقق من الإدخال
                        //                 if (type === "individual") {
                        //                     return {
                        //                         valid: false,
                        //                         message: "المدينة مطلوبة.",
                        //                     };
                        //                 }
                        //                 return true;

                        //             },
                        //         },

                        //         // notEmpty: {
                        //         //     message: 'المدينة مطلوبة.'
                        //         // }
                        //     },
                        // },
                        // رقم الهوية (فقط في حالة كان الخصم فرد)
                        // identity_number: {
                        //     validators: {
                        //         callback: {
                        //             message:
                        //                 "رقم الهوية مطلوب للأفراد ويجب أن يكون بين 10 و 20 رقم.",
                        //             callback: function (input) {
                        //                 const type = getOpponentType();
                        //                 // إذا كان النوع "فرد" نتحقق من الإدخال
                        //                 if (type === "individual") {
                        //                     // تحقق من أن الرقم مكوّن من 10 إلى 20 خانة رقمية
                        //                     if (
                        //                         !/^\d{10,20}$/.test(input.value)
                        //                     ) {
                        //                         return {
                        //                             valid: false,
                        //                             message:
                        //                                 "رقم الهوية يجب أن يتكون من 10 إلى 20 رقم.",
                        //                         };
                        //                     }
                        //                     return true;
                        //                 }
                        //                 // إذا كان النوع مؤسسة فلا نحتاج لهذا الحقل
                        //                 return {
                        //                     valid: true,
                        //                 };
                        //             },
                        //         },
                        //     },
                        // },
                    };
                }

                // ---------------------- الخطوة الثانية (معلومات الاتصال الإضافية) ----------------------
                if (index === 1) {
                    validators = {
                        // رقم السجل التجاري (فقط في حالة كان الخصم مؤسسة)
                        // commercial_registration: {
                        //     validators: {
                        //         callback: {
                        //             message: "رقم السجل التجاري مطلوب للمؤسسات",
                        //             callback: function (input) {
                        //                 const type = getOpponentType();
                        //                 // إذا كان النوع "مؤسسة" نتحقق من الإدخال
                        //                 if (type === "company") {
                        //                     // تأكد من أن الطول بين 5 و 20
                        //                     if (
                        //                         input.value.length < 5 ||
                        //                         input.value.length > 20
                        //                     ) {
                        //                         return {
                        //                             valid: false,
                        //                             message:
                        //                                 "رقم السجل التجاري يجب أن يكون بين 5 و 20 رقم.",
                        //                         };
                        //                     }
                        //                     return true;
                        //                 }
                        //                 // إذا كان النوع فرد فلا نحتاج هذا الحقل
                        //                 return {
                        //                     valid: true,
                        //                 };
                        //             },
                        //         },
                        //     },
                        // },
                        // // الرقم الموحد (فقط في حالة كان الخصم مؤسسة)
                        // unified_number: {
                        //     validators: {
                        //         callback: {
                        //             message: "الرقم الموحد مطلوب للمؤسسات",
                        //             callback: function (input) {
                        //                 const type = getOpponentType();
                        //                 if (type === "company") {
                        //                     if (
                        //                         input.value.length < 5 ||
                        //                         input.value.length > 20
                        //                     ) {
                        //                         return {
                        //                             valid: false,
                        //                             message:
                        //                                 "الرقم الموحد يجب أن يكون بين 5 و 20 رقم.",
                        //                         };
                        //                     }
                        //                     return true;
                        //                 }
                        //                 return {
                        //                     valid: true,
                        //                 };
                        //             },
                        //         },
                        //     },
                        // },
                        // إن أردت إضافة حقول أخرى مشروطة (مثل المفوضين) فقم بنفس المنطق هنا
                    };
                }

                // ---------------------------------------------------------------------
                // إنشاء كائن التحقق للخطوة الحالية
                // ---------------------------------------------------------------------
                const fv = FormValidation.formValidation(step, {
                    fields: validators,
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".col-md-6, .col-md-12",
                            eleValidClass: "",
                        }),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                    },
                }).on("core.form.valid", function () {
                    // إذا كانت هناك خطوة تالية انتقل إليها
                    if (index < steps.length - 1) {
                        validationStepper.next();
                    } else {
                        // وإلا في آخر خطوة أرسل الفورم
                        btnSubmit.setAttribute('disabled', 'disabled');
                        btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                        btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                        wizardValidationForm.submit();
                    }
                });

                // إدخال كائن التحقق في المصفوفة للاستخدام لاحقًا
                formValidations.push(fv);
            });

            // ---------------------------------------------------------------------
            // أزرار "التالي"
            // ---------------------------------------------------------------------
            btnNextList.forEach((btn, index) => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    // تحقّق من حقول الخطوة الحالية
                    formValidations[index].validate();
                });
            });

            // ---------------------------------------------------------------------
            // أزرار "السابق"
            // ---------------------------------------------------------------------
            btnPrevList.forEach((btn, index) => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    validationStepper.previous();
                });
            });

            // ---------------------------------------------------------------------
            // تهيئة الـ Select2
            // ---------------------------------------------------------------------
            if (select2.length) {
                select2.each(function () {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this
                        .select2({
                            placeholder:
                                $this.data("placeholder") || "اختر خيارًا",
                            dropdownParent: $this.parent(),
                            language: "ar",
                        })
                        .on("change", function () {
                            const fieldName = $this.attr("name");
                            const currentIndex =
                                validationStepper._currentIndex;
                            formValidations[currentIndex].revalidateField(
                                fieldName,
                            );
                        });
                });
            }

            // ---------------------------------------------------------------------
            // زر الإرسال النهائي (في آخر خطوة)
            // ---------------------------------------------------------------------
            if (btnSubmit) {
                btnSubmit.addEventListener("click", function (e) {
                    e.preventDefault();
                    // تحقق من جميع الخطوات (كاملة)
                    formValidations.forEach((fv) => fv.validate());
                });
            }

            // ---------------------------------------------------------------------
            // إضافة وإزالة المفوضين (للشركات) - كود مثالي قابل للتعديل
            // ---------------------------------------------------------------------
            const authorizationsWrapper = document.querySelector(
                "#authorizations-wrapper",
            );
            const addAuthorizationBtn =
                document.querySelector("#add-authorization");

            if (addAuthorizationBtn && authorizationsWrapper) {
                addAuthorizationBtn.addEventListener("click", function () {
                    const index = authorizationsWrapper.children.length;
                    const authorizationItem = document.createElement("div");
                    authorizationItem.classList.add(
                        "authorization-item",
                        "mb-3",
                    );
                    authorizationItem.innerHTML = `
            <div class="row g-3">
              <div class="col-sm-3">
                <input type="text" name="authorizations[${index}][name]" class="form-control" placeholder="اسم المفوض" required>
              </div>
              <div class="col-sm-3">
                <input type="text" name="authorizations[${index}][identity_number]" class="form-control" placeholder="رقم الهوية" required>
              </div>
              <div class="col-sm-3">
                <input type="tel" name="authorizations[${index}][phone]" class="form-control" placeholder="رقم الجوال" required>
              </div>
              <div class="col-sm-3">
                <input type="email" name="authorizations[${index}][email]" class="form-control" placeholder="البريد الإلكتروني" required>
              </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-authorization">إزالة</button>
          `;
                    authorizationsWrapper.appendChild(authorizationItem);
                });
            }

            // إزالة المفوض
            if (authorizationsWrapper) {
                authorizationsWrapper.addEventListener("click", function (e) {
                    if (
                        e.target &&
                        e.target.classList.contains("remove-authorization")
                    ) {
                        e.target.closest(".authorization-item").remove();
                        // يمكنك هنا إعادة تهيئة التحقق أو أي منطق إضافي
                    }
                });
            }
        }
    });
})();
