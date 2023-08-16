<?php

namespace Marvel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Marvel\Database\Models\Settings;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $setting = Settings::first();
        $useMustVerifyEmail = isset($setting->options['useMustVerifyEmail']) ? $setting->options['useMustVerifyEmail'] : false;

        if (
            $useMustVerifyEmail && $request->user() && ($request->user() instanceof MustVerifyEmail && !$request->user()->hasVerifiedEmail())
        ) {
            //return status code 409
            return response()->json(['message' => EMAIL_NOT_VERIFIED], 409);
        }

        return $next($request);
    }
}