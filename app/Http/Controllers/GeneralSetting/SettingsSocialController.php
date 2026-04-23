<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSetting\SettingsSocial\SettingsSocialRequest;
use App\Models\general_setting\SettingsSocial;
use App\Services\Social\LinkedIn\LinkedInService;
use App\Services\Social\SocialConnectionService;
use App\Services\Social\Twitter\TwitterService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsSocialController extends Controller
{

    public function __construct(
        private SocialConnectionService $socialConnectionService
    ) {
        $this->middleware('can:مواقع التواصل')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    private $model = SettingsSocial::class;


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index()
    {
        try {
            $connectionsData = $this->socialConnectionService->getAllConnectionsStatus();

            // dd($connectionsData);

            return view('general_setting.settings_social.index', [
                'settings'              => $connectionsData['settings'],
                'linkedinStatus'        => $connectionsData['linkedin'],
                'twitterStatus'         => $connectionsData['x'],
            ]);
        } catch (\Exception $e) {
            return view('general_setting.settings_social.index')
                ->with('error', 'حدث خطأ في تحميل الإعدادات: ' . $e->getMessage());
        }
    }


    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit($id)
    {
        $setting = $this->model::findOrFail($id);
        return view('general_setting.settings_social.edit', compact('setting'));
    }


    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(SettingsSocialRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $setting = $this->model::findOrFail($id);

            if ($setting->name == "linkedin") {
                $this->socialConnectionService->clearServiceCache('linkedin');
                $this->disconnectOldConnection($id);
                return $this->updateLinkedInSettings($setting, $data);
            } elseif ($setting->name == "x") {
                $this->socialConnectionService->clearServiceCache('x');
                $this->disconnectOldConnection($id);
                return $this->updateTwitterSettings($setting, $data);
            } else {
                $setting->update($data);
                return redirect()->route('settings-social.index')->with('success', 'تم تعديل إعدادات ' . $setting->name . ' بنجاح.');
            }
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage());
        }
    }


    /*
    |============================================================================
    | disconnect
    |============================================================================
    */
    public function disconnect($settings_social)
    {
        try {
            $setting = $this->model::findOrFail($settings_social);
            $serviceName = $setting->name;

            $setting->update([
                'api_key'           => null,
                'api_secret'    => null,
                'access_token' => null,
                'access_token_secret' => null,
                'active' => false, // تعطيل الخدمة
            ]);

            $this->socialConnectionService->clearServiceCache($serviceName);

            return redirect()->route('settings-social.index')->with('success', "تم فصل {$serviceName} بنجاح.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء فصل الخدمة: ' . $e->getMessage());
        }
    }




    /*
    |============================================================================
    |============================================================================
    |                         private functions
    |============================================================================
    |============================================================================
    */
    private function updateLinkedInSettings($setting, $data): RedirectResponse
    {
        if (empty($data['access_token'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'رمز الوصول  مطلوب لـ LinkedIn');
        }

        try {
            $connectionResult = $this->socialConnectionService->testSpecificConnection('linkedin', $data);

            if (!isset($connectionResult['success']) || !$connectionResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل في الاتصال مع LinkedIn ');
            }

            // مسح الكاش 


            $data['active'] = true;
            $setting->update($data);

            return redirect()->route('settings-social.index')->with('success', 'تم تعديل إعدادات LinkedIn بنجاح وتم تفعيل الخدمة.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ في تحديث إعدادات LinkedIn ' . $e->getMessage());
        }
    }

    private function updateTwitterSettings($setting, $data): RedirectResponse
    {
        foreach (['api_key', 'api_secret', 'access_token', 'access_token_secret'] as $field) {
            if (empty($data[$field])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "كل الحقول مطلوبة");
            }
        }

        try {
            $connectionResult = $this->socialConnectionService->testSpecificConnection('x', $data);

            if (!isset($connectionResult['success']) || !$connectionResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل في الاتصال مع Twitter ');
            }


            $data['active'] = true;
            $setting->update($data);

            return redirect()->route('settings-social.index')->with('success', 'تم تعديل إعدادات Twitter بنجاح وتم تفعيل الخدمة.');
        } catch (\Exception $e) {

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ في تحديث إعدادات Twitter: ' . $e->getMessage());
        }
    }

    // فصل الاتصال القديم
    private function disconnectOldConnection($id)
    {
        try {
            $setting = $this->model::findOrFail($id);
            $setting->update([
                'api_key'           => null,
                'api_secret'    => null,
                'access_token' => null,
                'access_token_secret' => null,
                'active' => false, // تعطيل الخدمة
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء فصل الخدمة: ' . $e->getMessage());
        }
    }
}
