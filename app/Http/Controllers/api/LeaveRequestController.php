<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class LeaveRequestController extends Controller
{
// public function __construct()
//     {
//         $this->middleware('api');
//     }
// ============================================================================================================
// create  -----------------------------------------------------------------------------------------------
// ============================================================================================================


// public function handleLeaveRequestCreate(Request $request)
// {
//     // Validate the request data
//     $validation = Validator::make($request->all(), [
//         'start_date' => ['required', 'string'],
//         'end_date' => ['nullable', 'string'],
//         'user_id' => ['required', 'string'],
//         'role' => ['required', 'string'],
//         'message' => ['required', 'string', 'min:1', 'max:1000'],
//     ]);

//     // Check if validation fails
//     if ($validation->fails()) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Validation Error',
//             'errors' => $validation->errors(),
//         ], 400);
//     }

//     try {
//         // Parse and format the dates
//         $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->format('Y-m-d');
//         $endDate = $request->input('end_date') ? Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->format('Y-m-d') : null;
           
//         // Create a new leave request
//         $leave_request = new LeaveRequest();
//         $leave_request->teacher_id = $request->input('user_id'); // Assuming teacher_id is stored based on authenticated user
//         $leave_request->start_date = $startDate;
//         $leave_request->end_date = $endDate;
//         $leave_request->role = $request->input('role');
//         $leave_request->message = $request->input('message');
//         $result = $leave_request->save();

//         if ($result) {
//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Leave Request Submitted',
//                 'data' => $leave_request, // Optionally return the created leave request data
//             ], 201); // HTTP status code 201 for resource created
//         } else {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Internal Server Error',
//             ], 500);
//         }
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Exception Occurred',
//             'error' => $e->getMessage(),
//         ], 500);
//     } 
// }

public function handleLeaveRequestCreate(Request $request)
{
    // Validate the request data
    $validation = Validator::make($request->all(), [
        'start_date' => ['required', 'date_format:d/m/Y'],
        'end_date' => ['nullable', 'date_format:d/m/Y', 'after_or_equal:start_date'],
        'user_id' => ['required', 'string'],
        'role' => ['required', 'string'],
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
        // Parse and format the dates
        $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->format('Y-m-d');
        $endDate = $request->input('end_date') ? Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->format('Y-m-d') : null;
        if($request->input('end_date')){

        // Check if there is already a leave request overlapping with the specified dates for the same user and role
        $existingLeaveRequest = LeaveRequest::where('teacher_id', $request->input('user_id'))
                                            ->where('role', $request->input('role'))
                                            ->where(function ($query) use ($startDate, $endDate) {
                                                $query->where(function ($q) use ($startDate, $endDate) {
                                                    $q->where('start_date', '<=', $startDate)
                                                        ->where('end_date', '>=', $startDate);
                                                })
                                                ->orWhere(function ($q) use ($startDate, $endDate) {
                                                    $q->where('start_date', '<=', $endDate)
                                                        ->where('end_date', '>=', $endDate);
                                                })
                                                ->orWhere(function ($q) use ($startDate, $endDate) {
                                                    $q->where('start_date', '>=', $startDate)
                                                        ->where('end_date', '<=', $endDate);
                                                });
                                            })
                                            ->exists();



        if ($existingLeaveRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request already exists for the specified date range and role.',
            ], 400);
        }
    }
                                                // Check if there is already a leave request overlapping with the specified single start date for the same user and role
$existingLeaveRequestforSingle = LeaveRequest::where('teacher_id', $request->input('user_id'))
                                    ->where('role', $request->input('role'))
                                    ->where(function ($query) use ($startDate) {
                                        $query->where(function ($q) use ($startDate) {
                                            $q->where('start_date', '<=', $startDate)
                                                ->where('end_date', '>=', $startDate);
                                        });
                                    })
                                    ->exists();
if ($existingLeaveRequestforSingle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request already exists for the specified date range and role.',
            ], 400);
        }
        // Create a new leave request
        $leave_request = new LeaveRequest();
        $leave_request->teacher_id = $request->input('user_id'); // Assuming teacher_id is stored based on authenticated user
        $leave_request->start_date = $startDate;
        $leave_request->end_date = $endDate;
        $leave_request->role = $request->input('role');
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

public function handleLeaveRequestUpdate(Request $request)
{
    // Validate the request data
    $validation = Validator::make($request->all(), [
        'status' => ['required', 'string'], 
        'id' => ['required', 'string'],
        'role' => ['required', 'string'],
        'name' => ['required', 'string'], 
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
        // Find the leave request by ID or fail if not found
        $leave_request = LeaveRequest::findOrFail($request->input('id'));

        // Concatenate the guard name and user name for approved_by field
        $userName = $request->input('name');
        $role = $request->input('role');
          $approvedBy = $userName . ' (' . $role . ')';

        // Update leave request status and approved_by 
        $leave_request->status = $request->input('status');
        $leave_request->approved_by = $approvedBy;

        // Save the leave request
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
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Leave Request not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception Occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
} 

 



 public function handleLeaveRequestUpdateRemark(Request $request)
{
    // Validate the request data
    $validation = Validator::make($request->all(), [
        'remark' => ['required', 'string'], 
        'id' => ['required', 'string'],
        'role' => ['required', 'string'],
        'name' => ['required', 'string'], 
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
        // Find the leave request by ID or fail if not found
        $leave_request = LeaveRequest::findOrFail($request->input('id'));

        // Concatenate the guard name and user name for approved_by field
        $userName = $request->input('name');
        $role = $request->input('role');
        $rejectedBy = $userName . ' (' . $role . ')'; 

        // Update leave request status and approved_by 
        $leave_request->remark = $request->input('remark');
        $leave_request->status = 'DENIED'; 

        $leave_request->rejected_by = $rejectedBy;

        // Save the leave request
        $result = $leave_request->save();

        // Check if update was successful 
        if ($result) { 
            return response()->json([
                'status' => 'success',
                'message' => 'Remark Done',
                'data' => $leave_request, // Optionally return updated leave request data
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Leave Request not found',
        ], 404);
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
        // Retrieve all leave requests with conditional join based on role
        $leave_requests = LeaveRequest::select('leave_requests.*')
            ->leftJoin('staff_models', function ($join) {
                $join->on('leave_requests.teacher_id', '=', 'staff_models.id')
                    ->where('leave_requests.role', '=', 'staff');
            })
            ->leftJoin('teachers', function ($join) {
                $join->on('leave_requests.teacher_id', '=', 'teachers.id')
                    ->where('leave_requests.role', '=', 'teacher');
            })
            ->selectRaw('COALESCE(staff_models.name, teachers.name) as user_name')
            ->orderBy('leave_requests.created_at', 'asc')
            ->get();

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


// public function viewLeaveRequestListForFaculty(Request $request)
// {
//     try {

//         $validation = Validator::make($request->all(), [
//         'user_id' => ['required', 'string'],
//         'role' => ['required', 'string'],
//     ]);

//     // Check if validation fails
//     if ($validation->fails()) {
//         return response()->json([ 
//             'status' => 'error',
//             'message' => 'Validation Error',
//             'errors' => $validation->errors(),
//         ], 400);
//     }

//         // Retrieve leave requests for the specified faculty ID
//        $leave_requests = LeaveRequest::where('role', $request->input('role'))
//                               ->where('teacher_id', $request->input('user_id'))
//                               ->orderBy('created_at', 'asc')->get();
                              


//         // Check if leave requests exist
//         if ($leave_requests->isEmpty()) {
//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'No leave requests found for the faculty',
//                 'data' => [],
//             ], 200);
//         }

//         // Return JSON response with leave requests data
//         return response()->json([
//             'status' => 'success',
//             'data' => $leave_requests,
//         ], 200);
//     } catch (\Exception $e) {
//         // Return error response if an exception occurs
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Failed to fetch leave requests',
//             'error' => $e->getMessage(),
//         ], 500); 
//     }
// }
public function viewLeaveRequestListForFaculty(Request $request)
{
    try {
        // Validate the request data
        $validation = Validator::make($request->all(), [
            'user_id' => ['required', 'string'],
            'role' => ['required', 'string'],
        ]);

        // Check if validation fails
        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 400);
        }

        // Retrieve leave requests for the specified faculty ID
        $leaveRequests = LeaveRequest::where('role', $request->input('role'))
                                     ->where('teacher_id', $request->input('user_id'))
                                     ->orderBy('created_at', 'asc')
                                     ->get();

        // Check if leave requests exist
        if ($leaveRequests->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No leave requests found for the faculty',
                'data' => [],
            ], 200);
        }

        // Format the leave requests data
        $formattedLeaveRequests = $leaveRequests->map(function ($request) {
            return [
                'lr@ID' => 'LR@' . $request->id,
                'teacher_id' => $request->teacher_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'message' => $request->message,
                'status' => $request->status,
                'created_at' => $request->created_at->toIso8601String(),
                'updated_at' => $request->updated_at->toIso8601String(),
                'approved_by' => $request->approved_by,
                'role' => $request->role,
                'remark' => $request->remark,
                'rejected_by' => $request->rejected_by
            ];
        });

        // Return JSON response with formatted leave requests data
        return response()->json([
            'status' => 'success',
            'data' => $formattedLeaveRequests,
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
