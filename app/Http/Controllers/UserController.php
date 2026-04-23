<?php

namespace App\Http\Controllers;

use App\Models\Fingerprint;
use App\Models\general_setting\SettingsCountry;
use App\Models\User;
use App\Services\BioStationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;


class UserController extends Controller
{

    public function captureFingerprint(Request $request, User $user, BioStationService $bioStationService)
    {
        // تحقق من إعدادات BioStation
        if (!$bioStationService->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'إعدادات BioStation غير مُكوّنة.']);
        }

        try {
            // تفاعل مع BioStation لالتقاط البصمة
            $captureResult = $bioStationService->captureFingerprint();

            if ($captureResult['success']) {
                // الحصول على finger_id و template من BioStation
                $fingerId = $captureResult['finger_id'];
                $template = $captureResult['template'];

                // إنشاء البصمة في قاعدة البيانات
                $fingerprint = Fingerprint::create([
                    'user_id'   => $user->id,
                    'finger_id' => $fingerId,
                    'template'  => $template,
                ]);

                return response()->json(['success' => true, 'message' => 'تم التقاط البصمة بنجاح.']);
            } else {
                return response()->json(['success' => false, 'message' => $captureResult['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Error capturing fingerprint: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التقاط البصمة.']);
        }
    }


    public function editFingerprints(Fingerprint $fingerprint)
    {
        return response()->json([
            'success' => true,
            'data' => $fingerprint
        ]);
    }

    public function updateFingerprints(Request $request, Fingerprint $fingerprint, BioStationService $bioStationService)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'finger_id' => 'required|string|unique:fingerprints,finger_id,' . $fingerprint->id,
            'template'  => 'required|string',
        ]);

        try {
            // تحديث البيانات في قاعدة البيانات
            $fingerprint->update([
                'finger_id' => $request->finger_id,
                'template'  => $request->template,
            ]);

            // تحديث البصمة في جهاز BioStation
            if ($bioStationService->updateFingerprintOnDevice($fingerprint->finger_id, $fingerprint->template)) {
                return response()->json(['success' => true, 'message' => 'تم تعديل البصمة بنجاح في النظام والجهاز.']);
            } else {
                return response()->json(['success' => false, 'message' => 'تم تعديل البصمة في النظام ولكن فشل في تعديلها في الجهاز.']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating fingerprint: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تعديل البصمة.']);
        }
    }

    /**
     * حذف بصمة
     */
    public function destroyFingerprints(Fingerprint $fingerprint, BioStationService $bioStationService)
    {
        DB::beginTransaction();

        try {
            // حذف البصمة من جهاز BioStation
            if ($bioStationService->removeFingerprintFromDevice($fingerprint->finger_id)) {
                // حذف البصمة من قاعدة البيانات
                $fingerprint->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'تم حذف البصمة بنجاح من النظام والجهاز.']);
            } else {
                // فشل في حذف البصمة من الجهاز
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'فشل حذف البصمة من الجهاز.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting fingerprint: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف البصمة.']);
        }
    }
}
