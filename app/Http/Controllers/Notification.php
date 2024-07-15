<?php

namespace App\Http\Controllers;
use App\Models\Student;
use Illuminate\Http\Request;

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
   $notifications = $student->notifications()->get();

    return response()->json([
        'status' => 'success',
        'notifications' => $notifications,
    ]);
}
}
