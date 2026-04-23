<?php
namespace App\Enums\Qoyod\Products;

enum TaxType: int
{
    case VAT_15     = 1; // ضريبة القيمة المضافة - 15%
    case ZERO       = 2; // الضريبة الصفرية - 0%
    case EXEMPT     = 3; // معفاة من الضريبة - 0%

    /**
     * ترجمة التعريف إلى الاسم الظاهر (بالعربية).
     */
    public function label(): string
    {
        return match($this) {
            TaxType::VAT_15 => 'ضريبة القيمة المضافة – 15.0%',
            TaxType::ZERO   => 'الضريبة الصفرية – 0.0%',
            TaxType::EXEMPT => 'معفاة من الضريبة – 0.0%',
        };
    }

    /**
     * نُعيد مصفوفة مناسبة للـ Blade (id / name) لاستخدامها في الفورم.
     */
    public static function options(): array
    {
        return array_map(
            fn(TaxType $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }
}
