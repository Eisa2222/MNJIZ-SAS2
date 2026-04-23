<?php

namespace App\Http\Requests\Survey;

use App\Enums\Survey\SurveyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class SurveyRequest extends FormRequest
{
    // حدود منطقية قابلة للتعديل
    private const MAX_QUESTIONS        = 50;
    private const MAX_OPTIONS_PER_Q    = 20;

    // المتغيرات المسموحة في قالب الرسالة
    private const ALLOWED_PLACEHOLDERS = ['name', 'survey_url', 'survey_title'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بيانات الاستبيان الأساسية
            'title'            => ['required', 'string', 'min:3', 'max:255'],
            'type'             => ['required', Rule::enum(SurveyType::class)],
            'message_template' => ['required', 'string', 'min:10', 'max:500'],
            'description'      => ['nullable', 'string', 'max:1000'],

            // الأسئلة
            'questions'        => ['required', 'array', 'min:1', 'max:' . self::MAX_QUESTIONS],
            // تأكد أن كل عنصر سؤال يحوي المفاتيح المطلوبة
            'questions.*'      => ['required', 'array', 'required_array_keys:question_text,options'],

            // نص السؤال
            'questions.*.question_text'         => ['required', 'string', 'max:1000'],

            // خيارات السؤال
            'questions.*.options'               => ['required', 'array', 'min:2', 'max:' . self::MAX_OPTIONS_PER_Q],
            'questions.*.options.*'             => ['required', 'array', 'required_array_keys:option_text'],
            'questions.*.options.*.option_text' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            // الاستبيان
            'title.required'    => 'عنوان الاستبيان مطلوب.',
            'title.string'      => 'عنوان الاستبيان يجب أن يكون نص.',
            'title.min'         => 'عنوان الاستبيان يجب أن يكون على الأقل 3 أحرف.',
            'title.max'         => 'عنوان الاستبيان يجب أن لا يتجاوز 255 حرف.',

            'type.required'     => 'نوع الاستبيان مطلوب.',
            'type.enum'         => 'نوع الاستبيان المحدد غير صحيح.',

            'description.string' => 'الوصف يجب أن يكون نص.',
            'description.max'   => 'الوصف يجب أن لا يتجاوز 1000 حرف.',

            'message_template.required' => 'قالب الرسالة مطلوب.',
            'message_template.string'   => 'قالب الرسالة يجب أن يكون نص.',
            'message_template.min'      => 'قالب الرسالة يجب أن يكون على الأقل 10 أحرف.',
            'message_template.max'      => 'قالب الرسالة يجب أن لا يتجاوز 500 حرف.',

            // الأسئلة
            'questions.required'  => 'يجب إضافة سؤال واحد على الأقل.',
            'questions.array'     => 'بيانات الأسئلة غير صحيحة.',
            'questions.min'       => 'يجب إضافة سؤال واحد على الأقل.',
            'questions.max'       => 'تجاوزت الحد الأقصى لعدد الأسئلة المسموح به.',

            'questions.*.required'               => 'بيانات السؤال مطلوبة.',
            'questions.*.question_text.required' => 'نص السؤال مطلوب.',
            'questions.*.question_text.string'   => 'نص السؤال يجب أن يكون نص.',
            'questions.*.question_text.max'      => 'نص السؤال يجب أن لا يتجاوز 1000 حرف.',

            'questions.*.options.required'       => 'يجب إضافة خيارات للسؤال.',
            'questions.*.options.array'          => 'بيانات الخيارات غير صحيحة.',
            'questions.*.options.min'            => 'يجب إضافة خيارين على الأقل لكل سؤال.',
            'questions.*.options.max'            => 'تجاوزت الحد الأقصى لعدد الخيارات في السؤال.',

            'questions.*.options.*.required'              => 'بيانات الخيار مطلوبة.',
            'questions.*.options.*.option_text.required'  => 'نص الخيار مطلوب.',
            'questions.*.options.*.option_text.string'    => 'نص الخيار يجب أن يكون نص.',
            'questions.*.options.*.option_text.max'       => 'نص الخيار يجب أن لا يتجاوز 255 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'                               => 'عنوان الاستبيان',
            'type'                                => 'نوع الاستبيان',
            'description'                         => 'الوصف',
            'message_template'                    => 'قالب الرسالة',
            'questions'                           => 'الأسئلة',
            'questions.*.question_text'           => 'نص السؤال',
            'questions.*.options'                 => 'خيارات السؤال',
            'questions.*.options.*.option_text'   => 'نص الخيار',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateMessageTemplate($validator);
            $this->validateQuestions($validator);
        });
    }

    /**
     * فحص المتغيرات داخل قالب الرسالة
     */
    protected function validateMessageTemplate(Validator $validator): void
    {
        $tpl = (string) ($this->input('message_template') ?? '');
        preg_match_all('/\{([a-z_]+)\}/iu', $tpl, $m);
        $found = array_unique($m[1] ?? []);
        $invalid = array_values(array_diff($found, self::ALLOWED_PLACEHOLDERS));

        if (!empty($invalid)) {
            $validator->errors()->add(
                'message_template',
                'المتغيرات التالية غير مدعومة: ' . implode('، ', $invalid) .
                    '. المتغيرات المسموحة: {' . implode('}, {', self::ALLOWED_PLACEHOLDERS) . '}.'
            );
        }
    }

    /**
     * التحقق المخصص من الأسئلة (تكرار الخيارات، فراغات، إلخ)
     */
    protected function validateQuestions(Validator $validator): void
    {
        $questions = $this->input('questions', []);

        foreach ($questions as $qIdx => $question) {
            $qNumber = $qIdx + 1;

            // نص السؤال
            $text = trim((string)($question['question_text'] ?? ''));
            if ($text === '') {
                $validator->errors()->add("questions.$qIdx.question_text", "السؤال رقم {$qNumber}: نص السؤال مطلوب.");
            }

            // الخيارات
            $options = $question['options'] ?? [];
            if (!is_array($options) || count($options) < 2) {
                $validator->errors()->add("questions.$qIdx.options", "السؤال رقم {$qNumber}: يجب إضافة خيارين على الأقل.");
                continue;
            }

            // إزالة الفراغات وتطبيع النص
            $normalized = [];
            foreach ($options as $oIdx => $opt) {
                $optText = trim((string)($opt['option_text'] ?? ''));
                if ($optText === '') {
                    $validator->errors()->add(
                        "questions.$qIdx.options.$oIdx.option_text",
                        "السؤال رقم {$qNumber}: جميع الخيارات يجب أن تحتوي على نص."
                    );
                }
                $normalized[] = mb_strtolower($optText);
            }

            // تحقق من التكرار داخل نفس السؤال (غير حسّاس لحالة الأحرف)
            $unique = array_unique($normalized);
            if (count($unique) < count($normalized)) {
                $validator->errors()->add(
                    "questions.$qIdx.options",
                    "السؤال رقم {$qNumber}: يجب أن تكون خيارات السؤال مختلفة ولا تحتوي على تكرار."
                );
            }
        }
    }

    /**
     * تنظيف البيانات قبل التحقق
     */
    protected function prepareForValidation(): void
    {
        // تنظيف حقول نصية (trim + strip_tags للحماية الأساسية)
        $title            = isset($this->title) ? strip_tags(trim((string)$this->title)) : '';
        $description      = isset($this->description) ? strip_tags(trim((string)$this->description)) : null;
        $messageTemplate  = isset($this->message_template) ? strip_tags(trim((string)$this->message_template)) : '';

        $this->merge([
            'title'            => $title,
            'description'      => $description ?: null,
            'message_template' => $messageTemplate,
        ]);

        // تنظيف وإعادة فهرسة الأسئلة
        if ($this->has('questions') && is_array($this->questions)) {
            $cleaned = [];

            foreach (array_values($this->questions) as $question) {
                $qText = isset($question['question_text']) ? strip_tags(trim((string)$question['question_text'])) : '';
                if ($qText === '') {
                    // تجاهل السؤال الفارغ (سيُرصد بالـ rules أيضًا)
                    continue;
                }

                $q = [
                    'question_text' => $qText,
                    'options'       => [],
                ];

                if (isset($question['options']) && is_array($question['options'])) {
                    foreach (array_values($question['options']) as $option) {
                        $oText = isset($option['option_text']) ? strip_tags(trim((string)$option['option_text'])) : '';
                        if ($oText !== '') {
                            $q['options'][] = ['option_text' => $oText];
                        }
                    }
                }

                $cleaned[] = $q;
            }

            $this->merge(['questions' => $cleaned]);
        }
    }
}