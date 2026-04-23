<?php

namespace App\Http\Controllers\Account_employee;

use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\Hr\Employee\QualificationDegree;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsBanks;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;


class AccountEmployeeComtroller extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | show function
    |--------------------------------------------------------------------------
    */
    public function profile($id)
    {
        $employee = Employees::findOrFail($id);

        $activities = Activity::where('causer_id', $employee->user_id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $customer_relations = $employee->customersRelationshipManager()->paginate(24, ['*'], 'customer_relations_page');

        /*
        |--------------------------------------------------------------------------
        | جلب المشاريع
        |--------------------------------------------------------------------------
        | 1- التي هو مدير المشروع فيها
        | 2- التي هو الذي اضافها
        | 3- التي هو في فريق المشروع فيها
        */
        $projects = Project::where('manager_user_id', $employee->user_id)
            ->orWhere('created_by', $employee->user_id)
            ->orWhereHas('teamMembers', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->with('teamMembers')
            ->distinct()
            ->paginate(12, ['*'], 'projects_page');

        // $userId = auth()->id();

        $userId = $employee->user_id;

        $tasks = Task::whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])->where(function ($query) use ($userId) {
            $query->whereHas('assignedUsers', function ($subQuery) use ($userId) {
                $subQuery->where('users.id', $userId);
            })
                ->orWhereHas('steps', function ($subQuery) use ($userId) {
                    $subQuery
                        ->whereHas('assignedUsers', function ($stepQuery) use ($userId) {
                            $stepQuery->where('users.id', $userId);
                        });
                });
        })->orderBy('created_at', 'desc')->get();

        // dd($tasks);

        // المهام المسندة للمستخدم مباشرة وحالتها قيد التنفيذ
        $directTasks = Task::where('status', 'in_progress')
            ->whereHas('assignedUsers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            });


        // المهام التي تحتوي على خطوات مسندة للمستخدم وحالتها قيد التنفيذ
        $tasksWithAssignedSteps = Task::whereHas('steps', function ($query) use ($userId) {
            $query->where('status', 'in_progress') // الخطوات قيد التنفيذ فقط
                ->whereHas('assignedUsers', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
        });

        $directTaskIds = $directTasks->pluck('id');
        $stepTaskIds = $tasksWithAssignedSteps->pluck('id');
        $allTaskIds = $directTaskIds->merge($stepTaskIds)->unique();

        // $tasks = Task::whereIn('id', $allTaskIds)
        //     ->orderBy('created_at', 'desc')
        //     ->take(5)
        //     ->get();

        // get user with custome

        $canChat = auth()->check() && auth()->id() !== $employee->user_id;

        $custodies =  $employee->custodies()->orderBy('created_at', 'desc')->get();

        // dd($custodies->count());

        // return view
        return view('account_employee.profile', compact('employee', 'activities', 'projects', 'tasks', 'customer_relations', 'canChat', 'custodies'));
    }


    public function publicProfile($id)
    {
        $employee = Employees::findOrFail($id);

        if ($employee->user->status == "inactive")
            abort(404);

        return view('account_employee.public_profile', compact('employee'));
    }


    public function edit($id)
    {
        $employee               = Employees::findOrFail($id);
        $banks                  = SettingsBanks::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $qualificationDegree    = QualificationDegree::options();

        return view('account_employee.edit', compact('employee', 'banks', 'qualificationDegree'));
    }


    /*
    |--------------------------------------------------------------------------
    | upload profile picture function
    |--------------------------------------------------------------------------
    */
    public function updateProfilePicture(Request $request, $id)
    {
        // التحقق من الصلاحيات
        $employee = Employees::findOrFail($id);
        // التحقق من صحة البيانات
        $validatedData = $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ], [
            'profile_picture.required' => 'يرجى تحميل صورة شخصية.',
            'profile_picture.image' => 'يجب أن تكون الصورة ملفًا بصيغة صورة.',
            'profile_picture.mimes' => 'يجب أن تكون الصورة بامتداد jpg, jpeg, png, gif.',
            'profile_picture.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت.',
        ]);

        // معالجة الصورة الجديدة
        if ($request->hasFile('profile_picture')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)) {
                Storage::disk('public')->delete($employee->profile_picture);
            }

            // تخزين الصورة الجديدة
            $employeeFolder = 'employees/' . str_replace(' ', '_', $employee->name);
            $path = $request->file('profile_picture')->store($employeeFolder . '/profile_pictures', 'public');

            // تحديث بيانات الموظف
            $employee->profile_picture = $path;
            $employee->save();
        }

        return redirect()->route('account.employee.profile', $employee->id)->with('success', 'تم تحديث الصورة الشخصية بنجاح.');
    }



    /*
    |--------------------------------------------------------------------------
    | upload background image function
    |--------------------------------------------------------------------------
    */
    public function updateBackgroundImage(Request $request, $id)
    {
        // التحقق من الصلاحيات
        $employee = Employees::findOrFail($id);
        // التحقق من صحة البيانات
        $validatedData = $request->validate([
            // 'background_image' => 'required|image|mimes:jpg,jpeg,png,gif|max:4096',
            'background_image' => 'required|image|mimes:jpg,jpeg,png,gif|dimensions:width=1693,height=376',

        ], [
            'background_image.required' => 'يرجى تحميل صورة خلفية.',
            'background_image.image' => 'يجب أن تكون الصورة ملفًا بصيغة صورة.',
            'background_image.mimes' => 'يجب أن تكون الصورة بامتداد jpg, jpeg, png, gif.',
            'background_image.max' => 'يجب ألا يتجاوز حجم الصورة 4 ميجابايت.',
        ]);

        // معالجة الصورة الجديدة
        if ($request->hasFile('background_image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($employee->background_image && Storage::disk('public')->exists($employee->background_image)) {
                Storage::disk('public')->delete($employee->background_image);
            }
            // تخزين الصورة الجديدة
            $employeeFolder = 'employees/' . str_replace(' ', '_', $employee->name);
            $path = $request->file('background_image')->store($employeeFolder . '/background_images', 'public');

            // تحديث بيانات الموظف
            $employee->background_image = $path;
            $employee->save();
        }

        return redirect()->route('account.employee.profile', $employee->id)->with('success', 'تم تحديث صورة الخلفية بنجاح.');
    }


    /*
    |--------------------------------------------------------------------------
    | change password
    |--------------------------------------------------------------------------
    */
    public function showChangePasswordForm()
    {
        return view('account_employee.edit_password'); // تأكد من أن هذا هو اسم العرض الصحيح لنموذج تغيير كلمة المرور
    }

    /*
    |--------------------------------------------------------------------------
    | update the password
    |--------------------------------------------------------------------------
    */
    public function updatePassword(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'كلمة المرور الجديدة وتأكيدها غير متطابقتين.',
            'new_password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',
        ]);

        $user = Auth::user();

        // التحقق من صحة كلمة المرور الحالية
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        // تحديث كلمة المرور
        $user->password = Hash::make($request->new_password);
        $user->save();

        // تسجيل الخروج من جميع الجلسات الأخرى
        Auth::logoutOtherDevices($request->new_password);

        // تسجيل الخروج من الجلسة الحالية
        Auth::logout();

        // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول مع رسالة نجاح
        return redirect()->route('login')->with('success', 'تم إعادة تعيين كلمة المرور بنجاح. يرجى تسجيل الدخول باستخدام كلمة المرور الجديدة.');
    }
}
