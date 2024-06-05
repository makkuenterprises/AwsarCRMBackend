<?php 

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Models\StaffModel;

class StaffAuthController extends Controller
{
    //
     public function staffAuthLogin(Request $request){
        // dd($request->all());
     $login = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    try {
        $user = StaffModel::whereEmail($login['email'])->first();

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

public function staffList(){
 $staff = StaffModel::orderByDesc('id')->get();
    return response()->json($staff);
}

public function UpdateView($id){
   $staffs = StaffModel::find($id);
   if($staffs){
   return response()->json($staffs);

   }else{
     return response()->json(['message' => 'Staff not found'], 404);
   }
}

public function staffAuthLogout(Request $request)
{
       $staff = Auth::guard('staff')->user();
        
        if ($staff) {
            $staff->tokens()->where('name', 'AwsarClass')->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }


     public function staffCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff_models',
            'phone' => 'required|numeric|digits:10|unique:staff_models',
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
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
           $destinationpath=public_path('/Staffs');
           $img=Image::make($uploadedImg->path());     
           $img->resize(200,null, function($constraint){
           $constraint->aspectRatio();
           })->save($destinationpath.'/'.$fileName);
          }else{
           $fileName='';
          }
            $staff = new StaffModel();
            $staff->name = $request->input('name');
            $staff->email = $request->input('email');
            $staff->phone = $request->input('phone');
            $staff->street = $request->input('street');
            $staff->postal_code = $request->input('postal_code');
            $staff->city = $request->input('city');
            $staff->state = $request->input('state');
            $staff->image = $fileName;
            $staff->password =Hash::make($request->password);
            $staff->save();
          return response()->json(['message' => 'Staff registered successfully', 'staff' => $staff], 201);
        }catch (Exception $e) {
         $data = ['error' => $e->getMessage()];
           return response()->json(['message' => 'An error occurred while Creating staff', 'data' => $data], 500);
         
        }
    }
    
    public function updateStaff(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff_models,email,' . $id,
            'phone' => 'required|numeric|digits:10|unique:staff_models,phone,' . $id,
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
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
           $destinationpath=public_path('/Staffs');
           $img=Image::make($uploadedImg->path());     
           $img->resize(200,null, function($constraint){
           $constraint->aspectRatio();
           })->save($destinationpath.'/'.$fileName);
          }else{
           $fileName='';
          }
            $staff = StaffModel::find($id);
             if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
            }
            $staff->name = $request->input('name');
            $staff->email = $request->input('email');
            $staff->phone = $request->input('phone');
            $staff->street = $request->input('street');
            $staff->postal_code = $request->input('postal_code');
            $staff->city = $request->input('city');
            $staff->state = $request->input('state');
            $staff->image = $fileName;
            $staff->password =Hash::make($request->password);
            $staff->save();
           
        return response()->json(['message' => 'Staff updated successfully', 'staff' => $staff], 200);
         }catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
       return response()->json(['message' => 'An error occurred while updating staff', 'data' => $data], 500);
         }
    }


    public function deleteStaff($id)
    {
        $staff = StaffModel::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $staff->delete();

        return response()->json(['message' => 'Staff deleted successfully'], 200);
    }
}
