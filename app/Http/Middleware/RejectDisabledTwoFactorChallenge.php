<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class RejectDisabledTwoFactorChallenge
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pendingLoginId = $request->session()->get('login.id');

        if (is_int($pendingLoginId) || (is_string($pendingLoginId) && ctype_digit($pendingLoginId))) {
            $user = User::find((int) $pendingLoginId);

            if ($user?->isDisabled()) {
                $request->session()->forget(['login.id', 'login.remember']);

                return redirect()->route('login')->withErrors([
                    Fortify::username() => trans('auth.failed'),
                ]);
            }
        }

        return $next($request);
    }
}
