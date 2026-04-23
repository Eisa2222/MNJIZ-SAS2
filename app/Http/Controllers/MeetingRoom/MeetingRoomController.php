<?php

namespace App\Http\Controllers\MeetingRoom;

use App\Data\MeetingRoom\MeetingRoomData;
use App\DataTables\MeetingRoom\MeetingRoomDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeetingRoom\MeetingRoomRequest;
use App\Models\Hr\Employees\Employees;
use App\Models\MeetingRoom\MeetingRoom;
use App\Models\OperationsCenter\Customer\Customers;
use App\Services\MeetingRoom\MeetingRoomService;
use App\Validators\MeetingRoom\ParticipantsValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeetingRoomController extends Controller
{

    private $route  = "meeting-rooms";
    private $page   = "meeting_rooms";


    public function __construct(private MeetingRoomService $service, private ParticipantsValidator $participantsValidator)
    {
        // $this->middleware('can:إدارة السلف')->only(['index', 'show']);
        // $this->middleware('can:إضافة سلفة')->only(['create', 'store']);
        // $this->middleware('can:تعديل سلفة')->only(['edit', 'update']);
        // $this->middleware('can:حذف سلفة')->only(['destroy']);
    }

    public function index(MeetingRoomDataTable $dataTable)
    {
        try {

            // // Statistics
            // $statusCounts = Advance::query()
            //     ->select('status')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('status')
            //     ->pluck('count', 'status')
            //     ->toArray();

            // $totalAdvance = array_sum($statusCounts);
            // $pendingAdvance  = $statusCounts[AdvanceStatus::Pending->value] ?? 0;
            // $approvedAdvance = $statusCounts[AdvanceStatus::Approved->value] ?? 0;
            // $rejectedAdvance = $statusCounts[AdvanceStatus::Rejected->value] ?? 0;

            // // Filters
            // $employees        = Employees::active()->select('id', 'name', 'nickname')->get();
            // $advanceTypes     = AdvanceType::options();
            // $advanceStatus    = AdvanceStatus::options();
            return $dataTable->render($this->page . '.index');
            // return $dataTable->render('hr.advances.index', compact(
            //     // Statistics
            //     'totalAdvance',
            //     'pendingAdvance',
            //     'approvedAdvance',
            //     'rejectedAdvance',
            //     // Filters
            //     'employees',
            //     'advanceTypes',
            //     'advanceStatus'
            // ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        $employees = Employees::active()->where('work_email', '!=', null)->select('id', 'name', 'nickname', 'work_email')->get();
        $customers = Customers::where('email', '!=', null)->select('id', 'name', 'email')->get();

        return view($this->page . '.create', compact('employees', 'customers'));
    }


    public function store(MeetingRoomRequest $request)
    {
        try {
            $participants = $this->participantsValidator->validateAndBuild($request->all());

            $data = $request->validated();

            $data['participants'] = $participants;

            $dto = new MeetingRoomData($data);

            $this->service->create($dto);

            return redirect()
                ->route($this->route . '.index')
                ->with('success', 'تم إضافة الحجز بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function show(MeetingRoom $meeting_room)
    {
        $meeting_room->load('participants');

        return view($this->page . '.show', compact('meeting_room'));
    }


    public function edit(MeetingRoom $meeting_room)
    {
        $meeting_room->load('participants');

        $employees = Employees::active()->where('work_email', '!=', null)->select('id', 'name', 'nickname', 'work_email')->get();
        $customers = Customers::where('email', '!=', null)->select('id', 'name', 'email')->get();

        // dd($meeting_room);
        return view($this->page . '.edit', compact('meeting_room', 'employees', 'customers'));
    }


    public function update(MeetingRoomRequest $request,  $meeting_room)
    {
        try {
            $participants = $this->participantsValidator->validateAndBuild($request->all());

            $data = $request->validated();
            $data['participants'] = $participants;

            $dto = new MeetingRoomData($data);

            $this->service->update($meeting_room, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث الحجز بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);

            return redirect()->route($this->route . '.index')->with('success', 'تم حذف الحجز بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
