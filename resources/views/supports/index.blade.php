@extends('layouts.layoutMaster')

@section('title', 'قائمة التذاكر ')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">قائمة التذاكر </a>
        <i class="ti ti-star favorite-icon" data-page-name="قائمة التذاكر" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // تهيئة Select2 لحقول الفلترة
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#users-table').DataTable({
                // كود تحريك النص
                "createdRow": function(row, data, dataIndex) {
                    // البحث فقط عن الخلايا التي تحتوي على فئة 'table-ellipsis' وتغليف النص داخل span
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                        // تغليف النص داخل عنصر span فقط في الحقول التي تحتوي على 'table-ellipsis'
                    });
                },
                // كود تحريك النص
                processing: true,
                serverSide: true,

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'user_id',
                        name: 'user_id',
                        className: 'table-ellipsis',


                    },
                    {
                        data: 'ticket_number',
                        name: 'ticket_number',


                    },
                    {
                        data: 'ticket_classification',
                        name: 'ticket_classification',
                        className: 'table-ellipsis',


                    },

                    {
                        data: 'priority',
                        name: 'priority',

                    },
                    {
                        data: 'status',
                        name: 'status',

                    },
                    {
                        data: 'details',
                        name: 'details'


                    },
                    {
                        data: 'attachment',
                        name: 'attachment',

                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],


                dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"fB>>rt<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>',
                buttons: [{
                        extend: 'collection',
                        className: 'btn btn-export btn-sm',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            },
                            {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            {
                                extend: 'print',
                                text: 'طباعة'
                            }
                        ]
                    },

                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true,
                order: [
                    [0, 'desc']
                ],
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-role').change(function() {
                table.draw();
            });

            // التعامل مع عرض الملاحظات في الموديل
            $('#users-table').on('click', '.view-note-btn', function() {
                var note = $(this).data('note'); // الحصول على الملاحظات من data-note
                $('#noteContent').text(note); // تعبئة محتوى الموديل بالملاحظات
            });
        });
    </script>






@endsection

@section('content')
    <!-- عرض قائمة الأدوار كما كانت سابقًا -->


    <!-- عرض الفلاتر و الجدول -->
    <div class="card">
        <div class="card-body">

            <table id="users-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>المستخدم</th>
                        <th>رقم التذكرة</th>
                        <th>تصنيف التذكرة</th>

                        <th>الاولوية</th>
                        <th>الحالة</th>
                        <th>التذكرة</th>
                        <th>المرفق</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- موديل عرض الملاحظات -->
    <div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">ملاحظات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p id="noteContent"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    {{--  Modal reply  --}}
    <!-- مودال الرد -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="replyForm">
                @csrf
                <input type="hidden" id="supportId" name="support_id" value="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="replyModalLabel">الرد على التذكرة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <!-- حقل الحالة -->
                        <div class="mb-3">
                            <label for="statusMessage" class="form-label">حالة البلاغ</label>
                            <select class="form-select" id="statusMessage" name="status" required>
                                <option selected value="قيد الانتظار">قيد الانتظار</option>
                                <option value="تحت المعالجة">تحت المعالجة</option>
                                <option value="تمت المعالجة">تمت المعالجة</option>
                                <option value="مغلق">مغلق</option>
                            </select>
                            <div class="invalid-feedback">
                                الرجاء اختيار حالة البلاغ.
                            </div>
                        </div>

                        <!-- حقل الرد -->
                        <div class="mb-3">
                            <label for="replyMessage" class="form-label">الرد</label>
                            <textarea class="form-control" id="replyMessage" name="reply" rows="4" required></textarea>
                            <div class="invalid-feedback">
                                الرجاء إدخال الرد.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إرسال الرد</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- مودال عرض الرد -->
    <div class="modal fade" id="viewReplyModal" tabindex="-1" aria-labelledby="viewReplyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReplyModalLabel">عرض الرد</h5>

                </div>
                <div class="modal-body" id="replyContent">
                    <!-- سيتم عرض الرد هنا -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>



    <script>
        // دالة لفتح مودال الرد وتمرير معرف الصف
        function openReplyModal(id) {
            $('#supportId').val(id); // تعيين معرف الصف في الحقل المخفي
            $('#replyMessage').val(''); // مسح حقل الرد
            $('#statusMessage').val(''); // مسح حقل الرد
            $('#replyModal').modal('show'); // فتح المودال
        }

        $(document).ready(function() {
            // التعامل مع تقديم نموذج الرد
            $('#replyForm').on('submit', function(e) {
                e.preventDefault(); // منع إعادة تحميل الصفحة

                // جلب البيانات من النموذج
                var formData = {
                    support_id: $('#supportId').val(),
                    reply: $('#replyMessage').val(),
                    status: $('#statusMessage').val(),
                    _token: '{{ csrf_token() }}' // تضمين التوكن CSRF
                };

                // إرسال البيانات عبر AJAX

            });

            // إزالة فئة الخطأ عند كتابة المستخدم في حقل الرد
            $('#replyMessage').on('input', function() {
                $(this).removeClass('is-invalid');
            });
        });

        function viewReply(id) {
            // اجلب الرد باستخدام AJAX
            $.ajax({
                url: '/get-reply/' + id, // URL لاسترجاع الرد، تأكد من إعداد هذا الرابط في Laravel
                method: 'GET',
                success: function(response) {
                    // وضع الرد في المودال
                    $('#replyContent').html(response.reply);
                    // عرض المودال
                    $('#viewReplyModal').modal('show');
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء تحميل الرد.');
                }
            });
        }
    </script>

@endsection
