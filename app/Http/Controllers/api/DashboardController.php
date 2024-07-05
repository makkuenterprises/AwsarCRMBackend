<?php

namespace App\Http\Controllers;
use App\Models\Teacher;
use App\Models\StaffModel;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request; 

class DashboardController extends Controller
{
    public function counts()
    {
        try {
            $teachersCount = Teacher::where('role', 'teacher')->count();
            $studentsCount = Student::where('role', 'student')->count();
            $staffCount = StaffModel::where('role', 'staff')->count();
            $coursesCount = Course::count();

            $partialPaymentStudentsCount = Student::where('payment_status', 'partial')
                                        ->count();

            $notEnrollStudentsCount = Student::where('course', 'Not Enrolled')
                                        ->count();

             $fullPaymentStudentsCount = Student::where('payment_status', 'full')
                                        ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'teachersCount' => $teachersCount,
                    'studentsCount' => $studentsCount,
                    'staffCount' => $staffCount,
                    'coursesCount' => $coursesCount,
                    'partialPaymentStudentsCount' => $partialPaymentStudentsCount,
                    'fullPaymentStudentsCount' => $pendingStudentsCount,
                    'pendingStudentsCount' => $pendingStudentsCount,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch counts',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}