@extends('layouts.layoutMaster')

@section('title', 'البريد الإلكتروني ')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> البريد الإلكتروني</a>
        <i class="ti ti-star favorite-icon" data-page-name="البريد الإلكتروني" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/quill/katex.scss',
'resources/assets/vendor/libs/quill/editor.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/toastr/toastr.scss',
])
<!-- إضافة SweetAlert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .sender-initials {
        width: 32px;
        height: 32px;
        background-color: #6c757d;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 50%;
        font-size: 14px;
        text-transform: uppercase;
    }

    /* تصميم مؤشر التحميل */
    #loading-spinner {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2000;
    }

    /* تصميم زر الحذف الجماعي */
    /* #bulk-delete-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    } */

    /* إخفاء عداد العناصر إذا كان صفر */
    #bulk-delete-btn .selected-count {
        display: none;
    }

    /* إظهار عداد العناصر إذا كانت أكبر من صفر */
    #bulk-delete-btn.active .selected-count {
        display: inline-block;
    }

</style>
@endsection

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/app-email.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/quill/katex.js',
'resources/assets/vendor/libs/quill/quill.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/block-ui/block-ui.js',
'resources/assets/vendor/libs/toastr/toastr.js',
])
<!-- إضافة SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/app-email.js'
])
<script>
    $(document).ready(function() {
        // إعداد CSRF Token لطلبات AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // تهيئة مكتبة toastr
        toastr.options = {
            "closeButton": true
            , "progressBar": true
            , "positionClass": "toast-top-right"
            , "timeOut": "3000"
        , };

        // تهيئة محرر Quill للرسائل
        var quill = new Quill('.email-editor', {
            theme: 'snow'
            , placeholder: 'اكتب رسالتك هنا...'
        });

        // تهيئة محرر Quill للردود
        var quillReply;

        // تحميل مؤشر التحميل الموجود في HTML
        var $loadingSpinner = $('#loading-spinner');

        // دالة لجلب وعرض الرسائل لمجلد معين
        function fetchEmails(folder) {
            // إظهار مؤشر التحميل
            $loadingSpinner.show();

            $.ajax({
                url: '/emails/folder/' + folder
                , method: 'GET'
                , success: function(response) {
                    var emails = response.emails;
                    var emailList = '';

                    if (emails.length > 0) {
                        $.each(emails, function(index, email) {
                            // توليد الحروف الأولى للمرسل
                            var senderName = email.from.name;
                            var senderInitials = senderName ? senderName.substring(0, 2).toUpperCase() : email.from.address.substring(0, 2).toUpperCase();

                            // توليد موضوع البريد
                            var subject = email.subject ? email.subject : '(بدون موضوع)';

                            // توليد وقت الاستلام
                            var receivedDate = new Date(email.dateTime);
                            var receivedTime = isNaN(receivedDate.getTime()) ? 'غير معروف' : receivedDate.toLocaleTimeString([], {
                                hour: '2-digit'
                                , minute: '2-digit'
                            });

                            // توليد التصنيفات بناءً على حالة العلامة
                            var labels = [];
                            if (email.flagStatus && email.flagStatus === 'flagged') {
                                labels.push('مهم');
                            }

                            // بناء HTML للتصنيفات
                            var labelsHtml = '';
                            $.each(labels, function(idx, label) {
                                labelsHtml += '<span class="email-list-item-label badge badge-dot bg-primary d-none d-md-inline-block me-2" data-label="' + label + '">' + label + '</span>';
                            });

                            // بناء عنصر قائمة البريد
                            emailList += `
                                <li class="email-list-item d-flex align-items-center" data-email-id="${email.id}">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="form-check mb-0 ms-2">
                                            <input class="email-list-item-input form-check-input" type="checkbox" id="email-${email.id}">
                                            <label class="form-check-label" for="email-${email.id}"></label>
                                        </div>
                                        <span class="ms-sm-3 me-4 d-sm-inline-block d-none">
                                            <i class="email-list-item-bookmark ti ti-star ti-md cursor-pointer ${email.isRead ? '' : 'text-warning'}"></i>
                                        </span>
                                        <div class="sender-initials rounded-circle me-sm-2 me-0">
                                            ${senderInitials}
                                        </div>
                                        <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                                            <span class="email-list-item-username me-2 text-heading">${email.from.name ? email.from.name : email.from.address}</span>
                                            <small class="email-list-item-subject d-xl-inline-block d-block">${subject.length > 50 ? subject.substring(0, 50) + '...' : subject}</small>
                                        </div>
                                        <div class="email-list-item-meta ms-auto d-flex align-items-center">
                                            ${labelsHtml}
                                            <small class="email-list-item-time text-muted">${receivedTime}</small>
                                            <ul class="list-inline email-list-item-actions">
                                                <li class="list-inline-item email-read btn btn-icon btn-text-secondary rounded-pill">
                                                    <i class='ti ti-mail ti-md'></i>
                                                </li>
                                                <li class="list-inline-item email-delete btn btn-icon btn-text-secondary rounded-pill">
                                                    <i class='ti ti-trash ti-md'></i>
                                                </li>
                                                <li class="list-inline-item btn btn-icon btn-text-secondary rounded-pill">
                                                    <i class='ti ti-info-circle ti-md'></i>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            `;
                        });
                    } else {
                        emailList = '<li class="email-list-empty text-center"></li>';
                    }

                    // تحديث قائمة البريد
                    $('.email-list ul').html(emailList);
                }
                , error: function(xhr) {
                    console.error(xhr);
                    toastr.error('فشل في جلب الرسائل.');
                    $('.email-list ul').html('<li class="email-list-error text-center">فشل في جلب الرسائل.</li>');
                }
                , complete: function() {
                    // إخفاء مؤشر التحميل
                    $loadingSpinner.hide();
                }
            });
        }

        // تحميل البريد الوارد عند تحميل الصفحة
        fetchEmails('inbox');

        // التعامل مع نقر عناصر المجلد في القائمة الجانبية
        $('.email-filter-folders li').on('click', function(e) {
            e.preventDefault();

            var folder = $(this).data('target');

            // إزالة فئة 'active' من جميع المجلدات وإضافتها إلى المجلد الذي تم النقر عليه
            $('.email-filter-folders li').removeClass('active');
            $(this).addClass('active');

            // جلب وعرض الرسائل للمجلد المحدد
            fetchEmails(folder);

            // إخفاء عرض البريد الإلكتروني عند تغيير المجلد
            $('#app-email-view').removeClass('show');
        });

        // التعامل مع تحديد كل الرسائل
        $('#email-select-all').on('change', function() {
            $('.email-list-item-input').prop('checked', $(this).is(':checked'));
            toggleBulkDeleteButton();
        });

        // التعامل مع تحديد أو إلغاء تحديد رسالة
        $('.app-emails-list').on('change', '.email-list-item-input', function() {
            toggleBulkDeleteButton();
        });

        // دالة لتبديل ظهور زر الحذف الجماعي
        function toggleBulkDeleteButton() {
            var selectedCount = $('.email-list-item-input:checked').length;
            if (selectedCount > 0) {
                $('#bulk-delete-btn').addClass('active');
                $('#bulk-delete-btn .selected-count').text(selectedCount);
            } else {
                $('#bulk-delete-btn').removeClass('active');
                $('#bulk-delete-btn .selected-count').text(0);
            }
        }

        // التعامل مع نقر زر الحذف الجماعي
        $('#bulk-delete-btn').on('click', function() {
            var selectedEmails = $('.email-list-item-input:checked').closest('.email-list-item').map(function() {
                return $(this).data('email-id');
            }).get();

            if (selectedEmails.length === 0) {
                toastr.error('يرجى تحديد رسائل لحذفها.');
                return;
            }

            Swal.fire({
                title: 'هل أنت متأكد؟'
                , text: "لن تتمكن من استعادة هذه الرسائل!"
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonColor: '#3085d6'
                , cancelButtonColor: '#d33'
                , confirmButtonText: 'نعم، احذفها!'
                , cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    // إظهار مؤشر التحميل
                    $loadingSpinner.show();

                    $.ajax({
                        url: '/emails/delete'
                        , method: 'POST'
                        , data: {
                            emailIds: selectedEmails
                            , _method: 'DELETE'
                        }
                        , success: function(response) {
                            Swal.fire(
                                'تم الحذف!'
                                , 'تم حذف الرسائل بنجاح.'
                                , 'success'
                            );
                            // تحديث قائمة الرسائل
                            var currentFolder = $('.email-filter-folders li.active').data('target') || 'inbox';
                            fetchEmails(currentFolder);
                            // إعادة تعيين اختيار جميع الرسائل
                            $('#email-select-all').prop('checked', false);
                            // إخفاء زر الحذف الجماعي
                            $('#bulk-delete-btn').removeClass('active');
                            $('#bulk-delete-btn .selected-count').text(0);
                        }
                        , error: function(xhr) {
                            Swal.fire(
                                'فشل!'
                                , 'فشل في حذف الرسائل.'
                                , 'error'
                            );
                        }
                        , complete: function() {
                            // إخفاء مؤشر التحميل
                            $loadingSpinner.hide();
                        }
                    });
                }
            });
        });

        // التعامل مع نقر عنصر البريد لعرض تفاصيله
        $('.app-emails-list').on('click', '.email-list-item', function(e) {
            // منع التفعيل عند النقر على صندوق الاختيار أو أزرار الإجراءات
            if ($(e.target).is('input') || $(e.target).is('.btn') || $(e.target).closest('.btn').length) {
                return;
            }

            var emailId = $(this).data('email-id');

            // إظهار مؤشر التحميل
            $loadingSpinner.show();

            // طلب AJAX لجلب تفاصيل البريد الإلكتروني
            $.ajax({
                url: '/emails/' + emailId
                , method: 'GET'
                , success: function(data) {
                    // بناء محتوى عرض البريد الإلكتروني
                    var emailView = `
                        <div class="card-body pt-5">
                            <h5 class="fw-medium">${data.subject}</h5>
                            <p>من: ${data.from.name ? data.from.name : data.from.address} &lt;${data.from.address}&gt;</p>
                            <p>إلى: ${data.toRecipients.map(r => r.emailAddress.address).join(', ')}</p>
                            <div>${data.body}</div>
                            <hr>
                            <button class="btn btn-primary btn-reply" data-email-id="${data.id}">رد</button>
                        </div>
                    `;
                    // تحديث المحتوى في العنصر الصحيح
                    $('#app-email-view .app-email-view-content').html(emailView);
                    // إظهار قسم عرض البريد الإلكتروني
                    $('#app-email-view').addClass('show');
                }
                , error: function() {
                    toastr.error('فشل في جلب تفاصيل البريد الإلكتروني.');
                }
                , complete: function() {
                    // إخفاء مؤشر التحميل
                    $loadingSpinner.hide();
                }
            });
        });

        // التعامل مع إرسال البريد الإلكتروني
        $('.email-compose-form').on('submit', function(e) {
            e.preventDefault();

            // جمع بيانات النموذج
            var toEmail = $('#email-to').val().trim();
            var subject = $('#email-subject').val().trim();
            var body = quill.root.innerHTML;
            var cc = $('#email-cc').val().trim();
            var bcc = $('#email-bcc').val().trim();
            var attachments = $('#attach-file')[0].files;

            // التحقق من صحة حقل "إلى"
            if (!validateEmail(toEmail)) {
                toastr.error('يرجى إدخال عنوان بريد إلكتروني صالح في حقل "إلى".');
                return;
            }

            // تجهيز بيانات الإرسال
            var formData = new FormData();
            formData.append('subject', subject);
            formData.append('body', body);
            formData.append('to', toEmail);
            formData.append('cc', cc);
            formData.append('bcc', bcc);

            // إضافة المرفقات
            $.each(attachments, function(i, file) {
                formData.append('attachments[]', file);
            });

            // إظهار مؤشر التحميل
            $loadingSpinner.show();

            // إرسال طلب AJAX
            $.ajax({
                url: '{{ route("emails.send") }}'
                , method: 'POST'
                , data: formData
                , processData: false
                , contentType: false
                , success: function(response) {
                    toastr.success(response.success);
                    // إغلاق المودال
                    $('#emailComposeSidebar').modal('hide');
                    // إعادة تحميل قائمة الرسائل
                    var currentFolder = $('.email-filter-folders li.active').data('target') || 'inbox';
                    fetchEmails(currentFolder);
                    // إعادة تعيين النموذج
                    $('.email-compose-form')[0].reset();
                    quill.setContents([]);
                }
                , error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        toastr.error(errorMessage);
                    } else if (xhr.status === 401) {
                        toastr.error(xhr.responseJSON.error);
                        window.location.href = '{{ route("dashboard") }}';
                    } else {
                        toastr.error('فشل في إرسال البريد الإلكتروني.');
                    }
                }
                , complete: function() {
                    // إخفاء مؤشر التحميل
                    $loadingSpinner.hide();
                }
            });
        });

        // التعامل مع الرد على البريد الإلكتروني بواجهة مدمجة
        $('#app-email-view').on('click', '.btn-reply', function() {
            var emailId = $(this).data('email-id');

            // التحقق من عدم وجود نموذج رد مسبق
            if ($('.email-reply').length === 0) {
                // إنشاء نموذج الرد داخل واجهة عرض البريد الإلكتروني
                var replyForm = `
                    <div class="email-reply card mt-4 mx-sm-6 mx-3 mb-4">
                        <div class="card-body pt-0 ps-3">
                            <div class="d-flex justify-content-start">
                                <div class="email-editor-toolbar border-0 w-100 px-0 pb-4">
                                    <span class="ql-formats me-0">
                                        <button class="ql-bold"></button>
                                        <button class="ql-italic"></button>
                                        <button class="ql-underline"></button>
                                        <button class="ql-list" value="ordered"></button>
                                        <button class="ql-list" value="bullet"></button>
                                        <button class="ql-link"></button>
                                        <button class="ql-image"></button>
                                    </span>
                                </div>
                            </div>
                            <form class="email-reply-form">
                                @csrf
                                <div class="email-reply-editor"></div>
                                <div class="d-flex justify-content-end align-items-center mt-4">
                                    <label for="reply-attach-file" class="cursor-pointer btn btn-text-secondary text-secondary me-4">
                                        <i class="ti ti-paperclip ti-16px text-heading me-2"></i>
                                        <span class="align-middle">المرفقات</span>
                                    </label>
                                    <input type="file" name="attachments[]" class="d-none" id="reply-attach-file" multiple>
                                    <button type="submit" class="btn btn-primary">إرسال <i class="ti ti-send ti-xs scaleX-n1-rtl ms-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                // إضافة نموذج الرد بعد محتوى البريد الحالي
                $('#app-email-view .app-email-view-content').append(replyForm);
                // تهيئة محرر Quill للرد
                quillReply = new Quill('.email-reply-editor', {
                    theme: 'snow'
                    , placeholder: 'اكتب ردك هنا...'
                });
            }
        });

        // التعامل مع إرسال الرد
        $('#app-email-view').on('submit', '.email-reply-form', function(e) {
            e.preventDefault();

            var emailId = $('.btn-reply').data('email-id');
            var replyBody = quillReply.root.innerHTML;
            var attachments = $('#reply-attach-file')[0].files;

            // التحقق من صحة الرد
            if (replyBody.trim() === '') {
                toastr.error('يرجى كتابة رد قبل الإرسال.');
                return;
            }

            // تجهيز بيانات الإرسال
            var formData = new FormData();
            formData.append('message_id', emailId);
            formData.append('comment', replyBody);

            // إضافة المرفقات
            $.each(attachments, function(i, file) {
                formData.append('attachments[]', file);
            });

            // إظهار مؤشر التحميل
            $loadingSpinner.show();

            $.ajax({
                url: '{{ route("emails.reply") }}'
                , method: 'POST'
                , data: formData
                , processData: false
                , contentType: false
                , success: function(response) {
                    toastr.success(response.success);
                    // إزالة نموذج الرد
                    $('.email-reply').remove();
                    // تحديث حالة الرسالة كمقروءة
                    var currentFolder = $('.email-filter-folders li.active').data('target') || 'inbox';
                    fetchEmails(currentFolder);
                }
                , error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        toastr.error(errorMessage);
                    } else if (xhr.status === 401) {
                        toastr.error(xhr.responseJSON.error);
                        window.location.href = '{{ route("dashboard") }}';
                    } else {
                        toastr.error('فشل في الرد على البريد الإلكتروني.');
                    }
                }
                , complete: function() {
                    // إخفاء مؤشر التحميل
                    $loadingSpinner.hide();
                }
            });
        });

        // وظيفة للتحقق من صحة البريد الإلكتروني
        function validateEmail(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@(([^<>()[\]\\.,;:\s@"]+\.)+[^<>()[\]\\.,;:\s@"]{2,})$/i;
            return re.test(String(email).toLowerCase());
        }

        // التعامل مع عرض/إخفاء حقول CC و BCC
        $('.email-compose-toggle-cc').on('click', function(e) {
            e.preventDefault();
            $('.email-compose-cc').toggleClass('d-none');
        });

        $('.email-compose-toggle-bcc').on('click', function(e) {
            e.preventDefault();
            $('.email-compose-bcc').toggleClass('d-none');
        });

        // التعامل مع حذف البريد الإلكتروني باستخدام SweetAlert
        $('.app-emails-list').on('click', '.email-delete', function(e) {
            e.stopPropagation();
            var emailId = $(this).closest('.email-list-item').data('email-id');

            Swal.fire({
                title: 'هل أنت متأكد؟'
                , text: "لن تتمكن من استعادة هذه الرسالة!"
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonColor: '#3085d6'
                , cancelButtonColor: '#d33'
                , confirmButtonText: 'نعم، احذفها!'
                , cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    // إظهار مؤشر التحميل
                    $loadingSpinner.show();

                    $.ajax({
                        url: '/emails/delete/' + emailId
                        , method: 'DELETE'
                        , success: function(response) {
                            Swal.fire(
                                'تم الحذف!'
                                , 'تم حذف الرسالة بنجاح.'
                                , 'success'
                            );
                            // تحديث قائمة الرسائل
                            var currentFolder = $('.email-filter-folders li.active').data('target') || 'inbox';
                            fetchEmails(currentFolder);
                            // إعادة تعيين اختيار جميع الرسائل إذا كانت محددة
                            $('#email-select-all').prop('checked', false);
                            toggleBulkDeleteButton();
                        }
                        , error: function(xhr) {
                            Swal.fire(
                                'فشل!'
                                , 'فشل في حذف البريد الإلكتروني.'
                                , 'error'
                            );
                        }
                        , complete: function() {
                            // إخفاء مؤشر التحميل
                            $loadingSpinner.hide();
                        }
                    });
                }
            });
        });

        // التعامل مع الحذف الجماعي للبريد الإلكتروني من خلال القائمة المنسدلة
        $('.dropdown-menu').on('click', '.delete-selected', function(e) {
            e.preventDefault();
            var selectedEmails = $('.email-list-item-input:checked').closest('.email-list-item').map(function() {
                return $(this).data('email-id');
            }).get();

            if (selectedEmails.length === 0) {
                toastr.error('يرجى تحديد رسائل لحذفها.');
                return;
            }

            Swal.fire({
                title: 'هل أنت متأكد؟'
                , text: "لن تتمكن من استعادة هذه الرسائل!"
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonColor: '#3085d6'
                , cancelButtonColor: '#d33'
                , confirmButtonText: 'نعم، احذفها!'
                , cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    // إظهار مؤشر التحميل
                    $loadingSpinner.show();

                    $.ajax({
                        url: '/emails/delete'
                        , method: 'POST'
                        , data: {
                            emailIds: selectedEmails
                            , _method: 'DELETE'
                        }
                        , success: function(response) {
                            Swal.fire(
                                'تم الحذف!'
                                , 'تم حذف الرسائل بنجاح.'
                                , 'success'
                            );
                            // تحديث قائمة الرسائل
                            var currentFolder = $('.email-filter-folders li.active').data('target') || 'inbox';
                            fetchEmails(currentFolder);
                            // إعادة تعيين اختيار جميع الرسائل
                            $('#email-select-all').prop('checked', false);
                            toggleBulkDeleteButton();
                        }
                        , error: function(xhr) {
                            Swal.fire(
                                'فشل!'
                                , 'فشل في حذف الرسائل.'
                                , 'error'
                            );
                        }
                        , complete: function() {
                            // إخفاء مؤشر التحميل
                            $loadingSpinner.hide();
                        }
                    });
                }
            });
        });

    });

</script>
@endsection

@section('content')
<!-- مؤشر التحميل -->
<div id="loading-spinner">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">جارٍ التحميل...</span>
    </div>
</div>

<div class="app-email card">
    <div class="row g-0">
        <!-- الشريط الجانبي للبريد الإلكتروني -->
        <div class="col app-email-sidebar border-end flex-grow-0" id="app-email-sidebar">
            <div class="btn-compost-wrapper d-grid">
                <button class="btn btn-primary btn-compose" data-bs-toggle="modal" data-bs-target="#emailComposeSidebar" id="emailComposeSidebarLabel">إرسال رسالة</button>
            </div>
            <!-- فلاتر البريد الإلكتروني -->
            <div class="email-filters pt-4 pb-2">
                <!-- فلاتر البريد الإلكتروني: المجلدات -->
                <ul class="email-filter-folders list-unstyled">
                    <li class="active d-flex justify-content-between align-items-center mb-1" data-target="inbox">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-mail"></i>
                            <span class="align-middle ms-2">صندوق الوارد</span>
                        </a>
                        <div class="badge bg-label-primary rounded-pill">{{ $counts['inbox'] ?? 0 }}</div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-1" data-target="sentitems">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-send"></i>
                            <span class="align-middle ms-2">المرسلة</span>
                        </a>
                        <div class="badge bg-label-primary rounded-pill">{{ $counts['sentitems'] ?? 0 }}</div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-1" data-target="drafts">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-edit"></i>
                            <span class="align-middle ms-2">المسودات</span>
                        </a>
                        <div class="badge bg-label-warning rounded-pill">{{ $counts['drafts'] ?? 0 }}</div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-1" data-target="junkemail">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-alert-octagon"></i>
                            <span class="align-middle ms-2">البريد العشوائي</span>
                        </a>
                        <div class="badge bg-label-danger rounded-pill">{{ $counts['junkemail'] ?? 0 }}</div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-1" data-target="deleteditems">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-trash"></i>
                            <span class="align-middle ms-2">سلة المهملات</span>
                        </a>
                        <div class="badge bg-label-danger rounded-pill">{{ $counts['deleteditems'] ?? 0 }}</div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-1" data-target="archive">
                        <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                            <i class="ti ti-archive"></i>
                            <span class="align-middle ms-2">الأرشيف</span>
                        </a>
                        <div class="badge bg-label-primary rounded-pill">{{ $counts['archive'] ?? 0 }}</div>
                    </li>
                </ul>
            </div>
            <!--/ فلاتر البريد الإلكتروني -->
        </div>
        <!--/ الشريط الجانبي للبريد الإلكتروني -->

        <!-- قائمة البريد الإلكتروني -->
        <div class="col app-emails-list">
            <div class="card shadow-none border-0 rounded-0">
                <div class="card-body emails-list-header p-3 py-2">
                    <!-- قائمة البريد الإلكتروني: البحث والإجراءات -->
                    <div class="d-flex justify-content-between align-items-center px-3 mt-2">
                        <div class="d-flex align-items-center w-100">
                            <i class="ti ti-menu-2 ti-lg cursor-pointer d-block d-lg-none me-4 mb-4" data-bs-toggle="sidebar" data-target="#app-email-sidebar" data-overlay></i>
                            <div class="mb-4 w-100">
                                <div class="input-group input-group-merge shadow-none">
                                    <span class="input-group-text border-0 ps-0 py-0" id="email-search">
                                        <i class="ti ti-search ti-lg"></i>
                                    </span>
                                    <input type="text" class="form-control email-search-input border-0 py-0" placeholder="البحث في البريد" aria-label="البحث في البريد" aria-describedby="email-search">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mx-n3 emails-list-header-hr mb-2">
                    <!-- قائمة البريد الإلكتروني: الإجراءات -->
                    <div class="d-flex justify-content-between align-items-center ps-1">
                        <div class="d-flex align-items-center">
                            <div class="form-check mb-0 ms-2">
                                <input class="form-check-input" type="checkbox" id="email-select-all">
                                <label class="form-check-label" for="email-select-all"></label>
                            </div>
                            <div class="form-check mb-0 ms-2">
                                <!-- زر الحذف الجماعي -->
                                <button id="bulk-delete-btn" class="btn btn-defult" title="حذف الرسائل المحددة">
                                    <i class="ti ti-trash ti-md"></i>
                                    <span class="selected-count badge bg-primary ms-1">0</span>
                                </button>
                            </div>
                        </div>
                        {{-- <div class="d-flex align-items-center">
                            <div class="form-check mb-0 ms-2">
                              <!-- زر الحذف الجماعي -->
                              <button id="bulk-delete-btn" class="btn btn-light" title="حذف الرسائل المحددة">
                                <i class="ti ti-trash ti-md"></i>
                                <span class="selected-count badge bg-primary ms-1">0</span>
                            </button>
                        </div>
                    </div> --}}
                        <div class="d-flex align-items-center">
                            <span class="btn btn-icon btn-text-secondary rounded-pill me-1">
                                <i class="ti ti-refresh ti-md scaleX-n1-rtl cursor-pointer email-refresh"></i>
                            </span>
                            <div class="dropdown me-1">
                                <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button" id="emailsActions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="emailsActions">
                                    <a class="dropdown-item mark-as-read">تحديد كمقروء</a>
                                    <a class="dropdown-item mark-as-unread">تحديد كغير مقروء</a>
                                    <a class="dropdown-item delete-selected">حذف</a>
                                    <a class="dropdown-item archive-selected">أرشيف</a>
                                </div>
                            </div>
                            <!-- زر الحذف الجماعي -->
                            {{-- <button id="bulk-delete-btn" class="btn btn-light btn-icon rounded-pill me-1" title="حذف الرسائل المحددة">
                                <i class="ti ti-trash ti-md"></i>
                                <span class="selected-count badge bg-primary ms-1">0</span>
                            </button> --}}
                        </div>
                    </div>
                </div>
                <hr class="container-m-nx m-0">
                <!-- قائمة البريد الإلكتروني: العناصر -->
                <div class="email-list pt-0">
                    <ul class="list-unstyled m-0">
                        <!-- سيتم تحديث قائمة البريد هنا بواسطة AJAX -->
                    </ul>
                    <ul class="list-unstyled m-0">
                        <li class="email-list-empty text-center"></li>
                    </ul>
                </div>
            </div>
            <div class="app-overlay"></div>
        </div>
        <!-- /قائمة البريد الإلكتروني -->

        <!-- عرض البريد الإلكتروني -->
        <div class="col app-email-view flex-grow-0 bg-lighter" id="app-email-view">
            <div class="card shadow-none border-0 rounded-0 app-email-view-header p-5 pt-md-4 py-2">
                <!-- عرض البريد الإلكتروني: شريط العنوان -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="ms-sm-2 me-4"><i class="ti ti-chevron-left ti-md cursor-pointer" data-bs-toggle="sidebar" data-target="#app-email-view"></i></span>
                        <h6 class="text-truncate mb-0 me-2 fw-normal">عرض البريد الإلكتروني</h6>
                    </div>
                    <!-- عرض البريد الإلكتروني: شريط الإجراءات -->
                    <div class="d-flex align-items-center">
                        <span class="btn btn-icon btn-text-secondary rounded-pill p-0 me-2 text-muted">
                            <i class='ti ti-chevron-left ti-md'></i>
                        </span>
                        <span class="btn btn-icon btn-text-secondary rounded-pill p-0">
                            <i class="ti ti-chevron-right ti-md"></i>
                        </span>
                    </div>
                </div>
                <hr class="app-email-view-hr mx-n5 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="btn btn-icon btn-text-secondary rounded-pill me-1 email-delete"><i class='ti ti-trash ti-md cursor-pointer'></i></span>
                        <span class="btn btn-icon btn-text-secondary rounded-pill me-1"><i class='ti ti-mail-opened ti-md cursor-pointer' data-bs-toggle="sidebar" data-target="#app-email-view"></i></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="btn btn-icon btn-text-secondary rounded-pill p-0">
                            <i class='ti ti-star ti-md'></i>
                        </span>
                    </div>
                </div>
            </div>
            <hr class="m-0">
            <!-- عرض البريد الإلكتروني: المحتوى-->
            <div class="app-email-view-content py-4">
                <!-- سيتم إضافة محتوى البريد هنا بواسطة AJAX -->
            </div>
        </div>
        <!-- عرض البريد الإلكتروني -->
    </div>

    <!-- إنشاء رسالة -->
    <div class="app-email-compose modal fade" id="emailComposeSidebar" tabindex="-1" aria-labelledby="emailComposeSidebarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-0">
                <div class="modal-header py-3 justify-content-between">
                    <h5 class="modal-title text-body fs-5">إرسال رسالة جديدة</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                            <i class='ti ti-x'></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body flex-grow-1 pb-sm-0 p-5 py-2">
                    <form class="email-compose-form">
                        @csrf
                        <!-- حقل "إلى" كإدخال واحد -->
                        <div class="email-compose-to d-flex justify-content-between align-items-center mb-3">
                            <label class="fw-medium mb-1 text-muted" for="email-to">إلى:</label>
                            <div class="flex-grow-1 mx-2">
                                <input type="email" class="form-control" id="email-to" name="to" placeholder="someone@example.com" required>
                            </div>
                        </div>

                        <!-- حقول CC و BCC -->
                        <div class="email-compose-cc d-none mb-3">
                            <div class="d-flex align-items-center">
                                <label for="email-cc" class="fw-medium text-muted">نسخة:</label>
                                <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-cc" placeholder="someone@example.com">
                            </div>
                        </div>
                        <div class="email-compose-bcc d-none mb-3">
                            <div class="d-flex align-items-center">
                                <label for="email-bcc" class="fw-medium text-muted">نسخة مخفية:</label>
                                <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-bcc" placeholder="someone@example.com">
                            </div>
                        </div>

                        <!-- أزرار إظهار حقول CC و BCC -->
                        <div class="d-flex justify-content-end mb-2 d-none">
                            <a href="#" class="email-compose-toggle-cc me-3">إضافة CC</a>
                            <a href="#" class="email-compose-toggle-bcc">إضافة BCC</a>
                        </div>

                        <hr class="mx-n5 my-3">

                        <div class="email-compose-subject d-flex align-items-center mb-3">
                            <label for="email-subject" class="fw-medium text-muted">الموضوع:</label>
                            <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-subject" name="subject" required>
                        </div>

                        <div class="email-compose-message mb-3">
                            <div class="d-flex justify-content-end mx-n1 mb-2">
                                <div class="email-editor-toolbar border-0 w-100 px-0 pb-4">
                                    <span class="ql-formats me-0">
                                        <button class="ql-bold"></button>
                                        <button class="ql-italic"></button>
                                        <button class="ql-underline"></button>
                                        <button class="ql-list" value="ordered"></button>
                                        <button class="ql-list" value="bullet"></button>
                                        <button class="ql-link"></button>
                                        <button class="ql-image"></button>
                                    </span>
                                </div>
                            </div>
                            <div class="email-editor border-0 mx-n5"></div>
                        </div>

                        <hr class="mx-n5 my-3">

                        <div class="email-compose-actions d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <label for="attach-file" class="btn btn-sm btn-icon btn-text-secondary rounded-pill">
                                    <i class="ti ti-paperclip cursor-pointer"></i>
                                </label>
                                <input type="file" name="attachments[]" class="d-none" id="attach-file" multiple>
                            </div>
                            <button type="submit" class="btn btn-primary">إرسال <i class="ti ti-send ti-xs scaleX-n1-rtl ms-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /إنشاء رسالة -->
</div>
@endsection
