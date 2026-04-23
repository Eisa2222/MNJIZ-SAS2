@extends('layouts.layoutMaster')

@section('title', 'تعديل إستبيان')

@section('breadcrumb')
    <li><a href="{{ route('surveys.index') }}">إدارة الإستبيانات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل إستبيان</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل إستبيان" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/surveys/surveys-request-wizard.js'])
    {{-- مهم: تهيئة عدّاد الأسئلة بناءً على الموجود --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عدّ الأسئلة الحالية لضبط questionIndex في ملف JS
            if (typeof window.$ !== 'undefined') {
                window.$(function() {
                    if (typeof window.questionIndex !== 'undefined') {
                        window.questionIndex = window.$('#questionsContainer .question-item').length || 0;
                    }
                    // إخفاء/إظهار تنبيه "لا توجد أسئلة"
                    const hasQs = window.$('#questionsContainer .question-item').length > 0;
                    window.$('#noQuestionsAlert')[hasQs ? 'hide' : 'show']();
                });
            }
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
                                <span class="bs-stepper-subtitle">تعديل بيانات الاستبيان</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-2">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">الأسئلة والخيارات</span>
                                <span class="bs-stepper-subtitle">تعديل أسئلة الاستبيان</span>
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

                    <form id="form" action="{{ route('surveys.update', $survey->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- الخطوة 1 --}}
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="type" class="form-label">نوع الاستبيان <span
                                            class="text-danger">*</span></label>
                                    <select id="type" name="type" class="form-select select2"
                                        data-placeholder="اختر نوع الاستبيان" required>
                                        <option value="">اختر النوع</option>
                                        @foreach ($surveyTypes as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('type', $survey->type->value) == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="title" class="form-label">عنوان الإستبيان <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" class="form-control"
                                        value="{{ old('title', $survey->title) }}" required>
                                </div>

                                <div class="col-md-12">
                                    <label for="message_template" class="form-label">قالب الرسالة <span
                                            class="text-danger">*</span></label>
                                    <textarea id="message_template" name="message_template" class="form-control" rows="3" required>{{ old('message_template', $survey->message_template) }}</textarea>
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $survey->description) }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- الخطوة 2 --}}
                        <div id="additional-info-2" class="content">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">أسئلة الاستبيان</h5>
                                    <button type="button" class="btn btn-primary btn-sm" id="addQuestionBtn">
                                        <i class="ti ti-plus me-1"></i> إضافة سؤال
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="questionsContainer">
                                        @php
                                            $oldQuestions = old('questions');
                                            $questions = is_array($oldQuestions)
                                                ? $oldQuestions
                                                : $survey->questions
                                                    ->map(function ($q) {
                                                        return [
                                                            'question_text' => $q->question_text,
                                                            'options' => $q->options
                                                                ->map(fn($o) => ['option_text' => $o->option_text])
                                                                ->toArray(),
                                                        ];
                                                    })
                                                    ->toArray();
                                        @endphp

                                        @if (empty($questions))
                                            <div class="alert alert-info text-center" id="noQuestionsAlert">
                                                <i class="ti ti-info-circle me-2"></i>
                                                لا توجد أسئلة مضافة بعد. اضغط على "إضافة سؤال" لبدء إضافة الأسئلة.
                                            </div>
                                        @else
                                            @foreach ($questions as $qIndex => $q)
                                                <div class="question-item border rounded p-3 mb-3"
                                                    data-question-index="{{ $qIndex }}">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <h6 class="question-title">السؤال <span
                                                                class="question-number">{{ $qIndex + 1 }}</span></h6>
                                                        <div class="btn-group btn-group-sm">

                                                            <button type="button"
                                                                class="btn btn-outline-danger remove-question"
                                                                title="حذف السؤال">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <label class="form-label">نص السؤال <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea name="questions[{{ $qIndex }}][question_text]" class="form-control question-text" rows="2"
                                                                placeholder="أدخل نص السؤال هنا..." required>{{ $q['question_text'] }}</textarea>
                                                        </div>

                                                        <div class="col-12">
                                                            <div class="card">
                                                                <div
                                                                    class="card-header d-flex justify-content-between align-items-center py-2">
                                                                    <small class="text-muted">خيارات الإجابة</small>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary add-option">
                                                                        <i class="ti ti-plus me-1"></i>إضافة خيار
                                                                    </button>
                                                                </div>
                                                                <div class="card-body py-2">
                                                                    <div class="options-list">
                                                                        @if (!empty($q['options']))
                                                                            @foreach ($q['options'] as $oIndex => $opt)
                                                                                <div class="option-item d-flex align-items-center mb-2"
                                                                                    data-option-index="{{ $oIndex }}">
                                                                                    <div class="flex-grow-1 me-2">
                                                                                        <input type="text"
                                                                                            name="questions[{{ $qIndex }}][options][{{ $oIndex }}][option_text]"
                                                                                            class="form-control option-text"
                                                                                            value="{{ $opt['option_text'] }}"
                                                                                            placeholder="نص الخيار..."
                                                                                            required>
                                                                                    </div>
                                                                                    <div class="btn-group btn-group-sm">
                                                                                        <button type="button"
                                                                                            class="btn btn-outline-danger remove-option"
                                                                                            title="حذف الخيار">
                                                                                            <i
                                                                                                class="ti ti-trash ti-xs"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="ti ti-device-floppy me-1"></i> حفظ التعديلات
                                </button>
                            </div>
                        </div>

                        {{-- Templates: نسخة واحدة فقط لكل تمبليت --}}
                        <template id="questionTemplate">
                            <div class="question-item border rounded p-3 mb-3" data-question-index="">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="question-title">السؤال <span class="question-number"></span></h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-danger remove-question"
                                            title="حذف السؤال">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                                        <textarea name="questions[][question_text]" class="form-control question-text" rows="2"
                                            placeholder="أدخل نص السؤال هنا..." required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="card">
                                            <div
                                                class="card-header d-flex justify-content-between align-items-center py-2">
                                                <small class="text-muted">خيارات الإجابة</small>
                                                <button type="button" class="btn btn-sm btn-outline-primary add-option">
                                                    <i class="ti ti-plus me-1"></i>إضافة خيار
                                                </button>
                                            </div>
                                            <div class="card-body py-2">
                                                <div class="options-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template id="optionTemplate">
                            <div class="option-item d-flex align-items-center mb-2" data-option-index="">
                                <div class="flex-grow-1 me-2">
                                    {{-- بدون name: سيُضبط عبر JS --}}
                                    <input type="text" class="form-control option-text" placeholder="نص الخيار..."
                                        required>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-danger remove-option"
                                        title="حذف الخيار">
                                        <i class="ti ti-trash ti-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>



                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
