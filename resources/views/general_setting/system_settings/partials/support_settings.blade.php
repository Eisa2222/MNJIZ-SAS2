<div class="tab-pane fade" id="supportSettings">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-headset text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات الدعم الفني</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="supportSettingsForm" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" value="#supportSettings">

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني لتلقي رسائل مستخدمين النظام</label>
                    <!-- لا نعرض حقلًا افتراضيًا إذا لم يكن هناك بيانات محفوظة -->
                    <div id="support-emails-container">
                        @if (isset($settings->support_emails) && is_array($settings->support_emails) && count($settings->support_emails) > 0)
                            @foreach ($settings->support_emails as $email)
                                <div class="input-group mb-2 support-email-row">
                                    <input type="email" name="support_emails[]" class="form-control"
                                        placeholder="أدخل البريد الإلكتروني" value="{{ $email }}" required>
                                    <button class="btn btn-sm btn-outline-danger remove-email-btn" type="button">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <!-- زر لإضافة حقل جديد -->
                    <div class="mt-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-email-field">
                            <i class="bi bi-plus"></i> أضف بريد جديد
                        </button>
                    </div>
                </div>

                <div class="mt-5 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
