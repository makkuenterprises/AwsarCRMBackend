<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        'favicon_icon' => 'required|file|mimes:jpeg,png|max:2048', // File upload validation for favicon icon
        'business_name' => 'required|string|max:255', // Validation for business name as a string up to 255 characters
        'email' => 'required|email|max:255', // Validation for email format and maximum length
        'smtp_host' => 'required|string|max:255', // Validation for SMTP host as a string up to 255 characters
        'smtp_ports' => 'required|array', // Validation for SMTP ports as an array (optional)
        'smtp_ports.*' => 'required|integer', // Validation for each element in the SMTP ports array as an integer (optional)
        'smtp_username' => 'required|string|max:255', // Validation for SMTP username as a string up to 255 characters
        'smtp_password' => 'required|string|max:255', // Validation for SMTP password as a string up to 255 characters
  
    ]);
         // Handle file uploads if provided
        $filePaths = [];
        foreach (['logo', 'side_logo', 'favicon_icon'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filePath = $file->store('uploads', 'public'); // Example storage path
                $filePaths[$field] = $filePath;
            }
        }

        // Handle smtp_ports conversion to array if provided as a comma-separated string
        if ($request->has('smtp_ports')) {
            $smtpPortsArray = explode(',', $validator['smtp_ports']);
            $validator['smtp_ports'] = $smtpPortsArray;
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



}