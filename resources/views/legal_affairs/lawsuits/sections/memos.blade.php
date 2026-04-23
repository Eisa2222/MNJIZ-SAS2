<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5 d-flex justify-content-between">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-file-text ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> المذكرات
            </h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemoModal">
                <i class="ti ti-plus me-1"></i> إضافة مذكرة جديدة
            </button>
        </div>
        <div class="card-body pt-3">
            <div class="row g-5">
                <!-- مذكرات المدعي -->
                <div class="col-md-6" id="plaintiff">
                    <p class="mb-3 text-primary fw-bold">مذكرات المدعي</p>
                    @forelse($lawsuit->memos->where('type', 'plaintiff') as $memo)
                        <div class="card mb-3 memo-card" id="memo-card-{{ $memo->id }}">
                            <div class="card-body">
                                <h5 class="card-title">مذكرة المدعي</h5>
                                <p class="card-text">{{ $memo->text }}</p>
                                <div class="mt-3 d-flex flex-wrap ">
                                    @if ($memo->attachment)
                                        <div class="mt-2 d-flex align-items-center">
                                            <a href="{{ asset('storage/' . $memo->attachment) }}"
                                                class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                                <i class="ti ti-eye me-1"></i> عرض
                                            </a>
                                            <a href="{{ asset('storage/' . $memo->attachment) }}" download
                                                class="btn btn-sm btn-outline-primary me-2">
                                                <i class="ti ti-download me-1"></i> تنزيل
                                            </a>
                                        </div>
                                    @endif
                                    <div class="mt-2 d-flex">
                                        <button class="btn btn-sm btn-outline-warning me-2 edit-memo"
                                            data-id="{{ $memo->id }}" data-type="{{ $memo->type }}"
                                            data-text="{{ htmlspecialchars($memo->text) }}"
                                            data-attachment="{{ $memo->attachment }}">
                                            <i class="fas fa-edit me-1"></i> تعديل
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-memo"
                                            data-id="{{ $memo->id }}"
                                            data-delete-url="{{ route('legal-affairs.lawsuits.lawsuit-section.memos.destroy', [$lawsuit->id, $memo->id]) }}">

                                            <i class="fas fa-trash-alt me-1"></i> حذف
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>لا توجد مذكرات للمدعي.</p>
                    @endforelse
                </div>

                <!-- مذكرات المدعى عليه -->
                <div class="col-md-6" id="defendant">
                    <p class="mb-3 text-primary fw-bold">مذكرات المدعى عليه</p>
                    @forelse($lawsuit->memos->where('type', 'defendant') as $memo)
                        <div class="card mb-3 memo-card" id="memo-card-{{ $memo->id }}">
                            <div class="card-body">
                                <h5 class="card-title">مذكرة المدعى عليه</h5>
                                <p class="card-text">{{ $memo->text }}</p>

                                <div class="mt-3 d-flex flex-wrap ">
                                    @if ($memo->attachment)
                                        <div class="mt-2 d-flex align-items-center">
                                            <a href="{{ asset('storage/' . $memo->attachment) }}"
                                                class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                                <i class="ti ti-eye me-1"></i> عرض
                                            </a>
                                            <a href="{{ asset('storage/' . $memo->attachment) }}" download
                                                class="btn btn-sm btn-outline-primary me-2">
                                                <i class="ti ti-download me-1"></i> تنزيل
                                            </a>
                                        </div>
                                    @endif
                                    <div class="mt-2 d-flex">
                                        <button class="btn btn-sm btn-outline-warning me-2 edit-memo"
                                            data-id="{{ $memo->id }}" data-type="{{ $memo->type }}"
                                            data-text="{{ htmlspecialchars($memo->text) }}"
                                            data-attachment="{{ $memo->attachment }}">
                                            <i class="fas fa-edit me-1"></i> تعديل
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-memo"
                                            data-id="{{ $memo->id }}"
                                            data-delete-url="{{ route('legal-affairs.lawsuits.lawsuit-section.memos.destroy', [$lawsuit->id, $memo->id]) }}">
                                            <i class="fas fa-trash-alt me-1"></i> حذف
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>لا توجد مذكرات للمدعى عليه.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودل إضافة مذكرة جديدة -->
<div class="modal fade" id="addMemoModal" tabindex="-1" aria-labelledby="addMemoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addMemoForm" method="POST"
                action="{{ route('legal-affairs.lawsuits.lawsuit-section.memos.store', $lawsuit->id) }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemoModalLabel">إضافة مذكرة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="memo_type" class="form-label">نوع المذكرة</label>
                        <select name="type" id="memo_type" class="form-control @error('type') is-invalid @enderror"
                            required>
                            <option value="">اختر النوع</option>
                            <option value="plaintiff">مذكرة المدعي</option>
                            <option value="defendant">مذكرة المدعى عليه</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="memo_text" class="form-label">النص</label>
                        <textarea name="text" id="memo_text" class="form-control @error('text') is-invalid @enderror" rows="4"
                            placeholder="أدخل نص المذكرة" required>{{ old('text') }}</textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="memo_attachment" class="form-label">المرفق</label>
                        <input type="file" name="attachment" id="memo_attachment"
                            class="form-control @error('attachment') is-invalid @enderror">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ المذكرة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودل تعديل مذكرة -->
<div class="modal fade" id="editMemoModal" tabindex="-1" aria-labelledby="editMemoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editMemoForm" method="POST" enctype="multipart/form-data" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemoModalLabel">تعديل مذكرة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="memo_id" id="edit_memo_id">
                    <div class="mb-3">
                        <label for="edit_memo_type" class="form-label">نوع المذكرة</label>
                        <select name="type" id="edit_memo_type"
                            class="form-control @error('type') is-invalid @enderror" required>
                            <option value="">اختر النوع</option>
                            <option value="plaintiff">مذكرة المدعي</option>
                            <option value="defendant">مذكرة المدعى عليه</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_memo_text" class="form-label">النص</label>
                        <textarea name="text" id="edit_memo_text" class="form-control @error('text') is-invalid @enderror" rows="4"
                            placeholder="أدخل نص المذكرة" required></textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_memo_attachment" class="form-label">المرفق</label>
                        <input type="file" name="attachment" id="edit_memo_attachment"
                            class="form-control @error('attachment') is-invalid @enderror">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="current_attachment" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تحديث المذكرة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.edit-memo', function() {
        const memoId = $(this).data('id');
        const memoType = $(this).data('type');
        const memoText = $(this).data('text');
        const memoAttachment = $(this).data('attachment');

        // تحديث رابط نموذج التعديل
        const editForm = $('#editMemoForm');

        const updateMemoUrl =
            "{{ route('legal-affairs.lawsuits.lawsuit-section.memos.update', [$lawsuit->id, '+memoId+']) }}"
            .replace('+memoId+', memoId);

        editForm.attr('action', updateMemoUrl);


        // ملء الحقول
        $('#edit_memo_id').val(memoId);
        $('#edit_memo_type').val(memoType);
        $('#edit_memo_text').val(memoText);

        // عرض المرفق الحالي إذا وجد
        const currentAttachmentDiv = $('#current_attachment');
        if (memoAttachment) {
            currentAttachmentDiv.html(`
        <p>المرفق الحالي:</p>
        <a href="{{ asset('storage/') }}/${memoAttachment}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
            <i class="ti ti-eye me-1"></i> عرض
        </a>
        <a href="{{ asset('storage/') }}/${memoAttachment}" download class="btn btn-sm btn-outline-primary me-2">
            <i class="ti ti-download me-1"></i> تنزيل
        </a>
    `);
        } else {
            currentAttachmentDiv.html('');
        }

        // فتح المودل
        const editMemoModal = new bootstrap.Modal(document.getElementById('editMemoModal'));
        editMemoModal.show();
    });

    // حذف المذكرة
    $(document).on('click', '.delete-memo', function() {
        const memoId = $(this).data('id');
        const deleteUrl = $(this).data('delete-url');

        const memoCard = $(`#memo-card-${memoId}`);
        const parentSection = memoCard.closest('.col-md-6'); // تحديد القسم (المدعي أو المدعى عليه)


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
            reverseButtons: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        if (memoCard) {
                            memoCard.remove();
                            if (parentSection.find('.memo-card').length === 0) {
                                parentSection.append(
                                    `<p>${parentSection.attr('id') === 'plaintiff' ? 'لا توجد مذكرات للمدعي.' : 'لا توجد مذكرات للمدعى عليه.'}</p>`
                                );
                            }
                        }
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء حذف المذكرة.');
                    }
                });
            }
        });
    });


    $(document).ready(function() {
        $('#addMemoForm').on('submit', function(e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة

            let formData = new FormData(this); // جمع بيانات النموذج
            let actionUrl = $(this).attr('action'); // رابط الإجراء

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);

                        // إنشاء HTML للمذكرة الجديدة
                        let memoHtml = `
                            <div class="card mb-3 memo-card" id="memo-card-${response.data.id}">
                                <div class="card-body">
                                    <h5 class="card-title">مذكرة ${response.data.type === 'plaintiff' ? 'المدعي' : 'المدعى عليه'}</h5>
                                    <p class="card-text">${response.data.text}</p>
                                    <div class="mt-3 d-flex flex-wrap">
                                        ${response.data.attachment ? `
                                                                <a href="${response.data.attachment_url}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                                                    <i class="ti ti-eye me-1"></i> عرض
                                                                </a>
                                                                <a href="${response.data.attachment_url}" download class="btn btn-sm btn-outline-primary me-2">
                                                                    <i class="ti ti-download me-1"></i> تنزيل
                                                                </a>
                                                            ` : ''}
                                        <button class="btn btn-sm btn-outline-warning me-2 edit-memo" data-id="${response.data.id}" data-type="${response.data.type}" data-text="${response.data.text}" data-attachment="${response.data.attachment}">
                                            <i class="fas fa-edit me-1"></i> تعديل
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        // إضافة المذكرة إلى القسم المناسب
                        if (response.data.type === 'plaintiff') {
                            $('#plaintiff').append(memoHtml);
                            $('#plaintiff p.text-primary + p')
                                .remove(); // إزالة النص "لا توجد مذكرات للمدعي"
                            // قسم مذكرات المدعي
                        } else {
                            $('#defendant').append(memoHtml);
                            $('#defendant p.text-primary + p')
                                .remove(); // إزالة النص "لا توجد مذكرات للمدعى عليه"
                            // قسم مذكرات المدعى عليه
                        }

                        // إغلاق المودل وإعادة تعيين النموذج
                        $('#addMemoModal').modal('hide');
                        $('#addMemoForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء إضافة المذكرة.');
                }
            });
        });


        // لتعديل الملاحظة
        $('#editMemoForm').on('submit', function(e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة

            let formData = new FormData(this); // جمع بيانات النموذج
            let actionUrl = $(this).attr('action'); // رابط الإجراء

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);

                        let memoCard = $(`#memo-card-${response.data.id}`);
                        let currentSection = memoCard.closest('.col-md-6').attr(
                            'id'); // القسم الحالي
                        let newSection = response.data.type === 'plaintiff' ? 'plaintiff' :
                            'defendant'; // القسم الجديد

                        // تحديث محتوى المذكرة
                        let updatedHtml = `
                            <div class="card-body">
                                <h5 class="card-title">مذكرة ${response.data.type === 'plaintiff' ? 'المدعي' : 'المدعى عليه'}</h5>
                                <p class="card-text">${response.data.text}</p>
                                <div class="mt-3 d-flex flex-wrap">
                                    ${response.data.attachment ? `
                                        <a href="${response.data.attachment_url}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                            <i class="ti ti-eye me-1"></i> عرض
                                        </a>
                                        <a href="${response.data.attachment_url}" download class="btn btn-sm btn-outline-primary me-2">
                                            <i class="ti ti-download me-1"></i> تنزيل
                                        </a>
                                    ` : ''}
                                    <button class="btn btn-sm btn-outline-warning me-2 edit-memo" data-id="${response.data.id}" data-type="${response.data.type}" data-text="${response.data.text}" data-attachment="${response.data.attachment}">
                                        <i class="fas fa-edit me-1"></i> تعديل
                                    </button>
                                </div>
                            </div>
                        `;

                        // إذا تغير النوع، انقل البطاقة إلى القسم الجديد
                        if (currentSection !== newSection) {
                            memoCard.remove(); // إزالة البطاقة القديمة
                            $(`#${newSection}`).append(`
                                <div class="card mb-3 memo-card" id="memo-card-${response.data.id}">
                                    ${updatedHtml}
                                </div>
                            `);

                            // إذا أصبح القسم القديم فارغًا، أضف النص الافتراضي
                            if ($(`#${currentSection} .memo-card`).length === 0) {
                                $(`#${currentSection}`).append(
                                    `<p>${currentSection === 'plaintiff' ? 'لا توجد مذكرات للمدعي.' : 'لا توجد مذكرات للمدعى عليه.'}</p>`
                                );
                            }

                            // إزالة النص الافتراضي من القسم الجديد (إذا كان موجودًا)
                            $(`#${newSection} p:contains('لا توجد مذكرات')`).remove();
                        } else {
                            // إذا لم يتغير النوع، قم بتحديث المحتوى فقط
                            memoCard.html(updatedHtml);
                        }

                        // إغلاق المودل
                        $('#editMemoModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء تعديل المذكرة.');
                }
            });
        });



    });
</script>
