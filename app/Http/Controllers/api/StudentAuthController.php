<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Student;

class StudentAuthController extends Controller
{
    public function studentAuthLogin(Request $request)
    {

      if (Auth::guard('student')->check()) {
    // User is already authenticated via token
    $user = Auth::guard('student')->user();
    $token = $user->createToken('YourAppName')->plainTextToken;
    $name = $user->name;

    return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Student login successfully.',
        ]);
    } else {
    // Attempt to authenticate the user using email and password
    $credentials = $request->only('email', 'password');

    if (Auth::guard('student')->attempt($credentials)) {
        $user = Auth::guard('student')->user();
        $token = $user->createToken('AwsarClass')->plainTextToken;
        $name = $user->name;

      return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Student login successfully.',
        ]);
    } else {
        return response()->json(['error' => 'Unauthorized']);
    }
    }
    }

    public function studentAuthLogout(Request $request)
    {
       $admin = Auth::guard('student')->user();
        
        if ($admin) {
            $admin->tokens()->where('student', 'AwsarClass')->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
