<?php

namespace App\Http\Controllers\microsoft;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CalendarController extends Controller
{
    protected $graphService;
    protected $calenderService;

    public function __construct(MicrosoftGraphBaseService $graphService, CalendarService $calenderService)
    {
        $this->graphService = $graphService;
        $this->calenderService = $calenderService;
    }

    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return view('onedrive.calendar.index');
    }

    /**
     * API لجلب الأحداث من التقاويم.
     */
    // app/Http/Controllers/CalendarController.php


    public function getEvents(Request $request)
    {
        try {
            // الحصول على رمز الوصول الصالح
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                // سيتم إعادة التوجيه داخل دالة getAccessToken في حالة الخطأ
                return;
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors('يرجى ربط حساب Microsoft الخاص بك.');
            }

            $calendars = $this->calenderService->getUserCalendars($accessToken);

            if (!$calendars) {
                return response()->json(['error' => 'فشل في جلب التقاويم.'], 500);
            }

            $allEvents = [];

            // الحصول على معلمات التاريخ من الطلب
            $start = $request->query('start'); // مثال: "2024-10-27T00:00:00Z"
            $end = $request->query('end');     // مثال: "2024-12-08T00:00:00Z"
            $timeZone = $request->query('timeZone', 'UTC'); // الافتراضي: 'UTC'

            foreach ($calendars as $calendar) {
                $calendarId = $calendar->getId();
                $calendarName = $calendar->getName();
                // Log::info('Processing calendar', ['calendar_id' => $calendarId, 'calendar_name' => $calendarName]);

                // تجاهل التقاويم ذات المعرفات غير الصحيحة مثل '-='
                if (!$calendarId || $calendarId === '-=') {
                    // Log::warning('Skipping calendar with invalid ID', [
                    //     // 'user_id' => $user->id,
                    //     'calendar_id' => $calendarId,
                    //     'calendar_name' => $calendarName,
                    // ]);
                    continue; // تجاهل التقويم غير الصحيح
                }

                $events = $this->calenderService->getUserEvents($accessToken, $calendarId, $start, $end, $timeZone);

                if ($events) {
                    foreach ($events as $event) {
                        $allEvents[] = [
                            'id' => $event->getId(),
                            'title' => $event->getSubject(),
                            'start' => $event->getStart()->getDateTime(),
                            'end' => $event->getEnd()->getDateTime(),
                            'allDay' => $event->getIsAllDay() ?? false,
                        ];
                    }
                }
            }

            // Log::info('Events fetched successfully', ['events_count' => count($allEvents)]);

            return response()->json($allEvents);
        } catch (\Exception $e) {
            // Log::critical('Exception in CalendarController@getEvents', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.'], 500);
        }
    }




    /**
     * إنشاء حدث جديد في التقويم.
     */


    public function createEvent(Request $request)
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return;
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors('يرجى ربط حساب Microsoft الخاص بك.');
            }

            // التحقق من صحة البيانات المدخلة
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'body' => 'nullable|string',
                'start_date' => 'required|date',
                'start_time' => 'nullable',
                'end_date' => 'required|date|after_or_equal:start_date',
                'end_time' => 'nullable',
                'label' => 'nullable|string',
                'url' => 'nullable|url',
                'location' => 'nullable|string|max:255',
                'all_day' => '',
            ]);

            // تجهيز تفاصيل الحدث
            $eventDetails = [
                "subject" => $validated['subject'],
                "body" => [
                    "contentType" => "HTML",
                    "content" => $validated['body'] ?? '',
                ],
                "start" => [
                    "dateTime" => $validated['start_date'] . 'T' . ($validated['start_time'] ?? '00:00:00'),
                    "timeZone" => "UTC"
                ],
                "end" => [
                    "dateTime" => $validated['end_date'] . 'T' . ($validated['end_time'] ?? '00:00:00'),
                    "timeZone" => "UTC"
                ],
                "location" => [
                    "displayName" => $validated['location'] ?? '',
                ],
                "isAllDay" => $validated['all_day'] ?? false,
                "webLink" => $validated['url'] ?? '',
            ];

            // إنشاء الحدث عبر خدمة Graph
            $event = $this->calenderService->createEvent($accessToken, $eventDetails);

            // التحقق مما إذا تم إنشاء الحدث بنجاح
            if (!$event) {
                // Log::error('Failed to create event', [
                //     // 'user_id' => $user->id,
                //     'event_subject' => $validated['subject'],
                //     'start_date' => $validated['start_date'],
                //     'end_date' => $validated['end_date'],
                //     // يمكن تضمين تفاصيل إضافية غير حساسة حسب الحاجة
                // ]);
                return back()->withErrors(__('messages.event_creation_failed'));
            }

            // تسجيل نجاح إنشاء الحدث في قناة النشاط
            // Log::info('Event created successfully', [
            //     // 'user_id' => $user->id,
            //     'event_id' => $event->getId(),
            //     'event_subject' => $event->getSubject(),
            //     'start_time' => $event->getStart()->getDateTime(),
            //     'end_time' => $event->getEnd()->getDateTime(),
            // ]);

            // إعادة التوجيه مع رسالة نجاح
            return redirect()->route('calendar.index')->with('success', 'تم اضافة الحدث بنجاح');
        } catch (ValidationException $e) {
            // التعامل مع أخطاء التحقق من الصحة
            // Log::warning('Validation failed for creating event', [
            //     'user_id' => Auth::id(),
            //     'errors' => $e->errors(),
            // ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // التعامل مع الاستثناءات العامة
            // Log::critical('Exception in CalendarController@createEvent', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            //     'user_id' => Auth::id(),
            // ]);
            return back()->withErrors(__('messages.unexpected_error'));
        }
    }


    public function getEventDetails($eventId)
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return;
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors('يرجى ربط حساب Microsoft الخاص بك.');
            }
            // جلب تفاصيل الحدث
            $event = $this->calenderService->getEventDetails($accessToken, $eventId);

            if (!$event) {
                // Log::error('Failed to fetch event details', ['event_id' => $eventId]);
                return response()->json(['error' => 'فشل في جلب تفاصيل الحدث.'], 500);
            }

            // تحويل الحدث إلى مصفوفة قابلة للعرض
            $eventDetails = [
                'id' => $event->getId(),
                'title' => $event->getSubject(),
                'label' => $event->getCategories()[0] ?? 'etc',
                'start_date' => substr($event->getStart()->getDateTime(), 0, 10),
                'start_time' => substr($event->getStart()->getDateTime(), 11, 5),
                'end_date' => substr($event->getEnd()->getDateTime(), 0, 10),
                'end_time' => substr($event->getEnd()->getDateTime(), 11, 5),
                'all_day' => $event->getIsAllDay(),
                'url' => $event->getOnlineMeetingUrl(),
                'location' => $event->getLocation()->getDisplayName(),
                'body' => $event->getBody()->getContent(),
            ];

            // Log::info('Fetched event details successfully', ['event_id' => $eventId]);

            return response()->json($eventDetails);
        } catch (\Exception $e) {
            // Log::critical('Exception in CalendarController@getEventDetails', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.'], 500);
        }
    }


    public function updateEvent(Request $request, $eventId)
    {
        try {
            // الحصول على رمز الوصول الصالح
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return;
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors('يرجى ربط حساب Microsoft الخاص بك.');
            }

            // تحقق من صحة البيانات المدخلة
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'body' => 'nullable|string',
                'start_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_date' => 'required|date|after_or_equal:start_date',
                'end_time' => 'required|date_format:H:i',
                'label' => 'nullable|string',
                'url' => 'nullable|url',
                'location' => 'nullable|string|max:255',
            ]);

            // تحويل البيانات إلى تنسيق يتناسب مع Microsoft Graph API
            $eventData = [
                "subject" => $validated['subject'],
                "body" => [
                    "contentType" => "HTML",
                    "content" => $validated['body'] ?? '',
                ],
                "start" => [
                    "dateTime" => $validated['start_date'] . 'T' . $validated['start_time'],
                    "timeZone" => "UTC"
                ],
                "end" => [
                    "dateTime" => $validated['end_date'] . 'T' . $validated['end_time'],
                    "timeZone" => "UTC"
                ],
                "location" => [
                    "displayName" => $validated['location'] ?? '',
                ],
                "isAllDay" => $request->has('all_day') ? true : false,
                "webLink" => $validated['url'] ?? '',
            ];


            // تحديث الحدث عبر Microsoft Graph API
            $updatedEvent = $this->calenderService->updateEvent($accessToken, $eventId, $eventData);

            if (!$updatedEvent) {
                // Log::error('Failed to update event', ['event_id' => $eventId]);
                return response()->json(['error' => 'فشل في تحديث الحدث.'], 500);
            }

            // Log::info('Event updated successfully', ['event_id' => $eventId]);

            return response()->json(['success' => 'تم تحديث الحدث بنجاح.']);
        } catch (\Exception $e) {
            // Log::critical('Exception in CalendarController@updateEvent', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.'], 500);
        }
    }


    public function deleteEvent($eventId)
    {
        try {
            // الحصول على رمز الوصول الصالح
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                // سيتم إعادة التوجيه داخل دالة getAccessToken في حالة الخطأ
                return;
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors('يرجى ربط حساب Microsoft الخاص بك.');
            }

            // حذف الحدث عبر Microsoft Graph API
            $deleted = $this->calenderService->deleteEvent($accessToken, $eventId);

            if (!$deleted) {
                // Log::error('Failed to delete event', ['event_id' => $eventId]);
                return response()->json(['error' => 'فشل في حذف الحدث.'], 500);
            }

            // Log::info('Event deleted successfully', ['event_id' => $eventId]);

            return response()->json(['success' => 'تم حذف الحدث بنجاح.']);
        } catch (\Exception $e) {
            // Log::critical('Exception in CalendarController@deleteEvent', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.'], 500);
        }
    }


    protected function getAccessToken()
    {
        // جلب المستخدم الحالي
        $user = Auth::user();

        // التحقق من اتصال Microsoft
        if (!$user->microsoft_token || !$user->microsoft_refresh_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        // فرض تحديث الرمز حتى لو لم تنته صلاحيته
        $accessToken = $this->graphService->refreshAccessToken($user);

        // التحقق من نجاح الحصول على الرمز
        if (!$accessToken) {
            return redirect()->route('microsoft.login')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft الخاص بك.');
        }
        return $accessToken;
    }
}
