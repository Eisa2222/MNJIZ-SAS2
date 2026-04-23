@extends('layouts.layoutMaster')

@section('title', 'إضافة حجز')

@section('breadcrumb')
    <li><a href="{{ route('meeting-rooms.index') }}">قاعات الإجتماعات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة حجز</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة حجز" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <style>
        .hide-elements {
            display: none !important;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/meeting-rooms/meeting-rooms-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل الحجز </span>
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
                    <form id="form" action="{{ route('meeting-rooms.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="title" class="form-label">عنوان الإجتماع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="title" name="title" class="form-control"
                                        value="{{ old('title') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="hall" class="form-label">
                                        القاعة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="hall" name="hall" class="form-select select2"
                                        data-placeholder="اختر القاعة" required>
                                        <option value=""></option>
                                        <option value="big" {{ old('hall') == 'big' ? 'selected' : '' }}>
                                            القاعة الكبرى
                                        </option>
                                        <option value="small" {{ old('hall') == 'small' ? 'selected' : '' }}>
                                            القاعة الصغرى
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="date" class="form-label">اليوم
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="date" name="date" class="form-control"
                                        value="{{ old('date') }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="from_time" class="form-label">من الساعة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" id="from_time" name="from_time" class="form-control"
                                        value="{{ old('from_time') }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="to_time" class="form-label">حتى الساعة</label>
                                    <input type="time" id="to_time" name="to_time" class="form-control"
                                        value="{{ old('to_time') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="meeting_participants" class="form-label">أطراف الإجتماع</label>
                                    <select id="meeting_participants" name="meeting_participants[]"
                                        class="form-control select2" multiple="multiple"
                                        data-placeholder="اختر أطراف الإجتماع">
                                        <option value="employees">الموظفين</option>
                                        <option value="customers">العملاء</option>
                                        <option value="additional">بريد آخر</option>
                                    </select>
                                </div>

                                <!-- حقل الموظفين -->
                                <div class="col-md-6 hide-elements" id="field-employees">
                                    <label for="attendees_employee" class="form-label">اختر الموظفين</label>
                                    <select id="attendees_employee" name="attendees_employee[]" class="form-control select2"
                                        data-placeholder="اختر الموظفين" multiple="multiple">
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}

                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- حقل العملاء -->
                                <div class="col-md-6 hide-elements" id="field-customers">
                                    <label for="attendees_customer" class="form-label">اختر العملاء</label>
                                    <select id="attendees_customer" name="attendees_customer[]"
                                        class="form-control select2" data-placeholder="اختر العملاء" multiple="multiple">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}

                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- حقل البريد الإضافي -->
                                <div class="col-md-6 hide-elements" id="field-additional">
                                    <label for="additional_emails" class="form-label">إضافة طرف اخر</label>
                                    <select id="additional_emails" name="additional_emails[]"
                                        class="form-control select2" multiple="multiple"
                                        data-placeholder="أدخل بريد إلكتروني جديد">
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">ملاحظات الإجتماع</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // دالة عامة لتفعيل Select2
            function initializeSelect2(selector, options = {}) {
                $(selector).select2({
                    placeholder: options.placeholder || "اختر قيمة",
                    allowClear: true,
                    width: "100%",
                    language: "ar",
                    dir: "rtl",
                    ...options,
                });
            }

            // تهيئة Select2 لجميع الحقول الأساسية
            initializeSelect2(".select2");

            // تعريف الحقول المرتبطة بأطراف الاجتماع
            const participantFields = {
                'employees': '#field-employees',
                'customers': '#field-customers',
                'additional': '#field-additional'
            };

            // التأكد من أن جميع الحقول مخفية في البداية
            Object.values(participantFields).forEach(function(fieldSelector) {
                $(fieldSelector).addClass('hide-elements');
                $(fieldSelector).find('select').removeAttr('required');
            });

            // معالج تغيير أطراف الاجتماع
            $('#meeting_participants').on('change', function() {
                const selectedValues = $(this).val() || [];

                // إخفاء وإفراغ جميع الحقول أولاً
                Object.entries(participantFields).forEach(function([key, fieldSelector]) {
                    const $field = $(fieldSelector);
                    const $select = $field.find('select');

                    if (!selectedValues.includes(key)) {
                        // إخفاء الحقل
                        $field.addClass('hide-elements');
                        $select.removeAttr('required');

                        // إفراغ القيم بشكل فوري ومؤكد
                        $select.val(null);
                        if ($select.hasClass('select2-hidden-accessible')) {
                            // $select.select2('val', '');
                            // $select.trigger('change.select2');
                        }

                        // إفراغ إضافي للتأكد
                        setTimeout(function() {
                            $select.empty().trigger('change');
                            // إعادة تحميل الخيارات الأصلية
                            if (key === 'employees') {
                                @foreach ($employees as $employee)
                                    $select.append(
                                        '<option value="{{ $employee->id }}">{{ $employee->name }} </option>'
                                    );
                                @endforeach
                            } else if (key === 'customers') {
                                @foreach ($customers as $customer)
                                    $select.append(
                                        '<option value="{{ $customer->id }}">{{ $customer->name }} </option>'
                                    );
                                @endforeach
                            }
                        }, 50);
                    } else {
                        // إظهار الحقل
                        $field.removeClass('hide-elements');
                        $select.attr('required', 'required');
                    }
                });
            });

            // تهيئة خاصة لحقل البريد الإضافي
            $('#additional_emails').select2({
                placeholder: "أدخل بريد إلكتروني جديد",
                tags: true,
                tokenSeparators: [',', ' '],
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl',
                createTag: function(params) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    if (!emailPattern.test(term)) {
                        return null;
                    }

                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // التحقق من صحة البريد الإلكتروني عند الاختيار
            $('#additional_emails').on('select2:select', function(e) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const selectedEmail = e.params.data.text;

                if (!emailPattern.test(selectedEmail)) {
                    const $select = $(this);
                    let values = $select.val() || [];
                    values = values.filter(function(value) {
                        return value !== selectedEmail;
                    });
                    $select.val(values).trigger('change');

                    // عرض رسالة خطأ
                    if (typeof toastr !== 'undefined') {
                        toastr.error('يرجى إدخال بريد إلكتروني صالح.', 'خطأ');
                    } else {
                        alert('يرجى إدخال بريد إلكتروني صالح');
                    }
                }
            });

            // استعادة القيم المحفوظة عند إعادة تحميل الصفحة (في حالة وجود أخطاء)
            @if (old('meeting_participants'))
                const oldParticipants = @json(old('meeting_participants'));
                $('#meeting_participants').val(oldParticipants).trigger('change');

                @if (old('attendees_employee'))
                    $('#attendees_employee').val(@json(old('attendees_employee'))).trigger('change');
                @endif

                @if (old('attendees_customer'))
                    $('#attendees_customer').val(@json(old('attendees_customer'))).trigger('change');
                @endif

                @if (old('additional_emails'))
                    $('#additional_emails').val(@json(old('additional_emails'))).trigger('change');
                @endif
            @endif
        });
    </script>
@endsection
