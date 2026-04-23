<?php

namespace App\Http\Controllers\PolicyAgreement;

use App\Http\Controllers\Controller;
use App\Models\Hr\CompanyPolicy\CompanyPolicy;
use App\Models\PolicyAgreement\UserPolicyAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolicyAgreementController extends Controller
{
    public function index()
    {
        $user       = Auth::user();
        $employeeId = $user->employee->id;

        $policies   = $this->getPendingPolicies($employeeId);

        if ($policies->isEmpty()) {
            return redirect()->route('dashboard');
        }

        return view('policies.index', compact('policies'));
    }

    public function agree(Request $request)
    {
        try {
            $user       = Auth::user();
            $employeeId = $user->employee->id;

            $policies = $this->getPendingPolicies($employeeId);

            if ($policies->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد سياسات معلقة للموافقة عليها'
                ]);
            }

            // حفظ الموافقات
            DB::transaction(function () use ($employeeId, $policies, $request) {
                $agreements = [];
                $now = now();

                foreach ($policies as $policy) {
                    $agreements[] = [
                        'employee_id' => $employeeId,
                        'company_policy_id' => $policy->id,
                        'agreed_at' => $now,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }

                UserPolicyAgreement::insert($agreements);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم الموافقة على جميع السياسات بنجاح',
                'redirect' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الموافقات'
            ], 500);
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                            Private Methods
    |============================================================================
    |============================================================================
    */
    private function getPendingPolicies(int $employeeId)
    {
        $allPolicyIds = CompanyPolicy::pluck('id')->toArray();

        if (empty($allPolicyIds)) {
            return collect();
        }

        $agreedPolicyIds = UserPolicyAgreement::where('employee_id', $employeeId)
            ->pluck('company_policy_id')
            ->toArray();

        $unagreedPolicyIds = array_diff($allPolicyIds, $agreedPolicyIds);

        return CompanyPolicy::whereIn('id', $unagreedPolicyIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
