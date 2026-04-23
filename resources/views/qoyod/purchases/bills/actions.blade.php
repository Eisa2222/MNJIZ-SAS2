<div class="d-flex justify-content-center">
    <button type="button" class="btn btn-sm border-0 me-2" title="دفع"
        onclick="
            openPaymentModal(
            {{ $row['id'] }},
            '',
            @js($row['contact']['name']),
            {{ $row['due_amount'] }}
            )
        ">
        <i class="ti ti-credit-card"></i>
    </button>


    <button disabled type="button" class="btn btn-sm border-0"
        title="عذرًا، لا يمكن إتمام العملية حاليًا لعدم توفر الخدمة اللازمة">
        <i class="ti ti-edit text-danger"></i>
    </button>


    <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row['id'] }})">
        <i class="ti ti-trash"></i>
    </button>

    <form id="delete-form-{{ $row['id'] }}" action="{{ route('qoyod.bills.destroy', $row['id']) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>



</div>

{{-- modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="ti ti-credit-card me-2"></i>
                    إضافة عملية دفع
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form id="paymentForm" action="{{ route('qoyod.bill-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" id="billId" name="bill_id">
                <div class="modal-body">
                    <div class="row g-2">
                        <!-- رقم السند -->
                        <div class="col-md-6 mb-3">
                            <label for="reference" class="form-label required">رقم السند</label>
                            <input type="text" class="form-control" id="reference" name="reference" required>
                        </div>

                        <!-- اسم الجهة -->
                        <div class="col-md-6 mb-3">
                            <label for="companyName" class="form-label required">اسم الجهة</label>
                            <input type="text" class="form-control" id="companyName" disabled>
                        </div>

                        <!-- الحساب -->
                        <div class="col-md-6 mb-3">
                            <label for="account_id" class="form-label required">الحساب</label>
                            <select class="select2 form-select" id="account_id" name="account_id" required>
                                <option value="">اختر الحساب</option>
                                @foreach ($pettyCashAccount as $account)
                                    <option value="{{ $account['id'] }}">{{ $account['code'] }} -
                                        {{ $account['name_ar'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- التاريخ -->
                        <div class="col-md-6 mb-3">
                            <label for="paymentDate" class="form-label required">التاريخ</label>
                            <input type="date" class="form-control" id="paymentDate" name="date" required>
                        </div>

                        <!-- الوصف -->
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">الوصف</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="أدخل وصف العملية (اختياري)"></textarea>
                        </div>

                        <!-- المبلغ -->
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label required">المبلغ </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                                    min="0" required inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?">
                                <span class="input-group-text"> <span class="icon-saudi_riyal"></span></span>
                            </div>
                        </div>

                        <!-- الرصيد المتبقي -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الرصيد المتبقي </label>
                            <div class="balance-info">
                                <span id="remainingBalance" class="fw-bold text-primary">0.00 <span
                                        class="icon-saudi_riyal"></span></span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary mx-0">
                        تأكيد الدفع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- js --}}
<script>
    let currentTotalAmount = 0;
    $(document).ready(function() {
        // تهيئة Select2 عند فتح المودال
        $('#paymentModal').on('shown.bs.modal', function() {
            $('#account_id').select2({
                dropdownParent: $('#paymentModal'),
                width: '100%',
                placeholder: 'اختر الحساب',
                allowClear: true,
                dir: 'rtl'
            });
        });

        // تنظيف Select2 عند إغلاق المودال
        $('#paymentModal').on('hidden.bs.modal', function() {
            if ($('#account_id').data('select2')) {
                $('#account_id').select2('destroy');
            }
        });
    });
    // فتح مودال الدفع مع بيانات السجل المحدد
    function openPaymentModal(billId, reference, companyName, totalAmount) {
        // تعيين البيانات في المودال
        document.getElementById('billId').value = billId;
        document.getElementById('reference').value = reference;
        document.getElementById('companyName').value = companyName;

        // حفظ المبلغ الإجمالي للحسابات
        currentTotalAmount = totalAmount;

        // إعادة تعيين المبلغ والرصيد
        document.getElementById('amount').value = '';
        document.getElementById('remainingBalance').innerHTML = totalAmount.toFixed(2) +
            ` <span class="icon-saudi_riyal"></span>`;
        document.getElementById('remainingBalance').className = 'fw-bold text-primary';

        // تعيين التاريخ الحالي
        document.getElementById('paymentDate').valueAsDate = new Date();

        // إعادة تعيين باقي الحقول
        document.getElementById('account_id').value = '';
        document.getElementById('description').value = '';

        // فتح المودال
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }

    // حساب الرصيد المتبقي
    document.getElementById('amount').addEventListener('input', function() {
        const enteredAmount = parseFloat(this.value) || 0;
        const remaining = Math.max(0, currentTotalAmount - enteredAmount);
        const remainingBalanceSpan = document.getElementById('remainingBalance');

        remainingBalanceSpan.innerHTML = remaining.toFixed(2) + ` <span class="icon-saudi_riyal"></span>`;


        // تغيير اللون حسب الرصيد
        if (remaining === 0) {
            remainingBalanceSpan.className = 'fw-bold text-success';
        } else if (remaining < currentTotalAmount * 0.3) {
            remainingBalanceSpan.className = 'fw-bold text-warning';
        } else {
            remainingBalanceSpan.className = 'fw-bold text-primary';
        }
    });

    // معالجة إرسال النموذج
    document.getElementById('paymentForm').addEventListener('submit', function(e) {

        // إغلاق المودال
        const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        modal.hide();
    });
</script>
