<?php

namespace App\Http\Controllers\SystemAdministration\Roles;

use App\DataTables\SystemAdministration\Roles\RolesDataTable;
use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:الصلاحيات الوظيفية')->only(['index']);
        $this->middleware('can:إضافة دور')->only(['create', 'store']);
        $this->middleware('can:تعديل دور')->only(['edit', 'update']);
        $this->middleware('can:حذف دور')->only(['destroy']);
        $this->middleware('can:تغيير حالة الموظف')->only(['updateStatus']);
        $this->middleware('can:منح الادوار')->only(['updateEmployeeRoles']);
        $this->middleware('can:إضافة / نزع صلاحية للدور')->only(['getUserPermissions', 'updateUserPermissions']);
    }


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(RolesDataTable $dataTable)
    {
        try {
            $roles = Role::all();
            // جلب جميع الصلاحيات المتاحة وتجميعها حسب القسم
            $permissions = Permission::all()->groupBy('section');

            $hr_statuses = SettingsHrStatus::select('id', 'name')->get();

            return $dataTable->render('system_administration.roles.index', compact(
                // Filters
                'roles',
                'permissions',
                'hr_statuses'
            ));
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
        // تجميع الصلاحيات حسب القسم
        $permissions = Permission::all()->groupBy('section');

        // الأقسام الرئيسية في النظام
        $sections = [
            'التقارير',
            'إعدادات النظام',
            'إدارة النظام',
            'مركز العمليات',
            'مكتب إدارة المشاريع',
            'الشؤون القانونية',
            'الموارد البشرية',
            'مركز تنظيم الأعمال',
            'التفاعل الجماعي',
            'إدارة الملفات',

        ];

        // التأكد من أن جميع الأقسام موجودة في مجموعة الصلاحيات
        foreach ($sections as $section) {
            if (!$permissions->has($section)) {
                $permissions->put($section, collect());
            }
        }

        return view('system_administration.roles.create', compact('permissions', 'sections'));
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
            'name' => 'required|unique:roles,name',
            'permissions' => 'required_if:select_all,0|array',
            'select_all' => 'sometimes|boolean',
        ], [
            'name.required' => 'حقل الاسم مطلوب.',
            'name.unique' => 'اسم الدور يجب أن يكون فريدًا.',
            // 'permissions.required_if' => 'حقل الأذونات مطلوب عندما لا يتم تحديد "تحديد الكل".',
            // 'permissions.array' => 'الأذونات يجب أن تكون على شكل مصفوفة.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // بدء معاملة قاعدة البيانات للتأكد من سلامة البيانات
        DB::beginTransaction();

        try {
            // إنشاء الدور
            $role = Role::create(['name' => $request->name]);

            if ($request->input('select_all')) {
                // تعيين جميع الصلاحيات
                $permissions = Permission::pluck('name')->toArray();
            } else {
                // تعيين الصلاحيات المحددة
                $permissions = $request->input('permissions', []);
            }

            // تعيين الصلاحيات إلى الدور
            $role->syncPermissions($permissions);

            // إتمام المعاملة
            DB::commit();

            return redirect()->route('roles.index')->with('success', 'تم إضافة الدور بنجاح');
        } catch (\Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            DB::rollBack();
            // تسجيل الخطأ في السجل
            // \Log::error('Error creating role: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الدور.'])->withInput();
        }
    }



    /*
    |--------------------------------------------------------------------------
    | edit
    |--------------------------------------------------------------------------
    */
    public function edit(Role $role)
    {
        // تجميع الصلاحيات حسب القسم بدلاً من الفئة
        $permissions = Permission::all()->groupBy('section');

        // قائمة الأقسام الرئيسية في النظام (تأكد من تطابقها مع Seeder)
        $sections = [
            'التقارير',
            'إعدادات النظام',
            'إدارة النظام',
            'مركز العمليات',
            'مكتب إدارة المشاريع',
            'الشؤون القانونية',
            'الموارد البشرية',
            'مركز تنظيم الأعمال',
            'التفاعل الجماعي',
            'إدارة الملفات',
        ];

        // التأكد من أن جميع الأقسام موجودة في مجموعة الصلاحيات
        foreach ($sections as $section) {
            if (!$permissions->has($section)) {
                $permissions->put($section, collect());
            }
        }

        return view('system_administration.roles.edit', compact('role', 'permissions', 'sections'));
    }



    /*
    |--------------------------------------------------------------------------
    | update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Role $role)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required_if:select_all,0|array',
            'select_all' => 'sometimes|boolean',
        ], [
            'name.required' => 'حقل الاسم مطلوب.',
            'name.unique' => 'اسم الدور يجب أن يكون فريدًا.',
            'permissions.required_if' => 'حقل الأذونات مطلوب عندما لا يتم تحديد "تحديد الكل".',
            'permissions.array' => 'الأذونات يجب أن تكون على شكل مصفوفة.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // بدء معاملة قاعدة البيانات للتأكد من سلامة البيانات
        DB::beginTransaction();

        try {
            // تحديث اسم الدور
            $role->update(['name' => $request->name]);

            if ($request->input('select_all')) {
                // تعيين جميع الصلاحيات
                $permissions = Permission::pluck('name')->toArray();
            } else {
                // تعيين الصلاحيات المحددة
                $permissions = $request->input('permissions', []);
            }

            // تعيين الصلاحيات إلى الدور
            $role->syncPermissions($permissions);

            // إتمام المعاملة
            DB::commit();

            return redirect()->route('roles.index')->with('success', 'تم تحديث الدور بنجاح');
        } catch (\Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            DB::rollBack();
            // تسجيل الخطأ في السجل
            // \Log::error('Error updating role: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء تحديث الدور.'])->withInput();
        }
    }





    /*
    |--------------------------------------------------------------------------
    | destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(Role $role)
    {
        if ($role->id == 1) {
            return redirect()->route('roles.index')->with('error', 'عفوا لا يمكن حذف هذا الدور ');
        }
        try {
            $role->delete();
            return redirect()->route('roles.index')->with('success', 'تم حذف الدور بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')->with('error', 'حدث خطأ أثناء حذف الدور.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | getEmployeeRoles
    |--------------------------------------------------------------------------
    */
    public function getEmployeeRoles($employeeId)
    {
        try {
            $employee = Employees::with('user.roles')->findOrFail($employeeId);

            if (!$employee->user) {
                return response()->json(['error' => 'المستخدم غير موجود.'], 404);
            }

            $assignedRole = $employee->user->roles->first(); // افتراض دور واحد فقط
            $assignedStatus = $employee->user->status; // 'active' أو 'inactive'

            $allRoles = Role::where('guard_name', 'web')->get();

            return response()->json([
                'roles' => $allRoles,
                'assigned_role' => $assignedRole ? $assignedRole->id : null,
                'assigned_status' => $assignedStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getEmployeeRoles: ' . $e->getMessage());
            return response()->json(['error' => 'فشل في جلب الأدوار .'], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | updateEmployeeRoles
    |--------------------------------------------------------------------------
    */
    public function updateEmployeeRoles(Request $request, $employeeId)
    {
        $request->validate([
            'role' => 'required|exists:roles,id',
        ]);

        DB::beginTransaction();

        try {
            // جلب الموظف والمستخدم المرتبط به
            $employee = Employees::with('user')->findOrFail($employeeId);
            $user = $employee->user;

            if (!$user) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.']);
            }

            // جلب الدور الجديد
            $role = Role::findOrFail($request->role);

            // تحديث الأدوار
            $user->syncRoles([$role]);

            // إزالة جميع الصلاحيات الإضافية والمنزوعة
            $user->additionalPermissions()->detach();
            $user->deniedPermissions()->detach();

            $user->save();

            // تحديث المسمى الوظيفي في جدول الموظفين بناءً على الدور
            $employee->job_title = $role->name;
            $employee->save();

            // تفريغ الكاش الخاص بالصلاحيات
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'تم تحديث الدور  بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'فشل في تحديث الدور .']);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | getUserPermissions
    |--------------------------------------------------------------------------
    */
    public function getUserPermissions($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // جلب الصلاحيات الممنوحة من الدور
            $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();

            // جلب الصلاحيات الفردية المضافة
            $directPermissions = $user->additionalPermissions()->pluck('name')->toArray();

            // جلب الصلاحيات المنزوعة
            $deniedPermissions = $user->deniedPermissions()->pluck('name')->toArray();

            return response()->json([
                'role_permissions' => $rolePermissions,
                'direct_permissions' => $directPermissions,
                'denied_permissions' => $deniedPermissions,
            ]);
        } catch (\Exception $e) {

            return response()->json(['error' => 'فشل في جلب الصلاحيات.'], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | updateUserPermissions
    |--------------------------------------------------------------------------
    */
    public function updateUserPermissions(Request $request, $userId)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
            'denied_permissions' => 'array',
            'denied_permissions.*' => 'string|exists:permissions,name',
        ]);

        try {
            $user = User::findOrFail($userId);

            // تحديث الصلاحيات الإضافية
            $additionalPermissionIds = Permission::whereIn('name', $request->permissions ?? [])->pluck('id')->toArray();
            $user->additionalPermissions()->sync($additionalPermissionIds);

            // تحديث الصلاحيات المنزوعة
            $deniedPermissionIds = Permission::whereIn('name', $request->denied_permissions ?? [])->pluck('id')->toArray();
            $user->deniedPermissions()->sync($deniedPermissionIds);

            // تفريغ الكاش الخاص بالصلاحيات
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['success' => true, 'message' => 'تم تحديث صلاحيات المستخدم بنجاح.']);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => 'فشل في تحديث صلاحيات المستخدم.'], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | updateStatus
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, $employeeId)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $employee = Employees::with('user')->findOrFail($employeeId);
            $user = $employee->user;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.']);
            }

            $user->status = $request->status;
            $user->save();

            // تحديث الكاش الخاص بالصلاحيات
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['success' => true, 'message' => 'تم تحديث الحالة بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'فشل في تحديث الحالة.'], 500);
        }
    }
}
