<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
use DB;


class NotificationController extends Controller
{

public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|min:1|max:255',
        'description' => ['required', 'string', 'min:1', 'max:250'],
        'sendTo' => ['required', 'string', 'min:1', 'max:250'],
        'batch' => 'nullable|array',
        'batch.*' => 'integer|exists:courses,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        $notification = new Notification();
        $notification->title = $request->input('title');
        $notification->description = $request->input('description');
        $notification->sendTo = $request->input('sendTo');
        $notification->batch = json_encode($request->input('batch')); // Store as JSON
        $notification->save();

        return response()->json(['status' => true, 'code' => 200, 'message' => 'Notification created successfully', 'notification' => $notification], 200);
    } catch (Exception $e) {
        $data = ['error' => $e->getMessage()];
        return response()->json(['status' => false, 'code' => 500, 'message' => 'An error occurred while creating notification', 'data' => $data], 500);
    }
}


 public function list()
{
    // Join the notifications and courses tables
    $notifications = DB::table('notifications')
        ->leftJoin('courses', 'notifications.batch', '=', 'courses.id')
          ->select(
            'notifications.id',
            'notifications.title',
            'notifications.description',
            'notifications.status',
            'notifications.created_at',
            'notifications.updated_at',
            'notifications.sendTo',
            'notifications.batch',
            'courses.name as course_name', // Alias the name column from courses
            'courses.id as course_id' // Alias the id column from courses
        )
        ->orderByDesc('notifications.id')
        ->get();
     

     // Return JSON response
    return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $notifications // Return notifications directly
    ]);
}


}
