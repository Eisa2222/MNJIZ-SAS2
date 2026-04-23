@extends('layouts.layoutMaster')

@section('title', 'تفاصيل طلب تحديث الملف الشخصي')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="">طلبات تحديث الملف الشخصي</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الطلب </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل طلب التحديث" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        الحقول المطلوب تحديثها
                    </h6>

                    <div class="">
                        <button id="approveAllBtn" class="btn btn-sm btn-success">
                            موافقة على الكل
                        </button>
                        <button id="rejectAllBtn" class="btn btn-sm btn-danger mx-1">
                            رفض الكل
                        </button>
                    </div>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th class="py-2" width="20%">اسم الحقل</th>
                                    <th class="py-2" width="25%">القيمة السابقة</th>
                                    <th class="py-2" width="25%">القيمة المطلوبة</th>
                                    <th class="py-2" width="15%">الحالة</th>
                                    <th class="py-2" width="15%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($request->fields as $field)
                                    <tr class="text-center" id="field-row-{{ $field->id }}">
                                        <td>
                                            <small>{{ $field->field_name->label() }}</small>
                                        </td>

                                        <td>
                                            @php
                                                $path = $field->old_value;
                                                $url = asset("storage/{$path}");
                                            @endphp

                                            @if (preg_match('/\.(jpg|jpeg|png|pdf|doc|docx)$/i', $path))
                                                <button type="button" class="btn btn-sm btn-outline-primary view-image-btn"
                                                    data-url="{{ $url }}">
                                                    عرض الصورة
                                                </button>
                                            @else
                                                <div class="small text-truncate">{{ $field->old_value }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            @php
                                                $path = $field->new_value;
                                                $url = asset("storage/{$path}");
                                            @endphp

                                            @if (preg_match('/\.(jpg|jpeg|png|pdf|doc|docx)$/i', $path))
                                                <button type="button" class="btn btn-sm btn-outline-primary view-image-btn"
                                                    data-url="{{ $url }}">
                                                    عرض الصورة
                                                </button>
                                            @else
                                                <div class="small text-truncate">{{ $field->new_value }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            <div class=" small">
                                                <span
                                                    class="badge bg-{{ $field->status->color() }}">{{ $field->status->label() }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex">
                                                @if ($field->status->value == 'rejected')
                                                    <a href="#" class="btn btn-sm text-success btn-review-field"
                                                        data-field-id="{{ $field->id }}" data-status="approved">
                                                        <i class="ti ti-square-rounded-check"></i>
                                                    </a>
                                                @elseif($field->status->value == 'approved')
                                                    <!-- زر الرفض -->
                                                    <a href="#" class="btn btn-sm text-danger btn-review-field"
                                                        data-field-id="{{ $field->id }}" data-status="rejected">
                                                        <i class="ti ti-square-rounded-x"></i>
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-sm text-success btn-review-field"
                                                        data-field-id="{{ $field->id }}" data-status="approved">
                                                        <i class="ti ti-square-rounded-check"></i>
                                                    </a>
                                                    <!-- زر الرفض -->
                                                    <a href="#" class="btn btn-sm text-danger btn-review-field"
                                                        data-field-id="{{ $field->id }}" data-status="rejected">
                                                        <i class="ti ti-square-rounded-x"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            لا توجد حقول للمراجعة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <!-- بطاقة الإحصائيات -->
            <!-- بطاقة الإحصائيات -->
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-info me-2"></i>
                        إحصائيات الطلب
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div id="totalCount" class="text-primary fs-4 fw-bold">
                                    {{ $request->fields->count() }}
                                </div>
                                <small class="text-muted">إجمالي الحقول</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div id="approvedCount" class="text-success fs-4 fw-bold">
                                    {{ $approved }}
                                </div>
                                <small class="text-muted">الحقول المقبولة</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div id="rejectedCount" class="text-danger fs-4 fw-bold">
                                    {{ $rejected }}
                                </div>
                                <small class="text-muted">الحقول المرفوضة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- سجل النشاطات --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-clock-history text-info me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="timeline">

                        <li class="timeline-item">
                            <span class="timeline-point bg-warning"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تم إضافة الطلب بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $request->created_at->format('Y-m-d H:i') }}
                                </small>
                                <small>
                                    <b> بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $request->employee_id) }}">
                                        {{ $request->employee->getRawNameAttribute() }}
                                    </a>
                                </small>
                            </div>
                        </li>


                        @if ($request->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-warning"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        اخر تحديث بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $request->updated_at }}
                                    </small>
                                    <small>
                                        <b> بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $request->updated_by) }}">
                                            {{ $request->updatedBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const approveRouteTemplate = "{{ route('hr.modification-requests.fields.approve', ':fieldId') }}";
        const rejectRouteTemplate = "{{ route('hr.modification-requests.fields.reject', ':fieldId') }}";

        // دالة معالج الحقل المنفرد أو الجماعي
        function reviewField(fieldId, status, skipConfirm = false) {
            const statusText = status === 'approved' ? 'الموافقة على' : 'رفض';
            const url = (status === 'approved' ?
                approveRouteTemplate :
                rejectRouteTemplate
            ).replace(':fieldId', fieldId);

            const exec = () => {
                const row = document.getElementById(`field-row-${fieldId}`);
                const badge = row.querySelector('span.badge');
                // قراءة الحالة السابقة قبل التعديل
                const prevText = badge.textContent.trim();

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message);

                        // --- تحديث الـ badge ---
                        Array.from(badge.classList)
                            .filter(c => c.startsWith('bg-'))
                            .forEach(c => badge.classList.remove(c));
                        const newText = status === 'approved' ? 'مقبولة' : 'مرفوضة';
                        badge.classList.add(status === 'approved' ? 'bg-success' : 'bg-danger');
                        badge.textContent = newText;

                        // --- إعادة رسم زرّ واحد عكسي ---
                        const actionsDiv = row.querySelector('.d-flex');
                        actionsDiv.innerHTML = '';
                        const opposite = status === 'approved' ? 'rejected' : 'approved';
                        const btn = document.createElement('a');
                        btn.href = '#';
                        btn.className =
                            `btn btn-sm text-${opposite==='approved'?'success':'danger'} btn-review-field`;
                        btn.dataset.fieldId = fieldId;
                        btn.dataset.status = opposite;
                        btn.innerHTML = opposite === 'approved' ?
                            '<i class="ti ti-square-rounded-check"></i>' :
                            '<i class="ti ti-square-rounded-x"></i>';
                        btn.addEventListener('click', e => {
                            e.preventDefault();
                            reviewField(fieldId, opposite);
                        });
                        actionsDiv.appendChild(btn);

                        // --- تحديث الإحصائيات بناءً على prevText و newText ---
                        const approvedEl = document.getElementById('approvedCount');
                        const rejectedEl = document.getElementById('rejectedCount');
                        if (prevText === 'مقبولة') {
                            approvedEl.textContent = Math.max(0, +approvedEl.textContent - 1);
                        } else if (prevText === 'مرفوضة') {
                            rejectedEl.textContent = Math.max(0, +rejectedEl.textContent - 1);
                        }
                        if (newText === 'مقبولة') {
                            approvedEl.textContent = +approvedEl.textContent + 1;
                        } else {
                            rejectedEl.textContent = +rejectedEl.textContent + 1;
                        }

                        toastr.success(data.message);
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            text: err.message
                        });
                    });
            };

            if (skipConfirm) return exec();

            Swal.fire({
                title: `هل أنت متأكد من ${statusText} هذا الحقل؟`,
                icon: 'question',
                showCancelButton: true,
                showConfirmButton: true,
                showDenyButton: false,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء',
                reverseButtons: false,
            }).then(res => res.isConfirmed && exec());
        }

        // دالة الموافقة/الرفض على الكل
        function processAll(status) {
            const buttons = Array.from(
                document.querySelectorAll(`.btn-review-field[data-status="${status}"]`)
            );
            if (!buttons.length) {
                return toastr.info('لا توجد حقول جديدة للتصنيف');
            }

            const actionText = status === 'approved' ? 'موافقة على' : 'رفض';
            Swal.fire({
                title: `هل تريد ${actionText} جميع الحقول؟`,
                icon: 'question',
                showCancelButton: true,
                showConfirmButton: true,
                showDenyButton: false,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء',
                reverseButtons: false,
            }).then(res => {
                if (!res.isConfirmed) return;
                // نفّذ بدون تأكيد فردي
                buttons.forEach(btn => {
                    const id = btn.dataset.fieldId;
                    reviewField(id, status, true);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // ربط أحداث الحقول المنفردة
            document.querySelectorAll('.btn-review-field').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    reviewField(btn.dataset.fieldId, btn.dataset.status);
                });
            });
            // ربط أزرار الموافقة/الرفض على الكل
            document.getElementById('approveAllBtn')
                .addEventListener('click', () => processAll('approved'));
            document.getElementById('rejectAllBtn')
                .addEventListener('click', () => processAll('rejected'));

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.view-image-btn');
                if (!btn) return;
                const url = btn.dataset.url;
                window.open(url, '_blank');
            });
        });
    </script>


@endsection
