<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-3">
        <div class="card-body">
            <p class="text-primary fw-bold fs-6 my-2">
                {{ $lawsuit->name }}
            </p>
        </div>
    </div>
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5 ">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-book ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> موضوع الدعوى
            </h5>
        </div>

        <div class="card-body pt-3">
            <form id="lawsuitFormSubject" method="POST"
                action="{{ route('legal-affairs.lawsuits.lawsuit-section.subject-update', $lawsuit->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-4">

                    <div class="col-12 ">
                        <div class="form-group">
                            <label for="lawsuit_subject" class="form-label text-primary fw-bold fs-6">موضوع
                                الدعوى</label>
                            <textarea id="lawsuit_subject" name="lawsuit_subject"
                                class="form-control @error('lawsuit_subject') is-invalid @enderror" rows="4"
                                placeholder="أضف موضوع الدعوى هنا">{{ old('lawsuit_subject', $lawsuit->lawsuit_subject) }}</textarea>
                            @error('lawsuit_subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 ">
                        <div class="form-group">
                            <label for="plaintiff_requests" class="form-label text-primary fw-bold fs-6">طلبات
                                المدعي</label>
                            <textarea id="plaintiff_requests" name="plaintiff_requests"
                                class="form-control @error('plaintiff_requests') is-invalid @enderror" rows="4"
                                placeholder="أضف طلبات المدعي هنا">{{ old('plaintiff_requests', $lawsuit->plaintiff_requests) }}</textarea>
                            @error('plaintiff_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 ">
                        <div class="form-group">
                            <label for="lawsuit_proofs" class="form-label text-primary fw-bold fs-6">اسانيد
                                الدعوى</label>
                            <textarea id="lawsuit_proofs" name="lawsuit_proofs" class="form-control @error('lawsuit_proofs') is-invalid @enderror"
                                rows="4" placeholder="أضف اسانيد الدعوى هنا">{{ old('lawsuit_proofs', $lawsuit->lawsuit_proofs) }}</textarea>
                            @error('lawsuit_proofs')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-save me-2"></i> تحديث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#lawsuitFormSubject').on('submit', function(e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة

            // جمع البيانات من النموذج
            let formData = $(this).serialize();

            // إرسال الطلب عبر AJAX
            $.ajax({
                url: $(this).attr('action'), // الرابط المحدد في النموذج
                type: 'put', // نوع الطلب
                data: formData, // البيانات المرسلة
                success: function(response) {
                    if (response.success) {
                        if (response.message === "لم يتم إجراء أي تغييرات.") {
                            // عرض رسالة توضيحية عند عدم وجود تغييرات
                            toastr.info(response.message);
                        } else {
                            // عرض رسالة نجاح عند وجود تغييرات
                            toastr.success(response.message);
                        }
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء معالجة الطلب الرجاء المحاولة مرة أخرى');
                }
            });
        });
    });
</script>
