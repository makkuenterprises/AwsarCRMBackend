<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Teacher;

class TeacherAuthController extends Controller
{
     public function teacherAuthLogin(Request $request)
    {

      if (Auth::guard('teacher')->check()) {
    // User is already authenticated via token
    $user = Auth::guard('teacher')->user();
    $token = $user->createToken('YourAppName')->plainTextToken;
    $name = $user->name;

    return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Teacher login successfully.',
        ]);
    } else {
    // Attempt to authenticate the user using email and password
    $credentials = $request->only('email', 'password');

    if (Auth::guard('teacher')->attempt($credentials)) {
        $user = Auth::guard('teacher')->user();
        $token = $user->createToken('AwsarClass')->plainTextToken;
        $name = $user->name;

      return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Teacher login successfully.',
        ]);
    } else {
        return response()->json(['error' => 'Unauthorized']);
    }
    }
    }

    public function teacherAuthLogout(Request $request)
    {
       $admin = Auth::guard('teacher')->user();
        
        if ($admin) {
            $admin->tokens()->where('name', 'AwsarClass')->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
