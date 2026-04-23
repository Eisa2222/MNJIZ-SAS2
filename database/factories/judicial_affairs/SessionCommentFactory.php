<?php

namespace Database\Factories\judicial_affairs;

use App\Models\judicial_affairs\Session;
use App\Models\judicial_affairs\SessionComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\SessionComment>
 */
class SessionCommentFactory extends Factory
{
    protected $model = SessionComment::class;

    public function definition()
    {
        // قائمة محتوىات التعليقات
        $commentContents = [
            'تمت مناقشة القضية بشكل شامل وتمت إضافة بعض النقاط الهامة.',
            'يرجى مراجعة الأدلة المقدمة وإضافة المزيد من التفاصيل.',
            'التقرير جيد ولكن يحتاج إلى بعض التعديلات لتحسين الوضوح.',
            'تم الاتفاق على النقاط الرئيسية وسيتم متابعة التنفيذ لاحقًا.',
            'يرجى إرسال النسخة النهائية من التقرير قبل الموعد المحدد.',
            'تمت إضافة الملاحظات اللازمة لتطوير التقرير.',
            'التقرير متميز ويعكس بدقة سير الجلسة.',
            'يرجى توضيح بعض النقاط في التقرير لتعزيز الفهم.',
            'تمت الموافقة على التقرير وسيتم اعتماده رسميًا.',
            'تمت مناقشة جميع الجوانب القانونية وتم اتخاذ القرار المناسب.',
        ];

        // الحصول على معرفات الجلسات والمستخدمين
        $sessionIds = Session::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        return [
            'session_id' => $this->faker->randomElement($sessionIds),
            'user_id' => $this->faker->randomElement($userIds),
            'content' => $this->faker->randomElement($commentContents),
        ];
    }
}
