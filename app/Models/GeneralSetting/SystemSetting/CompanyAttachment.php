<?php

namespace App\Models\GeneralSetting\SystemSetting;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercial_register',
        'commercial_register_end_date',
        'insurance',
        'insurance_end_date',
        'chamber_of_commerce',
        'chamber_end_date',
        'balady',
        'balady_end_date',
        'tawteen',
        'tawteen_end_date',
        'wage_protection',
        'wage_protection_end_date',
        'incorporation_contract',
        'national_address',
    ];

    protected $casts = [
        'commercial_register_end_date'   => 'date',
        'insurance_end_date'             => 'date',
        'chamber_end_date'               => 'date',
        'balady_end_date'                => 'date',
        'tawteen_end_date'               => 'date',
        'wage_protection_end_date'       => 'date',
        'metadata'                       => 'array',
    ];
}
