<!-- Send Invoice Sidebar -->
<div class="offcanvas offcanvas-end" id="sendInvoiceOffcanvas" aria-hidden="true">
    <div class="offcanvas-header mb-6 border-bottom ">
        <h5 class="offcanvas-title">تذكرة رقم - {{ $support->ticket_number }}</h5>
        {{-- <button type="button" class="btn-close text-reset " data-bs-dismiss="offcanvas" aria-label="Close"></button> --}}
    </div>
    <div class="offcanvas-body pt-0 flex-grow-1">
        <form action="{{ route('supports.reply',$support->id) }}" method="POST" onsubmit="return validateForm()">
            @csrf
            <div class="mb-6">
                <label for="ticket-status" class="form-label">تحديث الحالة</label>
                <select required class="form-select" id="ticket-status" name="status">
                    <option value="جديد" @if($support->status == 'جديد') selected @endif>جديد</option>
                    <option value="انتظار رد العميل" @if($support->status == 'انتظار رد العميل') selected @endif>انتظار رد العميل</option>
                    <option value="تمت المعالجة" @if($support->status == 'تمت المعالجة') selected @endif>تمت المعالجة</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="reply" class="form-label">الرد</label>
                <textarea class="form-control" name="reply" id="reply" cols="3" rows="8">{{ $support->reply }}</textarea>
            </div>

            <div class="mb-6 d-flex flex-wrap justify-content-end">
                <button type="button" class="btn btn-label-secondary me-4" data-bs-dismiss="offcanvas">إلغاء</button>
                <button type="submit" class="btn btn-primary">إضافة</button>
            </div>
        </form>

    </div>

    <script>
        // استمع لتغيير الحالة في القائمة المنسدلة
        document.getElementById("ticket-status").addEventListener("change", function() {
            const status = this.value;
            const reply = document.getElementById("reply");

            // اجعل حقل الرد غير إجباري إذا كانت الحالة "انتظار رد العميل" أو "جديد"
            if (status === "انتظار رد العميل" || status === "جديد") {
                reply.removeAttribute("required");
            } else {
                // اجعل حقل الرد إجباري في الحالة "تمت المعالجة"
                reply.setAttribute("required", "required");
            }
        });

        function validateForm() {
            const status = document.getElementById("ticket-status").value;
            const reply = document.getElementById("reply").value;

            // تحقق إذا كانت الحالة "تمت المعالجة" ويجب أن يحتوي الرد على قيمة
            if (status === "تمت المعالجة" && reply.trim() === "") {
                alert("الرجاء إدخال الرد عند اختيار الحالة 'تمت المعالجة'.");
                return false; // منع إرسال النموذج
            }

            return true; // السماح بإرسال النموذج
        }
    </script>



</div>

<!-- /Send Invoice Sidebar -->
