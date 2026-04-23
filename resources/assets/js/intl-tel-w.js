document.addEventListener("DOMContentLoaded", function () {
    // تهيئة مكتبة intl-tel-input
    var input = document.querySelector("#contact_number"); // تأكد من أن الـ input له المعرف الصحيح
    window.iti = window.intlTelInput(input, {
        initialCountry: "sa",
        onlyCountries: ["sa", "ae", "qa", "kw", "bh", "om"], // دول الخليج فقط
        utilsScript:
            "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        localizedCountries: {
            sa: "السعودية",
            ae: "الإمارات",
            qa: "قطر",
            kw: "الكويت",
            bh: "البحرين",
            om: "عُمان",
        },
        separateDialCode: true, // لعرض رمز الدولة داخل الحقل
    });

    // تعريف طول الأرقام المتوقع لكل دولة خليجية
    var countryMaxLengths = {
        sa: 9, // السعودية
        ae: 9, // الإمارات
        qa: 8, // قطر
        kw: 8, // الكويت
        bh: 8, // البحرين
        om: 8, // عُمان
    };

    function getSelectedCountryMaxLength() {
        var selectedCountry = window.iti.getSelectedCountryData().iso2;
        return countryMaxLengths[selectedCountry] || 15;
    }

    // التعامل مع الإدخال لمنع الأحرف غير الرقمية وتقييد الطول
    input.addEventListener("input", function () {
        // إزالة جميع الأحرف غير الرقمية
        var onlyDigits = input.value.replace(/\D/g, "");
        var maxLength = getSelectedCountryMaxLength();
        onlyDigits = onlyDigits.substring(0, maxLength);
        input.value = onlyDigits;

        // إعادة التحقق من صحة الحقل في FormValidation
        if (FormValidation2) { // تأكد من أن FormValidation2 معرف
            FormValidation2.revalidateField('contact_number');
        }
    });

    // عند تغيير الدولة، ضبط maxlength بناءً على رقم المثال وإعادة التحقق من الرقم
    input.addEventListener('countrychange', function () {
        var selectedCountry = iti.getSelectedCountryData().iso2;
        var maxLength = countryMaxLengths[selectedCountry] || 15;
        input.setAttribute('maxlength', maxLength);
        // validatePhone();
    });

    // ضبط maxlength للدولة الافتراضية عند تحميل الصفحة
    var event = new Event("countrychange");
    input.dispatchEvent(event);
});
