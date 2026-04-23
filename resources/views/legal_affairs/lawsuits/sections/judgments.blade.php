<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-gavel ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> الأحكام
            </h5>
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <!-- عرض الجلسات باستخدام الكارد -->
                @foreach ($lawsuit->sessions->sortByDesc('created_at') as $session)
                    <input type="hidden" id="last_objection_deadline" value="{{ $session->last_objection_deadline }}">
                    <input type="hidden" id="session_id_id" value="{{ $session->id }}">
                    <input type="hidden" id="lawsuit_id" value="{{ $lawsuit->id }}">

                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3 pb-1">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2">
                                            <i class="ti ti-gavel ti-xl me-1_5 text-primary"></i>
                                        </div>
                                        <div class="me-1 text-heading h5 mb-0">
                                            {{-- <p>{{ $session->sessionType->name }}</p> --}}
                                        </div>
                                    </div>
                                </div>

                                <p class="mb-3 pb-1">
                                    <strong>الجلسة:</strong> {{ $session->session_name }}<br>
                                    <strong>التقرير الاجمالي :</strong>
                                    {{ $session->summary_report_status->label() }}<br>
                                </p>

                                <ul class="list-group list-group-flush">
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center flex-wrap p-0">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <ul
                                                class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                            </ul>
                                        </div>
                                    </li>
                                </ul>

                                <!-- زر عرض المرفق -->
                                @if ($session->rule_attached)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#attachmentModal-{{ $session->id }}">
                                        عرض المرفق
                                    </button>
                                @else
                                    <p class="text-muted">لا يوجد مرفق</p>
                                @endif

                            </div>
                        </div>
                    </div>

                    <!-- مودال عرض المرفق -->
                    <div class="modal fade" id="attachmentModal-{{ $session->id }}" tabindex="-1"
                        aria-labelledby="attachmentModalLabel-{{ $session->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="attachmentModalLabel-{{ $session->id }}">مرفق الجلسة
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @if ($session->rule_attached)
                                        {{-- <iframe src="{{ asset('storage/' . $session->rule_attached) }}?t={{ time() }}" width="100%" height="400px" frameborder="0"></iframe> --}}
                                        <iframe id="ruleIframe-{{ $session->id }}"
                                            data-src="{{ route('legal-affairs.lawsuits.show-file', ['filePath' => $session->rule_attached]) }}"
                                            width="100%" height="400px" frameborder="0"></iframe>


                                        {{-- <iframe src="{{ route('show.file', ['filePath' => $session->rule_attached]) }}?t={{ time() }}" width="100%" height="400px" frameborder="0"></iframe> --}}

                                        <script>
                                            $('#myModal').on('shown.bs.modal', function(e) {
                                                // إضافة بارامتر زمني على الرابط لإجبار إعادة التحميل
                                                var iframe = $('#ruleIframe');
                                                var baseUrl =
                                                    "{{ route('legal-affairs.lawsuits.show-file', ['filePath' => $session->rule_attached]) }}";
                                                iframe.attr('src', baseUrl + '?t=' + new Date().getTime());
                                            });
                                        </script>
                                    @else
                                        <p>لا يوجد ملف مرفق لعرضه.</p>
                                    @endif

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">إغلاق</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

<script>
    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if (session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        // استمع لجميع الأزرار التي تفتح المودال
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                var targetModal = document.querySelector(button.getAttribute('data-bs-target'));
                var iframe = targetModal.querySelector('iframe');
                var dataSrc = iframe.getAttribute('data-src');


                // تعيين src فقط إذا لم يتم تعيينه مسبقًا
                if (!iframe.getAttribute('src')) {
                    iframe.setAttribute('src', dataSrc);
                }
            });
        });

        // إعادة تعيين src عند إغلاق المودال إذا كنت تريد إعادة تحميل الملف عند كل فتح
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                var iframe = modal.querySelector('iframe');
                iframe.setAttribute('src', '');
            });
        });
    });
</script>
