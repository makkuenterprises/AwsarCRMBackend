<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

use App\Models\Details;


class DetailsController extends Controller
{

// ------------------------------------------------------------------------------------------------
// DETAILS ADD
// ------------------------------------------------------------------------------------------------

public function index(Request $request){

    try {

    $validator = $request->validate([
        'logo' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for logo
        'side_logo' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for side logo
        'favicon_icon' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for favicon icon
        'business_name' => 'required|string|max:255', // Validation for business name as a string up to 255 characters
        'email' => 'required|email|max:255', // Validation for email format and maximum length
        'smtp_host' => 'required|string|max:255', // Validation for SMTP host as a string up to 255 characters
        'smtp_ports' => 'nullable|array', // Validate smtp_ports as an array
        'smtp_ports.*' => 'integer', // Ensure each port is an integer
        'smtp_username' => 'required|string|max:255', // Validation for SMTP username as a string up to 255 characters
        'smtp_password' => 'required|string|max:255', // Validation for SMTP password as a string up to 255 characters
  
    ]);
         // Handle file uploads if provided
        $filePaths = [];
        foreach (['logo', 'side_logo', 'favicon_icon'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filePath = $file->store('uploads', 'public'); 
                $filePaths[$field] = $filePath;
            }
        }
        // Convert smtp_ports array to JSON for storage
        if ($request->has('smtp_ports')) {
            $validator['smtp_ports'] = json_encode($validator['smtp_ports']);
        }

        // Create a new Details model instance
        $details = new Details();
        $details->logo = $filePaths['logo'];
        $details->side_logo = $filePaths['side_logo'];
        $details->favicon_icon = $filePaths['favicon_icon'];
        $details->business_name = $validator['business_name'];
        $details->email = $validator['email'];
        $details->smtp_host = $validator['smtp_host'];
        $details->smtp_ports = $validator['smtp_ports'];
        $details->smtp_username = $validator['smtp_username'];
        $details->smtp_password = $validator['smtp_password'];
        $details->save();

     // Return success response with HTTP status code 201 (Created)
        return response()->json(['message' => 'Details saved successfully'], Response::HTTP_CREATED);
    } catch (ValidationException $e) {
        // Return validation errors with HTTP status code 422 (Unprocessable Entity)
        return response()->json(['errors' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
    } catch (\Exception $e) {
        // Return error response with HTTP status code 500 (Internal Server Error) or handle specific exceptions
        return response()->json(['error' => 'Failed to save details. ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}

// ------------------------------------------------------------------------------------------------
// DETAILS SHOW
// ------------------------------------------------------------------------------------------------

public function show($id)
{
    try {
        // Find the existing Details model instance by ID
        $details = Details::findOrFail($id);

        // Add base URL to image paths
        $baseUrl = asset('storage');

        // Prepare the specific details to return
        $response = [
            'id' => $details->id,
            'logo' => $details->logo ? $baseUrl . '/' . $details->logo : null,
            'side_logo' => $details->side_logo ? $baseUrl . '/' . $details->side_logo : null,
            'favicon_icon' => $details->favicon_icon ? $baseUrl . '/' . $details->favicon_icon : null,
            'business_name' => $details->business_name,
            'email' => $details->email,
            'smtp_host' => $details->smtp_host,
            'smtp_ports' => $details->smtp_ports ? json_decode($details->smtp_ports) : [],
            'smtp_username' => $details->smtp_username,
            'smtp_password' => $details->smtp_password
        ];

        // Return success response with specific details data
        return response()->json([
            'success' => true,
            'message' => 'Details retrieved successfully',
            'data' => $response
        ], Response::HTTP_OK);
    } catch (\Exception $e) {
        // Return error response with HTTP status code 404 (Not Found)
        return response()->json([
            'success' => false,
            'message' => 'Details not found. ' . $e->getMessage()
        ], Response::HTTP_NOT_FOUND);
    }
}




// ------------------------------------------------------------------------------------------------
// DETAILS UPDATE
// ------------------------------------------------------------------------------------------------


public function update(Request $request, $id)
{
    try {
        // Validate incoming request data
        $validator = $request->validate([
            'logo' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for logo
            'side_logo' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for side logo
            'favicon_icon' => 'required|file|mimes:jpeg,png,svg,webp,jpg|max:2048', // File upload validation for favicon icon
            'business_name' => 'required|string|max:255', // Validation for business name as a string up to 255 characters
            'email' => 'required|email|max:255', // Validation for email format and maximum length
            'smtp_host' => 'required|string|max:255', // Validation for SMTP host as a string up to 255 characters
            'smtp_ports' => 'required|array', // Validate smtp_ports as an array
            'smtp_ports.*' => 'integer', // Ensure each port is an integer
            'smtp_username' => 'required|string|max:255', // Validation for SMTP username as a string up to 255 characters
            'smtp_password' => 'required|string|max:255', // Validation for SMTP password as a string up to 255 characters
        ]);

        // Find the existing Details model instance by ID
        $details = Details::findOrFail($id);

        // Handle file uploads if provided
        $filePaths = [];
        foreach (['logo', 'side_logo', 'favicon_icon'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filePath = $file->store('uploads', 'public'); 
                $filePaths[$field] = $filePath;
            }
        }

        // Update the Details model instance with validated data and file paths
        if (isset($filePaths['logo'])) {
            $details->logo = $filePaths['logo'];
        }
        if (isset($filePaths['side_logo'])) {
            $details->side_logo = $filePaths['side_logo'];
        }
        if (isset($filePaths['favicon_icon'])) {
            $details->favicon_icon = $filePaths['favicon_icon'];
        }
        $details->business_name = $validator['business_name'];
        $details->email = $validator['email'];
        $details->smtp_host = $validator['smtp_host'];

        // Convert smtp_ports array to JSON for storage
        if ($request->has('smtp_ports')) {
            $details->smtp_ports = json_encode($validator['smtp_ports']);
        }

        $details->smtp_username = $validator['smtp_username'];
        $details->smtp_password = $validator['smtp_password'];
        $details->save();

        // Return success response with HTTP status code 200 (OK)
        return response()->json(['status'=>true, 'code'=>200, 'message' => 'Details updated successfully'], Response::HTTP_OK);
    } catch (ValidationException $e) {
        // Return validation errors with HTTP status code 422 (Unprocessable Entity)
        return response()->json(['errors' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
    } catch (\Exception $e) {
        // Return error response with HTTP status code 500 (Internal Server Error) or handle specific exceptions
        return response()->json(['status'=>false, 'code'=>500,'error' => 'Failed to update details. ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}