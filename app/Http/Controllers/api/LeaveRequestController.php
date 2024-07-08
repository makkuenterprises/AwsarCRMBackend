<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class LeaveRequestController extends Controller
{

// ============================================================================================================
// create  -----------------------------------------------------------------------------------------------
// ============================================================================================================


public function handleLeaveRequestCreate(Request $request)
{
    // Validate the request data
    $validation = Validator::make($request->all(), [
        'start_date' => ['required', 'string'],
        'end_date' => ['nullable', 'string'],
        'message' => ['required', 'string', 'min:1', 'max:1000'],
    ]);

    // Check if validation fails
    if ($validation->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $validation->errors(),
        ], 400);
    }

    try {
        // Create a new leave request
        $leave_request = new LeaveRequest();
        $leave_request->teacher_id = Auth::user()->id; // Assuming teacher_id is stored based on authenticated user
        $leave_request->start_date = $request->input('start_date');
        $leave_request->end_date = $request->input('end_date');
        $leave_request->message = $request->input('message');
        $result = $leave_request->save();

        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'Leave Request Submitted',
                'data' => $leave_request, // Optionally return the created leave request data
            ], 201); // HTTP status code 201 for resource created
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception Occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// ============================================================================================================
// update status-----------------------------------------------------------------------------------------------
// ============================================================================================================


public function handleLeaveRequestUpdate(Request $request, $id)
{
    // Validate the request data
    $validation = Validator::make($request->all(), [
        'status' => ['required', 'string'],
    ]);

    // Check if validation fails
    if ($validation->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $validation->errors(),
        ], 400);
    }

    try {
        // Find the leave request by ID
        $leave_request = LeaveRequest::find($id);

        // Check if leave request exists
        if (!$leave_request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave Request not found',
            ], 404);
        }

         $userName = Auth::user()->name;

    // Get the name of the guard being used
    $guardName = Auth::getDefaultDriver();

    // Concatenate the guard name and user name
    $approvedBy = $guardName . ': ' . $userName;

        // Update leave request status
        $leave_request->status = $request->input('status');
        $leave_request->approved_by = $approvedBy;  
        $result = $leave_request->save();

        // Check if update was successful
        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'Leave Request Updated',
                'data' => $leave_request, // Optionally return updated leave request data
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception Occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}
 
// ============================================================================================================
// lists status-----------------------------------------------------------------------------------------------
// ============================================================================================================

public function viewLeaveRequestList()
{
    try {
        // Retrieve all leave requests
        $leave_requests = LeaveRequest::all();

        // Return JSON response with leave requests data
        return response()->json([
            'status' => 'success',
            'data' => $leave_requests,
        ], 200);
    } catch (\Exception $e) {
        // Return error response if an exception occurs
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch leave requests',
            'error' => $e->getMessage(),
        ], 500);
    }
}


// list for faculty


public function viewLeaveRequestListForFaculty($facultyId)
{
    try {
        // Retrieve leave requests for the specified faculty ID
        $leave_requests = LeaveRequest::where('teacher_id', $facultyId)->get();

        // Check if leave requests exist
        if ($leave_requests->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No leave requests found for the faculty',
                'data' => [],
            ], 200);
        }

        // Return JSON response with leave requests data
        return response()->json([
            'status' => 'success',
            'data' => $leave_requests,
        ], 200);
    } catch (\Exception $e) {
        // Return error response if an exception occurs
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch leave requests',
            'error' => $e->getMessage(),
        ], 500); 
    }
}
} 
