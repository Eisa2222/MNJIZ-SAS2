<?php

namespace App\Services\Marketing\ContentManagement;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Marketing\ContentManagement\ContentManagementData;
use App\Enums\Marketing\ContentManagement\ContentStatus;
use App\Enums\Marketing\ContentManagement\PublicationStatus;
use App\Enums\Marketing\ContentManagement\PublishType;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
use App\Models\general_setting\SettingsSocial;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContentManagementService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}


    public function createContent(ContentManagementData $dto, ?UploadedFile $file = null): ContentManagement
    {
        return $this->errorHandler->execute(function () use ($dto, $file) {
            return DB::transaction(function () use ($dto, $file) {

                $data               = $dto->toArray();
                $data['created_by'] = auth()->id();

                if ($file) {
                    $data['media'] = $file->store('content_media', 'public');
                }

                $content = ContentManagement::create($data)->refresh();
                $content->socials()->sync($dto->social_ids);

                if ($content->publication_status === PublicationStatus::Scheduled) {
                    $this->scheduleInitialPublications($content, $dto->social_ids);
                }

                return $content;
            });
        }, 'حدث خطأ أثناء إنشاء المحتوى');
    }


    public function updateContent(int $id, ContentManagementData $dto, ?UploadedFile $file = null): ContentManagement
    {
        return $this->errorHandler->execute(function () use ($id, $dto, $file) {
            return DB::transaction(function () use ($id, $dto, $file) {

                $content            = ContentManagement::findOrFail($id);
                $data               = $dto->toArray();
                $data['updated_by'] = auth()->id();

                /* استبدال الملف عند الرفع الجديد */
                if ($file) {
                    if ($content->media) {
                        Storage::disk('public')->delete($content->media);
                    }
                    $data['media'] = $file->store('content_media', 'public');
                } else {
                    // لا يوجد ملف جديد → احتفظ بالقديم
                    $data['media'] = $content->media;
                }

                $content->update($data);
                $content->refresh();
                $content->socials()->sync($dto->social_ids);

                /* إعادة بناء جدول النشر */
                SocialPublication::where('content_management_id', $content->id)->delete();

                if ($content->publication_status === PublicationStatus::Scheduled) {
                    $this->scheduleInitialPublications($content, $dto->social_ids);
                }

                return $content;
            });
        }, 'حدث خطأ أثناء تحديث المحتوى');
    }


    public function deleteContent(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $content = ContentManagement::findOrFail($id);

                // حذف الملف من التخزين إن وُجد
                if ($content->media) {
                    Storage::disk('public')->delete($content->media);
                }

                return $content->delete();
            });
        }, 'حدث خطأ أثناء حذف المحتوى');
    }


    private function scheduleInitialPublications(ContentManagement $content, array $socialIds): void
    {
        $next = $this->computeNextRun($content, now());
        if (!$next) {
            return;
        }

        $platforms = SettingsSocial::whereIn('id', $socialIds)->pluck('name');
        foreach ($platforms as $platform) {
            SocialPublication::create([
                'content_management_id' => $content->id,
                'platform'              => $platform,
                'scheduled_for'         => $next,
                'status'                => 'pending',
            ]);
        }
    }


    public function computeNextRun(ContentManagement $content, Carbon $from): ?Carbon
    {
        // OneTime
        if ($content->publish_type === PublishType::OneTime) {
            if (!$content->one_time_at) {
                return null;
            }
            $ts = Carbon::parse($content->one_time_at);
            return $ts->gte($from) ? $ts : null;
        }


        $time     = $content->publish_time   ?? '00:00'; // تاريخ النشر 
        [$h, $m]   = array_pad(explode(':', $time), 2, 0);

        $cursor   = ($content->start_date ? Carbon::parse($content->start_date) : $from)->setTime($h, $m, 0);
        if ($cursor->lt($from)) $cursor = $from->copy()->setTime($h, $m, 0);


        // daily
        if ($content->recurring_type->value === 'daily') {
            $candidate = $cursor->gte($from) ? $cursor : $cursor->addDay();
            return $this->withinEndDate($content, $candidate);
        }


        // weekly
        if ($content->recurring_type->value === 'weekly') {
            $days = is_array($content->week_days) ? $content->week_days : json_decode((string)$content->week_days, true);
            $days = collect($days)->map(fn($d) => intval($d))->filter(fn($d) => $d >= 1 && $d <= 7)->unique()->values()->all();
            if (empty($days)) return null;

            for ($i = 0; $i < 14; $i++) {
                if (in_array($cursor->dayOfWeekIso, $days) && $cursor->gte($from)) {
                    return $this->withinEndDate($content, $cursor);
                }
                $cursor->addDay();
            }
            return null;
        }


        // monthly
        if ($content->recurring_type->value === 'monthly') {

            $day = intval($content->month_day);
            if ($day < 1 || $day > 31) return null;

            [$h, $m] = array_pad(explode(':', $content->publish_time ?? '00:00'), 2, 0);

            $probe = $cursor->copy();              // يحمل الوقت الصحيح منذ البداية
            for ($i = 0; $i < 18; $i++) {

                // اضبط اليوم المطلوب إن وُجد في هذا الشهر
                $candidate = $probe->copy()->day(min($day, $probe->daysInMonth));

                // أعِد ضبط الساعة بعد setDay لأنه قد يغيّرها
                $candidate->setTime($h, $m, 0);

                if ($candidate->day === $day && $candidate->gte($from)) {
                    return $this->withinEndDate($content, $candidate);
                }

                // انتقل إلى أول يوم في الشهر التالي مع الاحتفاظ بالوقت
                $probe = $probe->addMonthNoOverflow()->startOfMonth()->setTime($h, $m, 0);
            }

            return null; // لم نجد موعدًا صالحًا خلال 18 شهر
        }



        return null;
    }

    private function withinEndDate(ContentManagement $content, Carbon $candidate): ?Carbon
    {
        if ($content->end_date && $candidate->gt(Carbon::parse($content->end_date)->endOfDay())) {
            return null;
        }
        return $candidate;
    }
}
