<div class="tab-pane fade" id="printSettings">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-printer text-warning me-2"></i>
            <h6 class="card-title mb-0">الإعدادات الخاصة بالطباعة</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="formPrintSettings" method="POST" action="{{ route('general-settings.system-settings.update') }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" id="" value="#printSettings">

                <div class="row mb-12">
                    <div class="col-md-6">
                        <!-- نموذج القوالب الأفقية -->
                        <div class="">
                            <img src="{{ $settings->template_image ? asset('storage/' . $settings->template_image) : asset('assets/img/avatars/image-blank.jpg') }}"
                                alt="نموذج القوالب " class="d-block w-100 mb-5 h-px-150 rounded object-fit-contain"
                                id="uploadedHorizontalHeader" />

                            <div class="button-wrapper text-center">
                                <label for="uploadHorizontalHeader" class="btn btn-sm btn-primary me-3 mb-4"
                                    tabindex="0">
                                    <span class="d-none d-sm-block">تحميل نموذج القوالب </span>
                                    <i class="fas fa-upload d-block d-sm-none"></i>
                                    <input type="file" id="uploadHorizontalHeader" name="template_image"
                                        class="account-file-input" hidden accept="image/png, image/jpeg, image/gif" />
                                </label>
                                @if ($errors->has('template_image'))
                                    <p class="text-danger small">{{ $errors->first('template_image') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- الختم الخاص بالمؤسسة -->
                    <div class="col-md-6">
                        <div class="">

                            <img src="{{ $settings->signature ? asset('storage/' . $settings->signature) : asset('assets/img/avatars/image-blank.jpg') }}"
                                alt="الختم الخاص بالمؤسسة"
                                class="d-block w-100 mb-5 h-px-150 rounded object-fit-contain" id="uploadedSignature" />
                            <div class="button-wrapper text-center">
                                <label for="uploadSignature" class="btn btn-sm btn-primary me-3 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">تحميل الختم</span>
                                    <i class="fas fa-upload d-block d-sm-none"></i>
                                    <input type="file" id="uploadSignature" name="signature"
                                        class="account-file-input" hidden accept="image/png, image/jpeg, image/gif" />
                                </label>
                                @if ($errors->has('signature'))
                                    <p class="text-danger small">{{ $errors->first('signature') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <script>
                        document.getElementById('uploadSignature').addEventListener('change', function() {
                            if (this.files && this.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    document.getElementById('uploadedSignature').src = e.target.result;
                                }
                                reader.readAsDataURL(this.files[0]);
                            }
                        });
                    </script>


                </div>
                <!-- زر الحفظ -->
                <div class="mt-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-secondary me-3" data-bs-toggle="modal"
                        data-bs-target="#previewModal">معاينة</button>

                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">معاينة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-right mb-4" style="position: relative;">
                    <img src="{{ $settings->template_image ? asset('storage/' . $settings->template_image) : asset('assets/img/avatars/image-blank.jpg') }}"
                        alt="نموذج القوالب" class="mb-3 w-100 h-auto" id="previewHorizontalHeader" />
                </div>

                <div class="text-end" style="position: absolute; bottom: 150px; left: 100px;">
                    <img src="{{ $settings->signature ? asset('storage/' . $settings->signature) : asset('assets/img/avatars/image-blank.jpg') }}"
                        alt="الختم" class="mb-3 w-50 h-50" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
