<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class HandleForbidden
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
        $response = $next($request);

        // Check if the response status is 403 Forbidden
        if ($response->status() === Response::HTTP_FORBIDDEN) {
            return redirect('/app');
        }

        return $response;
    }
}
