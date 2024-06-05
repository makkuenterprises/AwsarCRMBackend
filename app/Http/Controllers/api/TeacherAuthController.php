<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Teacher;
use Image;

class TeacherAuthController extends Controller
{
    //  public function teacherAuthLogin(Request $request)
    // {

    // if (Auth::guard('teacher')->check()) {
    // // User is already authenticated via token
    // $user = Auth::guard('teacher')->user();
    // $token = $user->createToken('YourAppName')->plainTextToken;
    // $name = $user->name;

    // return response()->json([
    //         'token' => $token,
    //         'name' => $name,
    //         'message' => 'Teacher login successfully.',
    //     ]);
    // } else {
    // // Attempt to authenticate the user using email and password
    // $credentials = $request->only('email', 'password');

    // if (Auth::guard('teacher')->attempt($credentials)) {
    //     $user = Auth::guard('teacher')->user();
    //     $token = $user->createToken('AwsarClass')->plainTextToken;
    //     $name = $user->name;

    //   return response()->json([
    //         'token' => $token,
    //         'name' => $name,
    //         'message' => 'Teacher login successfully.',
    //     ]);
    // } else {
    //     return response()->json(['error' => 'Unauthorized']);
    // }
    // }
    // }

    public function teacherAuthLogin(Request $request){
     $login = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    try {
        $user = Teacher::whereEmail($login['email'])->first();

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

public function teacherList(){
    $teacher = Teacher::all();
    return response()->json($teacher);
}

 public function UpdateView($id){
   $teacher = Teacher::find($id);
   if($teacher){
   return response()->json($teacher);

   }else{
     return response()->json(['message' => 'Student not found'], 404);
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


     public function teacherCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers',
            'phone' => 'required|numeric|digits:10|unique:teachers',
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
            'classes' => 'required|array',
            'image' => 'nullable',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try{
            if($request->image!=''){
           $uploadedImg=$request->image;
           $fileName=time().'.'.$request->image->extension();          
           $destinationpath=public_path('/Teachers');
           $img=Image::make($uploadedImg->path());     
           $img->resize(200,null, function($constraint){
           $constraint->aspectRatio();
           })->save($destinationpath.'/'.$fileName);
          }else{
           $fileName='';
          }
            $teacher = new Teacher();
            $teacher->name = $request->input('name');
            $teacher->email = $request->input('email');
            $teacher->phone = $request->input('phone');
            $teacher->street = $request->input('street');
            $teacher->postal_code = $request->input('postal_code');
            $teacher->city = $request->input('city');
            $teacher->state = $request->input('state');
            $teacher->image = $fileName;
            $teacher->password =Hash::make($request->password);
            $teacher->classes =$request->input('classes');
            $teacher->save();
          return response()->json(['message' => 'Teacher registered successfully', 'teacher' => $teacher], 201);
        }catch (Exception $e) {
         $data = ['error' => $e->getMessage()];
        }
    }
    
    public function updateTeacher(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|numeric|digits:10',
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
            'classes' => 'required|array',
            'image' => 'nullable',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try{
            if($request->image!=''){
           $uploadedImg=$request->image;
           $fileName=time().'.'.$request->image->extension();          
           $destinationpath=public_path('/Teachers');
           $img=Image::make($uploadedImg->path());     
           $img->resize(200,null, function($constraint){
           $constraint->aspectRatio();
           })->save($destinationpath.'/'.$fileName);
          }else{
           $fileName='';
          }
            $teacher = Teacher::find($id);
            $teacher->name = $request->input('name');
            $teacher->email = $request->input('email');
            $teacher->phone = $request->input('phone');
            $teacher->street = $request->input('street');
            $teacher->postal_code = $request->input('postal_code');
            $teacher->city = $request->input('city');
            $teacher->state = $request->input('state');
            $teacher->image = $fileName;
            $teacher->password =Hash::make($request->password);
            $teacher->classes =$request->input('classes');
            $teacher->save();
            if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
            }
        return response()->json(['message' => 'Teacher updated successfully', 'teacher' => $teacher], 200);
         }catch (Exception $e) {
         $data = ['error' => $e->getMessage()];
        }
    }


    public function deleteTeacher($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully'], 200);
    }
}
