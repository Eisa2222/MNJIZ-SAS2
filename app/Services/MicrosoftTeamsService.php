<?php

namespace App\Services;

use Microsoft\Graph\Exception\GraphException;
use Carbon\Carbon;

class MicrosoftTeamsService extends MicrosoftGraphBaseService
{
    /**
     * إنشاء حدث في التقويم مع اجتماع عبر الإنترنت
     */
    public function createCalendarEventWithOnlineMeeting($userAccessToken, $subject, $startTime, $endTime, $attendees = [])
    {
        if (!$userAccessToken) {
            return null;
        }
        $this->graph->setAccessToken($userAccessToken);
        // معالجة قائمة المدعوين
        $attendeeData = array_map(function ($email) {
            return [
                "emailAddress" => [
                    "address" => $email,
                    "name" => "" // الاسم (اختياري)
                ],
                "type" => "required" // نوع الحضور (يمكن تغييره إلى optional إذا لزم الأمر)
            ];
        }, $attendees);

        // إعداد بيانات الحدث مع المدعوين
        $eventData = [
            "subject" => $subject,
            "start" => [
                "dateTime" => Carbon::parse($startTime)->format('Y-m-d\TH:i:s'),
                "timeZone" => "Asia/Riyadh"
            ],
            "end" => [
                "dateTime" => Carbon::parse($endTime)->format('Y-m-d\TH:i:s'),
                "timeZone" => "Asia/Riyadh"
            ],
            "isOnlineMeeting" => true,
            "onlineMeetingProvider" => "teamsForBusiness",
            "attendees" => $attendeeData // إضافة قائمة الحضور هنا
        ];

        try {
            $response = $this->graph->createRequest("POST", "/me/events")
                ->attachBody($eventData)
                ->setReturnType(\Microsoft\Graph\Model\Event::class)
                ->execute();

            if ($response->getOnlineMeeting()) {
                return [
                    'joinUrl' => $response->getOnlineMeeting()->getJoinUrl(),
                    'eventId' => $response->getId()
                ];
            }

            return null;
        } catch (GraphException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * تحديث حدث في التقويم مع اجتماع عبر الإنترنت
     */
    public function updateCalendarEvent($userAccessToken, $eventId, $subject, $startTime, $endTime, $attendees = [])
    {
        if (!$userAccessToken) {
            return null;
        }

        $this->graph->setAccessToken($userAccessToken);

        // معالجة قائمة المدعوين
        $attendeeData = array_map(function ($email) {
            return [
                "emailAddress" => [
                    "address" => $email,
                    "name" => "" // الاسم (اختياري)
                ],
                "type" => "required" // يمكن تغييرها إلى optional
            ];
        }, $attendees);

        // إعداد بيانات التحديث
        $eventData = [
            "subject" => $subject,
            "start" => [
                "dateTime" => Carbon::parse($startTime)->format('Y-m-d\TH:i:s'),
                "timeZone" => "Asia/Riyadh"
            ],
            "end" => [
                "dateTime" => Carbon::parse($endTime)->format('Y-m-d\TH:i:s'),
                "timeZone" => "Asia/Riyadh"
            ],
            "attendees" => $attendeeData // إضافة المدعوين
        ];

        try {
            $response = $this->graph->createRequest("PATCH", "/me/events/{$eventId}")
                ->attachBody($eventData)
                ->setReturnType(\Microsoft\Graph\Model\Event::class)
                ->execute();

            return true;
        } catch (GraphException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * حذف حدث من التقويم
     */
    public function deleteCalendarEvent($userAccessToken, $eventId)
    {
        if (!$userAccessToken) {
            return false;
        }

        $this->graph->setAccessToken($userAccessToken);

        try {
            $this->graph->createRequest("DELETE", "/me/events/{$eventId}")
                ->execute();
            return true;
        } catch (GraphException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * جلب جميع الاجتماعات التي تمت دعوتك إليها والتي قمت بإنشائها
     */
    public function getAllMeetings($userAccessToken)
    {
        if (!$userAccessToken) {
            return null;
        }

        $this->graph->setAccessToken($userAccessToken);

        try {
            // جلب جميع الأحداث بدون فلترة زمنية
            $response = $this->graph->createRequest("GET", "/me/events")
                ->setReturnType(\Microsoft\Graph\Model\Event::class)
                ->execute();

            $meetings = [];
            foreach ($response as $event) {
                // التأكد من أن الحدث هو اجتماع عبر الإنترنت ويحتوي على رابط الانضمام
                if (
                    $event->getIsOnlineMeeting() &&
                    $event->getOnlineMeeting() &&
                    $event->getOnlineMeeting()->getJoinUrl()
                ) {
                    // تنسيق التواريخ باستخدام Carbon
                    $startDateTime = Carbon::parse($event->getStart()->getDateTime())->format('d/m/Y H:i');
                    $endDateTime = Carbon::parse($event->getEnd()->getDateTime())->format('d/m/Y H:i');

                    $meetings[] = [
                        'id' => $event->getId(), // إضافة معرف الحدث
                        'subject' => $event->getSubject(),
                        'startDateTime' => $startDateTime,
                        'endDateTime' => $endDateTime,
                        'joinWebUrl' => $event->getOnlineMeeting()->getJoinUrl(),
                        'organizer' => $event->getOrganizer()->getEmailAddress()->getName() ?? 'غير محدد',
                        'attendees_count' => count($event->getAttendees()), // إضافة عدد الحضور
                    ];
                }
            }

            return $meetings;
        } catch (GraphException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}