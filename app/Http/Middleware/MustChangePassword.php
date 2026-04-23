<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MustChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // تحقق من أن المستخدم يحتاج إلى تغيير كلمة المرور
            // واستثنِ مسارات تغيير كلمة المرور وأي مسارات أخرى لازمة
            if ($user->must_change_password
                && !$request->is('password/change')
                && !$request->is('password/change/*')
                && !$request->routeIs('password.change')
                && !$request->routeIs('password.change.post')) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
