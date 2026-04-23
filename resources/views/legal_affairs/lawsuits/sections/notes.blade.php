<style>
    /* تخصيص شريط التمرير في offcanvas */
    .offcanvas-body {
        scrollbar-width: thin;
        /* لفايرفوكس */
        scrollbar-color: var(--primary-color) #f1f1f1;
        /* تخصيص ألوان شريط التمرير في فايرفوكس */
    }

    /* تخصيص شريط التمرير في كروم وسفاري وإيدج */
    .offcanvas-body::-webkit-scrollbar {
        width: 8px;
    }

    .offcanvas-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 8px;
    }

    .offcanvas-body::-webkit-scrollbar-thumb {
        background-color: var(--primary-color);
        border-radius: 8px;
    }

    .offcanvas-body::-webkit-scrollbar-thumb:hover {
        background-color: #0056b3;
    }
</style>

<!-- Tribute.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tributejs/5.1.3/tribute.css"
    integrity="sha512-GnwBnXd+ZGO9CdP343MUr0jCcJXCr++JVtQRnllexRW2IDq4Zvrh/McTQjooAKnSUbXZ7wamp7AQSweTnfMVoA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Tribute.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tributejs/5.1.3/tribute.min.js"
    integrity="sha512-KJYWC7RKz/Abtsu1QXd7VJ1IJua7P7GTpl3IKUqfa21Otg2opvRYmkui/CXBC6qeDYCNlQZ7c+7JfDXnKdILUA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        // إنشاء خريطة بين name و id
        var nameToIdMap = {
            @foreach ($employees as $employee)
                '{{ addslashes($employee->name) }}': {{ $employee->id }},
            @endforeach
        };

        var tribute = new Tribute({
            values: [
                @foreach ($employees as $employee)
                    {
                        key: '{{ addslashes($employee->name) }}', // استخدام name كمفتاح
                        value: '{{ addslashes($employee->name) }}#', // تضمين id مع name
                        label: '{{ addslashes($employee->name) }}' // عرض الاسم كعلامة
                    },
                @endforeach
            ],
            lookup: 'key', // البحث بناءً على 'key' وهو 'name'
            fillAttr: 'value', // ملء النص بـ 'name'
            replace: function(mention) {
                return '@' + mention.original.value; // إزالة إضافة المسافة هنا
            },
            menuItemTemplate: function(item) {
                return '<div>' + item.original.label + '</div>'; // عرض الاسم فقط
            }
        });

        tribute.attach(document.getElementById('reply_text'));
        tribute.attach(document.getElementById('edit_reply_text'));

        // تخزين الخريطة في نافذة global للوصول إليها في parseMentions
        window.nameToIdMap = nameToIdMap;
    });
</script>
<div class="col-12 col-lg-12 pt-6 pt-lg-0">

    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-note ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> الملاحظات
            </h5>
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#addNoteOffcanvas" aria-controls="addNoteOffcanvas">
                    <i class="ti ti-plus ti-xs me-1"></i> إضافة ملاحظة جديدة
                </button>
            </div>
        </div>
        <!-- زر إضافة ملاحظة جديدة -->

        <!-- عرض الملاحظات الحالية -->
        @if ($lawsuit->notes->count() > 0)
            <div class="row px-5 mb-5">
                @foreach ($lawsuit->notes as $note)
                    <div class="col-md-4 mb-4 ">
                        <div class="card h-100 shadow-sm border-2 border-primary
                            {{ $note->type == 'public' || ($note->type == 'requires_manager_reply' && $lawsuit->project->manager_user_id == Auth::user()->id) || $note->user_id == Auth::user()->id ? 'clickable-card' : '' }}"
                            data-id="{{ $note->id }}" style="cursor: pointer;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>ملاحظة {{ $loop->iteration }}: {{ $note->title }}</strong>
                                    <span
                                        class="badge
                                        {{ $note->type == 'requires_manager_reply'
                                            ? 'bg-warning'
                                            : ($note->type == 'public'
                                                ? 'bg-info'
                                                : 'bg-secondary') }}
                                        ms-2">
                                        {{ $note->type == 'requires_manager_reply'
                                            ? 'تحتاج رد المدير'
                                            : ($note->type == 'public'
                                                ? 'عامة'
                                                : 'لا تحتاج لرد') }}
                                    </span>
                                </div>
                                <!-- زر ثلاث نقاط مع قائمة منسدلة -->
                                @if (Auth::check() && Auth::id() === $note->user_id)
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle-custom" type="button"
                                            id="dropdownMenuButton{{ $note->id }}" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>

                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="dropdownMenuButton{{ $note->id }}">
                                            <li>
                                                <a class="dropdown-item edit-note-button" href="#"
                                                    data-id="{{ $note->id }}">
                                                    <i class="bi bi-pencil-square"></i> تعديل
                                                </a>
                                            </li>
                                            <li>
                                                <form
                                                    action="{{ route('legal-affairs.lawsuits.lawsuit-section.notes.destroy', [$lawsuit->id, $note->id]) }}"
                                                    method="POST" class="mb-0 delete-note-form"
                                                    data-note-id="{{ $note->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="dropdown-item text-danger delete-note-btn"><i
                                                            class="bi bi-trash"></i> حذف</button>

                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <p>{{ Str::limit($note->text, 100, '...') }}</p>
                                <p>
                                    <small>أضافها: {{ $note->user->name }}</small>
                                </p>
                                <p>
                                    <small> {{ $note->hijri_created }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <style>
                .custom-pagination .text-muted {
                    display: none !important;
                }
            </style>
            <div class="d-flex justify-content-center mt-4">
                <div class="custom-pagination">
                    {{-- {{ $lawsuit->notes->links('pagination::bootstrap-5') }} --}}
                </div>
            </div>
        @else
            <div class="col-12 text-center mb-5">
                <i class="ti ti-notes ti-3x text-muted mb-3"></i>
                <p class="text-muted">لا توجد ملاحظات لعرضها حالياً.</p>
            </div>
        @endif

        <!-- Offcanvas لعرض وإضافة الردود -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="replyOffcanvas" aria-labelledby="replyOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="replyOffcanvasLabel">ردود الملاحظة</h5>
                {{-- <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="إغلاق"></button> --}}
            </div>
            <div class="offcanvas-body">
                <!-- عرض الردود -->
                <div id="replyContent">
                    <!-- الردود ستظهر هنا -->
                </div>
                <!-- نموذج إضافة رد جديد -->
                <div class="mt-4">
                    <h6>إضافة رد جديد</h6>
                    <form id="addReplyForm" method="POST">
                        @csrf
                        <input type="hidden" name="note_id" id="note_id" value="">
                        <div class="mb-3">
                            <label for="reply_text" class="form-label">نص الرد</label>
                            <textarea class="form-control @error('reply_text') is-invalid @enderror" id="reply_text" name="reply_text"
                                rows="3" required></textarea>
                            @error('reply_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">إضافة الرد</button>

                        </div>
                    </form>
                </div>
                <!-- نموذج تعديل الرد -->
                <div id="editReplyFormContainer" style="display: none;">
                    <h6>تعديل الرد</h6>
                    <form id="editReplyForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="reply_id" id="edit_reply_id" value="">
                        <div class="mb-3">
                            <label for="edit_reply_text" class="form-label">نص الرد</label>
                            <textarea class="form-control" id="edit_reply_text" name="reply_text" rows="3" required></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" id="cancelEditReply">إلغاء</button>
                            <button type="submit" class="btn btn-primary">تحديث الرد</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Offcanvas لإضافة ملاحظة جديدة -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="addNoteOffcanvas"
            aria-labelledby="addNoteOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="addNoteOffcanvasLabel">إضافة ملاحظة جديدة</h5>
                {{-- <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="إغلاق"></button> --}}
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('legal-affairs.lawsuits.lawsuit-section.notes.store', $lawsuit->id) }}"
                    method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">عنوان الملاحظة</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                            id="title" name="title" value="{{ old('title') }}" required maxlength="255">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="text" class="form-label">نص الملاحظة</label>
                        <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text" rows="4"
                            required>{{ old('text') }}</textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">نوع الملاحظة</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type"
                            required>
                            <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>لا تحتاج
                                لرد
                            </option>
                            <option value="requires_manager_reply"
                                {{ old('type') == 'requires_manager_reply' ? 'selected' : '' }}>تحتاج رد المدير
                            </option>
                            <option value="public" {{ old('type') == 'public' ? 'selected' : '' }}>عامة
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-primary">إضافة الملاحظة</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Offcanvas لتعديل الملاحظة -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="editNoteOffcanvas"
            aria-labelledby="editNoteOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="editNoteOffcanvasLabel">تعديل الملاحظة</h5>
                {{-- <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="إغلاق"></button> --}}
            </div>
            <div class="offcanvas-body">
                <form id="editNoteForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="note_id" id="edit_note_id" value="">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">عنوان الملاحظة</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                            id="edit_title" name="title" value="{{ old('title') }}" required maxlength="255">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_text" class="form-label">نص الملاحظة</label>
                        <textarea class="form-control @error('text') is-invalid @enderror" id="edit_text" name="text" rows="4"
                            required>{{ old('text') }}</textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">نوع الملاحظة</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="edit_type" name="type"
                            required>
                            <option value="private">لا تحتاج لرد</option>
                            <option value="requires_manager_reply">تحتاج رد المدير</option>
                            <option value="public">عامة</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-primary">تحديث الملاحظة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    .highlight-comment {
        background-color: #ffff99;
        /* لون خلفية فاتح */
        border-left: 4px solid #ffeb3b;
        /* شريط أيسر ملون */
        padding-left: 10px;
        /* transition: background-color 0.5s ease; */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // الحصول على معرف الدعوى من الـ Blade
        const lawsuitId = {{ $lawsuit->id }};

        // الحصول على جميع الكروت القابلة للنقر
        const clickableCards = document.querySelectorAll('.clickable-card');

        // الحصول على Offcanvas للردود
        // const replyOffcanvas = new bootstrap.Offcanvas(document.getElementById('replyOffcanvas'));
        const replyOffcanvasElement = document.getElementById('replyOffcanvas');
        const replyOffcanvas = new bootstrap.Offcanvas(replyOffcanvasElement);

        // الحصول على Offcanvas للتعديل
        const editNoteOffcanvas = new bootstrap.Offcanvas(document.getElementById('editNoteOffcanvas'));

        // الحصول على عناصر Offcanvas
        const replyContent = document.getElementById('replyContent');
        const addReplyForm = document.getElementById('addReplyForm');
        const noteIdInput = document.getElementById('note_id');

        const editNoteForm = document.getElementById('editNoteForm');
        const editNoteIdInput = document.getElementById('edit_note_id');
        const editTitleInput = document.getElementById('edit_title');
        const editTextInput = document.getElementById('edit_text');
        const editTypeSelect = document.getElementById('edit_type');

        // عناصر تعديل الرد
        const editReplyFormContainer = document.getElementById('editReplyFormContainer');
        const editReplyForm = document.getElementById('editReplyForm');
        const editReplyIdInput = document.getElementById('edit_reply_id');
        const editReplyTextInput = document.getElementById('edit_reply_text');
        const cancelEditReplyButton = document.getElementById('cancelEditReply');

        // إضافة مستمعات للنقر على كل كارد
        clickableCards.forEach(function(card) {
            card.addEventListener('click', function(event) {
                // إذا كان النقر على زر القائمة المنسدلة أو أي زر داخلها، لا تفعل شيء
                if (event.target.closest('.dropdown-menu') || event.target.closest(
                        '.dropdown-toggle-custom')) {
                    return;
                }

                const noteId = this.getAttribute('data-id');

                // تعيين معرف الملاحظة في النموذج
                noteIdInput.value = noteId;

                openComments(noteId);
                // جلب بيانات الملاحظة عبر AJAX

            });
        });

        window.openComments = function(noteId, reply_fun = null) {
            noteIdInput.value = noteId;

            const showCommentUrl =
                "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.show', [$lawsuit->id, '.noteId.']) }}"
                .replace('.noteId.', noteId);

            fetch(
                    showCommentUrl
                )
                .then(response => response.json())
                .then(data => {
                    // تحديث عنوان Offcanvas
                    document.getElementById('replyOffcanvasLabel').textContent =
                        `ردود الملاحظة: ${data.title}`;

                    // تعبئة الردود
                    let repliesHtml = '';
                    if (data.replies.length > 0) {
                        repliesHtml += '<h6>الردود:</h6>';
                        data.replies.forEach(function(reply) {
                            repliesHtml += `
                                    <div class="d-flex mb-2">
                                        <div class="flex-shrink-0">
                                            <i class="ti ti-user ti-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3 comment-item" data-comment-id="${reply.id}">
                                            <strong>${reply.user.name}</strong>
                                            <span class="text-muted">${reply.hijri_created_at}</span>
                                             <p>${parseMentions(reply.reply_text)}</p>
                                            ${reply.can_edit_delete ? `
                                                                                <div class="d-flex">
                                                                                    <button class="btn btn-sm btn-link text-primary edit-reply-button" data-id="${reply.id}" data-note-id="${noteId}"><i class="bi bi-pencil-square"></i> تعديل</button>
                                                                                    <button class="btn btn-sm btn-link text-danger delete-reply-button" data-id="${reply.id}" data-note-id="${noteId}"><i class="bi bi-trash"></i> حذف</button>
                                                                                </div>
                                                                                ` : ''}
                                        </div>
                                    </div>
                                `;
                        });
                    } else {
                        repliesHtml = '<p class="text-muted">لا توجد ردود لعرضها.</p>';
                    }

                    if (reply_fun) {
                        // إضافة مستمع للحدث 'shown.bs.offcanvas' على عنصر DOM
                        const handler = function() {
                            // إزالة مستمع الحدث بعد التنفيذ لضمان تنفيذه مرة واحدة فقط
                            replyOffcanvasElement.removeEventListener('shown.bs.offcanvas',
                                handler);

                            const commentElement = document.querySelector(
                                `.comment-item[data-comment-id="${reply_fun}"]`);
                            if (commentElement) {
                                // إضافة فئة CSS لتمييز التعليق
                                commentElement.classList.add('highlight-comment');

                                // قم بالتمرير إلى التعليق المحدد
                                commentElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        };

                        replyOffcanvasElement.addEventListener('shown.bs.offcanvas', handler);
                    }


                    replyContent.innerHTML = repliesHtml;

                    // فتح Offcanvas
                    replyOffcanvas.show();
                })
                .catch(error => {
                    console.error('Error fetching note details:', error);
                    alert('حدث خطأ أثناء جلب بيانات الملاحظة.');
                });
        }


        // التعامل مع فتح Offcanvas التعديل عند النقر على زر تعديل
        const editButtons = document.querySelectorAll('.edit-note-button');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation(); // منع تفعيل كارد النقر

                const noteId = this.getAttribute('data-id');

                const showNotetUrl =
                    "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.show', [$lawsuit->id, '.noteId.']) }}"
                    .replace('.noteId.', noteId);

                // جلب بيانات الملاحظة عبر AJAX
                fetch(showNotetUrl)
                    .then(response => response.json())
                    .then(data => {
                        // تعبئة النموذج ببيانات الملاحظة
                        editNoteIdInput.value = data.id;
                        editTitleInput.value = data.title;
                        editTextInput.value = data.text;
                        editTypeSelect.value = data.type;

                        // فتح Offcanvas التعديل
                        editNoteOffcanvas.show();
                    })
                    .catch(error => {
                        console.error('Error fetching note details for edit:', error);
                        alert('حدث خطأ أثناء جلب بيانات الملاحظة للتعديل.');
                    });
            });
        });

        function parseMentions(content) {
            return content.replace(/@([\p{L}]+(?:\s[\p{L}]+)*)/gu, function(match, p1) {
                var id = window.nameToIdMap[p1.trim()];
                if (id) {
                    return `<a href="/account/employee/${id}/profile" class="mention">@${p1}</a>`;
                } else {
                    return match; // اترك النص كما هو إذا لم يتم العثور على id
                }
            });
        }

        // التعامل مع إرسال نموذج إضافة رد
        addReplyForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(addReplyForm);
            const noteId = formData.get('note_id');
            const replyText = formData.get('reply_text');

            const addCommentUrl =
                "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.replies.store', [$lawsuit->id, '.noteId.']) }}"
                .replace('.noteId.', noteId);

            fetch(addCommentUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reply_text: replyText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // إضافة الرد الجديد إلى القائمة
                        const newReply = data.reply;
                        const newReplyHtml = `
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <i class="ti ti-user ti-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>${newReply.user.name}</strong>
                                <span class="text-muted">${newReply.hijri_created_at}</span>
                                <p>${parseMentions(newReply.reply_text)}</p>
                                ${newReply.can_edit_delete ? `
                                                                    <div class="d-flex">
                                                                        <button class="btn btn-sm btn-link text-primary edit-reply-button" data-id="${newReply.id}" data-note-id="${noteId}"><i class="bi bi-pencil-square"></i> تعديل</button>
                                                                        <button class="btn btn-sm btn-link text-danger delete-reply-button" data-id="${newReply.id}" data-note-id="${noteId}"><i class="bi bi-trash"></i> حذف</button>
                                                                    </div>
                                                                    ` : ''}
                            </div>
                        </div>
                    `;
                        replyContent.insertAdjacentHTML('beforeend', newReplyHtml);

                        // مسح النص في النموذج
                        addReplyForm.reset();

                        // إظهار رسالة نجاح باستخدام Toastr
                        toastr.success('تم إضافة الرد بنجاح.');
                    } else {
                        // إظهار رسالة خطأ باستخدام Toastr
                        toastr.error('حدث خطأ أثناء إضافة الرد.');
                    }
                })
                .catch(error => {
                    console.error('Error adding reply:', error);
                    toastr.error('حدث خطأ أثناء إضافة الرد.');
                });
        });

        // التعامل مع إرسال نموذج تعديل الملاحظة
        editNoteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const noteId = editNoteIdInput.value;
            const title = editTitleInput.value;
            const text = editTextInput.value;
            const type = editTypeSelect.value;

            const EditNotetUrl =
                "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.update', [$lawsuit->id, '.noteId.']) }}"
                .replace('.noteId.', noteId);

            fetch(EditNotetUrl, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        text: text,
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث الكارد في الصفحة
                        const updatedNote = data.note;
                        const card = document.querySelector(
                            `.clickable-card[data-id="${updatedNote.id}"]`);

                        if (card) {
                            // تحديث العنوان والنص والنوع ووقت الإنشاء
                            card.querySelector('strong').textContent =
                                `ملاحظة ${card.closest('.col-md-4').dataset.iteration || ''}: ${updatedNote.title}`;
                            card.querySelector('.badge').className = `badge ${
                                updatedNote.type === 'requires_manager_reply' ? 'bg-warning' :
                                (updatedNote.type === 'public' ? 'bg-info' : 'bg-secondary')
                            } ms-2`;
                            card.querySelector('.badge').textContent = updatedNote.type ===
                                'requires_manager_reply' ?
                                'تحتاج رد المدير' :
                                (updatedNote.type === 'public' ?
                                    'عامة' :
                                    'لا تحتاج لرد');
                            card.querySelector('.card-body p').textContent =
                                `${updatedNote.text.substring(0, 100)}${updatedNote.text.length > 100 ? '...' : ''}`;
                        }

                        // إغلاق Offcanvas التعديل
                        editNoteOffcanvas.hide();

                        // إظهار رسالة نجاح باستخدام Toastr
                        toastr.success('تم تعديل الملاحظة بنجاح.');
                    } else {
                        // إظهار رسالة خطأ باستخدام Toastr
                        toastr.error('حدث خطأ أثناء تعديل الملاحظة.');
                    }
                })
                .catch(error => {
                    console.error('Error updating note:', error);
                    toastr.error('حدث خطأ أثناء تعديل الملاحظة.');
                });
        });

        // منع تداخل النقر بين الكارد وأزرار التعديل والحذف
        document.querySelectorAll('.dropdown-item').forEach(function(dropdownItem) {
            dropdownItem.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        // التعامل مع أزرار التعديل والحذف للردود
        replyContent.addEventListener('click', function(e) {
            // تعديل الرد
            if (e.target.classList.contains('edit-reply-button') || e.target.closest(
                    '.edit-reply-button')) {
                const button = e.target.closest('.edit-reply-button');
                const replyId = button.getAttribute('data-id');
                const noteId = button.getAttribute('data-note-id');

                const showUrl =
                    "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.replies.show', [$lawsuit->id, '.noteId.', '.replyId.']) }}"
                    .replace('.noteId.', noteId)
                    .replace('.replyId.', replyId);

                // جلب بيانات الرد عبر AJAX
                fetch(
                        showUrl)
                    .then(response => response.json())
                    .then(data => {
                        // تعبئة النموذج ببيانات الرد
                        editReplyIdInput.value = data.reply.id;
                        editReplyTextInput.value = data.reply.reply_text;

                        // إظهار نموذج التعديل
                        editReplyFormContainer.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching reply details:', error);
                        toastr.error('حدث خطأ أثناء جلب بيانات الرد.');
                    });
            }

            // حذف الرد
            if (e.target.classList.contains('delete-reply-button') || e.target.closest(
                    '.delete-reply-button')) {
                const button = e.target.closest('.delete-reply-button');
                const replyId = button.getAttribute('data-id');
                const noteId = button.getAttribute('data-note-id');

                // تأكيد الحذف
                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
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
                        const deleteUrl =
                            "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.replies.destroy', [$lawsuit->id, '.noteId.', '.replyId.']) }}"
                            .replace('.noteId.', noteId)
                            .replace('.replyId.', replyId);
                        // إرسال طلب الحذف
                        fetch(deleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // إعادة تحميل الردود
                                    document.querySelector(
                                            `.clickable-card[data-id="${noteId}"]`)
                                        .click();

                                    toastr.success('تم حذف الرد بنجاح.');
                                } else {
                                    toastr.error('حدث خطأ أثناء حذف الرد.');
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting reply:', error);
                                toastr.error('حدث خطأ أثناء حذف الرد.');
                            });
                    }
                });
            }
        });

        // إلغاء عملية التعديل للرد
        cancelEditReplyButton.addEventListener('click', function() {
            editReplyFormContainer.style.display = 'none';
            editReplyForm.reset();
        });

        // إرسال نموذج تعديل الرد
        editReplyForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const replyId = editReplyIdInput.value;
            const replyText = editReplyTextInput.value;

            const noteId = noteIdInput.value;

            const updateUrl =
                "{{ route('legal-affairs.lawsuits.lawsuit-section.notes.replies.update', [$lawsuit->id, '.noteId.', '.replyId.']) }}"
                .replace('.noteId.', noteId)
                .replace('.replyId.', replyId);

            fetch(updateUrl, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reply_text: replyText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // إخفاء نموذج التعديل
                        editReplyFormContainer.style.display = 'none';
                        editReplyForm.reset();

                        // إعادة تحميل الردود
                        document.querySelector(`.clickable-card[data-id="${noteId}"]`).click();

                        toastr.success('تم تعديل الرد بنجاح.');
                    } else {
                        toastr.error('حدث خطأ أثناء تعديل الرد.');
                    }
                })
                .catch(error => {
                    console.error('Error updating reply:', error);
                    toastr.error('حدث خطأ أثناء تعديل الرد.');
                });
        });

        // التعامل مع أزرار حذف الملاحظة
        document.querySelectorAll('.delete-note-btn').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-note-form');

                // عرض SweetAlert للتأكيد
                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
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
                        // إرسال النموذج عند التأكيد
                        form.submit();
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (isset($lawsuit_note) && isset($note_reply))

            // تشغيل دالة openComments مع session_notification_id_id و comment_id (إذا كانت موجودة)
            window.openComments({{ $lawsuit_note }}, {{ $note_reply }});
        @endif
    });
</script>
