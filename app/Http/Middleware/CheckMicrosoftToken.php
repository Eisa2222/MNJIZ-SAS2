<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Support\Facades\Log;

class CheckMicrosoftToken
{
    protected $graphService;

    public function __construct(MicrosoftGraphBaseService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        // استخدام الخدمة للتحقق من صلاحية التوكن وتحديثه إذا لزم الأمر
        $accessToken = $this->graphService->getValidAccessToken($user);

        if (!$accessToken) {
            // فشل في الحصول على رمز وصول صالح
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }

        // الحصول على معرف المستخدم من Microsoft إذا لم يكن مخزنًا
        if (!$user->microsoft_user_id) {
            $userId = $this->graphService->getUserIdByEmail($accessToken, $user->email);
            if ($userId) {
                $user->update(['microsoft_id' => $userId]);
            } else {
                return redirect()->route('dashboard')->with('error', 'فشل في جلب معرف المستخدم من Microsoft.');
            }
        }

        // تمرير accessToken و userId إلى الـ Request
        $request->merge([
            'microsoft_token' => $accessToken,
            'microsoft_id' => $user->microsoft_id,
        ]);

        return $next($request);
    }
}
