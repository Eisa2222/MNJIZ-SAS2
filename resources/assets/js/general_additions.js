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