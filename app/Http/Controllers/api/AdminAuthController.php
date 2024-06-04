<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Hash;
use Illuminate\Support\Facades\Validator;


class AdminAuthController extends Controller
{
    //
    // public function adminAuthLogin(Request $request)
    // {
    //   if (Auth::guard('admin')->check()) {
    // $user = Auth::guard('admin')->user();
    // $token = $user->createToken('YourAppName')->plainTextToken;
    // $name = $user->name;

    // return response()->json([
    //         'token' => $token,
    //         'name' => $name,
    //         'message' => 'Admin login successfully.',
    //     ]);
    // } else {
    //     $credentials = $request->only('email', 'password');
    //     if (Auth::guard('admin')->attempt($credentials)) {
    //     $user = Auth::guard('admin')->user();
    //     $token = $user->createToken('AwsarClass')->plainTextToken;
    //     $name = $user->name;

    //   return response()->json([
    //         'token' => $token,
    //         'name' => $name, 
    //         'message' => 'Admin login successfully.',
    //     ]);
    // } else {
    //     return response()->json(['error' => 'Unauthorized']);
    // }
    // }
    // }
    
    public function adminAuthLogin(Request $request){
     $login = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    try {
        $user = Admin::whereEmail($login['email'])->first();

        if (!$user || !Hash::check($login['password'], $user->password)) {
            $data = 'Invalid Login Credentials';
            $code = 401;
        } else {

           $token = $user->createToken('AwsarClass')->plainTextToken;
            $code = 200;
            $data = [
                'user' => $user,
                'token' => $token,
            ];
        }
    } catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
    }
    return response()->json($data, $code);
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
    