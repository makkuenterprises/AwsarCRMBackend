<?php

namespace App\Http\Controllers;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response; // Import Response facade for returning JSON responses
use App\Models\Admin;
use App\Models\StaffModel;
use App\Models\Teacher;

class Notification extends Controller
{
    // 
    public function fetchNotifications($studentId)
{
    // Fetch the student by ID 
    $student = Student::find($studentId);

    if (!$student) {
        return response()->json([
            'status' => 'error',
            'message' => 'Student not found', 
        ], 404); 
    }

    // Fetch notifications for the student 
    $notifications = $student->unreadNotifications()->get();

    return response()->json([
        'status' => 'success',
        'notifications' => $notifications,
    ]);
}

public function markAsRead(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'notification_id' => 'required|string|exists:notifications,id',
        ]);

        // Fetch the student by ID
        $studentId = $validatedData['student_id'];
        $notificationId = $validatedData['notification_id'];
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        }

        // Find the notification by ID
        $notification = $student->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        // Mark the notification as read
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function markAllAsRead(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        // Fetch the student by ID
        $studentId = $validatedData['student_id'];
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        }

        // Get all unread notifications for the student
        $unreadNotifications = $student->unreadNotifications;

        if ($unreadNotifications->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No unread notifications to mark as read',
            ]);
        }

        // Mark all unread notifications as read
        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([ 
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
} 
 

    public function fetchNotificationsByRole(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'userId' => 'required|integer',
                'role' => 'required|in:admin,staff,teacher',
            ]);

            $userId = $validatedData['userId'];
            $role = $validatedData['role'];

            switch ($role) {
                case 'admin':
                    $user = Admin::find($userId);
                    break;
                case 'staff':
                    $user = StaffModel::find($userId);
                    break;
                case 'teacher':
                    $user = Teacher::find($userId);
                    break;
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid role specified',
                    ], 400);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => ucfirst($role) . ' not found',
                ], 404);
            }

            // Fetch notifications for the user
            $notifications = $user->unreadNotifications()->get();

            return response()->json([
                'status' => 'success',
                'notifications' => $notifications,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function markAsReadByrole(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'role' => 'required|in:admin,staff,teacher',
            'notification_id' => 'required|string|exists:notifications,id',
        ]);

        $userId = $validatedData['user_id'];
        $role = $validatedData['role'];
        $notificationId = $validatedData['notification_id'];

        switch ($role) {
            case 'admin':
                $user = Admin::find($userId);
                break;
            case 'staff':
                $user = StaffModel::find($userId);
                break;
            case 'teacher':
                $user = Teacher::find($userId);
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid role specified',
                ], 400);
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => ucfirst($role) . ' not found',
            ], 404);
        }

        // Find the notification by ID
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        // Mark the notification as read
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function markAllAsReadforRole(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'role' => 'required|in:admin,staff,teacher',
        ]);

        $userId = $validatedData['user_id'];
        $role = $validatedData['role'];

        switch ($role) {
            case 'admin':
                $user = Admin::find($userId);
                break;
            case 'staff':
                $user = StaffModel::find($userId);
                break;
            case 'teacher':
                $user = Teacher::find($userId);
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid role specified',
                ], 400);
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => ucfirst($role) . ' not found',
            ], 404);
        }

        // Get all unread notifications for the user
        $unreadNotifications = $user->unreadNotifications;

        if ($unreadNotifications->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No unread notifications to mark as read',
            ]);
        }

        // Mark all unread notifications as read
        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}



}
