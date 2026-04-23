<div class="tab-pane fade show active" id="vendor_details">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-user text-warning me-2"></i>
            <h6 class="card-title mb-0">تفاصيل المورد</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>

        <div class="card-body px-5">
            <div class="table-responsive">
                <table class="table table-striped  small text-center">
                    <tr>
                        <th class="fw-bold">اسم المورد</th>
                        <th>{{ $vendor['name'] }}</th>
                    </tr>
                    <tr>
                        <th class="fw-bold">اسم الجهة</th>
                        <th>{{ $vendor['organization'] ?: '--' }}</th>
                    </tr>
                    <tr>
                        <th class="fw-bold">الرقم الضريبي</th>
                        <th>{{ $vendor['tax_number'] ?: '--' }}</th>
                    </tr>
                    <tr>
                        <th class="fw-bold">البريد الإلكتروني</th>
                        <th>{{ $vendor['email'] ?: '--' }}</th>
                    </tr>
                    <tr>
                        <th class="fw-bold">رقم الإتصال</th>
                        <th>{{ $vendor['phone_number'] ?: '--' }}</th>
                    </tr>
                    <tr>
                        <th class="fw-bold">الحالة</th>
                        <th>{{ $vendor['status'] == 'Active' ? 'نشط' : 'غير نشط' }}</th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
