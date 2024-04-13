<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;


class AdminAuthController extends Controller
{
    //
    public function adminAuthLogin(Request $request)
    {

      if (Auth::guard('admin')->check()) {
    // User is already authenticated via token
    $user = Auth::guard('admin')->user();
    $token = $user->createToken('YourAppName')->plainTextToken;
    $name = $user->name;

    return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Admin login successfully.',
        ]);
    } else {
    // Attempt to authenticate the user using email and password
    $credentials = $request->only('email', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
        $user = Auth::guard('admin')->user();
        $token = $user->createToken('AwsarClass')->plainTextToken;
        $name = $user->name;

      return response()->json([
            'token' => $token,
            'name' => $name,
            'message' => 'Admin login successfully.',
        ]);
    } else {
        return response()->json(['error' => 'Unauthorized']);
    }
    }
    }

    public function adminAuthLogout(Request $request)
    {
       $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            $admin->tokens()->where('name', 'AwsarClass')->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
    
}
    