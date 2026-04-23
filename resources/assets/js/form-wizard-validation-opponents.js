$(document).ready(function () {
    // تهيئة Select2
    $(".select2").select2({
        width: "100%",
        placeholder: "نوع الخصم",
    });

    // الحصول على عنصر اختيار النوع
    const typeSelect = $("#type");

    // الحصول على الحقول الشرطية
    const companyFields = $(".company-fields");
    const individualFields = $(".individual-fields");

    // دالة للتحكم في إظهار وإخفاء الحقول
    function toggleFields() {
        const selectedType = typeSelect.val();

        if (selectedType === "company") {
            // إظهار حقول المؤسسة
            companyFields.show();
            companyFields.find("input").attr("required", true);

            // إخفاء حقل الفرد وتفريغ قيمته
            individualFields.hide();
            individualFields.find("input").attr("required", false).val("");
        } else if (selectedType === "individual") {
            // إخفاء حقول المؤسسة وتفريغ قيمها
            companyFields.hide();
            companyFields.find("input").attr("required", false).val("");

            // إظهار حقل الفرد
            individualFields.show();
            individualFields.find("input").attr("required", true);
        } else {
            // إخفاء جميع الحقول الشرطية وتفريغ قيمها
            companyFields.hide();
            companyFields.find("input").attr("required", false).val("");

            individualFields.hide();
            individualFields.find("input").attr("required", false).val("");
        }
    }

    // تشغيل الدالة عند تحميل الصفحة لضبط الحالة الأولية
    toggleFields();

    // إضافة مستمع لحدث التغيير على حقل النوع
    typeSelect.on("change", toggleFields);
});
