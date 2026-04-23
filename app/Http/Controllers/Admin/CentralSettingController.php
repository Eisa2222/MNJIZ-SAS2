<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Settings\UpdateCentralSettingAction;
use App\Http\Controllers\Controller;
use App\Models\CentralSetting;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CentralSettingController extends Controller
{
    public function index(SettingsRepository $repo): View
    {
        $settings = CentralSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return view('admin.settings.index', [
            'settings' => $settings,
            'groups'   => $settings->groupBy('group'),
        ]);
    }

    public function update(Request $request, string $key, UpdateCentralSettingAction $action): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['nullable', 'string'],
        ]);

        $action($key, $data['value']);

        return back()->with('status', "Setting '{$key}' updated.");
    }
}
