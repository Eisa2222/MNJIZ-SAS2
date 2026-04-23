<!-- Add Payment Sidebar -->
<div class="offcanvas offcanvas-end" id="addPaymentOffcanvas" aria-hidden="true">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">قائمة الردود</h5>
        {{-- <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button> --}}
    </div>
    <div class="offcanvas-body flex-grow-1 d-flex flex-column">
        <!-- قائمة الردود -->
        <div class="chat-messages flex-grow-1 overflow-auto mb-4">
            @foreach($support->replies as $reply)
                <div class="d-flex mb-4 @if(auth()->id() == $reply->user_id) justify-content-end @endif">
                    @if(auth()->id() != $reply->user_id)
                        <!-- صورة المستخدم الآخر -->
                        <img class="rounded-circle"
                        src="{{$reply->user->image ? asset('storage/' .$reply->user->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                        alt="Avatar" width="40" height="40">


                        {{-- <img src="{{ asset('storage/' . $reply->user->image) }}" alt="{{ $reply->user->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px;"> --}}

                    @endif
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            @if(auth()->id() == $reply->user_id)
                                <span class="fw-bold">{{ $reply->user->name }}</span>
                            @else
                                <span class="fw-bold">{{ $reply->user->name }}</span>
                            @endif
                            <small class="text-muted ms-2">{{ $reply->created_at->locale('ar')->diffForHumans() }}</small>
                        </div>
                        <div class="p-2 rounded @if(auth()->id() == $reply->user_id) bg-primary text-white @else bg-light text-dark @endif">
                            {{ $reply->content }}
                        </div>
                    </div>
                    @if(auth()->id() == $reply->user_id)
                        <!-- صورة المستخدم الحالي -->
                        <img class="rounded-circle"
                        src="{{auth()->user()->image ? asset('storage/' .auth()->user()->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                        alt="Avatar" width="40" height="40">


                        {{-- <img src="{{ asset('storage/'. auth()->user()->image) }}" alt="{{ auth()->user()->name }}" class="rounded-circle ms-2" style="width: 40px; height: 40px;"> --}}

                    @endif
                </div>
            @endforeach
        </div>

        <!-- نموذج إضافة رد جديد -->
        <form action="{{ route('add.reply.technecal', $support->id) }}" method="POST" class="mt-auto">
            @csrf
            <div class="mb-3">
                <label for="content" class="form-label">إضافة رد</label>
                <textarea class="form-control" name="content" id="content" rows="3" placeholder="أدخل ردك هنا..." required></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="offcanvas">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
<!-- /Add Payment Sidebar -->
