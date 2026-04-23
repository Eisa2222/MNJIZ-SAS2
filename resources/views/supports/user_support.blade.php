@extends('layouts.layoutMaster')

@section('title', 'الدعم الفني')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">الدعم الفني  </a>
        <i class="ti ti-star favorite-icon" data-page-name="الدعم الفني" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])

@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
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
            }
            , allowClear: true
            , width: '100%'
            , language: 'ar'
            , dir: 'rtl'
        });

        var table = $('#users-support').DataTable({

            // كود تحريك النص
            "createdRow": function(row, data, dataIndex) {
                // البحث فقط عن الخلايا التي تحتوي على فئة 'table-ellipsis' وتغليف النص داخل span
                $(row).find('td.table-ellipsis').each(function() {
                    var cellText = $(this).text();
                    $(this).html('<div class="cell-content"><span>' + cellText + '</span></div>');
                    // تغليف النص داخل عنصر span فقط في الحقول التي تحتوي على 'table-ellipsis'
                });
            },
            // كود تحريك النص
            processing: true
            , serverSide: true,

            columns: [{
                    data: 'DT_RowIndex'
                    , name: 'id'
                    , orderable: true
                    , searchable: false
                },

                {
                    data: 'ticket_number'
                    , name: 'ticket_number'
                    , className: 'table-ellipsis',


                }
                , {
                    data: 'details'
                    , name: 'details'

                }
                , {
                    data: 'priority'
                    , name: 'priority',

                },


                {
                    data: 'status'
                    , name: 'status'
                }
                , {
                    data: 'created_at'
                    , name: 'created_at'
                }
                , {
                    data: 'action'
                    , name: 'action'
                    , orderable: false
                    , searchable: false
                }
            ],


            dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"fB>>rt<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>'
            , buttons: [{
                    extend: 'collection'
                    , className: 'btn btn-export btn-sm'
                    , text: 'الإجراءات'
                    , buttons: [{
                            extend: 'copy'
                            , text: 'نسخ'
                        }
                        , {
                            extend: 'excel'
                            , text: 'إكسل'
                        }
                        , {
                            extend: 'pdf'
                            , text: 'PDF'
                        }
                        , {
                            extend: 'print'
                            , text: 'طباعة'
                        }
                    ]
                }
                , {
                    text: '<i class="fas fa-plus-circle me-1"></i> فتح تذكرة جديدة'
                    , className: 'btn btn-primary btn-add'
                    , action: function(e, dt, node, config) {
                        window.location.href = "{{ route('supports.create') }}";
                    }
                }

            ]
            , language: {
                url: "{{ asset('assets/json/ar.json') }}"
            }
            , responsive: true
            , order: [
                [0, 'desc']
            ]
        , });

        // تحديث الجدول عند تغيير الفلاتر


        // التعامل مع عرض الملاحظات في الموديل
        $('#users-support').on('click', '.view-note-btn', function() {
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

        <table id="users-support" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>م</th>
                    <th>رقم التذكرة</th>
                    <th>التذكرة</th>
                    <th>الاولوية</th>
                    {{-- <th>الوصف</th> --}}
                    {{-- <th>المرفق</th> --}}
                    <th>الحالة</th>
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







@endsection
