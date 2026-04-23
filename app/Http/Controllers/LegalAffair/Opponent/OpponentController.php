<?php

namespace App\Http\Controllers\LegalAffair\Opponent;

use App\Data\LegalAffair\Opponent\OpponentData;
use App\DataTables\LegalAffairs\Opponents\OpponentsDataTable;
use App\Enums\LegalAffair\Opponent\OpponentType;
use App\Helpers\General;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAffair\Opponent\StoreOpponentRequest;
use App\Http\Requests\LegalAffair\Opponent\UpdatOpponentRequest;
use App\Models\general_setting\SettingsRegion;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\MessageLog;
use App\Services\LegalAffair\Opponent\OpponentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OpponentController extends Controller
{
    private $route = "legal-affairs.opponents";
    private $page = "legal_affairs.opponents";


    public function __construct(private OpponentService $opponentService)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الخصوم') || $request->user()->can('الخصوم الخاصين بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة خصم')->only(['create', 'store']);
        $this->middleware('can:تعديل خصم')->only(['edit', 'update']);
        $this->middleware('can:حذف خصم')->only(['destroy']);
    }


    public function index(OpponentsDataTable $dataTable)
    {
        $query = Opponent::query();
        try {
            if (auth()->user()->can('الخصوم الخاصين بي') && !auth()->user()->can('كل الخصوم')) {
                $query->where('created_by', auth()->id());
            }
            // Statistics
            $typeCounts = $query
                ->select('type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            $totalOpponents         = array_sum($typeCounts);
            $individualOpponents    = $typeCounts[OpponentType::Individual->value] ?? 0;
            $companyOpponents       = $typeCounts[OpponentType::Company->value] ?? 0;

            // // // Filters
            $opponentType        = OpponentType::options();
            $settingsRegions   = SettingsRegion::select(['id', 'name'])->get();


            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalOpponents',
                'individualOpponents',
                'companyOpponents',
                // Filters
                'opponentType',
                'settingsRegions',
            ));
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // public function index_old(Request $request)
    // {
    //     $route = 'opponents';

    //     $baseQuery =  Opponent::select([
    //         'id',
    //         'name',
    //         'email',
    //         'contact_number',
    //         'type',
    //         'settings_region_id',
    //         'created_at',
    //     ]);

    //     // تطبيق فلتر الصلاحيات مرة واحدة
    //     if (auth()->user()->can('الخصوم الخاصين بي') && !auth()->user()->can('كل الخصوم')) {
    //         $baseQuery->where('created_by', auth()->id());
    //     }


    //     try {
    //         if ($request->ajax()) {
    //             $query = clone $baseQuery;

    //             if ($request->has('type') && $request->type != '') {
    //                 $query->where('type', $request->type);
    //             }

    //             if ($request->has('region') && $request->region != '') {
    //                 $query->where('settings_region_id', $request->region);
    //             }

    //             $data = $query->select('opponents.*');

    //             return datatables()->of($data)
    //                 ->addIndexColumn()
    //                 ->addColumn('checkbox', function ($row) {
    //                     return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
    //                 })
    //                 ->addColumn('action', function ($row) {
    //                     $editUrl = route('opponents.edit', $row->id);
    //                     $deleteUrl = route('opponents.destroy', $row->id);
    //                     $formId = 'delete-form-' . $row->id;
    //                     $csrfField = csrf_field();
    //                     $methodField = method_field('DELETE');

    //                     $buttons = '<div class="d-flex justify-content-center">';

    //                     if (auth()->user()->can('تعديل خصم')) {
    //                         $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';
    //                     }

    //                     if (auth()->user()->can('حذف خصم')) {
    //                         $buttons .= '<a href="javascript:void(0);" onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm text-secondary">
    //                                             <i class="ti ti-trash"></i>
    //                                          </a>
    //                                          <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
    //                                             ' . $csrfField . '
    //                                             ' . $methodField . '
    //                                          </form>';
    //                     }
    //                     $buttons .= '</div>';

    //                     return $buttons;
    //                 })

    //                 ->editColumn('settings_region_id', function ($row) {
    //                     return $row->region ? $row->region->name : '';
    //                 })
    //                 ->editColumn('created_at', function ($row) {
    //                     return $row->hijri_created_at ? $row->hijri_created_at : '';
    //                 })
    //                 ->editColumn('type', function ($row) {
    //                     return $row->type == 'individual' ? 'فرد' : 'شخصية اعتبارية';
    //                 })
    //                 // filtter
    //                 ->filterColumn('settings_region_id', function ($query, $keyword) {
    //                     $query->whereHas('region', function ($q) use ($keyword) {
    //                         $q->where('name', 'like', "%{$keyword}%");
    //                     });
    //                 })

    //                 ->rawColumns(['checkbox', 'action'])
    //                 ->make(true);
    //         }

    //         return view('judicial_affairs.opponents.index', compact('route'));
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
    //     }
    // } //end of index

    public function create()
    {
        $settings_regions = SettingsRegion::where('status', 'active')->select(['id', 'name'])->get();

        return view($this->page . '.create', compact('settings_regions'));
    }

    public function store(StoreOpponentRequest $request)
    {
        try {
            $dto = new OpponentData($request->validated());

            $this->opponentService->createOpponent($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة الخصم');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Opponent $opponent)
    {
        $lawsuitsCount  = $opponent->lawsuitsAsPlaintiff()->count() + $opponent->lawsuitsAsDefendant()->count();

        return view($this->page . '.show', compact(
            'opponent',
            'lawsuitsCount'
        ));
    }

    public function edit(Opponent $opponent)
    {
        $settings_regions   = SettingsRegion::where('status', 'active')->select(['id', 'name'])->get();

        return view($this->page . '.edit', compact('opponent', 'settings_regions'));
    }

    public function update(UpdatOpponentRequest $request, Opponent $opponent)
    {
        try {
            $dto = new OpponentData($request->validated());

            $this->opponentService->updateOpponent($opponent->id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث بيانات الخصم .');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $opponent = Opponent::findOrFail($id);

        $hasLawsuitsAsPlaintiff = $opponent->lawsuitsAsPlaintiff()->exists();
        $hasLawsuitsAsDefendant = $opponent->lawsuitsAsDefendant()->exists();

        if ($hasLawsuitsAsPlaintiff || $hasLawsuitsAsDefendant) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن حذف الخصم لارتباطه بسجلات أخرى.');
        }

        $opponent->delete();
        return redirect()->route($this->route . '.index')->with('success', 'تم حذف الخصم بنجاح!');
    }


    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message'           => 'required|string|max:1600',
            'opponent_ids'      => 'required|array',
            'opponent_ids.*'    => 'integer|exists:opponents,id',
        ], [
            'message.required'      => 'حقل الرسالة مطلوب.',
            'message.max'           => 'حقل الرسالة لا يجب أن يتجاوز 1600 حرف.',
            'opponent_ids.required' => 'يجب تحديد خصوم لإرسال الرسالة.',
            'opponent_ids.array'    => 'صيغة معرفات الخصوم غير صحيحة.',
            'opponent_ids.*.exists' => 'الخصم المحدد غير موجود.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $message = $request->input('message');
        $opponentIds = $request->input('opponent_ids');


        $opponents = Opponent::whereIn('id', $opponentIds)->get();

        $failedNumbers = [];
        $successfulRecipients = [];

        foreach ($opponents as $opponent) {
            if ($opponent->contact_number) {
                $formattedNumber = ltrim($opponent->contact_number, '0');
                $sent = General::sendSMS($message, $formattedNumber);

                if ($sent) {
                    $successfulRecipients[] = [
                        'type' => 'opponent',
                        'id' => $opponent->id,
                    ];
                } else {
                    $failedNumbers[] = $formattedNumber;
                }
            }
        }

        // تسجيل الرسالة في جدول message_logs
        MessageLog::create([
            'sender_id' => Auth::id(),
            'message_text' => $message,
            'platform' => 'SMS',
            'recipients' => $successfulRecipients,
        ]);

        if (empty($failedNumbers)) {
            return redirect()->route($this->route . '.index')->with('success', 'تم إرسال الرسائل النصية بنجاح.');
        } else {
            return redirect()->route($this->route . '.index')->with('error', 'فشل إرسال الرسائل إلى بعض الأرقام. الرجاء مراجعة السجل.');
        }
    }
}
