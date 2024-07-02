<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Models\Student;
use App\Models\Course;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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

            


             $imagePath = url('/Student/' . $user->image);

           $token = $user->createToken('AwsarClass')->plainTextToken;
           $email = $login['email'];
            $user = DB::table('students')
           ->where('students.email', $email)
           ->leftJoin('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
           ->leftJoin('courses', 'courses_enrollements.course_id', '=', 'courses.id')
           ->select('students.*', 'courses.name as course_name')
           ->first();

            $code = 200;
                            $menuList = [
                [
                    'title' => 'Dashboard',
                    'iconStyle' => ' <i className="material-symbols-outlined">home</i>',
                    'to' => 'dashboard',
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
                        ],
                ],
                [
                    'title' => 'Enrolled Batch',
                    'classsChange' => 'mm-collapse',
                    'iconStyle' => '<i className="merial-icons">article</i>',
                    'to' => 'batch',

                ],
                // [
                //     'title' => 'Reports',
                //      'classsChange' => 'mm-collapse',
                //      'iconStyle' => '<i className="merial-icons">article</i>',
                //         'content'=> [
                //         [
                //             'title'=> 'Live Classes',
                //             'to'=> 'live-classes',					
                //         ],
                //         [
                //             'title'=> 'Create Live Class',
                //             'to'=> 'page-lock-screen',
                //         ],
                      
           
                //         ],
                // ],
                 [
                    'title' => 'Live Classes',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">article</i>',
                        'content'=> [
                        [
                            'title'=> 'Live Classes',
                            'to'=> 'live-classes',					
                        ],
                        ],
                ],
               
                [
                    'title' => 'Fees System',
                    'iconStyle' => '<i className="material-icons">money</i>',
                    'to' => 'settings',
                ],
                [
                    'title' => 'Exams',
                    'iconStyle' => '<i className="material-icons">settings</i>',
                    'to' => 'settings',
                ],
                 [
                    'title' => 'Institute',
                     'classsChange' => 'mm-collapse',
                     'iconStyle' => '<i className="merial-icons">settings</i>',
                        'content'=> [
                        [
                            'title'=> 'Notice',
                            'to'=> 'view-notice',					
                        ],
                        [
                            'title'=> 'Study Materials',
                            'to'=> 'view-study-materials',
                        ],
                        [
                            'title'=> 'Attendance',
                            'to'=> 'page-lock-screen',					
                        ],
                         [
                            'title'=> 'Class Routine',
                            'to'=> 'page-lock-screen',					
                        ],
                        ],
                ],
                [
                    'title' => 'Settings',
                    'iconStyle' => '<i className="material-icons">settings</i>',
                    'to' => 'student/settings',
                ],
            ];

            $data = [
            'student' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
            'city' => $user->city,
            'state' => $user->state,
            'image' => $imagePath, // Include the full image URL
            'fname' => $user->fname,
            'femail' => $user->femail,
            'fphone' => $user->fphone,
            'fstreet' => $user->fstreet,
            'fpostal_code' => $user->postal_code,
            'fcity' => $user->fcity,
            'fstate' => $user->fstate, 
            'dob' => $user->dob,
            'payment_status' => $user->payment_status,
            'course' => $user->course_name ?? 'Not Enrolled',
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


    public function studentAuthLogout(Request $request)
    {
       $admin = Auth::guard('student')->user();
        
        if ($admin) {
            $admin->tokens()->where('student', 'AwsarClass')->delete();
        }

        return response()->json(['status'=>true,'code'=>200,'message' => 'Successfully logged out']);
    }




    public function StudentCreate(Request $request)
    {
         $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:students',
        'phone' => 'required|numeric|digits:10|unique:students',
        'street' => ['nullable', 'string', 'min:1', 'max:250'], 
        'postal_code' => ['nullable', 'numeric', 'digits:6'],
        'city' => ['nullable', 'string', 'min:1', 'max:250'],
        'state' => ['nullable', 'string', 'min:1', 'max:250'],
        'image' => 'nullable',
        'password' => 'required|string|min:6|confirmed',
        'fname' => ['required', 'string', 'min:1', 'max:250'],
        'femail' => ['nullable', 'string', 'min:1', 'max:250'],
        'fphone' => 'required|numeric|digits:10',
        'dob' => ['nullable', 'regex:/^(\d{2})\/(\d{2})\/(\d{4})$/'],
        'fstreet' => ['nullable', 'string', 'min:1', 'max:250'], 
        'fpostal_code' => ['nullable', 'numeric', 'digits:6'],
        'fcity' => ['nullable', 'string', 'min:1', 'max:250'],
        'fstate' => ['nullable', 'string', 'min:1', 'max:250'],
    ], [
        'dob.regex' => 'The dob field format is invalid. The correct format is dd/mm/yyyy.',
    ]);


        if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
         DB::beginTransaction();
     try{
        if($request->image!=''){
        $uploadedImg=$request->image;
        $fileName=time().'.'.$request->image->extension();          
        $destinationpath=public_path('/Student');
        $img=Image::make($uploadedImg->path());     
        $img->resize(200,null, function($constraint){
        $constraint->aspectRatio();
        })->save($destinationpath.'/'.$fileName);
       }else{
        $fileName='';
       }
            $student = new Student();
            $student->name = $request->input('name');
            $student->email = $request->input('email');
            $student->phone = $request->input('phone');
            $student->street = $request->input('street');
            $student->postal_code = $request->input('postal_code');
            $student->city = $request->input('city');
            $student->state = $request->input('state');
            // $student->dob = $request->input('dob');
            if ($request->input('dob')) {
              $dob = Carbon::createFromFormat('d/m/Y', $request->input('dob'))->format('Y-m-d');
              $student->dob =  $dob;

            }
            //  $dob = Carbon::createFromFormat('d/m/Y', $request->input('dob'))->format('Y-m-d');
            $student->image = $fileName;
            $student->password =Hash::make($request->password);
            $student->fname = $request->input('fname');
            $student->femail = $request->input('femail');
            $student->fphone = $request->input('fphone');
            $student->paymentType = 'deactive';
            $student->fstreet = $request->input('fstreet');
            $student->fpostal_code = $request->input('fpostal_code');
            $student->fcity = $request->input('fcity');
            $student->fstate = $request->input('fstate');
            $student->save();
            DB::commit();
            $imagePath = $student->image ? url('/Student/' . $student->image) : null;
            return response()->json(['status' => true,'code' => 200,'message' => 'Student registered successfully', 'student' => $student,'profileImage'=>$imagePath]);
        }catch (Exception $e) {
            DB::rollBack();
        $data = ['error' => $e->getMessage()];
        return response()->json(['status'=> false,'code'=>500,'message' => 'An error occurred while registering students','data' => $data,], 500);
         
    }
    }

    public function StudentList(){
    //  $students = Student::orderByDesc('id')->get();
    
    $students = DB::table('students')
    ->leftJoin('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->leftJoin('courses', 'courses_enrollements.course_id', '=', 'courses.id')
    ->select('students.*', 'courses.name as course_name')
    ->orderByDesc('students.id')
    ->get();

       $studentList = $students->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
            'city' => $user->city,
            'state' => $user->state,
             'image' => $user->image ? url('/Student/' . $user->image) : null, // Assuming $user->imagePath contains the relative path
            'fname' => $user->fname,
            'femail' => $user->femail,
            'fphone' => $user->fphone,
            'fstreet' => $user->fstreet,
            'fpostal_code' => $user->fpostal_code,
            'fcity' => $user->fcity,
            'fstate' => $user->fstate,
            'paymentType' => $user->paymentType,
            'dob' => $user->dob,
            'payment_status' => $user->payment_status,
            'course' =>$user->course_name ?? 'Not Enrolled'


        ];
    });

    return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $studentList
    ]);
    //  return response()->json(['status' => true  , 'code' => 200 , 'data'=>$students]);
    }
     

    public function UpdateView($id){
      $student = Student::find($id);
      if($student){
      $imagePath = $student->image ? url('/Student/' . $student->image) : null;

      return response()->json(['status' => true  , 'code' => 200 ,'data'=>$student ,'image'=>$imagePath]);
       }else{
      return response()->json(['status'=> false,'code'=>404,'message' => 'Student not found'], 404);
       }
    }

    public function updateStudent(Request $request, $id)
    {
        // dd($request->all());
          $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
       'email' => [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('students')->ignore($id),
        ],
       'phone' => [
            'required',
            'numeric',
            'digits:10',
            Rule::unique('students')->ignore($id),
        ],
        'street' => ['nullable', 'string', 'min:1', 'max:250'], 
        'postal_code' => ['nullable', 'numeric', 'digits:6'],
        'city' => ['nullable', 'string', 'min:1', 'max:250'],
        'state' => ['nullable', 'string', 'min:1', 'max:250'],
        'image' => 'nullable',
        'fname' => ['required', 'string', 'min:1', 'max:250'],
        'femail' => ['nullable', 'string', 'min:1', 'max:250'],
        'fphone' => 'required|numeric|digits:10',
        'dob' => ['nullable', 'regex:/^(\d{2})\/(\d{2})\/(\d{4})$/'],
        'fstreet' => ['nullable', 'string', 'min:1', 'max:250'], 
        'fpostal_code' => ['nullable', 'numeric', 'digits:6'],
        'fcity' => ['nullable', 'string', 'min:1', 'max:250'],
        'fstate' => ['nullable', 'string', 'min:1', 'max:250'],
        // 'nullable' => ['required', 'string', 'min:1', 'max:250'],
    ], [
        'dob.regex' => 'The dob field format is invalid. The correct format is dd/mm/yyyy.',
    ]);

         if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
     
            $student = Student::find($id);
             if (!$student) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Student not found'], 404);
        }
            $student->name = $request->input('name');
            $student->email = $request->input('email');
            $student->phone = $request->input('phone');
            $student->street = $request->input('street');
            $student->postal_code = $request->input('postal_code');
            $student->city = $request->input('city');
            $student->state = $request->input('state'); 

            if($request->image!=''){
        $uploadedImg=$request->image;
        $fileName=time().'.'.$request->image->extension();          
        $destinationpath=public_path('/Student');
        $img=Image::make($uploadedImg->path());     
        $img->resize(200,null, function($constraint){
        $constraint->aspectRatio();
        })->save($destinationpath.'/'.$fileName);
            $student->image = $fileName;
       }

            if ($request->input('dob')) {
                 $dob = Carbon::createFromFormat('d/m/Y', $request->input('dob'))->format('Y-m-d');
              $student->dob =  $dob;
            }
             
            
            // $student->password =Hash::make($request->password);
            $student->fname = $request->input('fname');
            $student->femail = $request->input('femail');
            $student->fphone = $request->input('fphone');
            // $student->paymentType = $request->input('paymentType');
            $student->fstreet = $request->input('fstreet');
            $student->fpostal_code = $request->input('fpostal_code');
            $student->fcity = $request->input('fcity');
            $student->fstate = $request->input('fstate');
            $student->save();
            $imagePath = $student->image ? url('/Student/' . $student->image) : null;

        return response()->json(['status'=>true,'code'=>200,'message' => 'Student updated successfully', 'student' => $student , 'image'=>$imagePath], 200);
    }


    public function deleteStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['status'=>true,'code'=>200,'message' => 'Student deleted successfully'], 200);
    }

    public function profileUpdateView($id){

        $student = Student::find($id);
        if($student){
        return response()->json(['status'=>true,'code'=>200,'data'=>$student]);
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'Student not found'], 404);
        }
    }

    public function profileUpdate(Request $request,$id){

       $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $id,
            'phone' => 'required|numeric|digits:10,|unique:students,phone,' . $id,
            'street' => ['nullable','string', 'min:1', 'max:250'], 
            'postal_code' => ['nullable', 'numeric', 'digits:6'],
            'city' => ['nullable', 'string', 'min:1', 'max:250'],
            'fname' => ['required', 'string', 'min:1', 'max:250'],
            'femail' => ['nullable', 'string', 'min:1', 'max:250'],
            'fphone' => 'required|numeric|digits:10',
            'state' => ['nullable', 'string', 'min:1', 'max:250'],
            'dob' => ['nullable', 'date', 'max:250'],
           
        ]);

         if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
        try{

       
            $student = Student::find($id);
            $student->name = $request->input('name');
            $student->email = $request->input('email');
            $student->phone = $request->input('phone');
            $student->street = $request->input('street');
            $student->postal_code = $request->input('postal_code');
            $student->city = $request->input('city');
            $student->state = $request->input('state');
            $student->dob = $request->input('dob');
            $student->fname = $request->input('fname');
            $student->femail = $request->input('femail');
            $student->fphone = $request->input('fphone');
             if($request->image!=''){
        $uploadedImg=$request->image;
        $fileName=time().'.'.$request->image->extension();          
        $destinationpath=public_path('/Student');
        $img=Image::make($uploadedImg->path());     
        $img->resize(200,null, function($constraint){
        $constraint->aspectRatio();
        })->save($destinationpath.'/'.$fileName);
            $student->image = $fileName;

        }
            $student->save();
              $imagePath = $student->image ? url('/Student/' . $student->image) : null;
            return response()->json(['status'=>true,'code'=>200,'message' => 'Profile Updated Successfully', 'student' => $student,'profileImage'=>$imagePath], 200);
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
        $student = Student::where('email',$request->input('email'))->first();
        
        if($student){

            if (Hash::check($request->input('password'), $student->password)) {
                $student->password = Hash::make($request->new_password);
                $student->save();
                return response()->json(['status'=>true,'code'=>200,'message' => 'Your password has been updated successfully.'], 200);
            }else{
            return response()->json(['status'=>false,'code'=>401,'message' => 'The password you entered is incorrect'], 401);
            }
        }else{
        return response()->json(['status'=>false,'code'=>404,'message' => 'We could not find an account with that email address. Please check and try again.'], 404);
        }

        
    }


    public function courseList(){
      $courses = Course::where('status', 'active')->orderByDesc('id')->get();

      $data = [];

      foreach ($courses as $course) {
        
      $data[] = [
        'id' => $course->id,
        'name' => $course->name,
        ];
        }
       return response()->json(['status'=>true,'code'=>200,'data'=>$data]);
    }
}
