<div class="tab-pane fade show active" id="generalSettings">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-settings text-warning me-2"></i>
            <h6 class="card-title mb-0">الإعدادات العامة</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <form id="formGeneralSettings" method="POST" action="{{ route('general-settings.system-settings.update') }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" id="" value="#generalSettings">
            <div class="card-body">
                <div class="d-flex align-items-start align-items-sm-center gap-6">
                    <img src="{{ $settings->image ? asset('storage/' . $settings->image) : asset('assets/img/avatars/image-blank.jpg') }}"
                        alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                    <div class="button-wrapper">
                        <label for="upload" class="btn btn-sm btn-primary me-3 mb-4" tabindex="0">
                            <span class="d-none d-sm-block">تحميل الشعار </span>
                            <i class="ti ti-upload d-block d-sm-none"></i>
                            <input type="file" name="image" id="upload" class="account-file-input" hidden
                                accept="image/png, image/jpeg" />
                        </label>
                        <div>
                            <small>الملفات المسموح بها: JPG, GIF, أو PNG. الأبعاد الموصى بها: 150px ×
                                150px</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-4">
                <div class="row">
                    <div class="mb-4 col-md-6">
                        <label for="officeName" class="form-label">إسم المكتب</label>
                        <input class="form-control" type="text" id="officeName" name="office_name" required
                            value="{{ old('office_name', $settings->office_name ?? '') }}"
                            placeholder="أدخل إسم المكتب" />
                        @error('office_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="archive_delete_duration" class="form-label">مدة حذف الأرشيف
                            (يوم)</label>
                        <input type="number" min="1" step="1" class="form-control"
                            id="archive_delete_duration" name="archive_delete_duration" required
                            value="{{ old('archive_delete_duration', $settings->archive_delete_duration ?? '') }}"
                            placeholder="أدخل عدد الأيام لحذف الأرشيف" />
                        @error('archive_delete_duration')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- وضع الصيانة -->
                    <div class="mb-4 col-md-6">
                        <label for="logoText" class="form-label">النص بجانب الشعار</label>
                        <input type="text" class="form-control" id="logoText" name="logo_text" required
                            value="{{ old('logo_text', $settings->logo_text ?? '') }}"
                            placeholder="أدخل النص بجانب الشعار" maxlength="14" />
                        @error('logo_text')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">الحد الأقصى لعدد الأحرف: 14.</small>
                    </div>


                    <div class="mb-4 col-md-3">
                        <label for="maintenance_mode" class="form-label">وضع الصيانة</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode"
                                name="maintenance_mode" {{ $settings->maintenance_mode ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_mode">تفعيل وضع
                                الصيانة</label>
                        </div>
                    </div>


                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </div>
        </form>

    </div>
</div>
