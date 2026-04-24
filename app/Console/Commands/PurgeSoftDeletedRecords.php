<?php

namespace App\Console\Commands;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Categories;
use App\Models\Client;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsEntity;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\general_setting\SettingsLawsuitsType;
use App\Models\general_setting\SettingsMainCourt;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsProduct;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSector;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsSocial;
use App\Models\general_setting\SettingsStagePriceOffer;
use App\Models\general_setting\SettingsSubcategories;
use App\Models\general_setting\SettingsTemplate;
use App\Models\general_setting\SettingsTypeRulings;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Court;
use App\Models\judicial_affairs\Courtroom;
use App\Models\judicial_affairs\Document;
use App\Models\judicial_affairs\Judge;
use App\Models\judicial_affairs\NoteCommentMention;
use App\Models\judicial_affairs\Opponent;
use App\Models\judicial_affairs\PowerOfAttorney;
use App\Models\judicial_affairs\Project;
use App\Models\judicial_affairs\ProjectAttachment;
use App\Models\judicial_affairs\Session;
use App\Models\judicial_affairs\SessionComment;
use App\Models\judicial_affairs\SessionCommentMention;
use App\Models\LitigationStage;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\Support;
use App\Models\Task\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurgeSoftDeletedRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge:soft-deletes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete soft-deleted records older than a specific time period';

    /**
     * List of models to purge.
     *
     * @var array
     */
    protected $models = [
        Customers::class, //1
        Categories::class, //2
        Client::class, //3
        LitigationStage::class, //4
        Support::class, //5
        SettingsCategories::class, //6
        SettingsClientStatus::class, //7
        SettingsContractStatus::class, //8
        SettingsCountry::class, //9
        SettingsDepartmentContractCase::class, //10
        // SettingsEntity::class, //11
        SettingsEntityRank::class, //12
        SettingsHrStatus::class, //13
        SettingsLawsuitsType::class, //14
        SettingsMainCourt::class, //15
        SettingsMarketingChannel::class, //16
        SettingsProduct::class, //17
        SettingsRegion::class, //18
        SettingsSector::class, //19
        SettingsSessionType::class, //20
        SettingsSocial::class, //21
        SettingsStagePriceOffer::class, //22
        SettingsSubcategories::class, //23
        SettingsTemplate::class, //24
        SettingsTypeRulings::class, //25
        Employees::class, //26
        Contract::class, //27
        Court::class, //28
        Courtroom::class, //29
        Document::class, //30
        Judge::class, //31
        // NoteCommentMention::class, //32
        Offers::class, //33
        Opponent::class, //34
        PowerOfAttorney::class, //35
        Project::class, //36
        // ProjectAttachment::class, //37
        Session::class, //38
        // SessionComment::class, //39
        // SessionCommentMention::class, //40
        Task::class, //41
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // استرجاع الإعدادات المطلوبة بشكل محدد
        $daysToKeep = $this->getArchiveDeleteDuration();

        if ($daysToKeep === false) {
            // $this->error('إعدادات فترة الحذف غير صحيحة أو غير موجودة.');
            // Log::error('إعدادات فترة الحذف غير صحيحة أو غير موجودة.');
            return Command::FAILURE;
        }

        foreach ($this->models as $model) {
            // التحقق مما إذا كان النموذج يستخدم Trait الخاص بـ SoftDeletes
            if (!in_array(SoftDeletes::class, class_uses_recursive($model))) {
                // $this->error("النموذج {$model} لا يستخدم خاصية SoftDeletes.");
                // Log::warning("النموذج {$model} لا يستخدم خاصية SoftDeletes.");
                continue;
            }

            try {
                $this->purgeModel($model, $daysToKeep);
                // $message = "تم تنظيف السجلات المحذوفة بنعومة القديمة لنموذج {$model}.";
                // $this->info($message);
                // Log::info($message);
            } catch (\Exception $e) {
                // $errorMessage = "فشل تنظيف السجلات لنموذج {$model}: " . $e->getMessage();
                // $this->error($errorMessage);
                // Log::error($errorMessage);
                // يمكنك اتخاذ إجراءات إضافية هنا إذا لزم الأمر
            }
        }

        return Command::SUCCESS;
    }

    /**
     * استرجاع مدة الاحتفاظ بالسجلات المحذوفة من الإعدادات.
     *
     * @return int|bool
     */
    protected function getArchiveDeleteDuration()
    {
        // افتراض أن الإعداد يحتوي على مفتاح محدد مثل 'archive_delete_duration'
        $settings = Settings::current();

        if ($settings->exists && is_numeric($settings->archive_delete_duration) && (int)$settings->archive_delete_duration > 0) {
            return (int)$settings->archive_delete_duration;
        }

        return false;
    }

    /**
     * تنظيف السجلات المحذوفة بنعومة لنموذج معين.
     *
     * @param string $model
     * @param int $daysToKeep
     * @return void
     */
    protected function purgeModel(string $model, int $daysToKeep)
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        $query = $model::onlyTrashed()->where('deleted_at', '<', $cutoffDate);

        // استخدام chunkById لتحسين الأداء عند التعامل مع كميات كبيرة
        $query->chunkById(1000, function ($records) use ($model) {
            foreach ($records as $record) {
                $record->forceDelete();
            }
        });
    }
}
