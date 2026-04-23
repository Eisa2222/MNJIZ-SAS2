@extends('layouts.layoutMaster')

@section('title', ' تفاصيل الاجتماع')

@section('breadcrumb')
    <li><a href="{{ route('microsoft.teams.index') }}">الاجتماعات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الاجتماع</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الإجتماع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite('resources/assets/vendor/scss/pages/app-invoice.scss')
    <style>
        <style>@media print {
            .invoice-preview-card {
                padding: 20px !important;
                background: white !important;
            }

            .card-body {
                page-break-inside: avoid;
            }

            img {
                max-width: 100% !important;
            }
        }
    </style>
    </style>
@endsection

{{-- 
    يُفضّل أن يكون رفع ملفات المكتبات في رأس الصفحة أو في المكان المناسب قبل استخدامه
    كي لا تواجه مشكلة عدم تعريف jsPDF أو html2canvas
--}}
@section('vendor-script')
    {{-- مكتبة jsPDF (نسخة UMD) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    {{-- مكتبة html2canvas --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    {{-- هنا نعرّف jsPDF من النافذة (ضروري للنسخة UMD) --}}
    <script>
        const {
            jsPDF
        } = window.jspdf;
    </script>
@endsection

@section('content')
    <div class="row invoice-preview" id="meeting-details">
        <!-- تفاصيل الاجتماع -->
        <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6">
            <div class="card invoice-preview-card">
                <div class="card-body">
                    <!-- الهيدر -->
                    <div class="d-flex justify-content-between mb-4">
                        <div class="company-logo">
                            @if (App\Helpers\SettingsHelper::get('horizontal_header_image'))
                                <img src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_header_image')) }}"
                                    alt="Header Image" class="mb-3" style="max-height: 100px; object-fit: cover;">
                            @endif
                        </div>
                    </div>

                    <!-- معلومات الاجتماع -->
                    <div class="d-flex justify-content-between pt-10">
                        <div class="meeting-info">
                            <h5 class="mb-2">تفاصيل الاجتماع</h5>
                            <p class="mb-1"><strong>عنوان الاجتماع:</strong> {{ $meeting['subject'] }}</p>
                            <p class="mb-1"><strong>مجال الاجتماع:</strong> {{ $meeting['meeting_field'] }}</p>
                            <p class="mb-1"><strong>عدد الحضور:</strong> {{ $meeting['attendees_count'] }}</p>
                            <p class="mb-1"><strong>منظم الاجتماع:</strong> {{ $meeting['organizer'] }}</p>
                        </div>
                        <div class="meeting-dates">
                            <h5 class="mb-2">توقيت الإجتماع</h5>
                            <p class="mb-1"><strong></strong> {{ $meeting['startDateTime'] }}</p>
                            <p class="mb-1"><strong></strong> {{ $meeting['endDateTime'] }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- نقاط ومخرجات الاجتماع -->
                    <div class="meeting-content mb-4">
                        <div class="mb-4">
                            <h6 class="mb-3">نقاط الاجتماع</h6>
                            <div class="p-3 bg-light rounded">
                                {{ $meeting['meeting_points'] ?? 'لم يتم تحديد نقاط للاجتماع' }}
                            </div>
                        </div>

                        <div>
                            <h6 class="mb-3">مخرجات الاجتماع</h6>
                            <div class="p-3 bg-light rounded">
                                {{ $meeting['meeting_outputs'] ?? 'لم يتم تحديد مخرجات بعد' }}
                            </div>
                        </div>
                    </div>

                    <!-- الفوتر -->
                    <div class="pt-5">
                        @if (App\Helpers\SettingsHelper::get('horizontal_footer_image'))
                            <img src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_footer_image')) }}"
                                alt="Footer Image" class="w-100" style=" object-fit: cover;">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- الإجراءات -->
        <div class="col-xl-3 col-md-4 col-12">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary d-grid w-100 mb-3" onclick="exportToPDF()">
                        <span class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-file-download me-2"></i>تصدير PDF
                        </span>
                    </button>

                    @if (!$isMeetingEnded)
                        <a href="{{ $meeting['joinWebUrl'] }}" class="btn btn-label-primary d-grid w-100 mb-3">
                            <span class="d-flex align-items-center justify-content-center">
                                <i class="ti ti-video me-2"></i>انضمام للاجتماع
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- كود PDF -->
    <script>
        // دالة التصدير إلى PDF
        function exportToPDF() {
            // حدد العنصر الذي سيُحوّل إلى صورة
            const element = document.querySelector('.invoice-preview-card');

            // تحويل العنصر إلى Canvas
            html2canvas(element, {
                scale: 2, // لتكبير الصورة لجودة أعلى
                useCORS: true, // السماح بجلب الصور من نفس الدومين
                logging: false
            }).then(canvas => {
                // إنشاء كائن PDF
                const pdf = new jsPDF('p', 'mm', 'a4');

                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();

                const imgData = canvas.toDataURL('image/png');
                // حساب أبعاد الصورة بناءً على عرض الصفحة
                const imgWidth = pageWidth - 20; // هامش 10ملم من الجانبين
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                let position = 10; // بداية من أعلى الصفحة مع هامش 10ملم
                let heightLeft = imgHeight; // المسافة المتبقية من الصورة للطباعة

                // إضافة الجزء الأول من الصورة للصفحة الأولى
                pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                heightLeft -= (pageHeight - 20);

                // في حالة كان المحتوى يمتد على أكثر من صفحة
                while (heightLeft > 0) {
                    pdf.addPage();
                    position = 10 - (imgHeight - heightLeft);
                    pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                    heightLeft -= (pageHeight - 20);
                }

                // إضافة ترقيم للصفحات
                const totalPages = pdf.internal.getNumberOfPages();
                for (let i = 1; i <= totalPages; i++) {
                    pdf.setPage(i);
                    pdf.setFontSize(8);
                    pdf.text(
                        `Page ${i} From ${totalPages}`,
                        pageWidth / 2,
                        pageHeight - 10, {
                            align: 'center'
                        }
                    );
                }

                // حفظ ملف الـ PDF
                pdf.save(`تفاصيل-الاجتماع-${new Date().toISOString().split('T')[0]}.pdf`);
            });
        }
    </script>
@endsection
