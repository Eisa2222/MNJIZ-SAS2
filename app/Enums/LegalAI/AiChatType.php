<?php

namespace App\Enums\LegalAI;

enum AiChatType: string
{

    case GeneralChat = 'general-chat';
    case Summarization = 'summarization';
    case Drafting = 'drafting';
    case Precedents = 'precedents';

    /**
     * دالة مساعدة للحصول على عنوان قابل للقراءة (Label) لكل نوع.
     * مفيدة جدًا في الواجهات الأمامية أو التقارير.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::GeneralChat   => 'المساعد الذكي',
            self::Summarization => 'تلخيص المستندات',
            self::Drafting      => 'صياغة المذكرات',
            self::Precedents    => 'بحث السوابق القضائية',
        };
    }

    /**
     * دالة مساعدة للحصول على أيقونة لكل نوع (مثال).
     * يمكن استخدامها في الشريط الجانبي للتمييز البصري.
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::GeneralChat   => 'ti-message-chatbot',
            self::Summarization => 'ti-file',
            self::Drafting      => 'ti-file-pencil',
            self::Precedents    => 'ti-gavel',
        };
    }
}
