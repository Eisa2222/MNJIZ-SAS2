document.addEventListener('DOMContentLoaded', function () {
    const typeInputs = document.querySelectorAll('input[name="type"]');

    // حقول المؤسسة
    const commercialRegistrationContainer = document.getElementById('commercial-registration-container');
    const unifiedNumberContainer = document.getElementById('unified-number-container');
    const authorizationsContainer = document.getElementById('authorizations-container');
    const commercialRegistrationInput = document.getElementById('commercial_registration');
    const unifiedNumberInput = document.getElementById('unified_number');
    const authorizationsWrapper = document.getElementById('authorizations-wrapper');

    // حقول الأفراد
    const identityNumberInput = document.getElementById('identity_number');

    function toggleFields() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;

        if (selectedType === 'individual') {
            // إخفاء الحقول الخاصة بالمؤسسات
            commercialRegistrationContainer.style.display = 'none';
            unifiedNumberContainer.style.display = 'none';
            authorizationsContainer.style.display = 'none';

            // إظهار حقل رقم الهوية
            identityNumberInput.parentElement.style.display = 'block';

            // تفريغ الحقول الخاصة بالمؤسسات عند إخفائها
            commercialRegistrationInput.value = '';
            unifiedNumberInput.value = '';

            // تفريغ جميع حقول المفوضين
            if (authorizationsWrapper) {
                authorizationsWrapper.innerHTML = '';
            }
        } else if (selectedType === 'company') {
            // عرض الحقول الخاصة بالمؤسسات
            commercialRegistrationContainer.style.display = 'block';
            unifiedNumberContainer.style.display = 'block';
            authorizationsContainer.style.display = 'block';

            // إخفاء حقل رقم الهوية
            identityNumberInput.parentElement.style.display = 'none';

            // تفريغ حقل رقم الهوية عند إخفائه
            identityNumberInput.value = '';
        }
    }

    // استدعاء الوظيفة عند تغيير اختيار النوع
    typeInputs.forEach(input => {
        input.addEventListener('change', toggleFields);
    });

    toggleFields();
});
