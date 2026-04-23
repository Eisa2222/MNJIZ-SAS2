<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MassDeleteController extends Controller
{
    public function massDelete(Request $request)
    {
        $model = $request->input('model'); // اسم الموديل من الجسون
        $ids   = $request->input('ids');   // مصفوفة الـ IDs

        if (!$model || !is_array($ids) || empty($ids)) {

            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة.',
            ], 400);
        }

        try {
            // تحويل الاسم إلى كلاس (config/models.php)
            $modelClass = config("models.$model");
            if (!class_exists($modelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموديل غير موجود.',
                ], 400);
            }

            // حاول الحذف واحتفظ بعدد المحذوفات
            $deletedCount = $modelClass::whereIn('id', $ids)->delete();

            if ($deletedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على سجلات للحذف.',
                ], 404);
            }


            return response()->json([
                'success' => true,
                'message' => "تم حذف {$deletedCount} سجلّ بنجاح.",
            ]);
        } catch (\Exception $e) {
            Log::error('Mass delete error: ' . $e->getMessage(), [
                'ids'   => $ids,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف.',
            ], 500);
        }
    }
}
