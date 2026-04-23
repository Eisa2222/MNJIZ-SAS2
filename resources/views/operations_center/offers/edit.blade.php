@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات العرض')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.offers.index') }}"> العروض</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات العرض</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات العرض" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('vendor-script')

    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-wizard-validation-offers.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            // تهيئة Select2 مع تنسيق العميل
            $('#customer_id').select2({
                placeholder: 'اختر العميل',
                templateResult: formatCustomer,
                templateSelection: formatCustomer
            });

            function formatCustomer(customer) {
                if (!customer.id) {
                    return customer.text;
                }
                // إضافة الأيقونة قبل اسم العميل
                var $customer = $(
                    '<span><i class="ti ti-rosette-discount-check-filled text-success" title="عميل" style="margin-right: 5px;"></i>' +
                    ' ' +
                    customer.text + '</span>'
                );
                return $customer;
            }


        });

        window.moment = moment;

        function loadHijriDatePicker() {
            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);
            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker();
            };
        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
        // تهيئة محررات Quill وتحديث الحقول المخفية
        document.addEventListener('DOMContentLoaded', function() {
            // تعريف مصفوفة تحتوي على معرفات المحررات
            var editors = [
                // {
                //     editorId: 'editor-scope-of-work',
                //     hiddenInputId: 'scope_of_work',
                //     initialContent: @json(old('scope_of_work', $offer->scope_of_work))
                // },
                {
                    editorId: 'editor-technical-offer',
                    hiddenInputId: 'technical_offer',
                    initialContent: @json(old('technical_offer', $offer->technical_offer))
                },
                {
                    editorId: 'editor-financial-offer',
                    hiddenInputId: 'financial_offer',
                    initialContent: @json(old('financial_offer', $offer->financial_offer))
                },
                // {
                //     editorId: 'editor-offer-terms',
                //     hiddenInputId: 'offer_terms',
                //     initialContent: @json(old('offer_terms', $offer->offer_terms))
                // }
            ];

            // تخزين كائنات المحررات
            var quillEditors = {};

            // تهيئة كل محرر Quill مع تعيين المحتوى الأولي
            editors.forEach(function(item) {
                quillEditors[item.editorId] = new Quill('#' + item.editorId, {
                    theme: 'snow',
                    placeholder: 'اكتب هنا...',
                    modules: {
                        toolbar: [
                            // أدوات العناوين
                            [{
                                header: [1, 2, 3, 4, 5, 6, false]
                            }],

                            // أدوات التنسيق النصي
                            ['bold', 'italic', 'underline', 'strike'],

                            // أدوات النصوص
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }],
                            [{
                                script: 'sub'
                            }, {
                                script: 'super'
                            }], // نص علوي وسفلي
                            [{
                                indent: '-1'
                            }, {
                                indent: '+1'
                            }], // زيادة ونقصان المسافة البادئة
                            [{
                                direction: 'rtl'
                            }], // اتجاه النص

                            // خيارات الخطوط والمحاذاة
                            [{
                                size: ['small', false, 'large', 'huge']
                            }], // أحجام الخط
                            [{
                                header: [1, 2, 3, 4, 5, 6, false]
                            }], // العناوين
                            [{
                                align: []
                            }], // المحاذاة

                            // أدوات اللون والخلفية
                            [{
                                color: []
                            }, {
                                background: []
                            }], // الألوان

                            // الوسائط المتعددة
                            ['link', 'image', 'video'],

                            // أدوات الحذف والإدراج
                            ['clean'], // إزالة التنسيقات
                        ]
                    }
                });

                // تعيين المحتوى المبدئي إلى محرر Quill
                if (item.initialContent) {
                    quillEditors[item.editorId].clipboard.dangerouslyPasteHTML(item.initialContent);
                }

                // تعيين المحتوى المبدئي إلى الحقل المخفي عند تحميل الصفحة
                var initialContent = quillEditors[item.editorId].root.innerHTML;
                document.getElementById(item.hiddenInputId).value = initialContent;

                // تحديث الحقل المخفي عند تغيير المحتوى
                quillEditors[item.editorId].on('text-change', function() {
                    var content = quillEditors[item.editorId].root.innerHTML;
                    document.getElementById(item.hiddenInputId).value = content;
                });
            });

            // تحديث الحقول المخفية قبل إرسال النموذج كخطوة احتياطية
            var form = document.getElementById('power-form');
            form.addEventListener('submit', function() {
                editors.forEach(function(item) {
                    var content = quillEditors[item.editorId].root.innerHTML;
                    document.getElementById(item.hiddenInputId).value = content;
                });
            });
        });
    </script>
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل العرض</span>
                            </span>
                        </button>
                    </div>

                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#detailed-offer">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الإضافية</span>
                                <span class="bs-stepper-subtitle">العرض الفني، العرض المالي</span>
                            </span>
                        </button>
                    </div>

                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="power-form" action="{{ route('operations-center.offers.update', $offer->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="offer_name" class="form-label">اسم العرض</label>
                                    <input type="text" id="offer_name" name="offer_name" class="form-control"
                                        value="{{ old('offer_name', $offer->offer_name) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">العميل</label>
                                    <select id="customer_id" name="customer_id" class="form-select select2" required
                                        data-placeholder="اختر العميل">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id', $offer->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="relationship_manager" class="form-label">
                                        مسؤول العلاقات
                                        <span title="لتغيير مسؤول العلاقة يجب تغييره من قائمة العملاء أولاً."
                                            style="color: #d5a047;">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select id="relationship_manager" name="relationship_manager"
                                        class="form-select select2" disabled data-placeholder=" مسؤول العلاقات">
                                        <option value=""></option>
                                        @if ($offer->relationshipManager)
                                            <option value="{{ $offer->relationshipManager->id }}" selected>
                                                {{ $offer->relationshipManager->name }}</option>
                                        @else
                                            <option value="">{{ 'اختر مسؤول العلاقات' }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="start_date" class="form-label">تاريخ بداية العرض</label>
                                    <input type="text" id="start_date" name="start_date"
                                        class="form-control hijri-picker"
                                        value="{{ old('start_date', $offer->hijri_start_date) }}" required
                                        autocomplete="off" />
                                </div>

                                <div class="mb-4 col-md-3">
                                    <label for="is_private_and_secret" class="form-label">سري و خاص</label>
                                    <span title="سيتم اضافة عبارة سري و خاص في العرض" style="color: #d5a047;">
                                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                    </span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_private_and_secret"
                                            name="is_private_and_secret"
                                            {{ old('is_private_and_secret', $offer->is_private_and_secret) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_private_and_secret">تفعيل وضع
                                            العرض سري و خاص</label>

                                    </div>
                                </div>


                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="detailed-offer" class="content">
                            <div class="row g-6">

                                <!-- العرض الفني -->
                                <div class="col-md-12">
                                    <label class="form-label" for="technical_offer">العرض الفني</label>
                                    <div id="editor-technical-offer" style="height: 300px;">
                                        {!! old('technical_offer', $offer->technical_offer) !!}
                                    </div>
                                    <input type="hidden" name="technical_offer" id="technical_offer">
                                </div>

                                <!-- العرض المالي -->
                                <div class="col-md-12">
                                    <label class="form-label" for="financial_offer">العرض المالي</label>
                                    <div id="editor-financial-offer" style="height: 300px;">
                                        {!! old('financial_offer', $offer->financial_offer) !!}
                                    </div>
                                    <input type="hidden" name="financial_offer" id="financial_offer">
                                </div>




                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>

                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            $('#customer_id').on('change', function() {
                var customerId = $(this).val();
                if (customerId) {
                    $.ajax({
                        url: "{{ route('operations-center.offers.get-relationship-manager', ['customer' => ':id']) }}"
                            .replace(':id', customerId),
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data) {
                                // تحديث حقل مسؤول العلاقات
                                $('#relationship_manager').empty();
                                $('#relationship_manager').append('<option value="">' +
                                    'اختر مسؤول العلاقات' + '</option>');
                                $('#relationship_manager').append('<option value="' + data.id +
                                    '" selected>' + data.name + '</option>');
                            }
                        },
                        error: function() {
                            $('#relationship_manager').empty();
                            $('#relationship_manager').append('<option value="">' +
                                'لا يوجد مسؤول علاقات' + '</option>');
                        }
                    });
                } else {
                    $('#relationship_manager').empty();
                    $('#relationship_manager').append('<option value="">' + 'اختر العميل أولاً' +
                        '</option>');
                }
            });
        });
    </script>

@endsection
