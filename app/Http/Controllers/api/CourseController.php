<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function courseCreate(Request $request){
          $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fee' => 'required|string|max:255|',
            'startDate' => ['required', 'date', 'max:250'],
            'endDate' => ['required', 'date', 'max:250'],
            'modeType' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try{
            $course = new Course();
            $course->name = $request->input('name');
            $course->fee = $request->input('fee');
            $course->startDate = $request->input('startDate');
            $course->endDate = $request->input('endDate');
            $course->modeType = $request->input('modeType');
            

            $timestamp = time(); // Get the current Unix timestamp
            sleep(1);
            $randomString = Str::random(10);
            $courseId = $timestamp . $randomString;

            $course->Course_id = $courseId;
            $course->save();
             return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
              }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['message' => 'An error occurred while Created Course', 'data' => $data], 500);
        }
         
    }

    public function courseList(){
         $courses = Course::where('status', 'active')->orderByDesc('id')->get();
         return response()->json($courses);
    }

    public function UpdateView($id){
      $course = Course::find($id);
      if($course){
      return response()->json($course);
      }else{
     return response()->json(['message' => 'Course not found'], 404);
      }
    }

    public function deleteCourse($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully'], 200);
    }

    public function courseUpdate(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fee' => 'required|string|max:255|',
            'startDate' => ['required', 'date', 'max:250'],
            'endDate' => ['required', 'date', 'max:250'],
            'modeType' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try{
            $course = Course::find($id);
            if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
            }
            $course->name = $request->input('name');
            $course->fee = $request->input('fee');
            $course->startDate = $request->input('startDate');
            $course->endDate = $request->input('endDate');
            $course->modeType = $request->input('modeType');
            $course->save();
            return response()->json(['message' => 'Course Updated successfully', 'course' => $course], 201);
            }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['message' => 'An error occurred while Updating Course', 'data' => $data], 500);
        }
         
    }
} 
