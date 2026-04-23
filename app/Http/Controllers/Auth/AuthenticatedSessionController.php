<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Providers\RouteServiceProvider;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $microsoftGraph;

    public function __construct(MicrosoftGraphBaseService $microsoftGraph)
    {
        $this->middleware('guest')->except('destroy');
        $this->microsoftGraph = $microsoftGraph;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // التحقق مما إذا كان المستخدم قد ربط حساب Microsoft بالفعل
        if (!$user->microsoft_id) {
            // إعادة التوجيه إلى مزود Microsoft OAuth لربط الحساب
            // return redirect()->route('microsoft.redirect')->with('info', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('employees');
    }
}
