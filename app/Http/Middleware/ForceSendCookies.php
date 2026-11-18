<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceSendCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($response->headers->getCookies() as $cookie) {
            header('Set-Cookie: ' . $cookie, false);
        }

        foreach (['Location', 'Content-Type', 'Cache-Control'] as $name) {
            if ($response->headers->has($name)) {
                header($name . ': ' . $response->headers->get($name), true);
            }
        }

        return $response;
    }
}