<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    // /**
    //  * Handle an incoming request.
    //  *
    //  * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
    //  */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request)
    //     ->header('Access-Control-Allow-Origin',"*")
    //     ->header('Access-Control-Allow-Methods',"GET,POST,PUT,DELETE,OPTIONS")
    //     ->header('Access-Control-Allow-Headers',"Accept,Authorization,Content-Type");

    // }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $response = $next($request);

    //     $response->headers->set('Access-Control-Allow-Origin', '*');
    //     $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    //     $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type');
    //     $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    //     $response->headers->set('X-Content-Type-Options', 'nosniff');
    //     $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; object-src 'none';");
    //     $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
                
    //     return $response;
    // }
    public function handle(Request $request, Closure $next): Response
{
    // Handle preflight requests
    if ($request->isMethod('OPTIONS')) {
        return response()->json([], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type');
    }

    $response = $next($request);

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type');
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; object-src 'none';");
    $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

    return $response;
}
}
