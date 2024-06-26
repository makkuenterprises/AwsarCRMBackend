<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Models\StudyMaterials;

class StudyMaterialsController extends Controller
{

public function store(Request $request)
{
    // Validate the request data
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'batch_id' => 'required|exists:batches,id',
        'description' => 'nullable|string',
        'uploaded_date' => 'nullable|date_format:d/m/Y', // Make uploaded_date nullable
        'material' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        'material_url' => 'nullable|url',
    ]);

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
        $studyMaterial->title = $validated['title'];
        $studyMaterial->batch_id = $validated['batch_id'];
        $studyMaterial->description = $validated['description'];
        $studyMaterial->uploaded_date = Carbon::now('Asia/Kolkata')->format('d-m-y');


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


// download materials-----------------------------------------------------------------------
public function downloadMaterial($id)
{
    // Find the study material by ID
    $studyMaterial = StudyMaterial::find($id);

    if (!$studyMaterial) {
        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Study material not found.',
        ], 404);
    }

    $materialPath = $studyMaterial->material_path;

    // Check if materialPath is a URL
    if (filter_var($materialPath, FILTER_VALIDATE_URL)) {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'URL material retrieved successfully.',
            'data' => [
                'title' => $studyMaterial->title,
                'description' => $studyMaterial->description,
                'url' => $materialPath,
            ],
        ], 200);
    }

    // Check if the file exists in storage
    if (Storage::exists($materialPath)) {
        return Storage::download($materialPath, $studyMaterial->title);
    }

    return response()->json([
        'status' => 'error',
        'code' => 404,
        'message' => 'File not found in storage.',
    ], 404);
}
}
