<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Hash;
use Image;
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
                 'message' => 'Login Successfully'
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
    
    public function profileUpdateView($id){

        $admin = Admin::find($id);
        if($admin){
        return response()->json($admin);
        }else{
        return response()->json(['message' => 'Admin not found'], 404);
        }
    }

    public function profileUpdate(Request $request,$id){

      $validator = Validator::make($request->all(), [
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:admins,email,' . $id,
         'phone' => 'required|numeric|digits:10|unique:admins,phone,' . $id,
         'image' => 'nullable',
        ]);
        if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
        }

    try{
         if($request->image!=''){
          $uploadedImg=$request->image;
          $fileName=time().'.'.$request->image->extension();          
          $destinationpath=public_path('/Admin');
          $img=Image::make($uploadedImg->path());     
          $img->resize(200,null, function($constraint){
          $constraint->aspectRatio();
          })->save($destinationpath.'/'.$fileName);
          }else{
          $fileName='';
           }
            $admin = Admin::find($id);
            $admin->name = $request->input('name');
            $admin->email = $request->input('email');
            $admin->phone = $request->input('phone');
            $admin->image = $fileName;
            $admin->save();
            return response()->json(['message' => 'Profile Updated Successfully', 'admin' => $admin], 201);
        }catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
        return response()->json(['message' => 'An error occurred while updating profile', 'data' => $data], 500);
        }
    }
}
    