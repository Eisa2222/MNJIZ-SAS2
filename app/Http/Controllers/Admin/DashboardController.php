<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        // Super Admin lives outside of tenant scope — use withoutTenancy()
        // for all counts so we see the whole platform.
        $stats = [
            'tenants_total'     => Tenant::withoutTrashed()->count(),
            'tenants_active'    => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'tenants_suspended' => Tenant::where('status', Tenant::STATUS_SUSPENDED)->count(),
            'users_total'       => User::withoutTenancy()->count(),
            'admins_total'      => Admin::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
