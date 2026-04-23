<?php
namespace App\Enums\Qoyod\Products;

enum ZeroTaxReason: int
{
    case EXPORT_GOODS                    = 1;  // تصدير بضائع
    case EXPORT_SERVICES                 = 2;  // تصدير خدمات
    case QUALIFYING_METALS               = 3;  // معادن مؤهلة
    case PRIVATE_EDUCATION               = 4;  // تعليم خاص للمواطنين
    case PRIVATE_HEALTHCARE              = 5;  // رعاية صحية خاصة للمواطنين
    case MEDICINES_AND_MEDICAL_EQUIPMENT = 6;  // أدوية ومعدات طبية
    case INTERNATIONAL_GOODS_TRANSPORT    = 7;  // النقل الدولي للسلع
    case INTERNATIONAL_PASSENGER_TRANSPORT = 8; // النقل الدولي للركاب
    case QUALIFIED_MEANS_OF_TRANSPORT    = 9;  // توريد وسيلة نقل مؤهلة
    case GOODS_PASSENGER_SERVICE         = 10; // خدمات متعلقة بنقل البضائع أو الركاب
    case INTERNATIONAL_PASSENGER_SERVICE = 11; // خدمات مرتبطة بإمداد النقل الدولي للركاب

    public function label(): string
    {
        return match($this) {
            self::EXPORT_GOODS                    => 'تصدير بضائع',
            self::EXPORT_SERVICES                 => 'تصدير خدمات',
            self::QUALIFYING_METALS               => 'معادن مؤهلة',
            self::PRIVATE_EDUCATION               => 'تعليم خاص للمواطنين',
            self::PRIVATE_HEALTHCARE              => 'رعاية صحية خاصة للمواطنين',
            self::MEDICINES_AND_MEDICAL_EQUIPMENT => 'أدوية ومعدات طبية',
            self::INTERNATIONAL_GOODS_TRANSPORT    => 'النقل الدولي للسلع',
            self::INTERNATIONAL_PASSENGER_TRANSPORT => 'النقل الدولي للركاب',
            self::QUALIFIED_MEANS_OF_TRANSPORT    => 'توريد وسيلة نقل مؤهلة',
            self::GOODS_PASSENGER_SERVICE         => 'خدمات متعلقة بنقل البضائع أو الركاب',
            self::INTERNATIONAL_PASSENGER_SERVICE => 'خدمات مرتبطة بإمداد النقل الدولي للركاب',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(ZeroTaxReason $r) => ['id' => $r->value, 'name' => $r->label()],
            self::cases()
        );
    }
}
