<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TourController extends Controller
{
    /**
     * تحقق من حالة الجولة.
     */
    public function checkTourStatus(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم الحالي

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['tourCompleted' => $user->tour_completed]);
    }

    /**
     * تحديث حالة الجولة.
     */
    public function markTourComplete(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم الحالي

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->tour_completed = true; // تحديث الحقل
        $user->save(); // حفظ التحديث

        return response()->json(['success' => true]);
    }


     /**
     * تحقق من حالة الجولة الخاصة بالمهام.
     */
    public function checkTourStatusTasks(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم الحالي

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['tourCompleted' => $user->tour_task_completed]);
    }

    /**
     * تحديث حالة الجولة الخاصة بالمهام.
     */
    public function markTourCompleteTasks(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم الحالي

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->tour_task_completed = true; // تحديث الحقل
        $user->save(); // حفظ التحديث

        return response()->json(['success' => true]);
    }
    
}
