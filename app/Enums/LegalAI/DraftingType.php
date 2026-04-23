<?php

namespace App\Enums\LegalAI;

enum DraftingType: string
{


    /*
    |--------------------------------------------------------------------------
    | BASIC TYPE
    |--------------------------------------------------------------------------
    */
    case DefenseMemo = 'defense_memo';
    case ReplyMemo = 'reply_memo';
    case ObjectionMemo = 'objection_memo';
    case LawsuitForm = 'lawsuit_form';
    case ReconsiderationPetition = 'reconsideration_petition';

    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */
    public function label(): string
    {
        return match ($this) {
            self::DefenseMemo => 'مذكرة جوابية',
            self::ReplyMemo => 'مذكرة رد',
            self::ObjectionMemo => 'مذكرة اعتراض',
            self::LawsuitForm => 'صحيفة دعوى',
            self::ReconsiderationPetition => 'التماس إعادة النظر',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | ALL
    |--------------------------------------------------------------------------
    */
    public static function all(): array
    {
        return self::cases();
    }
}
