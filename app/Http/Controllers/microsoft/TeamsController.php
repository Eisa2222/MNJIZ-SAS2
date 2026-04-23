<?php

namespace App\Http\Controllers\microsoft;

use App\Http\Controllers\Controller;
use App\Models\AttendeeMeeting;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\MeetingNote;
use App\Services\MicrosoftGraphBaseService;
use App\Services\MicrosoftTeamsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TeamsController extends Controller
{
    protected $graphService;
    protected $teamsService;

    /*
    |--------------------------------------------------------------------------
    | constructor of TeamsController
    |--------------------------------------------------------------------------
    | إنشاء متغيرات من الخدمات المطلوبة
    */
    public function __construct(MicrosoftGraphBaseService $graphService, MicrosoftTeamsService $teamsService)
    {
        $this->graphService = $graphService;
        $this->teamsService = $teamsService;
    }

    /*
    |--------------------------------------------------------------------------
    | index of TeamsController
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            // الحصول على رمز الوصول الصالح
            $accessToken = $this->getAccessToken();

            // الاضافة الجديدة في الكود الاجتماعات
            $user = Auth::user();
            if (!$user->microsoft_token) {
                return redirect()->route('microsoft.login')->withErrors(['error' => 'يرجى ربط حساب Microsoft الخاص بك.']);
            }

            if (!$accessToken) {
                return redirect()->route('dashboard')->withErrors(['error' => 'غير قادر على الحصول على رمز الوصول.']);
            }

            // جلب الاجتماعات
            $meetings = $this->teamsService->getAllMeetings($accessToken);

            // إذا لم يتم جلب الاجتماعات بنجاح، قم بتعيين مجموعة فارغة
            $collection = is_null($meetings) ? collect() : collect($meetings);

            // التعامل مع البحث
            if ($request->has('search')) {
                $search = $request->input('search');
                $collection = $collection->filter(function ($meeting) use ($search) {
                    return stripos($meeting['subject'], $search) !== false;
                });
            }

            // جلب MeetingNotes المرتبطة بالاجتماعات
            $eventIds = $collection->pluck('id')->toArray();
            $meetingNotes = MeetingNote::whereIn('event_id', $eventIds)
                ->with(['user.roles', 'user.employee'])
                ->get()
                ->keyBy('event_id');

            // إرفاق بيانات MeetingNote بالاجتماعات
            $collection = $collection->map(function ($meeting) use ($meetingNotes) {
                $note = $meetingNotes->get($meeting['id']);
                if ($note) {
                    $meeting['meeting_points'] = $note->meeting_points;
                    $meeting['meeting_outputs'] = $note->meeting_outputs;
                    // $meeting['meeting_field'] = $note->meeting_field ?? 'غير محدد';
                    if ($note->meeting_field == 'projects') {
                        $meeting['meeting_field'] = "مشاريع";
                    } elseif ($note->meeting_field == 'lawsuits') {
                        $meeting['meeting_field'] = "دعاوى";
                    } else {
                        $meeting['meeting_field'] = "عام";
                    }
                    $meeting['creator_image'] = $note->user->employee->profile_picture ?? null;
                } else {
                    $meeting['meeting_points'] = null;
                    $meeting['meeting_outputs'] = null;
                    $meeting['meeting_field'] = 'غير محدد';
                    $meeting['creator_image'] = null;
                }
                // عدد المدعوين موجود بالفعل في 'attendees_count'
                return $meeting;
            });

            // Pagination
            $perPage = 6; // عدد الاجتماعات في كل صفحة
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $paginatedMeetings = new LengthAwarePaginator(
                $currentPageItems,
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('microsoft.teams.index', compact('paginatedMeetings'));
        } catch (\Exception $e) {
            $paginatedMeetings = new LengthAwarePaginator([], 0, 6, 1, [
                'path' => $request->url(),
                'query' => $request->query()
            ]);
            return view('microsoft.teams.index', compact('paginatedMeetings'))->withErrors(['error' => 'حدث خطأ أثناء جلب الاجتماعات.']);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | create of TeamsController
    |--------------------------------------------------------------------------
    | عرض نموذج إنشاء اجتماع
    */
    public function create()
    {
        $projects = Project::select('id', 'project_name')->get();  // جلب المشاريع
        $lawsuits = Lawsuit::select('id', 'name')->get(); // جلب الدعاوى
        /*
        |--------------------------------------------------------------------------
        | المدعوين للاجتماع
        |--------------------------------------------------------------------------
        */
        $employees = Employees::where('work_email', '!=', null)->select('id', 'name', 'nickname', 'work_email')->get();
        $opponent = Opponent::where('email', '!=', null)->select('id', 'name', 'email')->get();
        $customers = Customers::where('email', '!=', null)->select('id', 'name', 'email')->get();
        return view('microsoft.teams.create', compact(
            'projects',
            'lawsuits',
            'employees',
            'opponent',
            'customers'
        ));
    }

    /*
|--------------------------------------------------------------------------
| Section Title
|--------------------------------------------------------------------------
| Description of this section.
*/
    /**
     * عرض تفاصيل الاجتماع
     */
    public function show($id)
    {
        try {
            // الحصول على رمز الوصول
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return redirect()->route('microsoft.teams.index')
                    ->with('error', 'فشل في الوصول إلى بيانات الاجتماع');
            }

            // جلب تفاصيل الاجتماع من قاعدة البيانات
            $meetingNote = MeetingNote::where('event_id', $id)->first();

            // جلب الاجتماع من Microsoft Teams
            $this->graphService = new \Microsoft\Graph\Graph();
            $this->graphService->setAccessToken($accessToken);

            $event = $this->graphService->createRequest("GET", "/me/events/{$id}")
                ->setReturnType(\Microsoft\Graph\Model\Event::class)
                ->execute();

            if (!$event) {
                return redirect()->route('microsoft.teams.index')
                    ->with('error', 'لم يتم العثور على الاجتماع');
            }

            // تجهيز البيانات للعرض
            $meetingField = 'عام'; // القيمة الافتراضية
            if ($meetingNote) {
                if ($meetingNote->meeting_field == 'projects') {
                    $meetingField = 'المشاريع';
                } elseif ($meetingNote->meeting_field == 'lawsuits') {
                    $meetingField = 'الدعاوى';
                }
            }

            $meeting = [
                'id' => $event->getId(),
                'subject' => $event->getSubject(),
                'organizer' => $event->getOrganizer()->getEmailAddress()->getName(),
                'attendees_count' => count($event->getAttendees()),
                'meeting_field' => $meetingField, // الحقل المحدّث
                'meeting_points' => $meetingNote ? $meetingNote->meeting_points : null,
                'meeting_outputs' => $meetingNote ? $meetingNote->meeting_outputs : null,
                'joinWebUrl' => $event->getOnlineMeeting()->getJoinUrl(),
                'startDateTime' => Carbon::parse($event->getStart()->getDateTime())
                    ->setTimezone('Asia/Riyadh')
                    ->translatedFormat('d/m/Y h:i A'),
                'endDateTime' => Carbon::parse($event->getEnd()->getDateTime())
                    ->setTimezone('Asia/Riyadh')
                    ->translatedFormat('d/m/Y h:i A'),
            ];

            // التحقق مما إذا كان الاجتماع قد انتهى
            $now = Carbon::now('Asia/Riyadh');
            $endDateTime = Carbon::parse($event->getEnd()->getDateTime())->setTimezone('Asia/Riyadh');
            $isMeetingEnded = $now->gt($endDateTime);

            // إضافة معلومات إضافية إذا كان الاجتماع مرتبطًا بمشروع أو دعوى
            if ($meetingNote) {
                if ($meetingNote->project_id) {
                    $project = Project::find($meetingNote->project_id);
                    $meeting['project_name'] = $project ? $project->project_name : null;
                }
                if ($meetingNote->lawsuits_id) {
                    $lawsuit = Lawsuit::find($meetingNote->lawsuits_id);
                    $meeting['lawsuit_name'] = $lawsuit ? $lawsuit->name : null;
                }

                // جلب المدعوين مع أنواعهم
                $attendees = AttendeeMeeting::where('meeting_id', $meetingNote->id)
                    ->select('email', 'type')
                    ->get()
                    ->groupBy('type');

                $meeting['attendees'] = $attendees;
            }

            return view('microsoft.teams.show', compact('meeting', 'isMeetingEnded'));
        } catch (\Exception $e) {
            return redirect()->route('microsoft.teams.index')
                ->with('error', 'حدث خطأ أثناء عرض تفاصيل الاجتماع');
        }
    }

    /*
   |--------------------------------------------------------------------------
   | createMeeting of TeamsController
   |--------------------------------------------------------------------------
   | انشاء الاجتماع
   */
    public function createMeeting(Request $request)
    {
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'meeting_field' => 'required|in:projects,lawsuits,public',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'meeting_participants' => 'nullable|array',
                'attendees_employee' => 'nullable|array',
                'attendees_customer' => 'nullable|array',
                'attendees_opponent' => 'nullable|array',
                'additional_emails' => 'nullable|array',

            ], [
                'subject.required' => 'حقل الموضوع مطلوب.',
                'subject.string' => 'حقل الموضوع يجب أن يكون نصًا.',
                'subject.max' => 'حقل الموضوع يجب ألا يزيد عن 255 حرفًا.',
                'start_time.required' => 'حقل وقت البدء مطلوب.',
                'start_time.date' => 'حقل وقت البدء يجب أن يكون تاريخًا صالحًا.',
                'end_time.required' => 'حقل وقت الانتهاء مطلوب.',
                'end_time.date' => 'حقل وقت الانتهاء يجب أن يكون تاريخًا صالحًا.',
                'end_time.after' => 'حقل وقت الانتهاء يجب أن يكون بعد وقت البدء.',
                'attendees.array' => 'حقل المدعوين يجب أن يكون قائمة.',
                'attendees.*.email' => 'كل مدعو يجب أن يكون بريدًا إلكترونيًا صالحًا.',
            ]);
            /*
            |--------------------------------------------------------------------------
            | معالجة التواريخ لتتناسب مع تنسيق Microsoft Graph
            |--------------------------------------------------------------------------
            */
            $startTime = \Carbon\Carbon::parse($request->input('start_time'), 'Asia/Riyadh')->toIso8601String();
            $endTime = \Carbon\Carbon::parse($request->input('end_time'), 'Asia/Riyadh')->toIso8601String();
            /*
            |--------------------------------------------------------------------------
            | معالجة التواريخ ليتم عرضها بشكل صحيح في النموذج وللمستخدم
            |--------------------------------------------------------------------------
            */
            $startTimeFormatted = \Carbon\Carbon::parse($request->input('start_time'), 'Asia/Riyadh')->format('Y-m-d H:i');
            $endTimeFormatted = \Carbon\Carbon::parse($request->input('end_time'), 'Asia/Riyadh')->format('Y-m-d H:i');
            /*
            |--------------------------------------------------------------------------
            | معالجة المدعوين
            |--------------------------------------------------------------------------
            */
            // $attendees = $request->input('attendees');

            $attendees = array_merge(
                $request->input('attendees_employee', []),
                $request->input('attendees_customer', []),
                $request->input('attendees_opponent', []),
                $request->input('additional_emails', [])
            );

            /*
            |--------------------------------------------------------------------------
            | اذا كان الحقل نصًا مفصولًا بفواصل، قم بتحويله إلى مصفوفة
            |--------------------------------------------------------------------------
            */
            if (is_string($attendees)) {
                $attendees = array_map('trim', explode(',', $attendees));
            }

            /*
            |--------------------------------------------------------------------------
            | تأكد من أن الحقل هو مصفوفة
            |--------------------------------------------------------------------------
            */
            $attendees = is_array($attendees) ? $attendees : [];


            /*
            |--------------------------------------------------------------------------
            | تحديد المستخدم الحالي
            |--------------------------------------------------------------------------
            */
            $user = auth()->user();


            /*
            |--------------------------------------------------------------------------
            | الححصول على رمز الوصول الصالح و التحقق منه
            |--------------------------------------------------------------------------
            */
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ارسال الاجتماع الى خدمة Microsoft Teams
            |--------------------------------------------------------------------------
            */
            $result = $this->teamsService->createCalendarEventWithOnlineMeeting(
                $accessToken,
                $request->input('subject'),
                $startTime,
                $endTime,
                $attendees // إرسال قائمة المدعوين هنا
            );


            /*
            |--------------------------------------------------------------------------
            | التحقق من نجاح إنشاء الاجتماع
            |--------------------------------------------------------------------------
            */
            if (!$result) {
                return redirect()->back()->withErrors(['error' => 'Failed to create meeting.'])->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | تخزين بيانات الاجتماع في قاعدة البيانات للوصول إليها لاحقًا من مايكروسوفت
            |--------------------------------------------------------------------------
            */
            $meeting = MeetingNote::create([
                'event_id' => $result['eventId'],
                'user_id' => $user->id,
                'meeting_points' => $request->input('meeting_points'),
                'meeting_outputs' => null, // أو يمكنك إضافة هذا الحقل في النموذج إذا رغبت في ذلك
                'meeting_field' => $request->input('meeting_field'),
                'project_id' => $request->input('meeting_field') === 'projects' ? $request->input('project_id') : null,
                'lawsuits_id' => $request->input('meeting_field') === 'projects' ? $request->input('lawsuits_id') : null,
                'meeting_name' => $request->input('subject'),
                'meeting_points' => $request->input('meeting_points'),
                'meeting_start_date' => $startTimeFormatted,
                'meeting_end_date' => $endTimeFormatted,
            ]);
            // حفظ المدعوين بناءً على النوع
            $this->saveAttendees($meeting->id, $request->input('attendees_employee', []), 'employees');
            $this->saveAttendees($meeting->id, $request->input('attendees_customer', []), 'customers');
            $this->saveAttendees($meeting->id, $request->input('attendees_opponent', []), 'opponents');
            $this->saveAttendees($meeting->id, $request->input('additional_emails', []), 'additional');

            // نجاح الإنشاء
            return redirect()->route('microsoft.teams.index')->with('success', 'تم إنشاء الاجتماع بنجاح! رابط الاجتماع ');
        } catch (\Exception $e) {
            // في حالة حدوث خطأ، سيتم تسجيل الخطأ وإعادة التوجيه مع رسالة خطأ
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the meeting.'])->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | لحفظ المدعويين في قاع=دة البيانات
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    private function saveAttendees($meetingId, array $emails, $type)
    {
        foreach ($emails as $email) {
            AttendeeMeeting::create([
                'meeting_id' => $meetingId,
                'email' => $email,
                'type' => $type,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | getMeetings of TeamsController
    |--------------------------------------------------------------------------
    | جلب كل الاجتماعات التي تمت دعوتك إليها والتي قمت بإنشائها
    */
    public function getMeetings(Request $request)
    {
        try {
            /*
           |--------------------------------------------------------------------------
           | الحصول على رمز الوصول الصالح و التحقق منه
           |--------------------------------------------------------------------------
           */
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return;
            }
            /*
            |--------------------------------------------------------------------------
            | جلب الاجتماعات من خدمة Microsoft Teams والتحقق من نجاح العملية
            |--------------------------------------------------------------------------
            */
            $meetings = $this->teamsService->getAllMeetings($accessToken);
            if (is_null($meetings)) {
                return response()->json(['error' => 'Failed to fetch meetings.'], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | التعامل مع البيانات المسترجعة وتنسيقها لعرضها في الجدول
            |--------------------------------------------------------------------------
            | Description of this section.
            */
            return DataTables::of($meetings)
                ->addIndexColumn() // لإضافة عمود رقمي تلقائي (DT_RowIndex)
                ->addColumn('action', function ($meeting) {
                    return '
                        <div class="dropdown">
                            <button
                                class="btn btn-sm btn-white dropdown-toggle"
                                type="button"
                                id="dropdownMenuButton' . $meeting['id'] . '"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                style="border: 1px solid #ddd; border-radius: 4px; padding: 5px 10px;">
                                الإجراءات
                            </button>
                            <ul
                                class="dropdown-menu"
                                aria-labelledby="dropdownMenuButton' . $meeting['id'] . '"
                                style="border: 1px solid #ddd; border-radius: 4px; padding: 0; background-color: #f9f9f9;">
                                <li>
                                    <a
                                        class="dropdown-item bg-white text-dark"
                                        href="' . $meeting['joinWebUrl'] . '"
                                        target="_blank"
                                        style="padding: 10px; border-bottom: 1px solid #ddd;">
                                        انضمام
                                    </a>
                                </li>
                                <li>
                                    <a
                                        class="dropdown-item bg-white text-dark"
                                        href="' . route('teams.edit', $meeting['id']) . '"
                                        style="padding: 10px; border-bottom: 1px solid #ddd;">
                                        تعديل
                                    </a>
                                </li>
                                <li>
                                    <a
                                        class="dropdown-item bg-white text-dark delete-meeting"
                                        href="#"
                                        data-id="' . $meeting['id'] . '"
                                        style="padding: 10px;">
                                        حذف
                                    </a>
                                </li>
                            </ul>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching meetings.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | edit of TeamsController
    |--------------------------------------------------------------------------
    | عرض نموذج تعديل الاجتماع
    */
    public function edit($id)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | الحصول على رمز الوصول الصالح و التحقق منه
            |--------------------------------------------------------------------------
            */
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | جلب تفاصيل الحدث باستخدام معرفه والتحقق من نجاح العملية
            |--------------------------------------------------------------------------
            */
            $event = $this->getEventDetails($accessToken, $id);
            if (!$event) {
                return redirect()->route('microsoft.teams.index')->withErrors(['error' => 'Failed to fetch event details.']);
            }


            /*
            |--------------------------------------------------------------------------
            | التحقق من المدعوين وتنسيقهم لعرضهم في النموذج
            |--------------------------------------------------------------------------
            */
            $attendees = [];
            if (is_array($event->getAttendees())) { // تحقق مما إذا كان الإرجاع مصفوفة
                foreach ($event->getAttendees() as $attendee) {
                    if (is_array($attendee) && isset($attendee['emailAddress']['address'])) {
                        $attendees[] = $attendee['emailAddress']['address'];
                    } elseif (is_object($attendee) && method_exists($attendee, 'getEmailAddress')) {
                        $attendees[] = $attendee->getEmailAddress()->getAddress();
                    }
                }
            }

            // جلب سجل MeetingNote المرتبط بالاجتماع
            $meetingNote = MeetingNote::where('event_id', $id)->first();
            $projects = Project::select('id', 'project_name')->get(); // جلب المشاريع
            $lawsuits = Lawsuit::select('id', 'name')->get(); // جلب الدعاوى

            /*
            |--------------------------------------------------------------------------
            | المدعوين للاجتماع
            |--------------------------------------------------------------------------
            */
            $employees = Employees::where('work_email', '!=', null)->select('id', 'name', 'work_email')->get();
            $opponents = Opponent::where('email', '!=', null)->select('id', 'name', 'email')->get();
            $customers = Customers::where('email', '!=', null)->select('id', 'name', 'email')->get();
            // جلب المدعوين حسب النوع
            $attendees_employee =   $meetingNote->attendeeMeetings()->where('type', 'employees')->pluck('email')->toArray();
            $attendees_customer =   $meetingNote->attendeeMeetings()->where('type', 'customers')->pluck('email')->toArray();
            $attendees_opponent =   $meetingNote->attendeeMeetings()->where('type', 'opponents')->pluck('email')->toArray();
            $additional_emails  =   $meetingNote->attendeeMeetings()->where('type', 'additional')->pluck('email')->toArray();
            // عرض النموذج
            return view('microsoft.teams.edit', [
                'event' => $event,
                'attendees' => implode(',', $attendees), // تحويل قائمة البريد الإلكتروني إلى نص مفصول بفواصل
                'meeting' => $meetingNote,
                'projects' => $projects,
                'lawsuits' => $lawsuits,
                'employees' => $employees,
                'opponents' => $opponents,
                'customers' => $customers,
                'attendees_employee' => $attendees_employee,
                'attendees_customer' => $attendees_customer,
                'attendees_opponent' => $attendees_opponent,
                'additional_emails' => $additional_emails,

            ]);
        } catch (\Exception $e) {
            return redirect()->route('microsoft.teams.index')->withErrors(['error' => 'An error occurred while fetching the event details.']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | update of TeamsController
    |--------------------------------------------------------------------------
    | تعديل الاجتماع بيانات الاجتماع
    */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | validate the request
        |--------------------------------------------------------------------------
        | التحقق من صحة الطلب
        */
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'meeting_participants' => 'nullable|array',
                'attendees_employee' => 'nullable|array',
                'attendees_customer' => 'nullable|array',
                'attendees_opponent' => 'nullable|array',
                'additional_emails' => 'nullable|array',
            ], [
                'subject.required' => 'حقل الموضوع مطلوب.',
                'subject.string' => 'حقل الموضوع يجب أن يكون نصًا.',
                'subject.max' => 'حقل الموضوع يجب ألا يزيد عن 255 حرفًا.',
                'start_time.required' => 'حقل وقت البدء مطلوب.',
                'start_time.date' => 'حقل وقت البدء يجب أن يكون تاريخًا صالحًا.',
                'end_time.required' => 'حقل وقت الانتهاء مطلوب.',
                'end_time.date' => 'حقل وقت الانتهاء يجب أن يكون تاريخًا صالحًا.',
                'end_time.after' => 'حقل وقت الانتهاء يجب أن يكون بعد وقت البدء.',
                'attendees.array' => 'حقل المدعوين يجب أن يكون مصفوفة.',
                'attendees.*.email' => 'كل مدعو يجب أن يكون بريدًا إلكترونيًا صالحًا.',
            ]);

            /*
            |--------------------------------------------------------------------------
            | معالجة التواريخ لتتناسب مع تنسيق Microsoft Graph
            |--------------------------------------------------------------------------
            */
            $startTime = \Carbon\Carbon::parse($request->input('start_time'), 'Asia/Riyadh')->toIso8601String();
            $endTime = \Carbon\Carbon::parse($request->input('end_time'), 'Asia/Riyadh')->toIso8601String();

            /*
            |--------------------------------------------------------------------------
            | معالجة التواريخ ليتم عرضها بشكل صحيح في النموذج وللمستخدم
            |--------------------------------------------------------------------------
            */
            $startTimeFormatted = \Carbon\Carbon::parse($request->input('start_time'), 'Asia/Riyadh')->format('Y-m-d H:i');
            $endTimeFormatted = \Carbon\Carbon::parse($request->input('end_time'), 'Asia/Riyadh')->format('Y-m-d H:i');


            /*
            |--------------------------------------------------------------------------
            | جلب الاجتماع بناءً على event_id
            |--------------------------------------------------------------------------
            */
            $meeting = MeetingNote::where('event_id', $id)->firstOrFail();




            /*
            |--------------------------------------------------------------------------
            | الحصول على الفئات المحددة
            |--------------------------------------------------------------------------
            */
            $selectedParticipants = $request->input('meeting_participants', []);

            /*
            |--------------------------------------------------------------------------
            | معالجة المدعوين بناءً على الفئات المحددة فقط
            |--------------------------------------------------------------------------
            */

            $attendees = [];

            if (in_array('employees', $selectedParticipants)) {
                $attendees = array_merge($attendees, $request->input('attendees_employee', []));
            }

            if (in_array('customers', $selectedParticipants)) {
                $attendees = array_merge($attendees, $request->input('attendees_customer', []));
            }

            if (in_array('opponents', $selectedParticipants)) {
                $attendees = array_merge($attendees, $request->input('attendees_opponent', []));
            }

            if (in_array('additional', $selectedParticipants)) {
                $attendees = array_merge($attendees, $request->input('additional_emails', []));
            }

            if (is_string($attendees)) {
                $attendees = array_map('trim', explode(',', $attendees));
            }

            // تأكد من أن الحقل هو مصفوفة
            $attendees = is_array($attendees) ? $attendees : [];


            // الحصول على رمز الوصول الصالح
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                // سيتم إعادة التوجيه داخل دالة getAccessToken في حالة الخطأ
                return;
            }


            /*
           |--------------------------------------------------------------------------
           | ارسال الاجتماع الى خدمة Microsoft Teams لتعديله
           |--------------------------------------------------------------------------
           */
            $updateResult = $this->teamsService->updateCalendarEvent(
                $accessToken,
                $id,
                $request->input('subject'),
                $startTime,
                $endTime,
                $attendees // إرسال قائمة المدعوين
            );

            /*
            |--------------------------------------------------------------------------
            | التحقق من نجاح تعديل الاجتماع
            |--------------------------------------------------------------------------
            */
            if ($updateResult) {
                // تحديث بيانات الاجتماع في قاعدة البيانات
                $meeting->update([
                    'meeting_field' => $request->input('meeting_field'),
                    'project_id' => $request->input('meeting_field') === 'projects' ? $request->input('project_id') : null,
                    'lawsuits_id' => $request->input('meeting_field') === 'projects' ? $request->input('lawsuits_id') : null,
                    'meeting_name' => $request->input('subject'),
                    'meeting_points' => $request->input('meeting_points'),
                    'meeting_start_date' => $startTimeFormatted,
                    'meeting_end_date' => $endTimeFormatted,
                    'meeting_outputs' => $request->input('meeting_outputs'),
                ]);

                // تحديث المدعوين
                $meeting->attendeeMeetings()->delete(); // حذف المدعوين الحاليين

                $this->saveAttendees($meeting->id, $request->input('attendees_employee', []), 'employees');
                $this->saveAttendees($meeting->id, $request->input('attendees_customer', []), 'customers');
                $this->saveAttendees($meeting->id, $request->input('attendees_opponent', []), 'opponents');
                $this->saveAttendees($meeting->id, $request->input('additional_emails', []), 'additional');
            }

            // نجاح التعديل والتوجيه
            return redirect()->route('microsoft.teams.index')->with('success', 'تم تعديل الاجتماع بنجاح!');
        } catch (\Exception $e) {
            // في حالة حدوث خطأ، سيتم تسجيل الخطأ وإعادة التوجيه مع رسالة خطأ
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the meeting.'])->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | destroy of TeamsController
    |--------------------------------------------------------------------------
    | حذف الاجتماع
    */
    public function destroy($id)
    {
        try {
            /*
           |--------------------------------------------------------------------------
           | التحقق من صحة التوكن
           |--------------------------------------------------------------------------
           */
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ارسال طلب الحذف إلى خدمة Microsoft Teams والتحقق من نجاح العملية
            |--------------------------------------------------------------------------
            */
            $deleteResult = $this->teamsService->deleteCalendarEvent($accessToken, $id);


            // التحقق من نجاح الحذف
            if ($deleteResult) {
                MeetingNote::where('event_id', $id)->delete();
            }

            // في حالة فشل الحذف
            if (!$deleteResult) {
                return response()->json(['error' => 'Failed to delete meeting.'], 500);
            }

            // في حالة نجاح الحذف
            return response()->json(['success' => 'تم حذف الاجتماع بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the meeting.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | getEventDetails of TeamsController
    |--------------------------------------------------------------------------
    | جلب تفاصيل الحدث باستخدام معرفه
    */
    private function getEventDetails($accessToken, $eventId)
    {
        try {
            $graph = new \Microsoft\Graph\Graph();
            $graph->setAccessToken($accessToken);

            $event = $graph->createRequest("GET", "/me/events/{$eventId}")
                ->setReturnType(\Microsoft\Graph\Model\Event::class)
                ->execute();

            return $event;
        } catch (\Exception $e) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | getAccessToken of TeamsController
    |--------------------------------------------------------------------------
    | جلب رمز الوصول الصالح
    */
    protected function getAccessToken()
    {
        // جلب المستخدم الحالي
        $user = Auth::user();

        // التحقق من ربط حساب Microsoft
        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        // الحصول على رمز الوصول الصالح
        $accessToken = $this->graphService->getValidUserAccessToken($user);

        // التحقق من نجاح الحصول على رمز الوصول
        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }

        return $accessToken;
    }

    /**
     * تحديث مخرجات الاجتماع
     */
    public function updateOutputs(string $id, Request $request)
    {
        try {
            $meeting = MeetingNote::where('event_id', $id)->firstOrFail();

            $request->validate([
                'meeting_outputs' => 'required|string'
            ], [
                'meeting_outputs.required' => 'حقل المخرجات مطلوب'
            ]);

            $meeting->update([
                'meeting_outputs' => $request->meeting_outputs
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث مخرجات الاجتماع بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المخرجات'
            ], 500);
        }
    }
}
