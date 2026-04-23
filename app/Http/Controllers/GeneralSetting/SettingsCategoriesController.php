<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SettingsCategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:التصنيفات الرئيسية')->only([
            'index',
            'store',
            'edit',
            'update',
            'destroy',
            'toggleStatus',
            'massDelete',
            'reorder',
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // $data = SettingsCategories::latest()->get();
            $data = SettingsCategories::select(['id', 'name', 'status', 'created_at'])->orderBy('position', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'active':
                            $badge = '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';
                            break;
                        case 'inactive':
                            $badge = '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">غير نشط</span>';
                            break;
                        default:
                            $badge = '<span class="badge bg-secondary">غير معروف</span>';
                            break;
                    }
                    return $badge;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // زر التعديل إذا كان لديه صلاحية "تعديل اعداد"
                    // if (auth()->user()->can('تعديل اعداد')) {
                    $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-edit btn-sm me-1"><i class="ti ti-edit"></i></button>';
                    // }

                    // زر الحذف إذا كان لديه صلاحية "حذف اعداد"
                    // if (auth()->user()->can('حذف اعداد')) {
                    $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-delete btn-sm"><i class="ti ti-trash"></i></button>';
                    // }

                    return $btn;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->hijri_created_at ? $row->hijri_created_at : '';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('general_setting.settings_category');
    }



    /*
    |--------------------------------------------------------------------------
    | store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'names' => 'required|array|min:1',
            'names.*' => 'required|string|max:255|distinct|unique:settings_categories,name',
        ], [
            'names.required' => 'يجب إدخال على الأقل تصنيف واحد.',
            'names.array' => 'بيانات التصنيفات غير صحيحة.',
            'names.min' => 'يجب إدخال على الأقل تصنيف واحد.',
            'names.*.required' => 'اسم التصنيف الرئيسي مطلوب.',
            'names.*.string' => 'اسم التصنيف الرئيسي يجب أن يكون نصًا.',
            'names.*.max' => 'اسم التصنيف الرئيسي لا يجب أن يتجاوز 255 حرفًا.',
            'names.*.distinct' => 'التصنيفات يجب أن تكون فريدة.',
            'names.*.unique' => 'بعض التصنيفات موجودة بالفعل.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $names = $request->input('names');
            $userId = Auth::id();

            // استخدام المعاملات لضمان التكامل
            DB::beginTransaction();

            $createdCategories = [];

            foreach ($names as $name) {
                $category = SettingsCategories::create([
                    'name' => $name,
                    'user_id' => $userId,
                    'status' => 'active', // القيمة الافتراضية
                ]);
                $createdCategories[] = $category;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة التصنيفات الرئيسية بنجاح.',
                'data' => $createdCategories,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة التصنيفات الرئيسية.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | edit
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        try {
            $category = SettingsCategories::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات التصنيف.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:settings_categories,name,' . $id,
        ], [
            'name.required' => 'اسم التصنيف الرئيسي مطلوب.',
            'name.string' => 'اسم التصنيف الرئيسي يجب أن يكون نصًا.',
            'name.max' => 'اسم التصنيف الرئيسي لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم التصنيف الرئيسي موجود بالفعل.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = SettingsCategories::findOrFail($id);

            // تحديث اسم التصنيف الرئيسي
            $category->update(['name' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التصنيف الرئيسي بنجاح.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث البيانات.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | destroy
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $category = SettingsCategories::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التصنيف بنجاح.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التصنيف.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | toggleStatus
    |--------------------------------------------------------------------------
    */
    public function toggleStatus($id)
    {
        try {
            $category = SettingsCategories::findOrFail($id);

            // تغيير الحالة
            $category->status = $category->status === 'active' ? 'inactive' : 'active';
            $category->save();

            return response()->json([
                'success' => true,
                'status' => $category->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير الحالة.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    /*
    |--------------------------------------------------------------------------
    | delete multiple records
    |--------------------------------------------------------------------------
    */
    public function massDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) === 0) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد أي بيانات.']);
        }

        try {
            SettingsCategories::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'تم حذف التصنيفات المحددة بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف البيانات.']);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | reorder categories
    |--------------------------------------------------------------------------
    */
    public function reorder(Request $request)
    {
        $order = $request->input('order');

        if (!$order || !is_array($order)) {
            return response()->json(['success' => false, 'message' => 'بيانات غير صالحة.'], 422);
        }

        try {
            foreach ($order as $item) {
                $category = SettingsCategories::find($item['id']);
                if ($category) {
                    $category->position = $item['position'];
                    $category->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'تم حفظ الترتيب بنجاح.']);
        } catch (\Exception $e) {
            // Log::error('Error reordering categories: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حفظ الترتيب.'], 500);
        }
    }
}
