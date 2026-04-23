<?php

namespace App\Http\Middleware;

use App\Models\Hr\CompanyPolicy\CompanyPolicy;
use App\Models\PolicyAgreement\UserPolicyAgreement;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolicyAgreementMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user || !$user->employee) {
            return $next($request);
        }

        if ($this->hasUnAgreedPolicies($user->employee->id)) {
            return redirect()->route('policies.agreement')
                ->with('warning', 'يجب الموافقة على السياسات للمتابعة');
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        $excludedRoutes = [
            'login',
            'logout',
            'policies.agreement',
            'policies.agree',
            'policies.agree-all',
            'policies.view',
        ];

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $excludedRoutes)) {
            return true;
        }

        return false;
    }

    private function hasUnAgreedPolicies(int $employeeId): bool
    {
        $allPolicyIds = CompanyPolicy::pluck('id')->toArray();

        if (empty($allPolicyIds)) {
            return false;
        }

        $agreedPolicyIds = UserPolicyAgreement::where('employee_id', $employeeId)
            ->pluck('company_policy_id')
            ->toArray();

        $unagreedPolicyIds = array_diff($allPolicyIds, $agreedPolicyIds);

        return count($unagreedPolicyIds) > 0;
    }
}
