<?php 

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

        if (!$user) {
          return response()->json(['status'=>true,'code'=>200,'message' => 'We could not find an account with that email address.Please check and try again.'], 404);
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
            $code = 200;
            $imagePath = url('/Staffs/' . $user->image);
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
                            'content'=> [
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
                        'content'=> [
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
                                            'content'=> [
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
                    'title' => 'Live Classes',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        'content'=> [
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
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        'to' => 'attendance-list-for-staff',
                ],
                 [
                    'title' => 'Exams',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">settings</i>',
                        'content'=> [
                        [
                            'title'=> 'View Exam',
                            'to'=> 'page-lock-screen',					
                        ],
                        [
                            'title'=> 'Create Exam',
                            'to'=> 'page-lock-screen',
                        ],
                      
           
                        ],
                ],
                 [
                    'title' => 'Class Routine',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">settings</i>',
                        'content'=> [
                        [
                            'title'=> 'View Routine',
                            'to'=> 'page-lock-screen',					
                        ],
                        [
                            'title'=> 'Create Routine',
                            'to'=> 'page-lock-screen',
                        ],
                      
           
                        ],
                ],
                 [
                    'title' => 'Notice',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">settings</i>',
                        'content'=> [
                        [
                            'title'=> 'View Notice',
                            'to'=> 'view-notice',					
                        ],
                        [
                            'title'=> 'Create Notice',
                            'to'=> 'create-notice',
                        ],
                      
           
                        ],
                ],
                   [
                    'title' => 'Leave Request',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">settings</i>',
                        'content'=> [
                        [
                            'title'=> 'lists Of Leave Request',
                            'to'=> 'view-leave-request',					
                        ],
                        [
                            'title'=> 'Create Leave Request',
                            'to'=> 'create-leave-request',
                        ],
                      
           
                        ], 
                ],
                [
                    'title' => 'Settings',
                    'iconStyle' => '<i className="material-icons">settings</i>',
                    'to' => 'staff/settings',
                ],
            ];

            
            $data = [
            'staff' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
            'city' => $user->city,
            'state' => $user->state,
            'image' => $user->image ? url('/Staffs/' . $user->image) : null,

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

public function staffList()
{
    // Retrieve all staff members ordered by descending ID
    $staff = StaffModel::orderByDesc('id')->get();

    // Process each staff member to include the full image URL
    $staff->transform(function ($staffMember) {
        $staffMember->image = $staffMember->image ? url('/Staffs/' . $staffMember->image) : null;
        return $staffMember;
    });

    // Return the response as JSON
    return response()->json(['status' => true, 'code' => 200, 'data' => $staff]);
}


public function UpdateView($id){
   $staffs = StaffModel::find($id);
       $imagePath = url('/Staffs/' . $staffs->image);

   if($staffs){
   return response()->json(['status'=>true,'code'=>200,'data'=>$staffs , 'image'=>$imagePath]);

   }else{
     return response()->json(['status'=>false,'code'=>404,'message' => 'Staff not found'], 404);
   }
}

public function staffAuthLogout(Request $request)
{
       $staff = Auth::guard('staff')->user();
        
        if ($staff) {
            $staff->tokens()->where('name', 'AwsarClass')->delete();
        }

        return response()->json(['status'=>true,'code'=>200,'message' => 'Successfully logged out']);
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
            $imagePath = url('/Staffs/' . $staff->image);
          return response()->json(['status'=>true,'code'=>200,'message' => 'Staff registered successfully', 'staff' => $staff,'imagePath'=>$imagePath], 200);
        }catch (Exception $e) {
         $data = ['error' => $e->getMessage()];
           return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Creating staff', 'data' => $data], 500);
         
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
        ]);

         if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }

        try{
           
            $staff = StaffModel::find($id);
             if (!$staff) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Staff not found'], 404);
            }
            $staff->name = $request->input('name');
            $staff->email = $request->input('email');
            $staff->phone = $request->input('phone');
            $staff->street = $request->input('street');
            $staff->postal_code = $request->input('postal_code');
            $staff->city = $request->input('city');
            $staff->state = $request->input('state');
            
    if ($request->has('image') && $request->image != '') {
        if (filter_var($request->image, FILTER_VALIDATE_URL)) {
            // Handle image URL
            $imageUrl = $request->image;
            $imageContent = Http::get($imageUrl)->body();
            $fileName = time() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
            $destinationPath = public_path('/Staffs');
            $imagePath = $destinationPath . '/' . $fileName;

            // Save the image content to the specified path
            file_put_contents($imagePath, $imageContent);

            // Resize the image
            $img = Image::make($imagePath);
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath);
        } else {
            // Handle uploaded image file
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->extension();
            $destinationPath = public_path('/Staffs');
            $img = Image::make($uploadedImg->path());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }

        // Update staff's image
        $staff->image = $fileName;
    }

            $staff->save();
        $imagePath = url('/Staffs/' . $staff->image);

           
        return response()->json(['status'=>true,'code'=>200,'message' => 'Staff updated successfully', 'staff' => $staff,'image'=>$imagePath], 200);
         }catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
       return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while updating staff', 'data' => $data], 500);
         }
    }


    public function deleteStaff($id)
    {
        $staff = StaffModel::find($id);

        if (!$staff) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Staff not found'], 404);
        }

        $staff->delete();

        return response()->json(['status'=>true,'code'=>200,'message' => 'Staff deleted successfully'], 200);
    }


     public function profileUpdateView($id){

        $staff = StaffModel::find($id);
        $imagePath = url('/Staffs/' . $staff->image);
        if($staff){
        return response()->json(['status'=>true,'code'=>200,'data'=>$staff,'image'=>$imagePath]);
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'Staff not found'], 404);
        }
    }

    public function profileUpdate(Request $request,$id){

       $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff_models,email,' . $id,
            'phone' => 'required|numeric|digits:10|unique:staff_models,phone,' . $id,
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
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
           
            $staff = StaffModel::find($id);
             if (!$staff) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Staff not found'], 404);
            }
            $staff->name = $request->input('name');
            $staff->email = $request->input('email');
            $staff->phone = $request->input('phone');
            $staff->street = $request->input('street');
            $staff->postal_code = $request->input('postal_code');
            $staff->city = $request->input('city');
            $staff->state = $request->input('state');
            $staff->password =Hash::make($request->password);
         
    if ($request->has('image') && $request->image != '') {
        if (filter_var($request->image, FILTER_VALIDATE_URL)) {
            // Handle image URL
            $imageUrl = $request->image;
            $imageContent = Http::get($imageUrl)->body();
            $fileName = time() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
            $destinationPath = public_path('/Staffs');
            $imagePath = $destinationPath . '/' . $fileName;

            // Save the image content to the specified path
            file_put_contents($imagePath, $imageContent);

            // Resize the image
            $img = Image::make($imagePath);
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath);
        } else {
            // Handle uploaded image file
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->extension();
            $destinationPath = public_path('/Staffs');
            $img = Image::make($uploadedImg->path());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }

        // Update staff's image
        $staff->image = $fileName;
    }

            $staff->save();
            return response()->json(['status'=>true,'code'=>200,'message' => 'Profile Updated Successfully', 'staff' => $staff], 200);
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

        $staff = StaffModel::where('email',$request->input('email'))->first();
        
        if($staff){

            if (Hash::check($request->input('password'), $staff->password)) {
                $staff->password = Hash::make($request->new_password);
                $staff->save();
                return response()->json(['status'=>true,'code'=>200,'message' => 'Your password has been updated successfully.'], 200);
            }else{
            return response()->json(['status'=>false,'code'=>401,'message' => 'The password you entered is incorrect'], 401);
            }
        }else{
        return response()->json(['status'=>false,'code'=>500,'message' => 'We could not find an account with that email address. Please check and try again.'], 404);
        }

        
    }
}
