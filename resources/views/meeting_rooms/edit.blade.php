@extends('layouts.layoutMaster')

@section('title', 'تعديل حجز')

@section('breadcrumb')
    <li>
        <a href="{{ route('meeting-rooms.index') }}">قاعات الإجتماعات</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل حجز</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل حجز" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <style>
        .hide-elements {
            display: none !important
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
    @php
        $existingTypes = $meeting_room->participants->pluck('type')->unique()->values()->all();

        // ✅ الصحيح: نجلب employee_id و customer_id من جدول participants
        $existingEmp = $meeting_room->participants
            ->where('type', 'employees')
            ->pluck('employee_id') // كان pluck('id')
            ->filter() // احتياط
            ->values()
            ->all();

        $existingCus = $meeting_room->participants
            ->where('type', 'customers')
            ->pluck('customer_id') // كان pluck('id')
            ->filter()
            ->values()
            ->all();

        $existingExt = $meeting_room->participants->where('type', 'additional')->pluck('email')->values()->all();

        $preTypes = old('meeting_participants', $existingTypes);
        $preEmp = old('attendees_employee', $existingEmp);
        $preCus = old('attendees_customer', $existingCus);
        $preExt = old('additional_emails', $existingExt);

        $dateValue = old('date', \Illuminate\Support\Carbon::parse($meeting_room->date)->format('Y-m-d'));
        $fromValue = old('from_time', \Illuminate\Support\Carbon::parse($meeting_room->from_time)->format('H:i'));
        $toValue = old('to_time', \Illuminate\Support\Carbon::parse($meeting_room->to_time)->format('H:i'));
    @endphp


    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل الحجز</span>
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

                    <form id="form" action="{{ route('meeting-rooms.update', $meeting_room->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="title" class="form-label">عنوان الإجتماع <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" class="form-control"
                                        value="{{ old('title', $meeting_room->title) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="hall" class="form-label">القاعة <span
                                            class="text-danger">*</span></label>
                                    <select id="hall" name="hall" class="form-select select2"
                                        data-placeholder="اختر القاعة" required>
                                        <option value=""></option>
                                        <option value="big"
                                            {{ old('hall', $meeting_room->hall) === 'big' ? 'selected' : '' }}>القاعة الكبرى
                                        </option>
                                        <option value="small"
                                            {{ old('hall', $meeting_room->hall) === 'small' ? 'selected' : '' }}>القاعة
                                            الصغرى</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="date" class="form-label">اليوم <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="date" name="date" class="form-control"
                                        value="{{ $dateValue }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="from_time" class="form-label">من الساعة <span
                                            class="text-danger">*</span></label>
                                    <input type="time" id="from_time" name="from_time" class="form-control"
                                        value="{{ $fromValue }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="to_time" class="form-label">حتى الساعة <span
                                            class="text-danger">*</span></label>
                                    <input type="time" id="to_time" name="to_time" class="form-control"
                                        value="{{ $toValue }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="meeting_participants" class="form-label">أطراف الإجتماع <span
                                            class="text-danger">*</span></label>
                                    <select id="meeting_participants" name="meeting_participants[]"
                                        class="form-control select2" multiple data-placeholder="اختر أطراف الإجتماع"
                                        required>
                                        <option value="employees"
                                            {{ in_array('employees', $preTypes ?? [], true) ? 'selected' : '' }}>الموظفين
                                        </option>
                                        <option value="customers"
                                            {{ in_array('customers', $preTypes ?? [], true) ? 'selected' : '' }}>العملاء
                                        </option>
                                        <option value="additional"
                                            {{ in_array('additional', $preTypes ?? [], true) ? 'selected' : '' }}>بريد آخر
                                        </option>
                                    </select>
                                </div>

                                <!-- الموظفون -->
                                <div class="col-md-6 hide-elements" id="field-employees">
                                    <label for="attendees_employee" class="form-label">اختر الموظفين</label>
                                    <select id="attendees_employee" name="attendees_employee[]" class="form-control select2"
                                        multiple data-placeholder="اختر الموظفين">
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ in_array($employee->id, $preEmp ?? [], true) ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- العملاء -->
                                <div class="col-md-6 hide-elements" id="field-customers">
                                    <label for="attendees_customer" class="form-label">اختر العملاء</label>
                                    <select id="attendees_customer" name="attendees_customer[]"
                                        class="form-control select2" multiple data-placeholder="اختر العملاء">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ in_array($customer->id, $preCus ?? [], true) ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- بريد إضافي (tags) -->
                                <div class="col-md-6 hide-elements" id="field-additional">
                                    <label for="additional_emails" class="form-label">إضافة طرف آخر</label>
                                    <select id="additional_emails" name="additional_emails[]"
                                        class="form-control select2" multiple data-placeholder="أدخل بريد إلكتروني جديد">
                                        @foreach ($preExt as $mail)
                                            <option value="{{ $mail }}" selected>{{ $mail }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">ملاحظات الإجتماع</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $meeting_room->notes) }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">

                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            function initializeSelect2(selector, options = {}) {
                $(selector).select2({
                    placeholder: options.placeholder || "اختر قيمة",
                    allowClear: true,
                    width: "100%",
                    language: "ar",
                    dir: "rtl",
                    ...options
                });
            }
            initializeSelect2(".select2");

            const participantFields = {
                'employees': '#field-employees',
                'customers': '#field-customers',
                'additional': '#field-additional'
            };

            // إخفاء مبدئي
            Object.values(participantFields).forEach(sel => $(sel).addClass('hide-elements').find('select')
                .removeAttr('required'));

            // إظهار/إخفاء حسب الاختيار
            $('#meeting_participants').on('change', function() {
                const selected = $(this).val() || [];
                Object.entries(participantFields).forEach(([key, sel]) => {
                    const $field = $(sel),
                        $select = $field.find('select');
                    if (selected.includes(key)) {
                        $field.removeClass('hide-elements');
                        $select.attr('required', 'required');
                    } else {
                        $field.addClass('hide-elements');
                        $select.removeAttr('required').val(null).trigger('change');
                    }
                });
            });

            // تهيئة.tags للبريد الإضافي
            $('#additional_emails').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                createTag: function(params) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const term = $.trim(params.term);
                    if (!term || !emailPattern.test(term)) return null;
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // ضبط القيم الحالية من السيرفر
            const preTypes = @json($preTypes ?? []);
            if (preTypes.length) {
                $('#meeting_participants').val(preTypes).trigger('change');
            } else {
                $('#meeting_participants').trigger('change');
            }
        });
    </script>
@endsection
