<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\StudyMaterials;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use DB;


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
        'material' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        'material_url' => 'nullable|url',
    ]);
     if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }

    // Ensure either material or material_url is provided, but not both
    if (!$request->hasFile('material') && !$request->input('material_url')) {
        return response()->json([
            'status' => 'error',
            'code' => 400,
            'message' => 'Please provide either a file or a URL for the study material.',
        ], 400);
    }

    if ($request->hasFile('material') && $request->input('material_url')) {
        return response()->json([
            'status' => 'error',
            'code' => 400,
            'message' => 'Please provide only one of either a file or a URL for the study material.',
        ], 400);
    }

    try {
        // Create a new StudyMaterial instance
        $studyMaterial = new StudyMaterials();
        $studyMaterial->title = $request['title'];
        $studyMaterial->batch_id = $request['batch_id'];
        $studyMaterial->description = $request['description'];
        $studyMaterial->uploadedDate = Carbon::now('Asia/Kolkata')->format('d-m-y');


        // Handle file upload
        if ($request->hasFile('material')) {
            $path = $request->file('material')->store('study_materials');
            $studyMaterial->material_path = $path;
        } elseif ($request->input('material_url')) {
            // Store the URL if provided
            $studyMaterial->material_path = $request->input('material_url');
        }

        // Save the study material
        $studyMaterial->save();

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

public function downloadMaterial($id)
{

    // Find the study material by ID
    $studyMaterial = StudyMaterials::find($id);

    if (!$studyMaterial) {
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Study material not found.',
        ], 404);
    }

    $materialPath = $studyMaterial->material_path;

    // Check if the material is a URL
    if (filter_var($materialPath, FILTER_VALIDATE_URL)) {
        return redirect()->away($materialPath);
    } 


    // Check if the file exists in storage
    if (Storage::exists($materialPath)) {
    $response = Storage::download($materialPath, $studyMaterial->title);
    $response->headers->set('Content-Type', 'application/pdf'); // set any additional headers
    return $response;
    }

    return response()->json([
        'status' => 'error',
        'code' => 404,
        'message' => 'File not found in storage.',
    ], 404);
}


// --------------------------------------------------------------------------------------
// LISTS OF  STUDY MATERIALS-------------------------------------------------------------
// --------------------------------------------------------------------------------------

public function index()
{
    try {
        // Retrieve all study materials
        $studyMaterials = StudyMaterials::all();

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
// LISTS OF  STUDY MATERIALS FOR STUDENT FILTER BY COURSE --------------------------------
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

        // Retrieve study materials for the specified batch_id
        $studyMaterials = StudyMaterials::where('batch_id', $course_id)->get();

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
}
