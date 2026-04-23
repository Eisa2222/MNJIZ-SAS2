<?php

use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use App\Models\ElectronicServices\UserViolation;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use App\Models\general_setting\HR\Custody\SettingsStorageLocation;
use App\Models\general_setting\Marketing\SettingsCampaignSection;
use App\Models\general_setting\Marketing\SettingsContentPillar;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\general_setting\Marketing\SettingsPublishingPattern;
use App\Models\general_setting\Marketing\SettingsContentPurpose;
use App\Models\general_setting\Marketing\SettingsDisplayLocation;
use App\Models\general_setting\Marketing\SettingsTargetAudience;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsEntity;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsHRClassification;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\general_setting\SettingsMainCourt;
use App\Models\general_setting\SettingsProduct;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSector;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsSocial;
use App\Models\general_setting\SettingsStagePriceOffer;
use App\Models\general_setting\SettingsTypeRulings;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Purchase\Invoice;
use App\Models\Hr\Violations\Violation;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\User;

return [
    // الاعدادات العامة
    'SettingsSessionType'                   => SettingsSessionType::class,
    'SettingsTypeRulings'                   => SettingsTypeRulings::class,
    'SettingsDepartmentContractCase'        => SettingsDepartmentContractCase::class,
    'SettingsContractStatus'                => SettingsContractStatus::class,
    'SettingsProduct'                       => SettingsProduct::class,
    'SettingsStagePriceOffer'               => SettingsStagePriceOffer::class,
    'SettingsClientStatus'                  => SettingsClientStatus::class,
    'SettingsSector'                        => SettingsSector::class,
    'SettingsRegion'                        => SettingsRegion::class,
    'SettingsCountry'                       => SettingsCountry::class,
    'SettingsSocial'                        => SettingsSocial::class,
    'SettingsHrStatus'                      => SettingsHrStatus::class,
    'SettingsHRClassification'              => SettingsHRClassification::class,
    'SettingsEntity'                        => SettingsEntity::class,
    'SettingsMainCourt'                     => SettingsMainCourt::class,
    'SettingsEntityRank'                    => SettingsEntityRank::class,

    'SettingsContentType'                    => SettingsContentType::class,
    'SettingsPublishingPattern'              => SettingsPublishingPattern::class,
    'SettingsContentPurpose'                 => SettingsContentPurpose::class,
    'SettingsCampaignSection'                => SettingsCampaignSection::class,
    'SettingsTargetAudience'                 => SettingsTargetAudience::class,

    'SettingsAssetCategory'                 => SettingsAssetCategory::class,
    'SettingsStorageLocation'                 => SettingsStorageLocation::class,




    // إدارة مركز العمليات
    'Customers'                             => Customers::class,
    'Offers'                                => Offers::class,
    'Contract'                              => Contract::class,
    'PowerOfAttorney'                       => PowerOfAttorney::class,
    'Opponent'                              => Opponent::class,
    'Project'                               => Project::class,

    'Lawsuit'                               => Lawsuit::class,
    'Employees'                             => Employees::class,                // الموظفين
    'User'                                  => User::class,                     // المستخدمين
    'PurchaseRequest'                       => PurchaseRequest::class,          // طلبات المشتريات
    'LeaveRequest'                          => LeaveRequest::class,             // طلبات الإجازة

    'UserViolation'                        => Violation::class,             // المخالفات
    'Invoice'                              => Invoice::class                    // الفواتير




];
