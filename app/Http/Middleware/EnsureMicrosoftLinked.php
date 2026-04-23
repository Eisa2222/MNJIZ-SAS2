<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMicrosoftLinked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     if (Auth::check() && !Auth::user()->microsoft_id) {
    //         // السماح باستثناء المسار الخاص بالربط لتجنب إعادة التوجيه المستمرة
    //         if (!$request->is('auth/microsoft/redirect') && !$request->is('auth/microsoft/callback')) {
    //             return redirect()->route('microsoft.redirect')->with('info', 'يرجى ربط حساب Microsoft الخاص بك.');
    //         }
    //     }

    //     return $next($request);
    // }
}
