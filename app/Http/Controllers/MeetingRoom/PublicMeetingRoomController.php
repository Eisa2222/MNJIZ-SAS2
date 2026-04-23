<?php

namespace App\Http\Controllers\MeetingRoom;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom\MeetingRoom;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicMeetingRoomController extends Controller
{
    private $page   = "meeting_rooms";

    public function show($meeting_rooms)
    {
        if (! in_array($meeting_rooms, ['big', 'small'], true)) {
            throw new NotFoundHttpException();
        }

        // اضبط المنطقة الزمنية (اختياري إذا كانت مضبوطة في config/app.php)
        $tz   = 'Asia/Riyadh';
        $now  = Carbon::now($tz);
        $date = $now->toDateString(); // اليوم فقط

        // جدول اليوم للقاعة المطلوبة
        $meetings = MeetingRoom::query()
            ->where('hall', $meeting_rooms)
            ->whereDate('date', $date)
            ->orderBy('from_time')
            ->get();

        // الاجتماع الجاري الآن
        $current = $meetings->first(function ($m) use ($now, $tz) {
            $start = Carbon::parse($m->date . ' ' . $m->from_time, $tz);
            $end   = Carbon::parse($m->date . ' ' . $m->to_time,   $tz);
            return $now->between($start, $end);
        });

        // الاجتماع القادم (أقرب بداية بعد الآن)
        $next = $meetings->first(function ($m) use ($now, $tz) {
            $start = Carbon::parse($m->date . ' ' . $m->from_time, $tz);
            return $start->gt($now);
        });

        return view($this->page . '.public', compact('meeting_rooms', 'now', 'meetings', 'current', 'next', 'tz'));
    }
}
