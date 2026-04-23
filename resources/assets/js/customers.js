$(document).ready(function () {
    // تهيئة Select2
    $(".select2").select2({
        placeholder: function () {
            return $(this).data("placeholder");
        },
        allowClear: true,
        width: "100%",
        language: "ar",
        dir: "rtl",
    });

    // وظيفة لمسح حقول نوع العميل الفردي
    function clearIndividualFields() {
        $("#title").val("");
        $("#nationality_id").val(null).trigger("change");
        $("#status_id").val(null).trigger("change");
        $("#civil_registry_number").val("");
        // إضافة أي حقول فردية أخرى تحتاج إلى مسحها هنا
    }

    // وظيفة لمسح حقول نوع العميل المؤسسة
    function clearCompanyFields() {
        $("#commercial_registration_number").val("");
        $("#unified_number").val("");
        $("#authorizations-wrapper").empty(); // مسح حقول المفوضين
        // إضافة أي حقول مؤسسية أخرى تحتاج إلى مسحها هنا
    }

    // إدارة إظهار وإخفاء الحقول بناءً على اختيار نوع العميل
    $('input[name="customer_type"]').on("change", function () {
        if ($(this).val() === "company") {
            $("#title-container").hide();
            $("#commercial-registration-container").show();
            $("#nationality-container").hide();
            $("#status-container").hide();
            $("#unified-number-container").show();
            $("#civil-registry-field").hide();
            $("#authorizations-container").show();

            // مسح الحقول الخاصة بالفرد
            clearIndividualFields();
        } else {
            $("#title-container").show();
            $("#commercial-registration-container").hide();
            $("#nationality-container").show();
            $("#status-container").show();
            $("#unified-number-container").hide();
            $("#civil-registry-field").show();
            $("#authorizations-container").hide();

            // مسح الحقول الخاصة بالمؤسسة
            clearCompanyFields();
        }

        // إعادة تهيئة Select2 بعد تغيير القيم
        $(".select2").trigger("change");

        // إعادة تهيئة التحقق من النموذج
        if (FormValidation1) {
            FormValidation1.revalidateField("status_id");
            FormValidation1.revalidateField("nationality_id");
            FormValidation1.revalidateField("commercial_registration_number");
            FormValidation1.revalidateField("unified_number");
        }

        if (FormValidation2) {
            FormValidation2.revalidateField("civil_registry_number");
        }
    });

    // التحقق من الاختيار المسبق عند تحميل الصفحة
    if ($('input[name="customer_type"]:checked').val() === "company") {
        $("#title-container").hide();
        $("#commercial-registration-container").show();
        $("#nationality-container").hide();
        $("#status-container").hide();
        $("#unified-number-container").show();
        $("#civil-registry-field").hide();
        $("#authorizations-container").show();

        // مسح الحقول الخاصة بالفرد
        clearIndividualFields();
    } else {
        $("#title-container").show();
        $("#commercial-registration-container").hide();
        $("#nationality-container").show();
        $("#status-container").show();
        $("#unified-number-container").hide();
        $("#civil-registry-field").show();
        $("#authorizations-container").hide();

        // مسح الحقول الخاصة بالمؤسسة
        clearCompanyFields();
    }

    // // إدارة إضافة وإزالة حقول المفوضين
    $("#add-authorization").on("click", function () {
        let index = $("#authorizations-wrapper .authorization-item").length;
        let authorizationHtml = `
            <div class="authorization-item mb-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="authorizations[${index}][name]" class="form-control" placeholder="اسم المفوض" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="authorizations[${index}][id_number]" class="form-control" placeholder="رقم الهوية" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="authorizations[${index}][phone]" class="form-control" placeholder="رقم الهاتف" >
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="authorizations[${index}][email]" class="form-control" placeholder="البريد الإلكتروني" >
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm mt-2 remove-authorization">إزالة</button>
            </div>
        `;
        $("#authorizations-wrapper").append(authorizationHtml);
    });

    // إزالة حقول المفوضين
    $("#authorizations-wrapper").on(
        "click",
        ".remove-authorization",
        function () {
            $(this).closest(".authorization-item").remove();
        },
    );

    $("#marketing_channel_id").on("change", function () {
        let selectedValue = $(this).val();
        // إخفاء جميع الحقول أولاً
        $(
            "#detailedMarketingChannelContainer, #clientsContainer, #socialMediaContainer",
        ).hide();
        $(
            "#detailed_marketing_channel_id, #parent_customer_id, #social_media_id",
        ).removeAttr("required");

        $("#detailed_marketing_channel_id").val(null).trigger("change");
        $("#parent_customer_id").val(null).trigger("change");
        $("#social_media_id").val(null).trigger("change");
        // إظهار الحقل المناسب بناءً على الاختيار
        if (selectedValue == "2") {
            // الموارد البشرية
            $("#detailedMarketingChannelContainer").show();
            $("#detailed_marketing_channel_id").attr("required", "required");
        } else if (selectedValue == "3") {
            // العملاء
            $("#clientsContainer").show();
            $("#parent_customer_id").attr("required", "required");
        } else if (selectedValue == "6") {
            // مواقع التواصل الاجتماعي
            $("#socialMediaContainer").show();
            $("#social_media_id").attr("required", "required");
        }
    });

    // إذا كانت هناك قيمة محفوظة مسبقًا
    let savedValue = $("#marketing_channel_id").val();
    if (savedValue == "2") {
        $("#detailedMarketingChannelContainer").show();
        $("#detailed_marketing_channel_id").attr("required", "required");
    } else if (savedValue == "3") {
        $("#clientsContainer").show();
        $("#parent_customer_id").attr("required", "required");
    } else if (savedValue == "6") {
        $("#socialMediaContainer").show();
        $("#social_media_id").attr("required", "required");
    }
});
