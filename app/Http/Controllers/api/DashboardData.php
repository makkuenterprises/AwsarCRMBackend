<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;

use App\Models\Teacher;
use App\Models\StaffModel;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;

class DashboardData extends Controller
{
    public function dashboardaData()
    {
        try {
            $teachersCount = Teacher::count();
            $studentsCount = Student::count();
            $staffCount = StaffModel::count();
            $coursesCount = Course::count();

            $partialPaymentStudentsCount = Student::where('payment_status', 'partial')->count();
            $notEnrollStudentsCount = Student::where('course', 'Not Enrolled')->count();
            $fullPaymentStudentsCount = Student::where('payment_status', 'full')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'teachersCount' => $teachersCount,
                    'studentsCount' => $studentsCount,
                    'staffCount' => $staffCount,
                    'coursesCount' => $coursesCount,
                    'partialPaymentStudentsCount' => $partialPaymentStudentsCount,
                    'fullPaymentStudentsCount' => $fullPaymentStudentsCount,
                    'notEnrollStudentsCount' => $notEnrollStudentsCount,
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