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
    // Join the notifications, notification_batch, and batches tables
    $notifications = DB::table('notifications')
        ->leftJoin('notification_batch', 'notifications.id', '=', 'notification_batch.notification_id')
        ->leftJoin('courses', 'notification_batch.batch_id', '=', 'courses.id')
        ->select('notifications.*', 'courses.id as batch_id', 'courses.name as batch_name')
        ->orderByDesc('notifications.id')
        ->get();

    // Group notifications by their ID and include batch names
    $groupedNotifications = $notifications->groupBy('id')->map(function ($notificationGroup) {
        $notification = $notificationGroup->first();
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'description' => $notification->description,
            'sendTo' => $notification->sendTo,
            'batches' => $notificationGroup->filter(function ($item) {
                return !is_null($item->batch_id);
            })->map(function ($item) {
                return [
                    'id' => $item->batch_id,
                    'name' => $item->batch_name
                ];
            })->values(),
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at
        ];
    })->values();

    return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $groupedNotifications
    ]);
}

}
