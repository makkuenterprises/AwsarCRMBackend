<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Image;
use App\Rules\DateFormat;

class CourseController extends Controller
{
    public function courseCreate(Request $request){
        
         $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:courses',
        'fee' => 'required|string|max:255|',
        'startDate' => ['required', new DateFormat('d/m/Y')],
        'endDate' => ['required', new DateFormat('d/m/Y')],
        'modeType' => ['required', 'string', 'min:1', 'max:250'],
        'summary' => 'nullable', 'string', 'min:1', 'max:250',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
        try{
            $course = new Course();
            $course->name = $request->input('name');
            $course->fee = $request->input('fee');
             $startDate = Carbon::createFromFormat('d/m/Y', $request->input('startDate'))->format('Y-m-d');
             $endDate = Carbon::createFromFormat('d/m/Y', $request->input('endDate'))->format('Y-m-d');
            $course->startDate = $startDate;
            $course->endDate = $endDate;
            $course->modeType = $request->input('modeType');
            $course->summary = $request->input('summary');

             // Handle image upload if present
        $fileName = '';
        if ($request->hasFile('image')) {
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->getClientOriginalExtension();          
            $destinationPath = public_path('/Courses');
            $img = Image::make($uploadedImg->path());     
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }
            $course->image = $fileName;

            

            $timestamp = time(); // Get the current Unix timestamp
            sleep(1);
            $randomString = Str::random(10);
            $courseId = $timestamp . $randomString;

            $course->Course_id = $courseId;
            $course->save();
            $imagePath = $course->image ? url('/Courses/' . $course->image) : null;

             return response()->json(['status'=>true,'code'=>200,'message' => 'Course created successfully', 'course' => $course,'image'=>$imagePath], 200);
              }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Created Course', 'data' => $data], 500);
        }
         
    }
 
    public function courseList(){
         $courses = Course::where('status', 'active')->orderByDesc('id')->get();
         return response()->json(['status'=>true,'code'=>200,'data'=>$courses]);
    }

    public function UpdateView($id){
      $course = Course::find($id);
      $imagePath = $course->image ? url('/Courses/' . $course->image) : null;
      if($course){
      return response()->json(['status'=>true,'code'=>200,'data'=>$course,'image'=>$imagePath]);
      }else{
     return response()->json(['status'=>false,'code'=>404,'message' => 'Course not found'], 404);
      }
    }

    public function deleteCourse($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Course not found'], 404);
        }
        $course->delete();
        return response()->json(['status'=>true,'code'=>200,'message' => 'Course deleted successfully'], 200);
    }

    public function courseUpdate(Request $request,$id){
         $validator = Validator::make($request->all(), [
         'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('courses')->ignore($id),
        ],
        'fee' => 'required|string|max:255|',
        'startDate' => ['required', new DateFormat('d/m/Y')],
        'endDate' => ['required', new DateFormat('d/m/Y')],
        'modeType' => ['required', 'string', 'min:1', 'max:250'],
        'summary' => 'nullable', 'string', 'min:1', 'max:250',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
        try{
            $course = Course::find($id);
            if (!$course) {
            return response()->json(['status'=>false,'code'=>404,'message' => 'Course not found'], 404);
            }
            $course->name = $request->input('name');
            $course->fee = $request->input('fee');
            $startDate = Carbon::createFromFormat('d/m/Y', $request->input('startDate'))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $request->input('endDate'))->format('Y-m-d');
            $course->startDate = $startDate;
            $course->endDate = $endDate;
            $course->modeType = $request->input('modeType');
            $course->summary = $request->input('summary');

            // Handle image upload if present
        $fileName = '';
        if ($request->hasFile('image')) {
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->getClientOriginalExtension();          
            $destinationPath = public_path('/Courses');
            $img = Image::make($uploadedImg->path());     
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
        }
            $course->image = $fileName;
            $course->save();
              $imagePath = $course->image ? url('/Courses/' . $course->image) : null;
            return response()->json(['status'=>true,'code'=>200,'message' => 'Course Updated successfully', 'course' => $course,'image'=>$imagePath], 200);
            }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Updating Course', 'data' => $data], 500);
        }
         
    }
} 
