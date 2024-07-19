<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Teacher;
use Image;
use App\Models\Student;
use App\Models\Course;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TeacherAuthController extends Controller
{
   
    

    public function teacherAuthLogin(Request $request){
     $login = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    try {
        $user = Teacher::whereEmail($login['email'])->first();
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
           
        $courses = $user->courses()->get();
        $courseCount = $courses->count();
        

          // Extract course IDs
        $courseIds = $courses->pluck('id')->toArray();
 
        // Get the students enrolled in these courses
        $students = DB::table('students')
            ->leftJoin('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
            ->leftJoin('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->whereIn('courses.id', $courseIds)
            ->select('students.*', 'courses.name as course_name')
            ->orderByDesc('students.id')
            ->get();

        $totalStudentCount = $students->count();

           $token = $user->createToken('AwsarClass')->plainTextToken;
           $code = 200;
           $imagePath = url('/Teachers/' . $user->image);

           $menuList = [
                [
                    'title' => 'Dashboard',
                    'iconStyle' => ' <i className="material-symbols-outlined">home</i>',
                    'to' => 'dashboard',
                ],
                [
                    'title' => 'Students',
                    'iconStyle' => '<i className="material-symbols-outlined">school</i>',
                    'to'=> 'student',	
                ],
                [
                    'title' => 'Courses (Batch)',
                    'iconStyle' => '<i className="merial-icons">article</i>',
                        'to'=> 'batch',		
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
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        'content'=> [
                        [
                            'title'=> 'Attendance',
                            'to'=> 'attendance-list-for-teacher',					
                        ],
                        [
                            'title'=> 'Create Attendance',
                            'to'=> 'create-attendance',
                        ],
                      
           
                        ],
                ],
                 [
                    'title' => 'Study Material',
                    'classsChange' => 'mm-collapse',	
                    'iconStyle' => '<i className="material-symbols-outlined">article</i>',
                        'content'=> [
                        [
                            'title'=> 'Study Materials',
                            'to'=> 'study-materials',					
                        ],
                        [
                            'title'=> 'Upload Study Material',
                            'to'=> 'add-study-material',
                        ],
           
                        ],
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
                    'to' => 'teacher/settings',
                ],
            ];

            
            $data = [
            'teacher' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
            'city' => $user->city,
            'state' => $user->state,
            'qualification' => $user->qualification,
            'image' => $user->image ? url('/Teachers/' . $user->image) : null,
            'classes' => $user->classes,
            'courseCount' => $courseCount,
            'totalStudentCount' => $totalStudentCount,
            'students' => $students
            ],
                'token' => $token,
                'message' => 'Login Successfully',
                'role' => $menuList,
            ];
        }
     } catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
    }
    return response()->json($data, $code);
} 

// public function teacherList(){
//     $teacher = Teacher::orderByDesc('id')->get();
//     return response()->json(['status'=>true,'code'=>200,'data'=>$teacher]);
// }
public function teacherList()
{
    // Retrieve all teachers ordered by descending ID
    $teacher = Teacher::orderByDesc('id')->get();

    // Process each teacher to include the full image URL
    $teacher->transform(function ($t) {
        $t->image = $t->image ? url('/Teachers/' . $t->image) : null;
        return $t;
    });

    // Return the response as JSON
    return response()->json(['status' => true, 'code' => 200, 'data' => $teacher]);
}


 public function UpdateView($id){
   $teacher = Teacher::find($id);
  $imagePath = $teacher ? ($teacher->image ? url('/Teachers/' . $teacher->image) : null) : null;


   if($teacher){
   return response()->json(['status'=>true,'code'=>200,'data'=>$teacher,'image'=>$imagePath]);

   }else{
     return response()->json(['status'=>false,'code'=>404,'message' => 'Teacher not found'], 404);
   }
  }

    public function teacherAuthLogout(Request $request)
    {
       $admin = Auth::guard('teacher')->user();
        
        if ($admin) {
            $admin->tokens()->where('name', 'AwsarClass')->delete();
        }

        return response()->json(['status'=>true,'code'=>200,'message' => 'Successfully logged out']);
    }


    public function teacherCreate(Request $request)
{
    // Validate request inputs
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:teachers',
        'phone' => 'required|numeric|digits:10|unique:teachers',
        'street' => 'nullable|string|min:1|max:250', 
        'postal_code' => 'nullable|numeric|digits:6',
        'city' => 'nullable|string|min:1|max:250',
        'state' => 'nullable|string|min:1|max:250',
        'qualification' => 'nullable|string|min:1|max:250',
        // 'classes' => 'required|array',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'password' => 'required|string|min:6|confirmed',
    ]);

    // If validation fails, return errors
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        // Start transaction
        DB::beginTransaction();

        // Handle image upload if present
        $fileName = '';
        if ($request->hasFile('image')) {
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->getClientOriginalExtension();          
            $destinationPath = public_path('/Teachers');
            $img = Image::make($uploadedImg->path());     
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }

        // Create new Teacher
        $teacher = new Teacher();
        $teacher->name = $request->input('name'); 
        $teacher->email = $request->input('email');
        $teacher->phone = $request->input('phone');
        $teacher->street = $request->input('street');
        $teacher->postal_code = $request->input('postal_code');
        $teacher->city = $request->input('city');
        $teacher->state = $request->input('state');
        $teacher->qualification = $request->input('qualification');
        $teacher->image = $fileName;
        $teacher->password = Hash::make($request->input('password'));
        // $teacher->classes = $request->input('classes');
        $teacher->save();

        // Commit transaction
        DB::commit();

        $imagePath = $teacher->image ? url('/Teachers/' . $teacher->image) : null;

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Teacher registered successfully',
            'teacher' => $teacher,
            'image' => $imagePath
        ], 200);
    } catch (\Exception $e) {
        // Rollback transaction if any error occurs
        DB::rollBack();

        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while registering Teacher',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    public function updateTeacher(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $id,
            'phone' => 'required|numeric|digits:10|unique:teachers,phone,' . $id,
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
        'qualification' => 'nullable|string|min:1|max:250',

            'state' => ['nullable', 'string', 'min:1', 'max:250'],
            // 'classes' => 'nullable|array',
            'image' => 'nullable',
            // 'password' => 'required|string|min:6|confirmed',
        ]);

         if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }

        try{
          
            $teacher = Teacher::find($id);
             if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
            }
            $teacher->name = $request->input('name');
            $teacher->email = $request->input('email');
            $teacher->phone = $request->input('phone');
            $teacher->street = $request->input('street');
            $teacher->postal_code = $request->input('postal_code');
            $teacher->city = $request->input('city');
            $teacher->state = $request->input('state');
            $teacher->qualification = $request->input('qualification');
            // $teacher->password =Hash::make($request->password);
            // $teacher->classes =$request->input('classes');

      if ($request->has('image') && $request->image !== null) {
        if (filter_var($request->image, FILTER_VALIDATE_URL)) {
            // Handle image URL
            $imageUrl = $request->image;
            $imageContent = Http::get($imageUrl)->body();
            $fileName = time() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
            $destinationPath = public_path('/Teachers');
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
            $destinationPath = public_path('/Teachers');
            $img = Image::make($uploadedImg->path());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }

        // Update student's image
        $teacher->image = $fileName; 
    }

            $teacher->save();
               $imagePath = $teacher->image ? url('/Teachers/' . $teacher->image) : null;
           
        return response()->json(['status'=>true,'code'=>200,'message' => 'Teacher updated successfully', 'teacher' => $teacher , 'image' =>$imagePath], 200);
         }catch (Exception $e) {
         $data = ['error' => $e->getMessage()];
          return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Updating Teacher', 'data' => $data], 500);

        }
    }


    public function deleteTeacher($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Teacher not found'], 404);
        }

        $teacher->delete();

        return response()->json(['status'=>true,'code'=>200,'message' => 'Teacher deleted successfully'], 200);
    }

    public function profileUpdateView($id){

        $teacher = Teacher::find($id);
          $imagePath = $teacher->image ? url('/Teachers/' . $teacher->image) : null;
        if($teacher){
        return response()->json(['status'=>true,'code'=>200,'data'=>$teacher,'image'=>$imagePath]);
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'Teacher not found'], 404);
        }
    }

    public function profileUpdate(Request $request,$id){

       $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $id,
            'phone' => 'required|numeric|digits:10|unique:teachers,phone,' . $id,
            'street' => ['nullable', 'string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'qualification' => ['nullable', 'string', 'min:1', 'max:250'],
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

        
            $teacher = Teacher::find($id);
             if (!$teacher) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Teacher not found'], 404);
            }
            $teacher->name = $request->input('name');
            $teacher->email = $request->input('email');
            $teacher->phone = $request->input('phone');
            $teacher->street = $request->input('street');
            $teacher->postal_code = $request->input('postal_code');
            $teacher->city = $request->input('city');
            $teacher->state = $request->input('state');
            $teacher->qualification = $request->input('qualification');
           if ($request->has('image') && $request->image !== null) {
        if (filter_var($request->image, FILTER_VALIDATE_URL)) {
            // Handle image URL
            $imageUrl = $request->image;
            $imageContent = Http::get($imageUrl)->body();
            $fileName = time() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
            $destinationPath = public_path('/Teachers');
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
            $destinationPath = public_path('/Teachers');
            $img = Image::make($uploadedImg->path());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }

        // Update teacher's image
        $teacher->image = $fileName;
    }

            $teacher->save();
                $imagePath = $teacher->image ? url('/Teachers/' . $teacher->image) : null;
            return response()->json(['status'=>true,'code'=>200,'message' => 'Profile Updated Successfully', 'teacher' => $teacher, 'image' =>$imagePath], 200);
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
        $teacher = Teacher::where('email',$request->input('email'))->first();
        
        if($teacher){

            if (Hash::check($request->input('password'), $teacher->password)) {
                $teacher->password = Hash::make($request->new_password);
                $teacher->save();
                return response()->json(['status'=>true,'code'=>200,'message' => 'Your password has been updated successfully.'], 200);
            }else{
            return response()->json(['status'=>false,'code'=>401,'message' => 'The password you entered is incorrect'], 401);
            }
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'We could not find an account with that email address. Please check and try again.'], 404);
        }

        
    }
}
