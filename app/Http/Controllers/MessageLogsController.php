<?php

namespace App\Http\Controllers;

use App\Models\MessageLog;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Opponent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class MessageLogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:سجل الرسائل')->only([
            'index',
            'show',
            'getRecipients',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $logs = MessageLog::with('sender')->select(['id', 'sender_id', 'message_text', 'platform', 'recipients', 'created_at']);

                $arabicTypes = [
                    'customer' => 'العملاء',
                    'employee' => 'الموظفين',
                    'opponent' => 'الخصوم',
                ];

                return DataTables::of($logs)
                    ->addIndexColumn()
                    ->addColumn('sender', function ($row) {
                        return $row->sender ? $row->sender->name : 'غير محدد';
                    })
                    ->addColumn('recipients_buttons', function ($row) use ($arabicTypes) {
                        $recipients = $this->normalizeRecipients($row->recipients);

                        $types = ['customer' => 0, 'employee' => 0, 'opponent' => 0];

                        if (is_array($recipients)) {
                            foreach ($recipients as $recipient) {
                                if (is_array($recipient) && isset($recipient['type']) && array_key_exists($recipient['type'], $types)) {
                                    $types[$recipient['type']] += 1;
                                }
                            }
                        }

                        $buttons = '';
                        foreach ($types as $type => $count) {
                            if ($count > 0) {
                                $buttonLabel = $arabicTypes[$type] . ' (' . $count . ')';
                                $buttons .= '<button type="button" class="btn btn-sm btn-primary view-recipients mx-1 mb-1" data-id="' . $row->id . '" data-type="' . $type . '">' . $buttonLabel . '</button> ';
                            }
                        }

                        return $buttons ?: '<span class="text-muted">لا يوجد مستلمين</span>';
                    })
                    ->addColumn('action', function ($row) {
                        $showUrl = route('messageLogs.show', $row->id);
                        return '<a href="' . $showUrl . '" class="btn btn-sm btn-info">عرض التفاصيل</a>';
                    })
                    ->editColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at)->isoFormat('D MMMM YYYY');
                    })
                    ->rawColumns(['recipients_buttons', 'action'])
                    ->make(true);
            }

            $totalMessages = MessageLog::count();

            $allMessages = MessageLog::select('recipients')->get();

            $employeeMessagesCount = 0;
            $customerMessagesCount = 0;
            $opponentMessagesCount = 0;

            foreach ($allMessages as $message) {
                $recipients = $this->normalizeRecipients($message->recipients);

                if (is_array($recipients)) {
                    foreach ($recipients as $recipient) {
                        if (is_array($recipient) && isset($recipient['type'])) {
                            if ($recipient['type'] === 'employee') {
                                $employeeMessagesCount++;
                            } elseif ($recipient['type'] === 'customer') {
                                $customerMessagesCount++;
                            } elseif ($recipient['type'] === 'opponent') {
                                $opponentMessagesCount++;
                            }
                        }
                    }
                }
            }

            return view('message_logs.index', compact(
                'totalMessages',
                'employeeMessagesCount',
                'customerMessagesCount',
                'opponentMessagesCount'
            ));
        } catch (\Exception $e) {
            Log::error("حدث خطأ أثناء جلب سجلات الرسائل: " . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب سجلات الرسائل. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Recipinents
    |--------------------------------------------------------------------------
    */
    private function normalizeRecipients($recipients)
    {
        if (empty($recipients)) {
            return [];
        }

        if (is_array($recipients)) {
            return $recipients;
        }

        if (is_string($recipients)) {
            try {
                // التحقق من وجود تشفير مزدوج (حالة خاصة)
                if (strpos($recipients, '\"') === 0 || strpos($recipients, '\\"') === 0) {
                    $firstDecode = json_decode($recipients, true);

                    // فك التشفير الثاني إذا كانت النتيجة نصية
                    if (is_string($firstDecode)) {
                        return json_decode($firstDecode, true) ?: [];
                    }
                    return is_array($firstDecode) ? $firstDecode : [];
                }

                $decoded = json_decode($recipients, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                return [];
            }
        }
        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | getRecipients
    |--------------------------------------------------------------------------
    */
    public function getRecipients($id, $type)
    {
        try {
            $log = MessageLog::findOrFail($id);

            $normalizedRecipients = $this->normalizeRecipients($log->recipients);

            $recipients = collect($normalizedRecipients)
                ->where('type', $type)
                ->pluck('id')
                ->filter();

            $data = [];
            switch ($type) {
                case 'customer':
                    if ($recipients->isNotEmpty()) {
                        $data = Customers::whereIn('id', $recipients)->get(['id', 'name', 'contact_number']);
                    }
                    break;

                case 'employee':
                    if ($recipients->isNotEmpty()) {
                        $data = Employees::whereIn('id', $recipients)->get(['id', 'name', 'mobile']);
                    }
                    break;

                case 'opponent':
                    if ($recipients->isNotEmpty()) {
                        $data = Opponent::whereIn('id', $recipients)->get(['id', 'name', 'phone']);
                    }
                    break;

                default:
                    return response()->json(['error' => 'نوع المستلم غير معروف.'], 400);
            }

            return response()->json(['recipients' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء جلب المستلمين. يرجى المحاولة لاحقاً.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | show
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $log = MessageLog::with('sender')->findOrFail($id);

            // استخدام نفس دالة التطبيع التي تم استخدامها في index
            $normalizedRecipients = $this->normalizeRecipients($log->recipients);

            // جلب المستلمين مقسمة حسب النوع
            $customers = collect($normalizedRecipients)->where('type', 'customer')->pluck('id')->filter();
            $employees = collect($normalizedRecipients)->where('type', 'employee')->pluck('id')->filter();
            $opponents = collect($normalizedRecipients)->where('type', 'opponent')->pluck('id')->filter();

            // استعلام قواعد البيانات فقط إذا كانت هناك معرفات
            $customerList = $customers->isNotEmpty()
                ? Customers::whereIn('id', $customers)->get(['name', 'contact_number'])
                : collect();

            $employeeList = $employees->isNotEmpty()
                ? Employees::whereIn('id', $employees)->get(['name', 'mobile'])
                : collect();

            $opponentList = $opponents->isNotEmpty()
                ? Opponent::whereIn('id', $opponents)->get(['name', 'phone'])
                : collect();

            return view('message_logs.show', compact('log', 'customerList', 'employeeList', 'opponentList'));
        } catch (\Exception $e) {
            Log::error("حدث خطأ أثناء جلب تفاصيل الرسالة: " . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب تفاصيل الرسالة. يرجى المحاولة لاحقاً.');
        }
    }
}
