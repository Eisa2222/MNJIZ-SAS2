<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\SettingsTemplate;
use App\Models\TemplateVariables;
use Carbon\Carbon;
use Database\Seeders\TemplateVariableSeeder;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SettingsTemplateController extends Controller
{

    use CommonSettingController;

    protected $model = SettingsTemplate::class;
    protected $view = 'general_setting.SettingsTemplate';

    /*
    |--------------------------------------------------------------------------
    | Constructor for Settings Templates
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware('can:النماذج')->only([
            'index',
            'show',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            'restore',
            'editStatus',
            'massDelete'
        ]);
    }


    public function index()
    {
        $route = 'settings-templates';

        $request = request();

        try {
            if ($request->ajax()) {
                $setting = $this->model::select([
                    'id',
                    'name',
                    'status',
                    'created_at',
                ]);

                return datatables()->of($setting)
                    ->addIndexColumn() // إضافة هذا السطر لتفعيل الترقيم
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })
                    ->addColumn('name', function ($row) {
                        $showtUrl = route('settings-templates.show', $row->id);
                        return '<a href="' . $showtUrl . '">' . $row->name . '</a>';
                    })
                    ->addColumn('status', function ($row) {


                        switch ($row->status) {
                            case 'active':
                                $badge =
                                    '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';
                                break;
                            case 'inactive':
                                $badge =
                                    '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">غير نشط</span>';
                                break;
                            default:
                                $badge = '<span class="badge bg-secondary">غير معروف</span>';
                                break;
                        }

                        return $badge;
                    })
                    ->addColumn('action', function ($row) {
                        $buttons = '';

                        // زر التعديل إذا كان لديه صلاحية "تعديل إعداد"

                        $editUrl = route('settings-templates.edit', $row->id);
                        $buttons .= '<a title="تعديل النموذج" href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';

                        // زر الحذف إذا كان لديه صلاحية "حذف إعداد"
                        // if (auth()->user()->can('حذف اعداد')) {
                        // $buttons .= '
                        //     <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete(' . $row->id . ')">
                        //         <i class="ti ti-trash"></i>
                        //     </button>
                        //     <form id="delete-form-' . $row->id . '" action="' . route('settings-templates.destroy', $row->id) . '" method="POST" style="display: none;">
                        //         ' . csrf_field() . '
                        //         ' . method_field('DELETE') . '
                        //     </form>';
                        // }
                        return $buttons;
                    })

                    ->editColumn('created_at', function ($row) {
                        return $row->hijri_created_at ? $row->hijri_created_at : '';
                    })
                    ->rawColumns(['checkbox', 'action', 'status', 'name'])
                    ->make(true);
            }

            return view($this->view, compact('route'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    // public function create()
    // {
    //     return abort(404);
    // }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */
    public function show(string $id)
    {
        $template = SettingsTemplate::findOrFail($id);

        $qrCode = QrCode::size(70)->generate(url()->current());

        $user = auth()->user();

        $data = [
            'user' => $user,
            'date' => Carbon::now(),
        ];

        // استبدال المتغيرات بالقيم الفعلية
        $processedContent = $this->replaceVariables($template->content, $data);

        return view('general_setting.settings_template.show', compact('template', 'processedContent', 'qrCode'));
    }


    /*
    |--------------------------------------------------------------------------
    | store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'show_header' => 'sometimes|boolean',
                'show_footer' => 'sometimes|boolean',
                'show_qr_code' => 'sometimes|boolean',
                'show_seal' => 'sometimes|boolean',
                // 'display_orientation' => 'required|in:أفقي,رأسي',
                'content' => 'required|string',
            ],
            [
                'name' => [
                    'required' => 'اسم النموذج مطلوب.',
                    'string' => 'يجب أن يكون الاسم نصاً.',
                    'max' => 'يجب ألا يزيد الاسم عن 255 حرفاً.',
                ],
                'show_header' => [
                    'boolean' => 'يجب أن تكون قيمة إظهار الترويسة إما صحيح أو خطأ.',
                ],
                'show_footer' => [
                    'boolean' => 'يجب أن تكون قيمة إظهار التذييل إما صحيح أو خطأ.',
                ],
                'show_qr_code' => [
                    'boolean' => 'يجب أن تكون قيمة إظهار رمز الاستجابة السريعة إما صحيح أو خطأ.',
                ],
                'show_seal' => [
                    'boolean' => 'يجب أن تكون قيمة إظهار الختم إما صحيح أو خطأ.',
                ],
                // 'display_orientation' => [
                //     'required' => 'الاتجاه مطلوب.',
                //     'in' => 'يجب أن يكون الاتجاه إما أفقي أو رأسي.',
                // ],
                'content' => [
                    'required' => 'محتوى النموذج مطلوب.',
                    'string' => 'يجب أن يكون المحتوى نصاً.',
                ],
            ],
        );

        // Assign validated fields to a variable
        $validated = $request->only(['name', 'display_orientation', 'content']);

        // Handle boolean fields explicitly
        $validated['show_header'] = $request->has('show_header') ? $request->boolean('show_header') : false;
        $validated['show_footer'] = $request->has('show_footer') ? $request->boolean('show_footer') : false;
        $validated['show_qr_code'] = $request->has('show_qr_code') ? $request->boolean('show_qr_code') : false;
        $validated['show_seal'] = $request->has('show_seal') ? $request->boolean('show_seal') : false;

        // Add user ID
        $validated['user_id'] = auth()->id();

        // Create the SettingsTemplate record
        SettingsTemplate::create($validated);

        // Redirect back with success message
        return redirect()->route('settings-templates.index')->with('success', 'تمت الإضافة بنجاح.');
    }


    /*
    |--------------------------------------------------------------------------
    | edit
    |--------------------------------------------------------------------------
    */
    public function edit(string $id)
    {
        $template = SettingsTemplate::find($id);


        return view('general_setting.settings_template.edit', compact('template'));
    }


    /*
    |--------------------------------------------------------------------------
    | update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // Find the existing SettingsTemplate by its ID
        $template = SettingsTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'show_header' => 'sometimes|boolean',
            'show_footer' => 'sometimes|boolean',
            'show_qr_code' => 'sometimes|boolean',
            'show_seal' => 'sometimes|boolean',
            // 'display_orientation' => 'required|in:أفقي,رأسي',
            'content' => 'required|string',
        ], [
            'name' => [
                'required' => 'اسم النموذج مطلوب.',
                'string' => 'يجب أن يكون الاسم نصاً.',
                'max' => 'يجب ألا يزيد الاسم عن 255 حرفاً.',
            ],
            'show_header' => [
                'boolean' => 'يجب أن تكون قيمة إظهار الترويسة إما صحيح أو خطأ.',
            ],
            'show_footer' => [
                'boolean' => 'يجب أن تكون قيمة إظهار التذييل إما صحيح أو خطأ.',
            ],
            'show_qr_code' => [
                'boolean' => 'يجب أن تكون قيمة إظهار رمز الاستجابة السريعة إما صحيح أو خطأ.',
            ],
            'show_seal' => [
                'boolean' => 'يجب أن تكون قيمة إظهار الختم إما صحيح أو خطأ.',
            ],
            // 'display_orientation' => [
            //     'required' => 'الاتجاه مطلوب.',
            //     'in' => 'يجب أن يكون الاتجاه إما أفقي أو رأسي.',
            // ],
            'content' => [
                'required' => 'محتوى النموذج مطلوب.',
                'string' => 'يجب أن يكون المحتوى نصاً.',
            ],
        ],);

        // Assign validated fields to a variable
        $validated = $request->only(['name', 'display_orientation', 'content']);

        // Handle boolean fields explicitly
        $validated['show_header'] = $request->has('show_header') ? $request->boolean('show_header') : false;
        $validated['show_footer'] = $request->has('show_footer') ? $request->boolean('show_footer') : false;
        $validated['show_qr_code'] = $request->has('show_qr_code') ? $request->boolean('show_qr_code') : false;
        $validated['show_seal'] = $request->has('show_seal') ? $request->boolean('show_seal') : false;

        // Add user ID
        $validated['user_id'] = auth()->id();

        // Update the SettingsTemplate record
        $template->update($validated);

        // Redirect back with success message
        return redirect()->route('settings-templates.index')->with('success', 'تم التحديث بنجاح.');
    }


    /*
    |--------------------------------------------------------------------------
    | destroy
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        return $this->destroy_trait($this->model, $id);
    }


    /*
    |--------------------------------------------------------------------------
    | replace the Variables
    |--------------------------------------------------------------------------
    */
    protected function replaceVariables($content, $data)
    {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) use ($data) {
            $variableName = $matches[1];

            switch ($variableName) {
                case 'employee_name':
                    return $data['user']->name;
                case 'identity_number':
                    return $data['user']->id_number;
                case 'job_title':
                    return $data['user']->job_title;
                case 'job_number':
                    return $data['user']->national_number;
                case 'license_title':
                    return $data['user']->license_type;
                case 'current_date':
                    return $data['date']->format('Y-m-d');
                    // أضف المزيد من المتغيرات حسب الحاجة
                default:
                    return $matches[0]; // إرجاع المتغير كما هو إذا لم يتم العثور على تطابق
            }
        }, $content);
    }



    /*
    |--------------------------------------------------------------------------
    | edit status
    |--------------------------------------------------------------------------
    */
    public function editStatus($id)
    {
        return $this->toggleStatus_trait($this->model, $id);
    }


    /*
    |--------------------------------------------------------------------------
    | get variable
    |--------------------------------------------------------------------------
    */
    public function getVariablesByType(Request $request)
    {
        $type = $request->query('type');

        // تعريف المتغيرات حسب النوع
        $allVariables = TemplateVariables::where('type', $type)->orWhere('type', 'all')->get();

        // إعادة المتغيرات بصيغة JSON
        return response()->json($allVariables);
    }




    /*
    |--------------------------------------------------------------------------
    | حذف المحدد
    |--------------------------------------------------------------------------
    */
    public function massDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) === 0) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد أي بيانات.']);
        }

        try {
            SettingsTemplate::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'تم حذف المحدد بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف البيانات.']);
        }
    }
}
