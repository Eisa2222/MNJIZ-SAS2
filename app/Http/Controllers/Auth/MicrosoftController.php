<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class MicrosoftController extends Controller
{
    protected $microsoftGraph;

    public function __construct(MicrosoftGraphBaseService $microsoftGraph)
    {
        $this->microsoftGraph = $microsoftGraph;
    }

    /**
     * Redirect the user to the Microsoft OAuth provider for account linking.
     */
    public function redirectToProvider()
    {
        // Log::info('Redirecting to Microsoft OAuth provider.');

        // Verify settings before starting
        if (!$this->microsoftGraph->verifySettings()) {
            // Log::error('Microsoft OAuth settings are invalid or incomplete.');
            return redirect('employees/dashboard')->with('error', 'إعدادات Microsoft OAuth غير مكتملة أو غير صحيحة. الرجاء التواصل مع الإدارة.');
        }

        try {
            return Socialite::driver('microsoft')
                ->scopes([
                    // 'Files.Read.All',
                    // 'Files.ReadWrite.All',
                    'Files.ReadWrite',
                    // 'User.Read.All',
                    // 'Group.Read.All',
                    'openid',
                    'profile',
                    // 'Mail.Read',
                    'Mail.Send',
                    'User.Read',
                    'offline_access', // Required to obtain refresh token
                    'Calendars.Read',
                    'Calendars.ReadWrite',
                    'Tasks.ReadWrite',
                    // 'Directory.ReadWrite.All',
                    // 'Group.ReadWrite.All',
                    'OnlineMeetings.ReadWrite',
                    // 'OnlineMeetings.Read',
                    'Mail.ReadWrite',
                ])
                ->with(['prompt' => 'select_account'])
                // ->with(['prompt' => 'consent'])
                ->redirect();
        } catch (\Exception $e) {
            // Log::error('Error during redirect to Microsoft OAuth: ' . $e->getMessage());
            return redirect('employees/dashboard')->with('error', 'حدث خطأ أثناء محاولة ربط حساب Microsoft. يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * Handle the callback from Microsoft OAuth provider.
     */
    public function handleProviderCallback()
    {
        // Log::info('Handling Microsoft OAuth callback.');

        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
            // Log::info('Microsoft user retrieved:', ['user' => $microsoftUser]);
        } catch (\Exception $e) {
            // Log::error('Microsoft OAuth Error: ' . $e->getMessage());
            return redirect('/employees/login')->with('error', 'حدث خطأ أثناء محاولة تسجيل الدخول عبر Microsoft. يرجى المحاولة لاحقاً.');
        }

        // البحث عن المستخدم باستخدام البريد الإلكتروني
        $user = User::where('email', $microsoftUser->getEmail())->first();

        if (!$user) {
            // Log::warning('User with email ' . $microsoftUser->getEmail() . ' not found.');
            return redirect('/employees/login')->with('error', 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني. الرجاء التواصل مع مدير النظام.');
        }

        // تسجيل دخول المستخدم
        Auth::login($user, true);

        // تحديث بيانات مايكروسوفت للمستخدم
        try {
            $user->update([
                'microsoft_id' => $microsoftUser->getId(),
                'microsoft_token' => $microsoftUser->token,
                'microsoft_refresh_token' => $microsoftUser->refreshToken,
                'microsoft_token_expires' => now()->addSeconds($microsoftUser->expiresIn),
            ]);

            // Log::info('Microsoft account linked for user:', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            // Log::error('Error updating user with Microsoft data: ' . $e->getMessage());
            return redirect('employees/dashboard')->with('error', 'حدث خطأ أثناء تحديث بيانات حسابك. يرجى المحاولة لاحقاً.');
        }

        return redirect()->intended('employees/dashboard')->with('success', 'تم تسجيل الدخول بنجاح عبر Microsoft.');
    }
}
