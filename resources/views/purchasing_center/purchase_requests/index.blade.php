@extends('layouts.layoutMaster')

@section('title', 'طلبات المشتريات')

@section('breadcrumb')
    <li><a href="#">المشتريات</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> طلبات المشتريات</a>
        <i class="ti ti-star favorite-icon" data-page-name=" طلبات المشتريات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {

            // التعامل مع زر تغيير الحالة
            $(document).on('click', '.change-status-btn', function() {
                var requestId = $(this).data('id');
                var currentStatus = $(this).data('current');
                var rejectionReason = $(this).data('reason') || '';

                // تعيين القيم في المودال
                $('#request-id').val(requestId);
                $('#status-change').val(currentStatus);
                $('#rejection_reason').val(rejectionReason);

                // إظهار أو إخفاء حقل سبب الرفض بناءً على الحالة المحددة
                toggleRejectionReasonField(currentStatus);

                // فتح المودال
                var modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
                modal.show();
            });

            // التعامل مع تغيير الحالة في المودال
            $('#status-change').on('change', function() {
                var newStatus = $(this).val();

                // إذا تم تغيير الحالة من "مرفوضة" إلى حالة أخرى، فرغ حقل سبب الرفض
                if (newStatus !== 'rejected') {
                    $('#rejection_reason').val('');
                }

                toggleRejectionReasonField(newStatus);
            });

            // دالة لإظهار أو إخفاء حقل سبب الرفض
            function toggleRejectionReasonField(status) {
                if (status === 'rejected') {
                    $('.rejection-reason-container').removeClass('d-none');
                } else {
                    $('.rejection-reason-container').addClass('d-none');
                }
            }
            // التعامل مع زر تأكيد تغيير الحالة
            $(document).on('click', '#confirm-change', function() {
                var requestId = $('#request-id').val();
                var newStatus = $('#status-change').val();
                var rejectionReason = $('#rejection_reason').val();

                // التحقق من إدخال سبب الرفض إذا كانت الحالة هي "مرفوضة"
                if (newStatus === 'rejected' && !rejectionReason.trim()) {
                    toastr.error('يرجى إدخال سبب الرفض');
                    return;
                }

                updateRequestStatus(requestId, newStatus, rejectionReason);

                // إغلاق المودال
                $('#changeStatusModal').modal('hide');
            });

            // دالة مساعدة لتحديث حالة الطلب
            function updateRequestStatus(requestId, status, reason = '') {
                $.ajax({
                    url: "{{ route('purchasing-center.purchase-requests.update-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: status,
                        request_id: requestId,
                        rejection_reason: reason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('تم تحديث الحالة بنجاح');

                            // تحديث الجدول بدون إعادة تحميل الصفحة
                            $('#request-table').DataTable().ajax.reload(null, false);
                        } else {
                            toastr.error('حدث خطأ أثناء تحديث الحالة');
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء تحديث الحالة');
                    }
                });
            }
        });
    </script>
    @vite(['resources/assets/js/electronic-services/purchase-requests.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('content')
    <div class="row g-4 mb-4">
        <!-- بطاقات الإحصائيات -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">طلبات المشتريات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalRequests }}</h4>
                            </div>
                            <small class="mb-0">إجمالي طلبات المشتريات</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-stack ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">بانتظار الموافقة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $pendingRequests }}</h4>
                            </div>
                            <small class="mb-0"> إجمالي الطلبات الجديدة </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-hourglass ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading"> المعتمدة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $approvedRequests }}</h4>
                            </div>
                            <small class="mb-0"> إجمالي الطلبات المعتمدة</small>

                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-file-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading"> المرفوضة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $rejectedRequests }}</h4>
                            </div>
                            <small class="mb-0"> إجمالي الطلبات المرفوضة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-circle-x ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-6">
                    <label for="filter-category">التصنيف</label>
                    <select id="filter-category" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($requestCategory as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-status">حالة الطلب</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر  حالة الطلب">
                        <option value=""></option>
                        @foreach ($requestStatus as $status)
                            <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>



    <!-- مودال تغيير الحالة -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تغيير حالة الطلب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="request-id">
                    <div class="mb-3">
                        <label for="status-change" class="form-label">الحالة</label>
                        <select class="form-select" id="status-change">
                            <option value="pending">قيد الانتظار</option>
                            <option value="approved">مقبولة</option>
                            <option value="rejected">مرفوضة</option>
                        </select>
                    </div>
                    <div class="mb-3 rejection-reason-container d-none">
                        <label for="rejection_reason" class="form-label">سبب الرفض</label>
                        <textarea class="form-control" id="rejection_reason" rows="3" placeholder="يرجى إدخال سبب الرفض..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="confirm-change">تأكيد</button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال سبب الرفض -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">أدخل سبب الرفض</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejected-request-id">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">سبب الرفض</label>
                        <textarea class="form-control" id="rejection_reason" rows="3" placeholder="يرجى إدخال سبب الرفض..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirm-reject">تأكيد الرفض</button>
                </div>
            </div>
        </div>
    </div>
@endsection
