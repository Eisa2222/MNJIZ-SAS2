<div class="tab-pane fade" id="attachmentsSettings">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-paperclip text-warning me-2"></i>
            <h6 class="card-title mb-0">مرفقات المنشأة </h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="formPrintSettings" method="POST"
                action="{{ route('general-settings.system-settings.company_attachments') }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" id="" value="#attachmentsSettings">

                <!-- السجل التجاري -->
                <div class="field-group">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف السجل التجاري
                            </label>
                            <input type="file" name="commercial_register" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->commercial_register)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->commercial_register) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->commercial_register) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="commercial_register_end_date">
                                تاريخ انتهاء السجل التجاري
                            </label>
                            <input type="date" id="commercial_register_end_date" name="commercial_register_end_date"
                                class="form-control"
                                value="{{ $attachments->commercial_register_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>


                <!-- التأمينات -->
                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف التأمينات
                            </label>
                            <input type="file" name="insurance" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->insurance)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->insurance) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->insurance) }}" download class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="insurance_end_date">
                                تاريخ انتهاء التأمينات
                            </label>
                            <input type="date" id="insurance_end_date" name="insurance_end_date" class="form-control"
                                value="{{ $attachments->insurance_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- الغرفة التجارية -->
                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف الغرفة التجارية
                            </label>
                            <input type="file" name="chamber_of_commerce" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->chamber_of_commerce)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->chamber_of_commerce) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->chamber_of_commerce) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="chamber_end_date">
                                تاريخ انتهاء الغرفة التجارية
                            </label>
                            <input type="date" id="chamber_end_date" name="chamber_end_date" class="form-control"
                                value="{{ $attachments->chamber_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- بلدي (البوابة الرقمية للاستثمار) -->
                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف بلدي
                            </label>
                            <input type="file" name="balady" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->balady)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->balady) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->balady) }}" download class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="balady_end_date">
                                تاريخ انتهاء بلدي
                            </label>
                            <input type="date" id="balady_end_date" name="balady_end_date" class="form-control"
                                value="{{ $attachments->balady_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- التوطين -->
                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف التوطين
                            </label>
                            <input type="file" name="tawteen" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->tawteen)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->tawteen) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->tawteen) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tawteen_end_date">
                                تاريخ انتهاء التوطين
                            </label>
                            <input type="date" id="tawteen_end_date" name="tawteen_end_date" class="form-control"
                                value="{{ $attachments->tawteen_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- حماية الأجور -->
                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف حماية الأجور
                            </label>
                            <input type="file" name="wage_protection" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->wage_protection)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->wage_protection) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->wage_protection) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="wage_protection_end_date">
                                تاريخ انتهاء حماية الأجور
                            </label>
                            <input type="date" id="wage_protection_end_date" name="wage_protection_end_date"
                                class="form-control"
                                value="{{ $attachments->wage_protection_end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>


                <div class="field-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف عقد التأسيس
                            </label>
                            <input type="file" name="incorporation_contract" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->incorporation_contract)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->incorporation_contract) }}"
                                        target="_blank" class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->incorporation_contract) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ملف العنوان الوطني
                            </label>
                            <input type="file" name="national_address" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @if (isset($attachments) && $attachments->national_address)
                                <small class="form-text text-muted mt-1">
                                    الملف الحالي:
                                    <a href="{{ Storage::url($attachments->national_address) }}" target="_blank"
                                        class="text-primary">
                                        عرض الملف
                                    </a>
                                    |
                                    <a href="{{ Storage::url($attachments->national_address) }}" download
                                        class="text-success">
                                        تحميل
                                    </a>
                                </small>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- زر الحفظ -->
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
