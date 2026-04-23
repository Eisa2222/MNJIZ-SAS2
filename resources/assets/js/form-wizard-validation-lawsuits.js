
"use strict";

(function () {
    const select2 = $(".select2");

    // Wizard Validation
    const wizardValidation = document.querySelector("#wizard-validation");
    if (wizardValidation) {
        const wizardValidationForm =
            wizardValidation.querySelector("#power-form");
        const steps = wizardValidationForm.querySelectorAll(".content");
        const btnNextList = wizardValidationForm.querySelectorAll(".btn-next");
        const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");
        const btnSubmit = wizardValidationForm.querySelector(".btn-submit");

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
            animation: true,
        });

        // Form Validation Instances
        const formValidations = [];

        // Initialize validation for each step
        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) {
                // Step 1: Basic Info
                validators = {
                    name: {
                        validators: {
                            notEmpty: {
                                message: "اسم الدعوى مطلوب.",
                            },
                            stringLength: {
                                min: 3,
                                max: 150,
                                message:
                                    "يجب أن يكون اسم الدعوى بين 3 و 150 حرف",
                            },
                        },
                    },
                    lawsuit_number: {
                        validators: {
                            notEmpty: {
                                message: "رقم الدعوى مطلوب ",
                            },
                            stringLength: {
                                min: 3,
                                max: 30,
                                message:
                                    "يجب أن يكون رقم الدعوى  بين 3 و 30 رقم",
                            },
                        },
                    },
                    "plaintiff_id[]": {
                        validators: {
                            notEmpty: {
                                message: "المدعي مطلوب ",
                            },
                        },
                    },
                    main_courts_id: {
                        validators: {
                            notEmpty: {
                                message: " المحكمة مطلوبة ",
                            },
                        },
                    },
                    "defendant_id[]": {
                        validators: {
                            notEmpty: {
                                message: "المدعى عليه مطلوب ",
                            },
                        },
                    },

                    department_contract_cases_id: {
                        validators: {
                            notEmpty: {
                                message: "التصنيف مطلوب",
                            },
                        },
                    },
                    project_id: {
                        validators: {
                            notEmpty: {
                                message: "المشروع مطلوب ",
                            },
                        },
                    },
                    "assigned_to[]": {
                        validators: {
                            notEmpty: {
                                message: "يجب اختيار عضو واحد على الاقل",
                            },
                        },
                    },

                    settings_main_courts: {
                        validators: {
                            notEmpty: {
                                message: "المحكمة الام مطلوبة",
                            },
                        },
                    },
                    // entity_ranks_id: {
                    //     validators: {
                    //         notEmpty: {
                    //             message: ' درجة الجهة مطلوبة'
                    //         },

                    //     }
                    // },
                    regions_id: {
                        validators: {
                            notEmpty: {
                                message: "  المدينة مطلوبة",
                            },
                        },
                    },
                    category_id: {
                        validators: {
                            notEmpty: {
                                message: "التصنيف الرئيسي مطلوب ",
                            },
                        },
                    },
                    subcategory_id: {
                        validators: {
                            notEmpty: {
                                message: "التصنيف الفرعي مطلوب ",
                            },
                        },
                    },
                    lawsuit_type_id: {
                        validators: {
                            notEmpty: {
                                message: "نوع الدعوى مطلوب ",
                            },
                        },
                    },
                };
            }

            if (index === 1) {
                // Step 2: Additional Info
                validators = {
                    circle: {
                        validators: {
                            notEmpty: {
                                message: "الدائرة مطلوبة",
                            },
                        },
                    },
                };
            }

            const fv = FormValidation.formValidation(step, {
                fields: validators,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-6, .col-md-12, .col-md-4",
                        eleValidClass: "",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            }).on("core.form.valid", function () {
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    // إرسال النموذج إذا كان في آخر خطوة
                    wizardValidationForm.submit();
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

        // Submit button handler (optional if you want to handle via AJAX)
        btnSubmit.addEventListener("click", function (e) {
            e.preventDefault();
            // قم بتفعيل التحقق من النموذج بأكمله أو خطوة معينة إذا لزم الأمر
            formValidations[1].validate(); // Assuming step 2 is the last step
        });
    }

    // دالة لإظهار عدد الأعضاء
    function showTeamCount(count, status = 'success') {
        const counter = $('#team-count');

        if (count > 0) {
            counter.text(`${count} عضو`)
                .removeClass('bg-label-warning bg-label-danger')
                .addClass('bg-label-success')
                .show();
        } else if (count === 0) {
            counter.text('0 عضو')
                .removeClass('bg-label-success bg-label-danger')
                .addClass('bg-label-warning')
                .show();
        } else {
            counter.text('خطأ')
                .removeClass('bg-label-success bg-label-warning')
                .addClass('bg-label-danger')
                .show();
        }
    }

    // دالة لإخفاء العداد
    function hideTeamCount() {
        $('#team-count').hide();
    }

    function fetchTeamMembers(projectId, preselected = []) {
        const assignedSelect = $("#assigned_to");

        if (projectId) {
            // تعطيل الحقل وإظهار "جاري التحميل"
            assignedSelect.prop('disabled', true);
            assignedSelect.empty();
            assignedSelect.append('<option value="">جاري التحميل...</option>');
            hideTeamCount(); // إخفاء العداد أثناء التحميل

            $.ajax({
                url: "/employees/projects/" + projectId + "/team-members",
                type: "GET",
                success: function (response) {
                    // تنظيف الحقل
                    assignedSelect.empty();

                    if (response.success && response.data && response.data.length > 0) {
                        // تعبئة الخيارات الجديدة
                        $.each(response.data, function (index, member) {
                            assignedSelect.append(
                                '<option value="' + member.id + '">' +
                                (member.employee ? member.employee.name : '—') +
                                "</option>"
                            );
                        });

                        // تفعيل الحقل بعد جلب البيانات
                        assignedSelect.prop('disabled', false);

                        // إظهار عدد الأعضاء
                        showTeamCount(response.data.length, 'success');

                        // إذا كانت هناك قيم محددة مسبقًا
                        if (preselected.length > 0) {
                            assignedSelect.val(preselected);
                        }

                        console.log("✅ تم تحميل " + response.data.length + " عضو");
                    } else {
                        // لا توجد بيانات
                        assignedSelect.append('<option value="">لا يوجد فريق متاح</option>');
                        assignedSelect.prop('disabled', true);
                        showTeamCount(0, 'warning');
                        console.log("⚠️ لا يوجد فريق للمشروع");
                    }

                    // تحديث Select2
                    assignedSelect.trigger("change");
                },
                error: function () {
                    // في حالة الخطأ
                    assignedSelect.empty();
                    assignedSelect.append('<option value="">خطأ في التحميل</option>');
                    assignedSelect.prop('disabled', true);
                    showTeamCount(-1, 'error');
                    console.log("❌ خطأ في تحميل الفريق");
                }
            });
        } else {
            // لم يتم اختيار مشروع
            assignedSelect.empty();
            assignedSelect.append('<option value="">اختر المشروع أولاً</option>');
            assignedSelect.prop('disabled', true);
            hideTeamCount();
            console.log("📋 لم يتم اختيار مشروع");
        }
    }

    // عند تغيير المشروع
    $(document).on("change", "#project_id", function () {
        var projectId = $(this).val();
        console.log("🔄 تم اختيار المشروع: " + projectId);
        fetchTeamMembers(projectId);
    });

    // عند تحميل الصفحة، إذا كانت هناك قيمة للمشروع (في صفحة التعديل)
    $(document).ready(function () {
        // تعطيل الحقل في البداية
        $("#assigned_to").prop('disabled', true);
        $("#assigned_to").empty();
        $("#assigned_to").append('<option value="">اختر المشروع أولاً</option>');
        hideTeamCount(); // إخفاء العداد في البداية

        // إذا كان هناك مشروع محدد مسبقاً (للتعديل)
        var projectId = $("#project_id").val();
        var preselected = window.assignedTeamMembers || [];

        if (projectId) {
            console.log("🔄 مشروع محدد مسبقاً: " + projectId);
            fetchTeamMembers(projectId, preselected);
        } else {
            console.log("📋 لا يوجد مشروع محدد");
        }
    });
})();

document
    .getElementById("lawsuit_number")
    .addEventListener("input", function () {
        let value = parseFloat(this.value);
        if (isNaN(value) || value < 0) {
            this.value = "";
        }
    });
