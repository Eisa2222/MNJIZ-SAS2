<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsLawsuitsType;
use App\Models\general_setting\SettingsSubcategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SettingsLawsuitsTypeController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:أنواع الدعاوى')->only([
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
        // جلب جميع التصنيفات الرئيسية والفرعية
        $mainCategories = SettingsCategories::all(['id', 'name']);
        $subcategories = SettingsSubcategories::all();

        $data = SettingsLawsuitsType::select([
            'id',
            'subcategory_id',
            'name',
            'status',
            'user_id',
            'position',
            'created_at',
        ])->orderBy('position', 'asc');


        if ($request->ajax()) {
            if ($request->has('status') && $request->status != '') {
                $data->where('status', $request->status);
            }

            if ($request->has('category') && $request->category != '') {
                $data->whereHas('subcategory', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }


            if ($request->has('subcategory') && $request->subcategory != '') {
                $data->where('subcategory_id', $request->subcategory);
            }



            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('subcategory', function ($row) {
                    return $row->subcategory ? $row->subcategory->name : 'غير متوفر';
                })

                ->addColumn('main_category', function ($row) {
                    return $row->subcategory ? $row->subcategory->category->name : 'غير متوفر';
                })

                ->editColumn('created_at', function ($row) {
                    return $row->hijri_created_at ? $row->hijri_created_at : '';
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
                    $btn .= '<button type="button" data-id="' . $row->id . '" data-name="' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '" data-subcategory_id="' . $row->subcategory_id . '" class="btn btn-edit btn-sm"><i class="ti ti-edit"></i></button>';
                    // }
                    // زر الحذف إذا كان لديه صلاحية "حذف اعداد"
                    // if (auth()->user()->can('حذف اعداد')) {
                    $btn .= ' <button type="button" data-id="' . $row->id . '" class="btn btn-delete btn-sm"><i class="ti ti-trash"></i></button>';
                    // }
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // تمرير التصنيفات الرئيسية والفرعية إلى الـ View
        return view('general_setting.SettingsLawsuitsTypes', compact('subcategories', 'mainCategories'));
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
            'main_category_id' => 'required|exists:settings_categories,id',
            'subcategory_id' => 'required|exists:settings_subcategories,id',
            'lawsuit_names' => 'required|array|min:1',
            'lawsuit_names.*' => 'required|string|max:255|distinct|unique:settings_lawsuits_types,name,NULL,id,subcategory_id,' . $request->subcategory_id,
        ], [
            'main_category_id.required' => 'التصنيف الرئيسي مطلوب.',
            'main_category_id.exists' => 'التصنيف الرئيسي غير موجود.',
            'subcategory_id.required' => 'التصنيف الفرعي مطلوب.',
            'subcategory_id.exists' => 'التصنيف الفرعي غير موجود.',
            'lawsuit_names.required' => 'يجب إدخال على الأقل نوع دعوى واحد.',
            'lawsuit_names.array' => 'بيانات أنواع الدعاوى غير صحيحة.',
            'lawsuit_names.min' => 'يجب إدخال على الأقل نوع دعوى واحد.',
            'lawsuit_names.*.required' => 'اسم نوع الدعوى مطلوب.',
            'lawsuit_names.*.string' => 'اسم نوع الدعوى يجب أن يكون نصًا.',
            'lawsuit_names.*.max' => 'اسم نوع الدعوى لا يجب أن يتجاوز 255 حرفًا.',
            'lawsuit_names.*.distinct' => 'أنواع الدعاوى يجب أن تكون فريدة.',
            'lawsuit_names.*.unique' => 'بعض أنواع الدعاوى موجودة بالفعل في هذا التصنيف الفرعي.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $mainCategoryId = $request->input('main_category_id');
            $subcategoryId = $request->input('subcategory_id');
            $lawsuitNames = $request->input('lawsuit_names');
            $userId = Auth::id();

            // التأكد من أن التصنيف الفرعي مرتبط بالتصنيف الرئيسي
            $subcategory = SettingsSubcategories::where('id', $subcategoryId)
                ->where('category_id', $mainCategoryId)
                ->first();

            if (!$subcategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'التصنيف الفرعي لا يتبع التصنيف الرئيسي المحدد.',
                ], 422);
            }

            // استخدام المعاملات لضمان التكامل
            DB::beginTransaction();

            $createdLawsuits = [];

            foreach ($lawsuitNames as $name) {
                $lawsuit = SettingsLawsuitsType::create([
                    'subcategory_id' => $subcategoryId,
                    'name' => $name,
                    'status' => 'active', // الحالة الافتراضية تكون نشطة
                    'user_id' => $userId,
                ]);
                $createdLawsuits[] = $lawsuit;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة أنواع الدعاوى بنجاح.',
                'data' => $createdLawsuits,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة أنواع الدعاوى.',
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
            $type = SettingsLawsuitsType::with(['subcategory.category'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $type,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات نوع الدعوى.',
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
            'subcategory_id' => 'required|exists:settings_subcategories,id',
            'name' => 'required|string|max:255|unique:settings_lawsuits_types,name,' . $id . ',id,subcategory_id,' . $request->subcategory_id,
        ], [
            'subcategory_id.required' => 'التصنيف الفرعي مطلوب.',
            'subcategory_id.exists' => 'التصنيف الفرعي غير موجود.',
            'name.required' => 'اسم نوع الدعوى مطلوب.',
            'name.string' => 'اسم نوع الدعوى يجب أن يكون نصًا.',
            'name.max' => 'اسم نوع الدعوى لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم نوع الدعوى موجود بالفعل في هذا التصنيف الفرعي.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'هناك أخطاء في البيانات المدخلة.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $type = SettingsLawsuitsType::findOrFail($id);
            $subcategoryId = $request->input('subcategory_id');

            // التأكد من أن التصنيف الفرعي مرتبط بنفس التصنيف الرئيسي الحالي لنوع الدعوى
            if ($type->subcategory_id != $subcategoryId) {
                $currentMainCategoryId = $type->subcategory->category_id;
                $newSubcategory = SettingsSubcategories::find($subcategoryId);

                if (!$newSubcategory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'التصنيف الفرعي غير موجود.',
                    ], 422);
                }

                $newMainCategoryId = $newSubcategory->category_id;

                if ($currentMainCategoryId != $newMainCategoryId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن تغيير التصنيف الفرعي إلى تصنيف فرعي يتبع تصنيف رئيسي مختلف.',
                    ], 422);
                }
            }

            $type->update([
                'subcategory_id' => $subcategoryId,
                'name' => $request->name,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['success' => 'تم تحديث نوع الدعوى بنجاح.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث نوع الدعوى.',
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
            $type = SettingsLawsuitsType::findOrFail($id);
            $type->delete();

            return response()->json(['success' => 'تم حذف نوع الدعوى بنجاح.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف نوع الدعوى.',
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
            $type = SettingsLawsuitsType::findOrFail($id);

            // تغيير الحالة
            $type->status = $type->status === 'active' ? 'inactive' : 'active';
            $type->save();

            return response()->json([
                'success' => true,
                'status' => $type->status,
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
            SettingsLawsuitsType::whereIn('id', $ids)->delete();
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
                $category = SettingsLawsuitsType::find($item['id']);
                if ($category) {
                    $category->position = $item['position'];
                    $category->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'تم حفظ الترتيب بنجاح.']);
        } catch (\Exception $e) {
            Log::error('Error reordering categories: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حفظ الترتيب.'], 500);
        }
    }
}
