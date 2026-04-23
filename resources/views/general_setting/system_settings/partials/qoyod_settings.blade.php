<div class="tab-pane fade" id="qoyodSettings">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-adjustments-code text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات نظام قيود </h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="qoyodSettings" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" value="#qoyodSettings">

                <div class="row">
                    <div class="col-12 col-md-6 mb-4">
                        <label for="qoyod_api_key" class="form-label">قيود API Key</label>
                        <input type="password" name="qoyod_api_key" id="qoyod_api_key" class="form-control"
                            placeholder="••••••••" required />

                        @error('qoyod_api_key')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 mb-4">
                        <label for="qoyod_base_url" class="form-label">رابط قيود الأساسي (Base URL API)</label>
                        <input type="url" name="qoyod_base_url" id="qoyod_base_url" class="form-control"
                            value="{{ old('qoyod_base_url', $settings->qoyod_base_url) }}"
                            required />
                        @error('qoyod_base_url')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
