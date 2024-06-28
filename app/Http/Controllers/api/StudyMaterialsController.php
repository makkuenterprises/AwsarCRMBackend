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

public function downloadMaterial($id, $filePath)
{
    dd('test');
    // Log the incoming request for debugging
    \Log::info("Download requested for ID: $id, File Path: $filePath");

    // Decode the file path from URL encoding
    $filePath = urldecode($filePath);

    // Log the decoded file path
    \Log::info("Decoded File Path: $filePath");

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

    // Log the material paths
    \Log::info("Material Paths: " . print_r($materialPaths, true));

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
        \Log::error("File path $filePath not found in study material paths for ID: $id");
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'File not found in study material paths.',
        ], 404);
    }

    // Check if the material is a URL
    if (filter_var($filePath, FILTER_VALIDATE_URL)) {
        \Log::info("Redirecting to URL: $filePath");
        return redirect()->away($filePath);
    }

    // Construct the full file path based on storage configuration
    $fullFilePath = storage_path('app/' . $filePath);

    // Log the full file path
    \Log::info("Full File Path: $fullFilePath");

    // Check if the file exists in storage
    if (Storage::exists($filePath)) {
        // Determine the MIME type based on the file extension
        $mimeType = Storage::mimeType($filePath);

        // Log the mime type
        \Log::info("MIME Type: $mimeType");

        // Download the file
        return response()->download($fullFilePath, basename($filePath), ['Content-Type' => $mimeType]);
    }

    \Log::error("File not found in storage: $filePath");
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

        // Retrieve all study materials for the given batch ID, sorted by created_at in descending order
        $studyMaterials = StudyMaterials::where('batch_id', $course_id)
            ->orderBy('created_at', 'desc')
            ->get();

          // Decode JSON data for each study material
        $studyMaterials->transform(function ($studyMaterial) {
            $studyMaterial->material_paths = json_decode($studyMaterial->material_paths);
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
}
