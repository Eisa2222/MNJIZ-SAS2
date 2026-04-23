<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\Event;

class CalendarService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * إنشاء حدث تقويم بناءً على مهمة
     *
     * @param string $accessToken
     * @param array $taskData
     * @return Event|array
     */
    public function createCalendarEvent($accessToken, array $taskData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // تحقق من أن startDateTime و dueDateTime موجودان، وإلا استخدم القيم الافتراضية
            $startDateTime = $taskData['startDateTime'] ?? date('c');
            $dueDateTime = $taskData['dueDateTime'] ?? date('c', strtotime('+1 hour'));

            // إعداد بيانات الحدث
            $event = [
                'subject' => $taskData['title'] ?? 'بدون عنوان',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $taskData['description'] ?? 'بدون محتوى',
                ],
                'start' => [
                    'dateTime' => $startDateTime,
                    'timeZone' => 'UTC',
                ],
                'end' => [
                    'dateTime' => $dueDateTime,
                    'timeZone' => 'UTC',
                ],
                'location' => [
                    'displayName' => 'Microsoft Planner Task',
                ],
                'categories' => [], // يمكنك إضافة فئات إذا رغبت
                'isAllDay' => false,
            ];

            // إنشاء الحدث في تقويم المستخدم
            $createdEvent = $this->graph->createRequest('POST', '/me/events')
                ->attachBody($event)
                ->setReturnType(Event::class)
                ->execute();

            return $createdEvent;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception creating calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * تحديث حدث تقويم بناءً على تحديث مهمة
     *
     * @param string $accessToken
     * @param string $eventId
     * @param array $updatedData
     * @return Event|array
     */
    public function updateCalendarEvent($accessToken, $eventId, array $updatedData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // الخطوة 1: جلب الحدث الحالي للحصول على القيم القديمة
            $currentEvent = $this->graph->createRequest('GET', "/me/events/{$eventId}")
                ->setReturnType(Event::class)
                ->execute();

            // الحصول على القيم القديمة
            $currentEventData = [
                'subject' => $currentEvent->getSubject(),
                'body' => [
                    'contentType' => $currentEvent->getBody()->getContentType(),
                    'content' => $currentEvent->getBody()->getContent(),
                ],
                'start' => [
                    'dateTime' => $currentEvent->getStart()->getDateTime(),
                    'timeZone' => $currentEvent->getStart()->getTimeZone(),
                ],
                'end' => [
                    'dateTime' => $currentEvent->getEnd()->getDateTime(),
                    'timeZone' => $currentEvent->getEnd()->getTimeZone(),
                ],
                'location' => [
                    'displayName' => $currentEvent->getLocation()->getDisplayName(),
                ],
                // يمكنك إضافة المزيد من الحقول إذا لزم الأمر
            ];

            // إعداد بيانات التحديث باستخدام القيم المحدثة أو القيم القديمة
            $event = [
                'subject' => $updatedData['title'] ?? $currentEventData['subject'],
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $updatedData['description'] ?? $currentEventData['body']['content'],
                ],
                // سنضيف 'start' و 'end' بشكل شرطي
            ];

            // التحقق من startDateTime
            $startDateTime = $updatedData['startDateTime'] ?? $currentEventData['start']['dateTime'] ?? null;
            if (!is_null($startDateTime)) {
                $event['start'] = [
                    'dateTime' => $startDateTime,
                    'timeZone' => 'UTC',
                ];
            }

            // التحقق من dueDateTime
            $dueDateTime = $updatedData['dueDateTime'] ?? $currentEventData['end']['dateTime'] ?? null;
            if (!is_null($dueDateTime)) {
                $event['end'] = [
                    'dateTime' => $dueDateTime,
                    'timeZone' => 'UTC',
                ];
            }

            // تحديث الحدث في التقويم
            $updatedEvent = $this->graph->createRequest('PATCH', "/me/events/{$eventId}")
                ->attachBody($event)
                ->setReturnType(Event::class)
                ->execute();

            return $updatedEvent;

        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException updating calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception updating calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * حذف حدث تقويم
     *
     * @param string $accessToken
     * @param string $eventId
     * @return bool|array
     */
    public function deleteCalendarEvent($accessToken, $eventId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $this->graph->createRequest('DELETE', "/me/events/{$eventId}")
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException deleting calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception deleting calendar event: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
