<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-clipboard-check ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> القرارات
            </h5>
        </div>
        <div class="card-body pt-3">
            <form id="decisionsFormId" method="POST"
                action="{{ route('legal-affairs.lawsuits.lawsuit-section.decisions', $lawsuit->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-12">
                        <label for="decision" class="form-label text-primary fw-bold fs-6">النص</label>
                        <textarea id="decision" name="decision" class="form-control @error('decision') is-invalid @enderror" rows="4"
                            placeholder="أدخل القرارات">{{ old('decision', $lawsuit->decision) }}</textarea>
                        @error('decision')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="decision_attachment" class="form-label text-primary fw-bold fs-6">المرفق</label>
                        <input type="file" id="decision_attachment" name="decision_attachment"
                            class="form-control @error('decision_attachment') is-invalid @enderror">
                        @error('decision_attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror


                        @if ($lawsuit->decision_attachment)
                            <div class="mt-2 d-flex align-items-center">
                                <!-- زر عرض المرفق -->
                                <a href="{{ asset('storage/' . $lawsuit->decision_attachment) }}"
                                    class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                    <i class="ti ti-eye me-1"></i> عرض
                                </a>

                                <!-- زر تنزيل المرفق -->
                                <a href="{{ asset('storage/' . $lawsuit->decision_attachment) }}" download
                                    class="btn btn-sm btn-outline-primary me-2">
                                    <i class="ti ti-download me-1"></i> تنزيل
                                </a>

                                <!-- زر حذف المرفق -->
                                <a class="btn btn-outline-danger btn-sm delete-attachment-decision text-danger"
                                    data-id="{{ $lawsuit->id }}">
                                    <i class="fas fa-trash-alt me-1"></i> حذف
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-save me-2"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).on('click', '.delete-attachment-decision', function() {
        var lawsuitId = $(this).data('id');

        // تأكيد الحذف باستخدام SweetAlert
        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
            text: " لا يمكن التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true, // يعرض زر الإلغاء
            showConfirmButton: true, // يعرض زر التأكيد
            showDenyButton: false, // لا يعرض زر الرفض
            buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
            customClass: {
                popup: 'custom-popup', // تخصيص شكل النافذة
                title: 'custom-title', // تخصيص شكل العنوان
                text: 'custom-text', // تخصيص شكل النص
                confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
            },
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false, // لعكس ترتيب الأزرار إذا رغبت
        }).then((result) => {
            if (result.isConfirmed) {
                // طلب AJAX لحذف المرفق
                $.ajax({
                    url: "{{ route('legal-affairs.lawsuits.attachments-delete', ['id' => $lawsuit->id]) }}",
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: 'decision'
                    },
                    success: function(response) {
                        // إزالة الأزرار بعد الحذف
                        $('#decision').val('');
                        $('.delete-attachment-decision').closest('div').remove();

                        // إظهار رسالة توستر للنجاح
                        toastr.success(response.message);
                    },
                    error: function(xhr) {
                        // إظهار رسالة خطأ
                        toastr.error('حدث خطأ أثناء حذف المرفق.');
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        $('#decisionsFormId').on('submit', function(e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة

            // إنشاء كائن FormData من النموذج
            let formData = new FormData(this);

            // إرسال الطلب عبر AJAX
            $.ajax({
                url: $(this).attr('action'), // الرابط المحدد في النموذج
                type: 'POST', // يجب أن يكون POST حتى تدعم الملفات، يمكن استخدام PUT مع بعض التعديلات
                data: formData, // البيانات المرسلة
                processData: false, // لمنع معالجة البيانات
                contentType: false, // لمنع تعيين نوع المحتوى
                success: function(response) {
                    if (response.success) {
                        if (response.message === "لم يتم إجراء أي تغييرات.") {
                            // عرض رسالة توضيحية عند عدم وجود تغييرات
                            toastr.info(response.message);
                        } else {
                            // عرض رسالة نجاح عند وجود تغييرات
                            toastr.success(response.message);

                            // تحديث الروابط الخاصة بالمرفق الجديد إذا كان موجودًا
                            if (response.decision_attachment) {
                                // بناء المسار الكامل للمرفق الجديد
                                var newAttachmentPath = "{{ asset('storage') }}/" + response
                                    .decision_attachment;

                                // إنشاء أو تحديث مجموعة الأزرار الخاصة بالمرفق
                                var attachmentButtons = `
                                            <div class="mt-2 d-flex align-items-center">
                                                <!-- زر عرض المرفق -->
                                                <a href="${newAttachmentPath}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                                    <i class="ti ti-eye me-1"></i> عرض
                                                </a>

                                                <!-- زر تنزيل المرفق -->
                                                <a href="${newAttachmentPath}" download class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="ti ti-download me-1"></i> تنزيل
                                                </a>

                                                <!-- زر حذف المرفق -->
                                                <a class="btn btn-outline-danger btn-sm delete-attachment-decision text-danger" data-id="{{ $lawsuit->id }}">
                                                    <i class="fas fa-trash-alt me-1"></i> حذف
                                                </a>
                                            </div>
                                        `;

                                // إزالة مجموعة الأزرار القديمة إن وجدت وإضافة الجديدة
                                $('.delete-attachment-decision').closest('div').remove();
                                $('#decision_attachment').closest('div').append(
                                    attachmentButtons);
                            } else {
                                // إذا لم يكن هناك مرفق، إزالة مجموعة الأزرار القديمة
                                $('.delete-attachment-decision').closest('div').remove();
                            }

                            // **تفريغ حقل الملف بعد الحفظ بنجاح**
                            $('#decision_attachment').val('');
                        }
                    }
                },

                error: function(xhr) {
                    // إظهار رسائل الخطأ من الخادم إذا كانت موجودة
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('حدث خطأ أثناء معالجة الطلب الرجاء المحاولة مرة أخرى');
                    }
                }
            });
        });
    });
</script>
