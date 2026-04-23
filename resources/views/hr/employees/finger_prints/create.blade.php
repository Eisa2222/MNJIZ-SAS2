@extends('layouts.layoutMaster')

@section('title', 'إضافة موظف جديد')

@section('breadcrumb')

    <li><a href="{{ route('hr.employees.index') }}"> الموظفين</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة موظف جديد</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة موظف جديد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('content')
    <div class="container">
        <h2>بصمات المستخدم: {{ $user->name }}</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <button id="add-fingerprint-btn" class="btn btn-primary mb-3">إضافة بصمة جديدة</button>

        <!-- Modal لالتقاط البصمة -->
        <div class="modal fade" id="fingerprintModal" tabindex="-1" role="dialog" aria-labelledby="fingerprintModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fingerprintModalLabel">التقاط بصمة جديدة</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- هنا سيتم إدراج واجهة تفاعل مع جهاز BioStation -->
                        <button id="capture-fingerprint" class="btn btn-success">التقاط البصمة</button>
                        <div id="fingerprint-status" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

       
    </div>

    <!-- تحميل مكتبة jQuery و Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- سكريبت لتشغيل المودال والتفاعل مع BioStation -->
    <script>
        $(document).ready(function(){
            $('#add-fingerprint-btn').click(function(){
                $('#fingerprintModal').modal('show');
            });

            $('#capture-fingerprint').click(function(){
                $('#fingerprint-status').html('جاري التقاط البصمة...');
                
                // هنا تحتاج إلى دمج طريقة تفاعل مع جهاز BioStation
                // تعتمد هذه الخطوة على طريقة التفاعل التي يدعمها جهازك

                // مثال افتراضي باستخدام API
                $.ajax({
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response){
                        if(response.success){
                            $('#fingerprint-status').html('<span class="text-success">تم التقاط البصمة بنجاح!</span>');
                            // تحديث الجدول أو إعادة تحميل الصفحة
                            setTimeout(function(){
                                location.reload();
                            }, 2000);
                        } else {
                            $('#fingerprint-status').html('<span class="text-danger">فشل في التقاط البصمة.</span>');
                        }
                    },
                    error: function(){
                        $('#fingerprint-status').html('<span class="text-danger">حدث خطأ أثناء التقاط البصمة.</span>');
                    }
                });
            });
        });
    </script>
@endsection
