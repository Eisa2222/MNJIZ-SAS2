{{-- <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
            text: "لا يمكن التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false,
            customClass: {
                confirmButton: 'btn btn-success me-2',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
 --}}

{{-- @foreach ($sessions as $session) --}}
    {{-- <li class="timeline-item timeline-item-transparent">
        <span class="timeline-point timeline-point-success"></span>
        <div class="timeline-event">
            <div class="timeline-header mb-3">
                <h6 class="mb-0">{{ $session->session_name }}</h6>
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($session->created_at)->diffForHumans() }}
                </small>
            </div>
            <p class="mb-2">
                <strong class="d-inline-block mb-2">التاريخ :</strong> {{ $session->hijri_date }} -
                {{ $session->gregorian_date }}<br>
                <strong class="d-inline-block mb-2">حالة محكمة البرهان :</strong>
                {{ $session->al_burhan_court_status }}<br>
                <strong class="d-inline-block mb-2">حالة التقرير الإجمالي :</strong>
                {{ $session->summary_report_status }}<br>
                <strong class="d-inline-block mb-2">دقائق التنفيذ :</strong> {{ $session->execution_minutes }}<br>
                <strong class="d-inline-block mb-2">طريقة إرسال التقرير :</strong>
                {{ $session->report_sending_method }}<br>
            </p>
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex flex-wrap align-items-center mb-50">
                    <div class="avatar avatar-sm me-3">
                        <img class="img-fluid rounded mb-4"
                            height="120" alt="User avatar" />
                    </div>
                    <div> --}}
                        {{--  <p class="mb-0 small fw-medium">{{ $session->employee->name }}</p>  --}}
                        {{-- <small>المكلف</small>
                    </div>
                </div>
                <div class="d-flex align-items-center"> --}}
                    <!-- زر عرض التفاصيل -->
                    {{-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#sessionModal" data-session-name="{{ $session->session_name }}"
                        data-hijri-date="{{ $session->hijri_date }}"
                        data-gregorian-date="{{ $session->gregorian_date }}"
                        data-al-burhan-court-status="{{ $session->al_burhan_court_status }}"
                        data-summary-report-status="{{ $session->summary_report_status }}"
                        data-execution-minutes="{{ $session->execution_minutes }}"
                        data-report-sending-method="{{ $session->report_sending_method }}"
                        data-detailed-report="{{ $session->detailed_report }}">
                        <i class="fas fa-eye"></i>
                    </button> --}}

                    <!-- زر تعديل -->
                    {{-- <a href="{{ route('sessions.edit', $session->id) }}" class="btn btn-sm btn-warning ms-2">
                        <i class="fas fa-edit"></i>
                    </a> --}}

                    <!-- زر الحذف -->
                    {{-- <form id="delete-form-{{ $session->id }}" class="m-0"
                        action="{{ route('sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-danger ms-2"
                            onclick="confirmDelete({{ $session->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form> --}}



                    <!-- زر المشاركة -->
                    {{-- <div class="dropdown d-inline-block ms-2">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="shareDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="shareDropdown">
                            <li>
                                <a class="dropdown-item"
                                    href="sms:?body=تفاصيل الجلسة: {{ $session->session_name }} - التاريخ الهجري: {{ $session->hijri_date }} - التاريخ الميلادي: {{ $session->gregorian_date }} - حالة محكمة البرهان: {{ $session->al_burhan_court_status }}">
                                    <i class="fas fa-sms"></i> مشاركة عبر SMS
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="mailto:?subject=تفاصيل الجلسة: {{ $session->session_name }}&body=تفاصيل الجلسة: {{ $session->session_name }} - التاريخ الهجري: {{ $session->hijri_date }} - التاريخ الميلادي: {{ $session->gregorian_date }} - حالة محكمة البرهان: {{ $session->al_burhan_court_status }}">
                                    <i class="fas fa-envelope"></i> مشاركة عبر البريد الإلكتروني
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="https://wa.me/?text=تفاصيل الجلسة: {{ $session->session_name }}%0Aالتاريخ الهجري: {{ $session->hijri_date }}%0Aالتاريخ الميلادي: {{ $session->gregorian_date }}%0Aحالة محكمة البرهان: {{ $session->al_burhan_court_status }}%0A"
                                    target="_blank">
                                    <i class="fab fa-whatsapp"></i> مشاركة عبر WhatsApp
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div> --}}

            <!-- قسم التعليقات -->
            {{-- <div class="comments-section mt-4">
                <h6>التعليقات</h6>

                <!-- عرض التعليقات الحالية -->
                @forelse ($session->comments as $comment)
                    <div class="card shadow-sm mb-3 p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-3">
                                <img class="img-fluid rounded"
                                     src="{{ asset($comment->user->profile_picture ? 'storage/' . $comment->user->profile_picture : 'assets/img/avatars/2.png') }}"
                                     alt="User avatar" />
                            </div>
                            <strong>{{ $comment->user->name }}</strong>
                            <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>

                            <!-- إذا كان التعليق خاص بالمستخدم الحالي، تظهر قائمة الخيارات -->
                            @if (Auth::check() && Auth::user()->id === $comment->user_id)
                            <div class="ms-auto dropdown">
                                <button class="btn btn-sm btn-link text-dark" type="button" id="commentMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: none;">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenuDropdown">

                                    <li>
                                        <form id="delete-form-{{ $comment->id }}" action="{{ route('sessions.comments.destroy', $comment->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="dropdown-item" onclick="confirmDelete({{ $comment->id }})">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </li>

                                </ul>
                            </div>
                        @endif
                        </div>
                        <p class="mt-2">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p class="text-muted">لا توجد تعليقات بعد.</p>
                @endforelse

                <!-- نموذج إضافة تعليق جديد -->
                @auth
                    <form action="{{ route('sessions.comments.store', $session->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label for="comment-{{ $session->id }}" class="form-label">إضافة تعليق</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="comment-{{ $session->id }}" name="content" rows="3" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">حفظ</button>
                    </form>
                @else
                    <p class="text-muted">يرجى <a href="{{ route('login') }}">تسجيل الدخول</a> لإضافة تعليق.</p>
                @endauth
            </div> --}}
        {{-- </div>
    </li> --}}
{{-- @endforeach --}}
