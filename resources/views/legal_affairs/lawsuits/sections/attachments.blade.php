<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-files ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> المرفقات
            </h5>
        </div>
        <div class="card-body pt-3">
            <div class="row g-4">
                <!-- إذا لم يكن هناك أي مرفقات -->
                @if (
                    !$lawsuit->defense_memo_attachment &&
                        !$lawsuit->judgment_attachment &&
                        !$lawsuit->request_attachment &&
                        !$lawsuit->decision_attachment)
                    <div class="col-12 text-center">
                        <i class="ti ti-file-off ti-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مرفقات لعرضها حالياً.</p>
                    </div>
                @else
                    <!-- عرض مرفقات مذكرة الدفاع الأولى -->
                    @if ($lawsuit->defense_memo_attachment)
                        <div class="col-md-6">
                            <h6 class="mb-1">مرفق مذكرة الدفاع الأولى</h6>
                            <div class="d-flex align-items-center">
                                <a href="{{ asset('storage/' . $lawsuit->defense_memo_attachment) }}"
                                    class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                    <i class="ti ti-eye me-1"></i> عرض
                                </a>
                                <a href="{{ asset('storage/' . $lawsuit->defense_memo_attachment) }}" download
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download me-1"></i> تنزيل
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- عرض مرفقات الأحكام -->
                    @if ($lawsuit->judgment_attachment)
                        <div class="col-md-6">
                            <h6 class="mb-1">مرفق الأحكام</h6>
                            <div class="d-flex align-items-center">
                                <a href="{{ asset('storage/' . $lawsuit->judgment_attachment) }}"
                                    class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                    <i class="ti ti-eye me-1"></i> عرض
                                </a>
                                <a href="{{ asset('storage/' . $lawsuit->judgment_attachment) }}" download
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download me-1"></i> تنزيل
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- عرض مرفقات الطلبات -->
                    @if ($lawsuit->request_attachment)
                        <div class="col-md-6">
                            <h6 class="mb-1">مرفق الطلبات</h6>
                            <div class="d-flex align-items-center">
                                <a href="{{ asset('storage/' . $lawsuit->request_attachment) }}"
                                    class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                    <i class="ti ti-eye me-1"></i> عرض
                                </a>
                                <a href="{{ asset('storage/' . $lawsuit->request_attachment) }}" download
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download me-1"></i> تنزيل
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- عرض مرفقات القرارات -->
                    @if ($lawsuit->decision_attachment)
                        <div class="col-md-6">
                            <h6 class="mb-1">مرفق القرارات</h6>
                            <div class="d-flex align-items-center">
                                <a href="{{ asset('storage/' . $lawsuit->decision_attachment) }}"
                                    class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                    <i class="ti ti-eye me-1"></i> عرض
                                </a>
                                <a href="{{ asset('storage/' . $lawsuit->decision_attachment) }}" download
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download me-1"></i> تنزيل
                                </a>
                            </div>
                        </div>
                    @endif
                @endif

                <hr>

                <div id="" class="row mt-5">
                    <p class="text-primary fw-bold fs-6">المرفقات الاضافية</p>
                    <div class="newAttachments"></div>
                    <!-- عرض المرفقات  الاضافية -->
                    @if ($lawsuit->attachments->count() > 0)
                        @foreach ($lawsuit->attachments->sortByDesc('id') as $attachment)
                            <div id="attachment-{{ $attachment->id }}" class="col-md-6 mb-2 ">
                                <h6 class="mb-1"> {{ $attachment->attachment_name }}</h6>
                                <div class="d-flex align-items-center">
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                        class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                        <i class="ti ti-eye me-1"></i> عرض
                                    </a>
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" download
                                        class="btn btn-sm btn-outline-primary me-2">
                                        <i class="ti ti-download me-1"></i> تنزيل
                                    </a>
                                    <a class="btn btn-outline-danger btn-sm delete-new-attachment text-danger"
                                        data-id="{{ $attachment->id }}">
                                        <i class="fas fa-trash-alt me-1"></i>
                                        حذف</a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center empty">
                            <i class="ti ti-file-off ti-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد مرفقات اضافية لعرضها حالياً.</p>
                        </div>
                    @endif

                </div>

                <!-- سكربت جافا سكريبت للحذف عبر الاجاكس -->
                <!-- إضافة مكتبة SweetAlert و toastr -->

                <script>
                    $(document).on('click', '.delete-new-attachment', function() {
                        var attachmentId = $(this).data('id');

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
                                    url: "{{ route('legal-affairs.lawsuits.attachments-destroy', ['id' => ':id']) }}"
                                        .replace(':id', attachmentId),
                                    type: 'DELETE',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        // إزالة العنصر من القائمة
                                        $('#attachment-' + attachmentId).remove();

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
                        $('#addAttachmentForm').on('submit', function(e) {
                            e.preventDefault(); // منع إعادة تحميل الصفحة

                            // إنشاء كائن FormData لجمع البيانات
                            let formData = new FormData(this);

                            // إرسال الطلب عبر AJAX
                            $.ajax({
                                url: $(this).attr('action'), // الرابط المحدد في النموذج
                                type: 'POST', // نوع الطلب
                                data: formData, // البيانات المرسلة
                                processData: false, // منع معالجة البيانات
                                contentType: false, // منع تعيين نوع المحتوى
                                success: function(response) {
                                    if (response.success) {
                                        // عرض رسالة نجاح
                                        toastr.success(response.message);

                                        // إضافة المرفق الجديد إلى القائمة
                                        $('#addAttachmentForm')[0].reset(); // إعادة تعيين النموذج
                                        let newAttachment = `
                                                <div id="attachment-${response.attachment.id}" class="col-md-6 mb-2">
                                                    <h6 class="mb-1">${response.attachment.attachment_name}</h6>
                                                    <div class="d-flex align-items-center">
                                                        <a href="${response.attachment.file_url}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                                            <i class="ti ti-eye me-1"></i> عرض
                                                        </a>
                                                        <a href="${response.attachment.file_url}" download class="btn btn-sm btn-outline-primary me-2">
                                                            <i class="ti ti-download me-1"></i> تنزيل
                                                        </a>
                                                        <a class="btn btn-outline-danger btn-sm delete-new-attachment text-danger" data-id="${response.attachment.id}">
                                                            <i class="fas fa-trash-alt me-1"></i> حذف
                                                        </a>
                                                    </div>
                                                </div>
                                            `;
                                        $('.newAttachments').prepend(
                                            newAttachment); // أضف المرفق الجديد إلى الأعلى
                                        $('.empty')
                                            .remove();
                                    } else {
                                        toastr.error('حدث خطأ أثناء إضافة المرفق.');
                                    }
                                },
                                error: function(xhr) {
                                    // عرض الأخطاء
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        $.each(xhr.responseJSON.errors, function(key, value) {
                                            toastr.error(value[0]);
                                        });
                                    } else {
                                        toastr.error('حدث خطأ أثناء إضافة المرفق.');
                                    }
                                }
                            });
                        });
                    });
                </script>

                <!-- نموذج إضافة مرفق جديد -->
                <hr>
                <h6 class="mt-4 text-primary fw-bold fs-6">إضافة مرفق جديد</h6>
                <form id="addAttachmentForm"
                    action="{{ route('legal-affairs.lawsuits.lawsuit-section.attachments-store', $lawsuit->id) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="attachment_name" class="form-label text-primary fw-bold fs-6">اسم المرفق</label>
                            <input type="text" class="form-control @error('attachment_name') is-invalid @enderror"
                                id="attachment_name" name="attachment_name" required>
                            @error('attachment_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="attachment_file" class="form-label text-primary fw-bold fs-6">تحميل
                                المرفق</label>
                            <input type="file" class="form-control @error('attachment_file') is-invalid @enderror"
                                id="attachment_file" name="attachment_file" required>
                            @error('attachment_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">إضافة المرفق</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
