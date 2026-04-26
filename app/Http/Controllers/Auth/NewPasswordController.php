<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {
        // التحقق من صلاحية التوكن
        if (!$this->isValidToken($request->token, $request->email)) {
            return $this->redirectToPasswordRequest('انتهت صلاحية رابط استعادة كلمة المرور. يرجى طلب رابط جديد لاستعادة كلمة المرور');
        }

        return view('auth.reset-password', ['request' => $request]);
    }


    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'     => ['required'],
            'email'     => ['required', 'email'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ]);


        if (!$this->isValidToken($request->token, $request->email)) {
            return $this->redirectToPasswordRequest('انتهت صلاحية رابط استعادة كلمة المرور. يرجى طلب رابط جديد لاستعادة كلمة المرور');
        }

        // ─── Hotfix: Tenant-Aware Password Reset (apply step) ─────────
        // Same root-cause as login + sendResetLink: Password::reset()
        // resolves the user via Eloquent under BelongsToTenant scope.
        // Without setting tenant context first, `$user` resolves to null
        // and the broker returns INVALID_USER even though the token is
        // valid. Bind the right tenant before the broker query.
        $candidate = User::query()->withoutGlobalScopes()
            ->where('email', $request->input('email'))
            ->first();

        if ($candidate && $candidate->tenant_id) {
            $tenant = Tenant::find($candidate->tenant_id);
            if ($tenant) {
                TenantContext::set($tenant);
            }
        }
        // ──────────────────────────────────────────────────────────────

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    // التحقق من صلاحية التوكن
    private function isValidToken($token, $email = null): bool
    {
        if (!$token) {
            return false;
        }

        $query = DB::table('password_reset_tokens');

        if ($email) {
            $query->where('email', $email);
        }

        $tokenData = $query->first();

        if (!$tokenData) {
            return false;
        }

        // التحقق من التوكن المُشفّر
        if (!Hash::check($token, $tokenData->token)) {
            return false;
        }

        $tokenAge = Carbon::parse($tokenData->created_at)->diffInMinutes(Carbon::now());
        $expireMinutes = config('auth.passwords.users.expire', 60);

        if ($tokenAge >= $expireMinutes) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        return true;
    }

    //   إعادة توجيه لصفحة طلب استعادة كلمة المرور
    private function redirectToPasswordRequest($message)
    {
        return redirect()->route('password.request')->withErrors(['email' => $message]);
    }
}
