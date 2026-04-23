@php
$configData = App\Helpers\Helpers::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'الدعم')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/nouislider/nouislider.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/nouislider/nouislider.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection


<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/front-page-landing.js'])
@endsection



@section('content')
<div data-bs-spy="scroll" class="scrollspy-example">
    <!-- اتصل بنا: البداية -->
    <section id="landingContact" class="section-py bg-body landing-contact mt-0">
        <div class="container">
            <div class="text-center mb-4">
                <span class="badge bg-label-primary">اتصل بنا</span>
            </div>
            <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">لنبدأ العمل
                    <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt=""
                        class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
                </span>
                معًا
            </h4>
            <p class="text-center mb-12 pb-md-4">هل لديك أي سؤال أو ملاحظة؟ فقط ارسل لنا رسالة</p>
            <div class="row g-6">
                <div class="col-lg-5">
                    <div class="contact-img-box position-relative border p-2 h-100">
                        <img src="{{ asset('assets/img/front-pages/icons/contact-border.png') }}" alt=""
                            class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
                        <img src="{{ asset('assets/img/pages/app-academy-tutor-3.png') }}"
                            alt="" class="contact-img w-100 h-75  scaleX-n1-rtl" />
                        <div class="p-4 pb-2">
                            <div class="row g-4 ">
                                <div class="col-md-6 col-lg-12 col-xl-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-label-primary rounded p-1_5 me-3"><i
                                                class="ti ti-mail ti-lg"></i></div>
                                        <div>
                                            <p class="mb-0">البريد الإلكتروني</p>
                                            <h6 class="mb-0"><a href="mailto:m@e-tec.sa"
                                                    class="text-heading">m@e-tec.sa</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-12 col-xl-6 pb-12">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-label-success rounded p-1_5 me-3"><i
                                                class="ti ti-phone-call ti-lg"></i></div>
                                        <div>
                                            <p class="mb-0">الهاتف</p>
                                            <h6 class="mb-0"><a href="tel:+1234-568-963" class="text-heading">+1234 568
                                                    963</a></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- نموذج إرسال الرسالة -->
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-2">أرسل رسالة</h4>
                            <p class="mb-6">
                                إذا كنت تواجه مشكلة أو تحتاج إلى مساعدة في أي من الأمور التالية، فأنت في المكان الصحيح.
                            </p>
                            <form action="{{ route('supports.update', $support->id) }}" method="POST" enctype="multipart/form-data"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT') <!-- إضافة هذا السطر لتحديد طريقة الطلب PUT -->

                                <div class="row g-4">
                                    <!-- تصنيف التذكرة -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="ticket-classification">تصنيف التذكرة</label>
                                        <select id="ticket-classification" name="ticket_classification"
                                            class="form-select @error('ticket_classification') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('ticket_classification', $support->ticket_classification) ? '' : 'selected' }}>
                                                اختر تصنيف التذكرة
                                            </option>
                                            <option value="اقتراح" {{ old('ticket_classification', $support->ticket_classification) == 'اقتراح' ? 'selected' : '' }}>
                                                اقتراح
                                            </option>
                                            <option value="شكوى" {{ old('ticket_classification', $support->ticket_classification) == 'شكوى' ? 'selected' : '' }}>
                                                شكوى
                                            </option>
                                            <option value="تعديلات برمجية" {{ old('ticket_classification', $support->ticket_classification) == 'تعديلات برمجية' ? 'selected' : '' }}>
                                                تعديلات برمجية
                                            </option>
                                            {{-- <option value="اخرى">اخرى</option> --}}
                                        </select>
                                        @error('ticket_classification')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- عنوان الرسالة -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="title">عنوان الرسالة</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" name="title" placeholder="عنوان الرسالة الخاصة بك"
                                            value="{{ old('title', $support->title) }}" required />
                                        @error('title')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- الأولوية -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="priority">الأولوية</label>
                                        <select id="priority" name="priority"
                                            class="form-select @error('priority') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('priority', $support->priority) ? '' : 'selected' }}>اختر
                                                الأولوية</option>
                                            <option value="عاجلة" {{ old('priority', $support->priority) == 'عاجلة' ? 'selected' : '' }}>
                                                عاجلة
                                            </option>
                                            <option value="عالية" {{ old('priority', $support->priority) == 'عالية' ? 'selected' : '' }}>
                                                عالية
                                            </option>
                                            <option value="متوسطة" {{ old('priority', $support->priority) == 'متوسطة' ? 'selected' : '' }}>
                                                متوسطة
                                            </option>
                                            <option value="منخفضة" {{ old('priority', $support->priority) == 'منخفضة' ? 'selected' : '' }}>
                                                منخفضة
                                            </option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- الوصف -->
                                    <div class="col-12">
                                        <label class="form-label" for="description">الوصف</label>
                                        <textarea id="description" name="notes" required
                                            class="form-control @error('notes') is-invalid @enderror" rows="4"
                                            placeholder="أضف الوصف هنا">{{ old('notes', $support->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- المرفقات الحالية وخيار الحذف -->
                                    @if ($support->attachment)
                                        <div class="col-12">
                                            <label class="form-label">المرفق الحالي</label>
                                            <div class="mb-2">
                                                <a href="{{ asset('storage/' . $support->attachment) }}" target="_blank">عرض المرفق</a>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="delete_attachment" name="delete_attachment">
                                                <label class="form-check-label" for="delete_attachment">
                                                    حذف المرفق الحالي
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- المرفقات الجديدة -->
                                    <div class="col-12">
                                        <label class="form-label" for="attachment">مرفق جديد </label>
                                        <input type="file" id="attachment" name="attachment"
                                            class="form-control @error('attachment') is-invalid @enderror" />
                                        @error('attachment')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <!-- زر الإرسال -->
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">حفظ</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- إضافة JavaScript للتحكم في ظهور وإخفاء حقل تاريخ المشكلة -->
                <script>
                                            // التحقق من صحة النموذج باستخدام Bootstrap
                        (function () {
                            'use strict'

                            // الحصول على جميع النماذج التي تحتاج إلى التحقق
                            var forms = document.querySelectorAll('.needs-validation')

                            // تفعيل التحقق من الصحة على النماذج عند الإرسال
                            Array.prototype.slice.call(forms)
                            .forEach(function (form) {
                                form.addEventListener('submit', function (event) {
                                if (!form.checkValidity()) {
                                    event.preventDefault()
                                    event.stopPropagation()
                                }

                                form.classList.add('was-validated')
                                }, false)
                            })
                        })();

                </script>



            </div>
        </div>
    </section>
    <!-- اتصل بنا: النهاية -->
</div>
@endsection
