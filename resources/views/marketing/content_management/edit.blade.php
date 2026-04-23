@extends('layouts.layoutMaster')

@section('title', 'تعديل محتوى')

@section('breadcrumb')
    <li><a href="#">التسويق</a></li>
    <li><a href="{{ route('marketing.content-management.index') }}">إدارة المحتوى</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل محتوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل محتوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/marketing/content-management/content-management-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل المحتوى </span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-2">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">معلومات جدولة النشر</span>
                                <span class="bs-stepper-subtitle">تفاصيل الجدول</span>
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
                    <form id="form"
                        action="{{ route('marketing.content-management.update', $content_management->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- ========================================================================= --}}
                        {{--  تغيير 3: ملء كل الحقول بالبيانات باستخدام old() أو بيانات المودل $content --}}
                        {{-- ========================================================================= --}}
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                {{-- نوع المحتوى --}}
                                <div class="col-md-6">
                                    <label for="content_type_id" class="form-label">نوع المحتوى <span
                                            class="text-danger">*</span></label>
                                    <select id="content_type_id" name="content_type_id" class="form-select select2"
                                        data-placeholder="اختر نوع المحتوى" required>
                                        <option value=""></option>
                                        @foreach ($contentTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('content_type_id', $content_management->content_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- نمط النشر --}}
                                <div class="col-md-6">
                                    <label for="publishing_pattern_id" class="form-label">نمط النشر <span
                                            class="text-danger">*</span></label>
                                    <select id="publishing_pattern_id" name="publishing_pattern_id"
                                        class="form-select select2" data-placeholder="اختر نمط النشر " required>
                                        <option value=""></option>
                                        @foreach ($publishingPatterns as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('publishing_pattern_id', $content_management->publishing_pattern_id) == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- الهدف من المحتوى --}}
                                <div class="col-md-6">
                                    <label for="content_purpose_id" class="form-label">الهدف من المحتوى <span
                                            class="text-danger">*</span></label>
                                    <select id="content_purpose_id" name="content_purpose_id" class="form-select select2"
                                        data-placeholder="اختر الهدف من المحتوى" required>
                                        <option value=""></option>
                                        @foreach ($purposePieces as $pp)
                                            <option value="{{ $pp->id }}"
                                                {{ old('content_purpose_id', $content_management->content_purpose_id) == $pp->id ? 'selected' : '' }}>
                                                {{ $pp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- المنصة --}}
                                <div class="col-md-6">
                                    <label for="socials" class="form-label">المنصة <span
                                            class="text-danger">*</span></label>
                                    <select id="socials" name="socials[]" class="form-select select2" multiple
                                        data-placeholder="اختر المنصة أو المنصات" required>
                                        @foreach ($socials as $s)
                                            <option value="{{ $s->id }}"
                                                {{ in_array($s->id, old('socials', $content_management->socials->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                {{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- نوع الوسائط --}}
                                <div class="col-md-6">
                                    <label for="media_type" class="form-label">نوع الوسائط <span
                                            class="text-danger">*</span></label>
                                    <select id="media_type" name="media_type" class="form-select select2"
                                        data-placeholder="اختر نوع الوسائط">
                                        <option value=""></option>
                                        @foreach ($mediaTypes as $mt)
                                            <option value="{{ $mt->value }}"
                                                {{ old('media_type', $content_management->media_type->value) == $mt->value ? 'selected' : '' }}>
                                                {{ $mt->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- ⇨ معاينة الوسيط إن وُجد --}}


                                {{-- حقل الملف (مخفى مبدئياً – يُظهره JS) --}}
                                <div class="col-md-6" id="media_wrapper" style="display: none">
                                    <label for="media" class="form-label" id="media_label">الوسائط</label>
                                    <input type="file" name="media" id="media"
                                        value="{{ $content_management->media ?? '' }}" class="form-control">

                                    @if ($content_management->media)
                                        <div class="mt-2" id="existing_media">
                                            @if (in_array($content_management->media_type->value, ['image', 'image_text']))
                                                <img src="{{ asset('storage/' . $content_management->media) }}"
                                                    alt="Preview" class="img-thumbnail mb-2" style="max-width: 140px">
                                            @else
                                                <video src="{{ asset('storage/' . $content_management->media) }}" controls
                                                    class="mb-2" style="max-width: 180px; height: 120px"></video>
                                            @endif
                                            <p class="small text-muted">يمكنك استبدال الملف برفع ملف جديد</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- نص المحتوى --}}
                                <div class="col-md-12" id="content_text_wrapper" style="display: none">
                                    <label for="content_text" class="form-label">نص المحتوى
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="content_text" name="content_text" rows="3" class="form-control" required>{{ old('content_text', $content_management->content_text) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- الخطوة الثانية: معلومات الجدولة والنشر --}}
                        <div id="additional-info-2" class="content">
                            <div class="row g-3">
                                {{-- الحالة --}}
                                <div class="col-md-6">
                                    <label for="publication_status" class="form-label">الحالة <span
                                            class="text-danger">*</span></label>
                                    <select id="publication_status" name="publication_status" class="form-select select2"
                                        data-placeholder="اختر الحالة" required>
                                        <option value=""></option>
                                        @foreach ($contentStatus as $st)
                                            <option value="{{ $st->value }}"
                                                {{ old('publication_status', $content_management->publication_status->value) == $st->value ? 'selected' : '' }}>
                                                {{ $st->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- حاوية تاريخ النشر --}}
                                <div id="published-fields-container" class="col-md-6" style="display: none;">
                                    <label for="publication_date" class="form-label">تاريخ النشر <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="publication_date" name="publication_date"
                                        class="form-control"
                                        value="{{ old('publication_date', $content_management->publication_date?->format('Y-m-d')) }}">
                                </div>


                                {{-- حاوية نوع الجدولة --}}
                                <div id="scheduling-type-container" class="col-md-6" style="display: none;">
                                    <label class="form-label">نوع الجدولة <span class="text-danger">*</span></label>
                                    <select name="publish_type" id="publish_type" class="form-select select2"
                                        data-placeholder="اختر نوع الجدولة">
                                        <option value=""></option>
                                        @foreach ($publishType as $item)
                                            <option value="{{ $item->value }}"
                                                {{ old('publish_type', $content_management->publish_type?->value) == $item->value ? 'selected' : '' }}>
                                                {{ $item->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="scheduling-details-container" class="row g-3 mt-1" style="display: none;">
                                {{-- مرة واحدة --}}
                                <div class="col-md-6 auto-one-time" style="display: none;">
                                    <label class="form-label">تاريخ ووقت النشر لمرة واحدة <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local" id="one_time_at" name="one_time_at"
                                        class="form-control"
                                        value="{{ old('one_time_at', $content_management->one_time_at?->format('Y-m-d\TH:i')) }}">
                                </div>

                                {{-- نمط التكرار --}}
                                <div class="col-md-6 auto-recurring" style="display: none;">
                                    <label class="form-label">نمط التكرار <span class="text-danger">*</span></label>
                                    <select name="recurring_type" id="recurring_type" class="form-select select2"
                                        data-placeholder="اختر نمط التكرار">
                                        <option value=""></option>
                                        @foreach ($recurringType as $item)
                                            <option value="{{ $item->value }}"
                                                {{ old('recurring_type', $content_management->recurring_type?->value) == $item->value ? 'selected' : '' }}>
                                                {{ $item->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- وقت التكرار اليومي --}}
                                <div class="col-md-6 auto-recurring" style="display: none;">
                                    <label class="form-label">وقت النشر <span class="text-danger">*</span></label>
                                    <input type="time" name="publish_time" class="form-control"
                                        value="{{ old('publish_time', $content_management->publish_time) }}">
                                </div>

                                {{-- يوم الشهر (شهري) --}}
                                <div class="col-md-6 auto-recurring recurring-monthly" style="display: none;">
                                    <label class="form-label">يوم الشهر <span class="text-danger">*</span></label>
                                    <input type="number" name="month_day" class="form-control" min="1"
                                        max="31" value="{{ old('month_day', $content_management->month_day) }}">
                                    <small class="text-primary">
                                        ⚠️ سيتم تخطي النشر تلقائياً في الأشهر التي لا تحتوي على اليوم المحدد
                                    </small>
                                </div>

                                {{-- أيام الأسبوع (أسبوعي) --}}
                                <div class="col-md-6 auto-recurring recurring-weekly" style="display: none;">
                                    <label class="form-label">أيام النشر <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($weekDays as $day)
                                            <div class="form-check">
                                                <input type="checkbox" name="week_days[]" value="{{ $day->value }}"
                                                    id="day-{{ $day->value }}" class="form-check-input"
                                                    {{ in_array($day->value, old('week_days', $content_management->week_days ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="day-{{ $day->value }}">{{ $day->label() }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- تاريخ البدء --}}
                                <div class="col-md-6 auto-recurring" style="display: none;">
                                    <label class="form-label">تاريخ البدء <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ old('start_date', $content_management->start_date) }}">
                                </div>

                                {{-- تاريخ الانتهاء --}}
                                <div class="col-md-6 auto-recurring" style="display: none;">
                                    <label class="form-label">تاريخ الانتهاء <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ old('end_date', $content_management->end_date) }}">
                                </div>

                                {{-- تفعيل الجدولة --}}
                                <div class="col-md-6 ">
                                    <label class="form-label">تفعيل النشر التلقائي؟</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                            {{ old('is_active', $content_management->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نعم</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">حفظ التعديلات</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
