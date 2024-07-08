<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
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
    
     try {
        $notifications = DB::table('notifications')
            ->leftJoin('courses', 'notifications.batch', '=', 'courses.id')
            ->select('notifications.*', 'courses.name as batch_name')
            ->get();

        // Group notifications by their IDs and collect batch names
        $notificationsGrouped = [];
        foreach ($notifications as $notification) {
            $notificationId = $notification->id;
            if (!isset($notificationsGrouped[$notificationId])) {
                $notificationsGrouped[$notificationId] = [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->description,
                    'batch_names' => [],
                ];
            }
            // Collect batch names
            if ($notification->batch_name) {
                $notificationsGrouped[$notificationId]['batch_names'][] = $notification->batch_name;
            }
        }

        // Convert to indexed array
        $notificationsTransformed = array_values($notificationsGrouped);

        return response()->json([
            'status' => true,
            'code' => 200,
            'notifications' => $notificationsTransformed,
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
