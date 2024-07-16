<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notice;
use DB;
use App\Models\Course;
 


class NotificationController extends Controller
{
 
public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|min:1|max:255',
        'description' => ['required', 'string', 'min:1', 'max:250'],
        'sendTo' => ['required', 'string', 'min:1', 'max:250'],
        'batch' => 'nullable|array',
        'batch.*' => 'string|exists:courses,name'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        $notification = new Notice();
        $notification->title = $request->input('title');
        $notification->description = $request->input('description');
        $notification->sendTo = $request->input('sendTo');
        $notification->batch = json_encode($request->input('batch')); // Store as JSON
        $notification->save();

        // Encode batch data to JSON for consistent response
        $notification->batch = json_decode($notification->batch, true); // Decode JSON to array

        return response()->json([
            'status' => true, 
            'code' => 200, 
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 200);
    } catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while creating notification',
            'data' => $data
        ], 500);
    }
}


public function list() 
{
    
     try {
      // Retrieve all notifications including the newly created one
        $notifications = Notice::orderBy('id', 'asc')->get();

        // Transform batch JSON data back to array format for each notification
        $notifications->transform(function ($notification) {
            $notification->batch = json_decode($notification->batch, true); // Decode JSON to array
            return $notification;
        });

      
        return response()->json([
            'status' => true,
            'code' => 200,
            'notifications' => $notifications,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while fetching notifications',
            'error' => $e->getMessage(),
        ], 500);
    }
}


   public function studentNoticelist(Request $request) 
{
    try {
        // Get the student_id from the request
        $studentId = $request->input('student_id');

        // Get the list of course names the student is enrolled in
        $enrolledCourseNames = DB::table('courses')
            ->join('courses_enrollements', 'courses.id', '=', 'courses_enrollements.course_id')
            ->where('courses_enrollements.student_id', $studentId)
            ->pluck('courses.name')
            ->toArray();

        // Retrieve all notifications
        $notifications = Notice::orderBy('id', 'asc')->get();

        // Filter notifications based on the student's enrolled courses
        $filteredNotifications = $notifications->filter(function ($notification) use ($enrolledCourseNames) {
            $batch = json_decode($notification->batch, true);
            if (is_array($batch)) {
                // Check if there is any intersection between the batch and enrolled courses
                return !empty(array_intersect($batch, $enrolledCourseNames));
            }
            return false;
        }); 

        // Transform batch JSON data back to array format for each notification
        $filteredNotifications->transform(function ($notification) {
            $notification->batch = json_decode($notification->batch, true); // Decode JSON to array
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'title' => $notification->created_at,
                'description' => strip_tags($notification->description), // Remove HTML tags from description
            ];
        }); 

        return response()->json([
            'status' => true,
            'code' => 200,
            'notifications' => $filteredNotifications->values(), // Reset array keys
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while fetching notifications',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
