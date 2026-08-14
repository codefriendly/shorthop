<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Uri;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NormalizeShortLinkReferrer
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $referrer = $request->headers->get('referer');

        if ($referrer !== null) {
            $origin = $this->origin($referrer);

            if ($origin === null) {
                $request->headers->remove('referer');
            } else {
                $request->headers->set('referer', $origin);
            }
        }

        return $next($request);
    }

    private function origin(string $referrer): ?string
    {
        try {
            $uri = Uri::of($referrer);
        } catch (Throwable) {
            return null;
        }

        if (! in_array($uri->scheme(), ['http', 'https'], true) || blank($uri->host())) {
            return null;
        }

        $origin = $uri
            ->withUser(null)
            ->withPath('')
            ->withQuery([], merge: false)
            ->withoutFragment()
            ->toString();

        return mb_strlen($origin) <= 255 ? $origin : null;
    }
}
