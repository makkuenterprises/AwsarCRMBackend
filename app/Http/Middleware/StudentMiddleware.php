<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
        // if (Auth::guard('admin')->check()) {
        // return $next($request);

        // }
        // if (Auth::guard('staff')->check()) {
        // return $next($request);

        // }
         if (Auth::guard('student')->check()) {
        return $next($request);

        }
        //  if (Auth::guard('teacher')->check()) {
        // return $next($request);

        // }

        return response()->json(['message' => 'Unauthorized, please log in as student'], 401);

    }
}
