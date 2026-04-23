<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAuthenticatedAndActive
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير مصرح. يجب تسجيل الدخول.'], 401);
            }
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للوصول للنظام.');
        }

        $user = Auth::user();

        // التحقق من حالة المستخدم
        if ($user->status !== 'active') {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'حسابك غير نشط.'], 403);
            }
            return redirect()->route('login')->with('error', 'حسابك غير نشط. يرجى التواصل مع الإدارة.');
        }


        return $next($request);
    }
}
