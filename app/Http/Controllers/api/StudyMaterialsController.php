<?php

namespace App\Http\Controllers\api;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; 
use App\Models\StudyMaterials;
use App\Models\Student;
use App\Models\Course;
use App\Models\StaffModel;
use App\Models\Teacher;
use Illuminate\Support\Facades\Validator;
use DB; 
use App\Models\Admin; 

use Crypt;
use App\Notifications\StudyMaterial;



class StudyMaterialsController extends Controller
{

// -------------------------------------------------------------------------------
// upload study materials
// -------------------------------------------------------------------------------

public function store(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'batch_id' => 'required|exists:courses,id',
        'description' => 'nullable|string',
        'material.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        'material_url.*' => 'nullable|url',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    // Ensure either materials or material_urls are provided, but not both
    if (!$request->hasFile('material') && !$request->input('material_url')) {
        return response()->json([
            'status' => 'error',
            'code' => 400,
            'message' => 'Please provide either files or URLs for the study materials.',
        ], 400);
    }

    if ($request->hasFile('material') && $request->input('material_url')) {
        return response()->json([
            'status' => 'error',
            'code' => 400,
            'message' => 'Please provide only one of either files or URLs for the study materials.',
        ], 400);
    }
 
    try {
        // Create a new StudyMaterial instance 
        $studyMaterial = new StudyMaterials();
        $studyMaterial->title = $request['title'];
        $studyMaterial->batch_id = $request['batch_id'];
        $studyMaterial->description = $request['description'];
        $studyMaterial->uploadedDate = Carbon::now('Asia/Kolkata')->format('d-m-y');

        $materialPaths = [];

        // Handle multiple file uploads
        if ($request->hasFile('material')) { 
            foreach ($request->file('material') as $file) {
                $path = $file->store('study_material');
                $materialPaths[] = $path;
            }
        }

        // Handle multiple URLs
        if ($request->input('material_url')) {
            foreach ($request->input('material_url') as $url) {
                $materialPaths[] = $url;
            }
        }

        // Store the file paths and URLs as JSON in the material_paths column
        $studyMaterial->material_path  = json_encode($materialPaths);

        // Save the study material
        $studyMaterial->save(); 

        // Get the student IDs enrolled in the course
        $studentIds = DB::table('courses_enrollements')
            ->where('course_id', $request['batch_id'])
            ->pluck('student_id');
 
        // Get User objects for each student
        $students = Student::whereIn('id', $studentIds)->get();

        $admins = Admin::all();
        $staffMembers = StaffModel::all();
          $course = Course::with('teachers')->find($request['batch_id']);
          // Add teachers' details to the study material
        $teachersList = $course->teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email, // Add any other relevant fields
            ];
        });

          Log::info('Sending notifications to students', ['students' => $students->pluck('id')]);

        // Send notifications to the students
        foreach ($students as $student) {
            $student->notify(new StudyMaterial($studyMaterial));
        } 

          foreach ($teachersList as $teacher) {
            // Assuming you have a notification class for notifying teachers about study materials
            $teacherModel = Teacher::find($teacher['id']);
            if ($teacherModel) {
                $teacherModel->notify(new StudyMaterialNotification($studyMaterial));
            }
        } 

         foreach ($admins as $admin) {
            $admin->notify(new StudyMaterial($studyMaterial));
        }

        // Send notifications to staff members
        foreach ($staffMembers as $staff) {
            $staff->notify(new StudyMaterial($studyMaterial));
        }
 

        // Return success response
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Study material saved successfully',
        ], 200); 

    } catch (\Exception $e) {
        // Return error response in case of exception
        return response()->json([
            'status' => 'error',
            'code' => 500,
            'message' => 'Failed to save study material',
            'error' => $e->getMessage(),
        ], 500);
    }
}


// -------------------------------------------------------------------------------
// DOWNLOADS STUDY MATERIALS-------------------------------------------------------------
// -------------------------------------------------------------------------------


public function downloadMaterial(Request $request)
{
    $id = $request->id;
    $filePath = $request->url;
 
    // Decode the file path from URL encoding
    $filePath = urldecode($filePath);

    // Find the study material by ID
    $studyMaterial = StudyMaterials::find($id);

    if (!$studyMaterial) {
        \Log::error("Study material not found for ID: $id");
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Study material not found.',
        ], 404);
    }

    // Decode the material paths from JSON
    $materialPaths = json_decode($studyMaterial->material_path, true);

    if (empty($materialPaths)) {
        \Log::error("No files found for download in study material ID: $id");
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'No files found for download.',
        ], 404);
    }
 
    // Check if the file path exists in the material_paths JSON
    if (!in_array($filePath, $materialPaths)) {
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'File not found in study material paths.',
        ], 404);
    }

    // Check if the material is a URL
    if (filter_var($filePath, FILTER_VALIDATE_URL)) {
        return redirect()->away($filePath);
    }

    // Construct the full file path based on storage configuration
    $fullFilePath = storage_path('app/' . $filePath);

    // Check if the file exists in storage
    if (file_exists($fullFilePath)) {
        // Download the file
        return response()->download($fullFilePath);
    }

    return response()->json([
        'status' => 'error',
        'code' => 404,
        'message' => 'File not found in storage: ' . $filePath,
    ], 404);
}



// --------------------------------------------------------------------------------------
// LISTS OF  STUDY MATERIALS-------------------------------------------------------------
// --------------------------------------------------------------------------------------

public function index()
{
    try {
        // Retrieve all study materials, sorted by created_at in descending order
        $studyMaterials = StudyMaterials::orderBy('created_at', 'desc')->get();

        // Decode JSON data for each study material and encode file paths
        $studyMaterials->transform(function ($studyMaterial) {
            $materialPaths = json_decode($studyMaterial->material_path);

            if (is_array($materialPaths)) {
                $studyMaterial->material_path = array_map('urlencode', $materialPaths);
            } else {
                $studyMaterial->material_path = [];
            }

            return $studyMaterial;
        });

        // Return success response with study materials data
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $studyMaterials,
        ], 200);

    } catch (\Exception $e) {
        // Return error response in case of exception
        return response()->json([
            'status' => 'error',
            'code' => 500,
            'message' => 'Failed to retrieve study materials',
            'error' => $e->getMessage(),
        ], 500);
    }
}

 
// --------------------------------------------------------------------------------------
// LISTS OF  STUDY MATERIALS FOR STUDENT FILTER BY COURSE -------------------------------
// --------------------------------------------------------------------------------------

public function studentMaterials(Request $request,$course_id)
{
    try { 

        // Get the batch_id from the request
        $course = Course::find($course_id);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }

        // Retrieve all study materials for the given batch ID, sorted by created_at in descending order
        $studyMaterials = StudyMaterials::where('batch_id', $course_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Decode JSON data for each study material and encode file paths
        $studyMaterials->transform(function ($studyMaterial) {
            $materialPaths = json_decode($studyMaterial->material_path);

            if (is_array($materialPaths)) {
                $studyMaterial->material_path = array_map('urlencode', $materialPaths);
            } else {
                $studyMaterial->material_path = [];
            }

            return $studyMaterial;
        });


        // Return success response with study materials data
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $studyMaterials,
        ], 200);

    } catch (\Exception $e) {
        // Return error response in case of exception
        return response()->json([
            'status' => 'error',
            'code' => 500,
            'message' => 'Failed to retrieve study materials',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// public function studentMaterials(Request $request, $course_id)
// {
//     try {
//         // Get the course by ID
//         $course = Course::find($course_id);
//         if (!$course) {
//             return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
//         }

//         // Retrieve all study materials for the given course ID, sorted by created_at in descending order
//         $studyMaterials = StudyMaterials::where('batch_id', $course_id)
//             ->orderBy('created_at', 'desc')
//             ->get();

//         // Decode JSON data for each study material and decode file paths
//         $studyMaterials->transform(function ($studyMaterial) {
//             $materialPaths = json_decode($studyMaterial->material_path);

//             if (is_array($materialPaths)) {
//                 $studyMaterial->material_path = array_map('urldecode', $materialPaths);
//             } else {
//                 $studyMaterial->material_path = [];
//             }

//             return [
//                 'id' => $studyMaterial->id,
//                 'title' => $studyMaterial->title,
//                 'description' => $studyMaterial->description,
//                 'material_path' => $studyMaterial->material_path,
//                 'created_at' => $studyMaterial->created_at,
//                 'updated_at' => $studyMaterial->updated_at,
//             ];
//         });

//         // Return success response with study materials data
//         return response()->json([
//             'status' => 'success',
//             'code' => 200,
//             'data' => $studyMaterials,
//         ], 200);
//     } catch (\Exception $e) {
//         // Return error response in case of exception
//         return response()->json([
//             'status' => 'error',
//             'code' => 500,
//             'message' => 'Failed to retrieve study materials',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }

}
