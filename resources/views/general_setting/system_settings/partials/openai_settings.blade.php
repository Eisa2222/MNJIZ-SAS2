<div class="tab-pane fade" id="chatGpt">
    <div class="card mb-6">
         <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-robot text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات OpenAI </h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="chatGptSettings" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" value="#chatGpt">

                <div class="row">
                    <!-- OpenAI API Key -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="openai_api_key" class="form-label">OpenAI API Key</label>
                        <input type="password" class="form-control" id="openai_api_key" name="openai_api_key"
                            value="{{ old('openai_api_key', $settings->openai_api_key ?? '') }}" required>
                        @error('openai_api_key')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- OpenAI Model -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="openai_model" class="form-label">OpenAI Model</label>
                        <select class="form-select select2" id="openai_model" name="openai_model" required>
                            <option value="gpt-4o" {{ ($settings->openai_model ?? '') == 'gpt-4o' ? 'selected' : '' }}>
                                GPT-4o (Versatile, High-Intelligence)
                            </option>
                            <option value="gpt-4o-mini"
                                {{ ($settings->openai_model ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>
                                GPT-4o-mini (Fast, Affordable)
                            </option>
                            <option value="gpt-4-turbo"
                                {{ ($settings->openai_model ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>
                                GPT-4 Turbo
                            </option>
                            <option value="gpt-3.5-turbo"
                                {{ ($settings->openai_model ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>
                                GPT-3.5 Turbo (Fast, Simple Tasks)
                            </option>
                        </select>
                        @error('openai_model')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Enable OpenAI  -->
                    <div class="col-12 col-md-6 mb-4">
                        <label for="chatgpt_enabled" class="form-label">حالة OpenAI </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chatgpt_enabled" name="chatgpt_enabled"
                                {{ $settings->chatgpt_enabled ?? false ? 'checked' : '' }}>
                            <label class="form-check-label" for="chatgpt_enabled">
                                تمكين خدمات OpenAI 
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
