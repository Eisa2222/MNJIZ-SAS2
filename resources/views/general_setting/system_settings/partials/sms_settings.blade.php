<div class="tab-pane fade" id="SmsSettings">
    <div class="card mb-6">
         <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-message text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات SMS</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="SmsSettings" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" id="" value="#SmsSettings">
                <div class="row">

                    <!-- إعدادات Microsoft الحالية -->
                    <!-- مزود الخدمة -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="sms_provider" class="form-label">مزود الخدمة</label>
                        <select class="form-select select2" required id="sms_provider"
                            name="sms_provider" data-placeholder="اختر مزود الخدمة">
                            <option value="4jawaly"
                                {{ old('sms_provider', $settings->sms_provider ?? '') == '4jawaly' ? 'selected' : '' }}>
                                4Jawaly</option>
                            <option disabled value="twilio"
                                {{ old('sms_provider', $settings->sms_provider ?? '') == 'twilio' ? 'selected' : '' }}>
                                Twilio - غير متاحة حاليا</option>
                            <option disabled value="nexmo"
                                {{ old('sms_provider', $settings->sms_provider ?? '') == 'nexmo' ? 'selected' : '' }}>
                                Nexmo - غير متاحة حاليا</option>
                            <option disabled value="nexmo"
                                {{ old('sms_provider', $settings->sms_provider ?? '') == 'nexmo' ? 'selected' : '' }}>
                                taqnyat - غير متاحة حاليا</option>
                            <!-- أضف مزودي خدمة آخرين حسب الحاجة -->
                        </select>
                        @error('sms_provider')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- مفتاح API -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="sms_api_key" class="form-label"> Api Key</label>
                        <input type="text" class="form-control" id="sms_api_key" name="sms_api_key" required
                            value="{{ old('sms_api_key', $settings->sms_api_key ?? '') }}">
                        @error('sms_api_key')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- سر API -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="sms_api_secret" class="form-label">Api Secret</label>
                        <input type="text" class="form-control" id="sms_api_secret" required
                            name="sms_api_secret"
                            value="{{ old('sms_api_secret', $settings->sms_api_secret ?? '') }}">
                        @error('sms_api_secret')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- معرف المرسل -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="sms_sender_id" class="form-label">معرف المرسل</label>
                        <input type="text" class="form-control" id="sms_sender_id" required
                            name="sms_sender_id"
                            value="{{ old('sms_sender_id', $settings->sms_sender_id ?? '') }}">
                        @error('sms_sender_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- تمكين الرسائل النصية -->

                    <div class="mb-4 col-md-6">
                        <label for="sms_enabled" class="form-label">حالة الرسائل</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="sms_enabled"
                                name="sms_enabled" {{ $settings->sms_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_enabled">
                                تمكين الرسائل النصية
                            </label>
                        </div>
                    </div>

                   

                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>