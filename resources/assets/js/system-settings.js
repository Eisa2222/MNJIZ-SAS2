function updatePreview(imgId, fileInputId, defaultSrc) {
    const fileInput = document.getElementById(fileInputId);
    const imgElement = document.getElementById(imgId);

    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            imgElement.src = e.target.result;
        };
        reader.readAsDataURL(fileInput.files[0]);
    } else {
        imgElement.src = defaultSrc;
    }
}

document
    .getElementById("uploadHorizontalHeader")
    .addEventListener("change", function () {
        updatePreview(
            "uploadedHorizontalHeader",
            "uploadHorizontalHeader",
            "{{ $settings->horizontal_header_image ? asset('storage/' . $settings->horizontal_header_image) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
        updatePreview(
            "previewHorizontalHeader",
            "uploadHorizontalHeader",
            "{{ $settings->horizontal_header_image ? asset('storage/' . $settings->horizontal_header_image) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
    });

document
    .getElementById("uploadHorizontalFooter")
    .addEventListener("change", function () {
        updatePreview(
            "uploadedHorizontalFooter",
            "uploadHorizontalFooter",
            "{{ $settings->horizontal_footer_image ? asset('storage/' . $settings->horizontal_footer_image) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
        updatePreview(
            "previewHorizontalFooter",
            "uploadHorizontalFooter",
            "{{ $settings->horizontal_footer_image ? asset('storage/' . $settings->horizontal_footer_image) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
    });

document
    .getElementById("uploadSignature")
    .addEventListener("change", function () {
        updatePreview(
            "uploadedSignature",
            "uploadSignature",
            "{{ $settings->signature ? asset('storage/' . $settings->signature) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
        updatePreview(
            "previewSignature",
            "uploadSignature",
            "{{ $settings->signature ? asset('storage/' . $settings->signature) : asset('assets/img/avatars/image-blank.jpg') }}",
        );
    });

document.addEventListener("DOMContentLoaded", function () {
    $(".select2").select2({
        placeholder: function () {
            return $(this).data("placeholder");
        },
        allowClear: true,
        width: "100%",
        language: "ar",
        dir: "rtl",
    });

    // تعريف دالة لتحميل الصورة
    function readURL(input, imgElementId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById(imgElementId).src = e.target.result;
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    // قائمة الحقول والعناصر
    var fields = [
        {
            inputId: "uploadHorizontalHeader",
            imgId: "uploadedHorizontalHeader",
        },
        {
            inputId: "uploadVerticalHeader",
            imgId: "uploadedVerticalHeader",
        },
        {
            inputId: "uploadHorizontalFooter",
            imgId: "uploadedHorizontalFooter",
        },
        {
            inputId: "uploadVerticalFooter",
            imgId: "uploadedVerticalFooter",
        },
    ];

    // إضافة مستمعات الحدث لكل حقل ملف
    fields.forEach(function (field) {
        var inputElement = document.getElementById(field.inputId);
        if (inputElement) {
            inputElement.addEventListener("change", function () {
                readURL(this, field.imgId);
            });
        }
    });

    // لتوليد الحقول الخاصة بالايميل

    const container = document.getElementById("support-emails-container");
    const addEmailButton = document.getElementById("add-email-field");

    // عند النقر على زر "أضف بريد آخر"
    addEmailButton.addEventListener("click", function (e) {
        e.preventDefault();
        const newRow = document.createElement("div");
        newRow.classList.add("input-group", "mb-2", "support-email-row");
        newRow.innerHTML = `
            <input type="email" name="support_emails[]" class="form-control" placeholder="أدخل البريد الإلكتروني" required>
            <button class="btn btn-sm btn-outline-danger remove-email-btn" type="button">
                <i class="fa fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    });

    // التعامل مع زر الحذف باستخدام التفويض الحدث
    container.addEventListener("click", function (e) {
        if (e.target.closest(".remove-email-btn")) {
            e.preventDefault();
            const row = e.target.closest(".support-email-row");
            if (row) {
                row.remove();
            }
        }
    });
});

document.getElementById("upload").addEventListener("change", function () {
    const file = this.files[0];
    const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    const maxSize = 800 * 1024; // 800 KB
    const img = new Image();

    // تحقق من نوع الملف
    if (!allowedTypes.includes(file.type)) {
        Swal.fire({
            text: "الملفات المسموح بها: JPG, GIF, أو PNG.",
            icon: "warning",
            showCancelButton: false,
            showConfirmButton: false,
            showDenyButton: false,
            buttonsStyling: false,
        });
        this.value = "";
        return;
    }

    // تحقق من حجم الملف
    if (file.size > maxSize) {
        Swal.fire({
            text: "حجم الملف يجب ألا يتجاوز 800KB.",
            icon: "warning",
            showCancelButton: false,
            showConfirmButton: false,
            showDenyButton: false,
            buttonsStyling: false,
        });
        this.value = "";
        return;
    }

    // تحقق من أبعاد الصورة
    const reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target.result;
    };

    img.onload = function () {
        if (img.width !== 150 || img.height !== 150) {
            Swal.fire({
                text: "يجب أن تكون أبعاد الصورة 150 × 150 بكسل.",
                icon: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                showDenyButton: false,
                buttonsStyling: false,
            });
            document.getElementById("upload").value = "";
        } else {
            // عرض الصورة إذا كانت الأبعاد صحيحة
            document.getElementById("uploadedAvatar").src = img.src;
        }
    };

    reader.readAsDataURL(file);
});

document
    .getElementById("archive_delete_duration")
    .addEventListener("input", function () {
        let value = parseFloat(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        }
    });
