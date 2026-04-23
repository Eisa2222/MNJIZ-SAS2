<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsSubcategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SettingsSubcategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:التصنيفات الفرعية')->only([
            'index',
            'store',
            'show',
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
        $data = SettingsSubcategories::select(['id', 'category_id', 'name', 'user_id', 'status', 'position', 'created_at'])->orderBy('position', 'asc');

        if ($request->ajax()) {
            // $data = SettingsSubcategories::with('category')->latest()->get();

            if ($request->has('status') && $request->status != '') {
                $data->where('status', $request->status);
            }

            if ($request->has('category') && $request->category != '') {
                $data->where('category_id', $request->category);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                // order

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category ? $row->category->name : 'غير محدد';
                })

                ->orderColumn('category_name', function ($query, $order) {
                    $query->orderBy('category_id', $order);
                })

                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('status', $order);
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

        // جلب التصنيفات الرئيسية لعرضها في النماذج
        $mainCategories = SettingsCategories::all(['id', 'name']);

        return view('general_setting.settings_subcategory', ['mainCategories' => $mainCategories]);
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
            'category_id' => 'required|exists:settings_categories,id',
            'names' => 'required|array|min:1',
            'names.*' => 'required|string|max:255|distinct|unique:settings_subcategories,name,NULL,id,category_id,' . $request->category_id,
        ], [
            'category_id.required' => 'التصنيف الرئيسي مطلوب.',
            'category_id.exists' => 'التصنيف الرئيسي غير موجود.',
            'names.required' => 'يجب إدخال على الأقل تصنيف فرعي واحد.',
            'names.array' => 'بيانات التصنيفات الفرعية غير صحيحة.',
            'names.min' => 'يجب إدخال على الأقل تصنيف فرعي واحد.',
            'names.*.required' => 'اسم التصنيف الفرعي مطلوب.',
            'names.*.string' => 'اسم التصنيف الفرعي يجب أن يكون نصًا.',
            'names.*.max' => 'اسم التصنيف الفرعي لا يجب أن يتجاوز 255 حرفًا.',
            'names.*.distinct' => 'التصنيفات الفرعية يجب أن تكون فريدة.',
            'names.*.unique' => 'بعض التصنيفات الفرعية موجودة بالفعل في هذا التصنيف الرئيسي.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category_id = $request->input('category_id');
            $names = $request->input('names');
            $userId = Auth::id();

            // استخدام المعاملات لضمان التكامل
            DB::beginTransaction();

            $createdSubcategories = [];

            foreach ($names as $name) {
                $subcategory = SettingsSubcategories::create([
                    'name' => $name,
                    'category_id' => $category_id,
                    'user_id' => $userId,
                    'status' => 'active', // القيمة الافتراضية
                ]);
                $createdSubcategories[] = $subcategory;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة التصنيفات الفرعية بنجاح.',
                'data' => $createdSubcategories,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة التصنيفات الفرعية.',
                'error' => $e->getMessage(),
            ], 500);
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
            $subcategory = SettingsSubcategories::with('category')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $subcategory,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات التصنيف الفرعي.',
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
            $subcategory = SettingsSubcategories::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $subcategory,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات التصنيف الفرعي.',
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
            'name' => 'required|string|max:255|unique:settings_subcategories,name,' . $id . ',id,category_id,' . $request->category_id,
            'category_id' => 'required|exists:settings_categories,id',
        ], [
            'name.required' => 'اسم التصنيف الفرعي مطلوب.',
            'name.string' => 'اسم التصنيف الفرعي يجب أن يكون نصًا.',
            'name.max' => 'اسم التصنيف الفرعي لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم التصنيف الفرعي موجود بالفعل في هذا التصنيف الرئيسي.',
            'category_id.required' => 'التصنيف الرئيسي مطلوب.',
            'category_id.exists' => 'التصنيف الرئيسي غير موجود.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subcategory = SettingsSubcategories::findOrFail($id);

            $subcategory->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                // يمكنك إضافة تحديثات أخرى إذا لزم الأمر
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التصنيفات الفرعية بنجاح.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث التصنيفات الفرعية.',
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
            $subcategory = SettingsSubcategories::findOrFail($id);
            $subcategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التصنيف الفرعي بنجاح.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التصنيف الفرعي.',
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
            $subcategory = SettingsSubcategories::findOrFail($id);

            $subcategory->status = $subcategory->status === 'active' ? 'inactive' : 'active';
            $subcategory->save();

            return response()->json([
                'success' => true,
                'status' => $subcategory->status,
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
    | getSubcategoriesByCategory
    |--------------------------------------------------------------------------
    */
    public function getSubcategoriesByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $subcategories = SettingsSubcategories::where('category_id', $categoryId)->get(['id', 'name']);

        return response()->json($subcategories);
    }


    /*
    |--------------------------------------------------------------------------
    | massDelete
    |--------------------------------------------------------------------------
    */
    public function massDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || count($ids) === 0) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد أي بيانات.']);
        }

        try {
            SettingsSubcategories::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'تم حذف المحدد بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف البيانات.']);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | reorder
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
                $category = SettingsSubcategories::find($item['id']);
                if ($category) {
                    $category->position = $item['position'];
                    $category->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'تم حفظ الترتيب بنجاح.']);
        } catch (\Exception $e) {
            //  Log::error('Error reordering categories: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حفظ الترتيب.'], 500);
        }
    }
}
