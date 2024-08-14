<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckMultipleGuards
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $staff = Auth::guard('staff')->user();
        $student = Auth::guard('student')->user();
        $teacher = Auth::guard('teacher')->user();
        $admin = Auth::guard('admin')->user();

        if ($staff || $student || $teacher || $admin) {
            return $next($request);
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthorized access'], 401);
    
    }
}
