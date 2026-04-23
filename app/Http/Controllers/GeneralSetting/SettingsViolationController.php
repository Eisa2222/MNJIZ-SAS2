<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSetting\SettingsViolationRequest;
use App\Models\general_setting\SettingsViolation;
use App\Models\general_setting\SettingsViolationCategory;
use Illuminate\Http\Request;

class SettingsViolationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware('can:أنواع المخالفات')->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $request = request();
        $route = 'settings-violations';

        try {
            if ($request->ajax()) {
                $setting = SettingsViolation::select([
                    'id',
                    'description',
                    'settings_violation_category_id',
                    'penalty_first',
                    'penalty_second',
                    'penalty_third',
                    'penalty_fourth',
                    'extra_deduction',
                ]);
                //    }

                return datatables()->of($setting)
                    ->addIndexColumn() // إضافة هذا السطر لتفعيل الترقيم
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })

                    ->editColumn('penalty_first', function ($row) {
                        return $this->formatPenaltyForDatatable($row->penalty_first);
                    })
                    ->editColumn('penalty_second', function ($row) {
                        return $this->formatPenaltyForDatatable($row->penalty_second);
                    })
                    ->editColumn('penalty_third', function ($row) {
                        return $this->formatPenaltyForDatatable($row->penalty_third);
                    })
                    ->editColumn('penalty_fourth', function ($row) {
                        return $this->formatPenaltyForDatatable($row->penalty_fourth);
                    })
                    ->addColumn('extra_deduction', function ($row) {
                        return $row->extra_deduction ?? '---';
                    })

                    ->addColumn('action', function ($row) use ($route) {
                        $buttons = '<div class="d-flex gap-2">';

                        $buttons .= '
                            <a  class="btn btn-sm text-secondary btn-edit" href="' . route('settings-violations.edit', $row->id) . '">
                                <i class="ti ti-edit"></i>
                            </a>';

                        $buttons .= '
                            <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete(' . $row->id . ')">
                                <i class="ti ti-trash"></i>
                            </button>

                            <form id="delete-form-' . $row->id . '" action="' . route($route . '.destroy', $row->id) . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                            </form>';

                        $buttons .= '</div>';

                        return $buttons;
                    })

                    ->rawColumns(['penalty_first', 'penalty_second', 'penalty_third', 'penalty_fourth', 'action', 'status'])
                    ->make(true);
            }

            return view('general_setting.violations.index', compact('route'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }




    /*
    |--------------------------------------------------------------------------
    | create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $violationCategory = SettingsViolationCategory::select(['id', 'name'])->get();
        return view('general_setting.violations.create', compact('violationCategory'));
    }


    /*
    |--------------------------------------------------------------------------
    | store
    |--------------------------------------------------------------------------
    */
    public function store(SettingsViolationRequest $request)
    {
        /*
        |--------------------------------------------------------------------------
        | validate request
        |--------------------------------------------------------------------------
        */
        $data = $request->validated();

        $data['user_id'] = auth()->id();

        SettingsViolation::create($data);

        return redirect()->route('settings-violations.index')->with('success', 'تمت إضافة نوع المخالفة بنجاح.');
    }




    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit(string $id)
    {
        $violation = SettingsViolation::find($id);
        $violationCategory = SettingsViolationCategory::select(['id', 'name'])->get();
        return view('general_setting.violations.edit', compact('violationCategory', 'violation'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(SettingsViolationRequest $request, string $id)
    {
        /*
        |--------------------------------------------------------------------------
        | validate request
        |--------------------------------------------------------------------------
        */
        $data = $request->validated();

        $violation = SettingsViolation::find($id);

        $violation->update($data);

        return redirect()->route('settings-violations.index')->with('success', 'تم تعديل نوع المخالفة بنجاح.');
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(string $id)
    {
        //
    }



    /*
    |--------------------------------------------------------------------------
    |  لعرض اسماء المخالفات 
    |--------------------------------------------------------------------------
    */
    private function formatPenaltyForDatatable($penalty)
    {
        if (empty($penalty)) {
            return '---';
        }

        if (strpos($penalty, ':') !== false) {
            list($type, $value) = explode(':', $penalty);

            switch ($type) {
                case 'percentage':
                    $displayText = $value . '%';
                    break;
                case 'days':
                    if ($value == 1) $displayText = 'يوم';
                    else if ($value == 2) $displayText = 'يومان';
                    else $displayText = $value . ' أيام';
                    break;
                case 'warning':
                    $displayText = 'إنذار كتابي';
                    break;
                case 'ban':
                    $displayText = 'الحرمان من الترقيات أو العلاوات لمرة واحدة';
                    break;
                case 'termination':
                    switch ($value) {
                        case 'with_benefit':
                            $displayText = 'فصل مع المكافأة';
                            break;
                        case 'without_benefit':
                            $displayText = 'فصل بدون مكافأة';
                            break;
                        case 'with_benefit_30':
                            $displayText = 'فصل من الخدمة مع المكافأة إذا لم يتجاوز الغياب (30) يوماً';
                            break;
                        case 'article_80':
                            $displayText = 'فصل من الخدمة طبقاً للمادة (الثمانون) من نظام العمل';
                            break;
                        case 'article_80_10days':
                            $displayText = 'الفصل دون مكافأة، أو تعويض، على أن يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام';
                            break;
                        case 'article_80_20days':
                            $displayText = 'الفصل دون مكافأة، أو تعويض، على أن يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً';
                            break;
                        default:
                            $displayText = 'فصل من الخدمة';
                    }
                    break;
                default:
                    $displayText = $penalty;
            }

            return  $displayText;
        }

        return $penalty;
    }
}
