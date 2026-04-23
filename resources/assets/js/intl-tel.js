
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector("#phone");
    var message = document.getElementById('message');

    // تعريف طول الأرقام المتوقع لكل دولة خليجية
    var countryMaxLengths = {
        'sa': 9, // السعودية
        'ae': 9, // الإمارات
        'qa': 8, // قطر
        'kw': 8, // الكويت
        'bh': 8, // البحرين
        'om': 8 // عُمان
    };

    var iti = window.intlTelInput(input, {
        initialCountry: "sa",
        onlyCountries: ["sa", "ae", "qa", "kw", "bh", "om"], // دول الخليج فقط
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        localizedCountries: {
            'sa': 'السعودية',
            'ae': 'الإمارات',
            'qa': 'قطر',
            'kw': 'الكويت',
            'bh': 'البحرين',
            'om': 'عُمان'
        },
        separateDialCode: true, // لعرض رمز الدولة داخل الحقل
    });

    function validatePhone() {
        var value = input.value.trim();
        var selectedCountry = iti.getSelectedCountryData().iso2;
        var expectedLength = countryMaxLengths[selectedCountry] || 15;

        if (value === "") {
            input.classList.remove('is-invalid', 'is-valid');
            message.textContent = "";
            message.classList.remove('invalid-message', 'valid-message');
            return;
        }

        // التحقق من صحة الرقم باستخدام مكتبة intl-tel-input
        if (iti.isValidNumber()) {
            // الرقم صحيح
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            message.textContent = "رقم الجوال صحيح";
            message.classList.remove('invalid-message');
            message.classList.add('valid-message');
        } else {
            // الرقم غير صحيح
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            message.textContent = "رقم الجوال غير صحيح";
            message.classList.remove('valid-message');
            message.classList.add('invalid-message');
        }
    }

    // التعامل مع الإدخال لمنع الأحرف غير الرقمية وتقييد الطول
    input.addEventListener('input', function () {
        // إزالة جميع الأحرف غير الرقمية
        var onlyDigits = input.value.replace(/\D/g, '');
        var selectedCountry = iti.getSelectedCountryData().iso2;
        var maxLength = countryMaxLengths[selectedCountry] || 15;
        onlyDigits = onlyDigits.substring(0, maxLength);
        input.value = onlyDigits;

        // تحقق من صحة الرقم بعد تنسيق الإدخال
        validatePhone();
    });

    // عند تغيير الدولة، ضبط maxlength بناءً على رقم المثال وإعادة التحقق من الرقم
    input.addEventListener('countrychange', function () {
        var selectedCountry = iti.getSelectedCountryData().iso2;
        var maxLength = countryMaxLengths[selectedCountry] || 15;
        input.setAttribute('maxlength', maxLength);
        validatePhone();
    });

    // عند إرسال النموذج
    var form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!iti.isValidNumber()) {
                e.preventDefault();
                input.classList.add('is-invalid');
                message.textContent = "رقم الجوال غير صحيح";
                message.classList.remove('valid-message');
                message.classList.add('invalid-message');
            } else {
                var fullNumber = iti.getNumber();
                var hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "phone";
                hiddenInput.value = fullNumber;
                form.appendChild(hiddenInput);
            }
        });
    }

    // ضبط maxlength للدولة الافتراضية عند تحميل الصفحة
    var event = new Event('countrychange');
    input.dispatchEvent(event);
});
