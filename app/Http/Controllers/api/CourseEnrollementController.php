<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoursesEnrollement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Str;

class CourseEnrollementController extends Controller
{
    //
    public function enrollCourse(Request $request){
         // Validate the request data
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required',
        'course_id' => 'required',
        'payment_type' => ['required', 'string', 'min:1', 'max:250'],
        'payment_status' => ['required', 'string', 'min:1', 'max:250'],
        
    ]);

    // Check if validation fails
    if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
 

    try {
        // Find the student and course

        $student = Student::find($request->student_id);
             if (!$student) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Student not found'], 404);
        }

         $course = Course::find($request->course_id);
             if (!$course) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Course not found'], 404);
        }

         $enrollcourse = new CoursesEnrollement();
         $enrollcourse->student_id = $request->input('student_id');
         $enrollcourse->course_id = $request->input('course_id');
         $enrollcourse->enrollment_date = Carbon::now()->toDateString();
         $enrollcourse->payment_type = $request->input('payment_type');
         $enrollcourse->payment_status = $request->input('payment_status');

         $timestamp = time(); // Get the current Unix timestamp
            sleep(1);
            $randomString = Str::random(4);
            $randomInteger = random_int(0, 999999);
            $enrollmentno = $timestamp . $randomInteger . $randomString;

         $enrollcourse->enrollment_no = $enrollmentno ;

            $enrollcourse->save();
        

        return response()->json(['status'=>true,'code'=>200,'message' => 'Student enrolled in the course successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['status'=>false,'code'=>500,'message' => 'Failed to enroll student in the course', 'error' => $e->getMessage()], 500);
    }
    }

   

}
