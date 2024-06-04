<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Models\Student;

class StudentAuthController extends Controller
{
    // public function studentAuthLogin(Request $request)
    // {

    // if (Auth::guard('student')->check()) {
    // // User is already authenticated via token
    // $user = Auth::guard('student')->user();
    // $token = $user->createToken('YourAppName')->plainTextToken;
    // $name = $user->name;

    // return response()->json([
    //         'token' => $token,
    //         'name' => $name,
    //         'message' => 'Student login successfully.',
    //     ]);
    // } else {
    // // Attempt to authenticate the user using email and password
    // $credentials = $request->only('email', 'password');

    // if (Auth::guard('student')->attempt($credentials)) {
    //     $user = Auth::guard('student')->user();
    //     $token = $user->createToken('AwsarClass')->plainTextToken;
    //     $name = $user->name;

    //   return response()->json([
    //         'token' => $token,
    //         'name' => $name,
    //         'message' => 'Student login successfully.',
    //     ]);
    // } else {
    //     return response()->json(['error' => 'Unauthorized']);
    // }
    // }
    // }

    public function studentAuthLogin(Request $request){
     $login = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    try {
        $user = Student::whereEmail($login['email'])->first();

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


    public function studentAuthLogout(Request $request)
    {
       $admin = Auth::guard('student')->user();
        
        if ($admin) {
            $admin->tokens()->where('student', 'AwsarClass')->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }




    public function StudentCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
           'phone' => 'required|numeric|phone_number|size:10',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

         if($request->image!=''){
        $uploadedImg=$request->image;
        $fileName=time().'.'.$request->image->extension();          
        $destinationpath=public_path('/Student');
        $img=Image::make($uploadedImg->path());     
        $img->resize(200,null, function($constraint){
        $constraint->aspectRatio();
        })->save($destinationpath.'/'.$fileName);
        $doctor->signImg =  $fileName;}

        $student = Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Student registered successfully', 'student' => $student], 201);
    }

    public function updateStudent(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:students,email,' . $id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->update([
            'name' => $request->name ?? $student->name,
            'email' => $request->email ?? $student->email,
            'password' => $request->password ? Hash::make($request->password) : $student->password,
        ]);

        return response()->json(['message' => 'Student updated successfully', 'student' => $student], 200);
    }


    public function deleteStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully'], 200);
    }
}
