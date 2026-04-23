@extends('layouts.layoutMaster')

@section('title', 'تفاصيل دفعات العقد')

@section('breadcrumb')
    <li><a href="#">المالية</a></li>
    <li><a href="{{ route('financial.contract-payments.index') }}">دفعات العقود</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل دفعات العقد</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل دفعات العقد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script>
        $(function() {
            function initSelect2(scope = document) {
                $(scope).find('.select2').each(function() {
                    const $sel = $(this);
                    const $modal = $sel.closest('.modal');
                    $sel.select2({
                        width: '100%',
                        dir: 'rtl',
                        language: 'ar',
                        dropdownParent: $modal.length ? $modal : undefined
                    });
                });
            }

            initSelect2();

            $(document).on('shown.bs.modal', '.modal', function() {
                initSelect2(this);
            });
        });
    </script>
@endsection

@section('page-style')
    @vite(['resources/css/contract-payments.css'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/financial/contract-payments/payments.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل العقد
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold w-25">اسم العقد</td>
                                <td>{{ $contract->contract_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">رقم العقد</td>
                                <td>{{ $contract->contract_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">العميل</td>
                                <td>{{ $contract->customer?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تاريخ البداية</td>
                                <td>{{ $contract->contract_start_date }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تاريخ الإغلاق التعاقدي</td>
                                <td>{{ $contract->expected_closure_date ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تاريخ النهاية</td>
                                <td>{{ $contract->contract_end_date ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">حالة العقد</td>
                                <td>{{ $contract->status->label() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body">
                    <ul class="timeline">
                        @if ($contract->created_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-secondary"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title">تم الإنشاء</small>
                                    <small class="text-muted d-block">{{ $contract->created_at }}</small>
                                    <small>
                                        <b>بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $contract->created_by) }}">
                                            {{ $contract->creator->name }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif

                        @if ($contract->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title">تم التعديل</small>
                                    <small class="text-muted d-block">{{ $contract->updated_at }}</small>
                                    <small>
                                        <b>بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $contract->updated_by) }}">
                                            {{ $contract->editor->name }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header py-4 d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i class="ti ti-wallet text-warning me-2"></i>
                    دفعات العقد
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2">
            </div>

            <div class="table-responsive px-4 pb-4">
                <table class="table table-hover text-center align-middle small fw-bold">
                    <thead>
                        <tr class="bg-light">
                            <th>#</th>
                            <th>المُضيف</th>
                            <th>تاريخ الإضافة</th>
                            <th>النوع</th>
                            <th>القيمة</th>
                            <th>الاستحقاق</th>
                            <th>المدة المتبقية</th>
                            <th>الدفع</th>
                            <th>الطريقة</th>
                            <th>الحالة</th>
                            <th>آخر تعديل</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contract->contractPayments as $idx => $pay)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $pay->creator->name ?? '-' }}</td>
                                <td>{{ $pay->created_at?->format('Y-m-d') ?? '-' }}</td>

                                <td>{{ $pay->payment_batch_type->label() }}</td>

                                <td>
                                    @if ($pay->calculation_type->isPercentage())
                                        {{ $pay->percentage }} %
                                    @else
                                        {{ number_format($pay->fixed_amount, 2) }} {{ $pay->currency }}
                                    @endif
                                </td>

                                <td>{{ $pay->due_date?->format('Y-m-d') ?? '-' }}</td>

                                <td>
                                    @if ($pay->status->isScheduled())
                                        <span class="text-info">
                                            متبقي {{ $pay->days_remaining }} يوم
                                        </span>
                                    @elseif($pay->status->isLate())
                                        <span class="text-danger">
                                            متأخر {{ $pay->days_overdue }} يوم
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $pay->payment_date?->format('Y-m-d') ?? '-' }}
                                </td>

                                <td>{{ $pay->payment_method?->label() ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-{{ $pay->status->color() }}">
                                        {{ $pay->status->label() }}
                                    </span>
                                </td>

                                <td>
                                    {{ $pay->editor?->name ?? '-' }}
                                </td>

                                <td>
                                    @if (!$pay->status->isPaid())
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#payModal{{ $pay->id }}">
                                            دفع
                                        </button>

                                        <div class="modal fade" id="payModal{{ $pay->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form
                                                    action="{{ route('financial.contract-payments.pay', [$contract->id, $pay->id]) }}"
                                                    method="POST" class="modal-content">
                                                    @csrf @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تسجيل دفع الدفعة </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="إغلاق"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label">طريقة الدفع <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="payment_method" class="form-select select2"
                                                                data-placeholder="اختر طريقة الدفع" required>
                                                                <option value=""></option>
                                                                @foreach ($paymentMethods as $m)
                                                                    <option value="{{ $m['id'] }}">
                                                                        {{ $m['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" class="btn btn-success">تأكيد الدفع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($pay->status->isPaid())
                                        <form
                                            action="{{ route('financial.contract-payments.cancel', [$contract->id, $pay->id]) }}"
                                            method="POST" class="d-inline cancel-payment-form">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-sm btn-danger cancel-payment-btn"
                                                title="إلغاء الدفعة">
                                                إلغاء
                                            </button>
                                        </form>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">لا توجد دفعات مسجلة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
