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
use App\Models\Teacher;
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
        'class_shift' => 'nullable', 'string', 'min:1', 'max:250',
        'class_time' => 'nullable', 'string', 'min:1', 'max:250',
        'summary' => 'nullable', 'string', 'min:1', 'max:250',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'teachers' => 'nullable|array', // Teachers IDs or names
        'teachers.*' => 'exists:teachers,id', 

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
            $course->class_shift = $request->input('class_shift');
            $course->class_time = $request->input('class_time');
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

               // Attach teachers to the course if selected
        if ($request->has('teachers')) {
            $teachers = $request->input('teachers');
            $course->teachers()->attach($teachers); // Attach multiple teachers
        }

            $imagePath = $course->image ? url('/Courses/' . $course->image) : null;

             return response()->json(['status'=>true,'code'=>200,'message' => 'Course created successfully', 'course' => $course,'image'=>$imagePath], 200);
              }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Created Course', 'data' => $data], 500);
        }
         
    }
 
 public function courseList()
{ 
    try {
        // Fetch courses with active status, ordered by descending ID
        $courses = Course::where('status', 'active')->orderByDesc('id')->get();

        // Map through each course to format the response
        $coursesList = $courses->map(function ($course) {
            // Retrieve associated teachers' IDs and names
            $teachers = $course->teachers()->get(['id', 'name'])->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name
                ];
            });

            return [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'fee' => $course->fee,
                    'startDate' => $course->startDate,
                    'endDate' => $course->endDate,
                    'modeType' => $course->modeType,
                    'status' => $course->status,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'course_id' => $course->course_id, // Assuming 'course_id' is the correct field name
                    'summary' => $course->summary,
                    'image' => $course->image ? url('/Courses/' . $course->image) : null,
                    'class_shift' => $course->class_shift,
                    'class_time' => $course->class_time,
                ],
                'teachers' => $teachers, // Include teachers with IDs and names
            ]; 
        });

        // Return JSON response with formatted data
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $coursesList,
        ]);
    } catch (\Exception $e) {
        // Handle any exceptions that may occur
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to fetch course list',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // public function UpdateView($id){
    //   $course = Course::find($id);
    //   $imagePath = $course->image ? url('/Courses/' . $course->image) : null;
    //   if($course){
    //   return response()->json(['status'=>true,'code'=>200,'data'=>$course,'image'=>$imagePath]);
    //   }else{
    //  return response()->json(['status'=>false,'code'=>404,'message' => 'Course not found'], 404);
    //   }
    // }
   public function UpdateView($id)
{
    try {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }

        // Retrieve image path
        $imagePath = $course->image ? url('/Courses/' . $course->image) : null;

        // Retrieve selected teachers associated with the course
        $teachers = $course->teachers()->select('teachers.id', 'teachers.name')->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'course' => $course,
                'teachers' => $teachers, // This already includes the id and name
            ],
            'image' => $imagePath,
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'code' => 500, 'message' => 'Failed to fetch course details', 'error' => $e->getMessage()], 500);
    }
}
    public function deleteCourse($id)
    {
        $course = Course::find($id);
                $course->teachers()->detach();

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
          'teachers' => 'nullable|array', // Optional if updating teachers
        'teachers.*' => 'exists:teachers,id', //
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
        if ($request->hasFile('image')) {
            $uploadedImg = $request->file('image');
            $fileName = time() . '.' . $uploadedImg->getClientOriginalExtension();          
            $destinationPath = public_path('/Courses');
            $img = Image::make($uploadedImg->path());     
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $fileName);
            $course->image = $fileName;

        }

           // Update teachers associated with the course
        if ($request->has('teachers')) {
            $teachers = $request->input('teachers');
            $course->teachers()->sync($teachers); // Syncs the teachers IDs
        }
            $course->save();
              $imagePath = $course->image ? url('/Courses/' . $course->image) : null;
            return response()->json(['status'=>true,'code'=>200,'message' => 'Course Updated successfully', 'course' => $course,'image'=>$imagePath], 200);
            }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Updating Course', 'data' => $data], 500);
        }
         
    }
} 


