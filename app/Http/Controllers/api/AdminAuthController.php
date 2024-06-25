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

        if (!$user) {
          return response()->json(['status'=>false,'code'=>404,'message' => 'We could not find an account with that email address.Please check and try again.'], 404);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
        // Return error response for incorrect password
        return response()->json(['status'=>false,'code'=>401,'message' => 'The password you entered is incorrect. Please try again.'], 401);
        }


        if (!$user || !Hash::check($login['password'], $user->password)) {
            $data = 'Invalid Login Credentials';
            $code = 401;
        } else {

           $token = $user->createToken('AwsarClass')->plainTextToken;
            $imagePath = url('/Admin/' . $user->image);
            $code = 200;

                $menuList = [
                [
                    'title' => 'Dashboard',
                    'iconStyle' => ' <i className="material-symbols-outlined">home</i>',
                    'to' => 'dashboard',
                ],
                [
                    'title' => 'Student',
                    'classsChange'=> 'mm-collapse',
                    'iconStyle' => '<i className="material-symbols-outlined">school</i>',
                            ' content'=> [
                        [
                            'title'=> 'Student',
                            'to'=> 'student',					
                        ],
                        [
                            'title'=> 'Student Detail',
                            'to'=> 'student-detail',
                        ],
                        [
                            'title'=> 'Add New Student',
                            'to'=> 'add-student',
                        ],
           
                        ],
                ],
                [
                    'title' => 'Teacher',
                    'classsChange' => 'mm-collapse',	
                    'iconStyle' => '<i className="material-symbols-outlined">person</i>',
                        ' content'=> [
                        [
                            'title'=> 'Teacher',
                            'to'=> 'teacher',					
                        ],
                        [
                            'title'=> 'Teacher Detail',
                            'to'=> 'teacher-detail',
                        ],
                        [
                            'title'=> 'Add New Teacher',
                            'to'=> 'add-teacher',
                        ],
           
                        ],
                ],
                [
                    'title' => 'Courses (Batch)',
                    'classsChange' => 'mm-collapse',
                    'iconStyle' => '<i className="merial-icons">article</i>',
                                            ' content'=> [
                        [
                            'title'=> 'Batch',
                            'to'=> 'batch',					
                        ],
                        [
                            'title'=> 'Add New Batch',
                            'to'=> 'add-batch',
                        ],
                      
           
                        ],
                ],
                [
                    'title' => 'Staff',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        ' content'=> [
                        [
                            'title'=> 'Staff',
                            'to'=> 'staff',					
                        ],
                        [
                            'title'=> 'Add New staff',
                            'to'=> 'add-staff',
                        ],
                      
           
                        ],
                ],
                [
                    'title' => 'Reports',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        ' content'=> [
                        [
                            'title'=> 'Live Classes',
                            'to'=> 'live-classes',					
                        ],
                        [
                            'title'=> 'Create Live Class',
                            'to'=> 'page-lock-screen',
                        ],
                      
           
                        ],
                ],
                 [
                    'title' => 'Live Classes',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        ' content'=> [
                        [
                            'title'=> 'Live Classes',
                            'to'=> 'live-classes',					
                        ],
                        [
                            'title'=> 'Create Live Class',
                            'to'=> 'page-lock-screen',
                        ],
                      
           
                        ],
                ],
                [
                    'title' => 'Attendance',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        ' content'=> [
                        [
                            'title'=> 'Attendance',
                            'to'=> 'page-lock-screen',					
                        ],
                        [
                            'title'=> 'Todays Attendance',
                            'to'=> 'page-lock-screen',
                        ],
                      
           
                        ],
                ],
                [
                    'title' => 'Settings',
                    'iconStyle' => '<i className="material-icons">settings</i>',
                    'to' => 'settings',
                ],
            ];

            $data = [
            'admin' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $imagePath, // Include the full image URL
            ],
                'token' => $token,
                'message' => 'Login Successfully',
                'role' => $menuList
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

        return response()->json(['status'=>true,'code'=>200,'message' => 'Successfully logged out']);
    }
    
    public function profileUpdateView($id){

        $admin = Admin::find($id);
         $imagePath = url('/Admin/' . $admin->image);
        if($admin){
        return response()->json(['status'=>true,'code'=>200,'data'=>$admin,'image'=>$imagePath]);
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'Admin not found'], 404);
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
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
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
            return response()->json(['status'=>true,'code'=>200,'message' => 'Profile Updated Successfully', 'admin' => $admin], 200);
        }catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
        return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while updating profile', 'data' => $data], 500);
        }
    }

    public function passwordUpdate(Request $request){

        $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }

        $admin = Admin::where('email',$request->input('email'))->first();
        
        if($admin){

            if (Hash::check($request->input('password'), $admin->password)) {
                $admin->password = Hash::make($request->new_password);
                $admin->save();
                return response()->json(['status'=>true,'code'=>200,'message' => 'Your password has been updated successfully.'], 200);
            }else{
            return response()->json(['status'=>false,'code'=>401,'message' => 'The password you entered is incorrect'], 401);
            }
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'We could not find an account with that email address. Please check and try again.'], 404);
        }

        
    }
}
    