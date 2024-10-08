<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

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

    //    public function getStudentOverview(Request $request)
    // {
    //     try {
    //         $duration = $request->query('duration', 'month'); // default to monthly data
    //         $data = $this->fetchChartData($duration);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $data,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to fetch student data',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // private function fetchChartData($duration)
    // {
    //     if ($duration === 'week') {
    //         return [
    //             'numberOfStudents' => $this->getWeeklyCounts('total'),
    //             'partialPayment' => $this->getWeeklyCounts('partial'),
    //             'fullPayment' => $this->getWeeklyCounts('full'),
    //             'unpaid' => $this->getWeeklyCounts('unpaid')
    //         ];
    //     } elseif ($duration === 'year') {
    //         return [
    //             'numberOfStudents' => $this->getYearlyCounts('total'),
    //             'partialPayment' => $this->getYearlyCounts('partial'),
    //             'fullPayment' => $this->getYearlyCounts('full'),
    //             'unpaid' => $this->getYearlyCounts('unpaid')
    //         ];
    //     } else {
    //         return [
    //             'numberOfStudents' => $this->getMonthlyCounts('total'),
    //             'partialPayment' => $this->getMonthlyCounts('partial'),
    //             'fullPayment' => $this->getMonthlyCounts('full'),
    //             'unpaid' => $this->getMonthlyCounts('unpaid')
    //         ];
    //     }
    // }

    // private function getMonthlyCounts($type)
    // {
    //     $counts = [];
    //     for ($month = 1; $month <= 12; $month++) {
    //         $query = Student::whereMonth('created_at', $month);
    //         if ($type !== 'total') {
    //             $query->where('payment_status', $type);
    //         }
    //         $counts[] = $query->count();
    //     }
    //     return $counts;
    // }

    // private function getWeeklyCounts($type)
    // {
    //     $counts = [];
    //     for ($week = 1; $week <= 52; $week++) {
    //         $query = Student::whereRaw('WEEKOFYEAR(created_at) = ?', [$week]);
    //         if ($type !== 'total') {
    //             $query->where('payment_status', $type);
    //         }
    //         $counts[] = $query->count();
    //     }
    //     return $counts;
    // }

    // private function getYearlyCounts($type)
    // {
    //     $counts = [];
    //     $currentYear = date('Y');
    //     for ($year = $currentYear - 4; $year <= $currentYear; $year++) { // last 5 years
    //         $query = Student::whereYear('created_at', $year);
    //         if ($type !== 'total') {
    //             $query->where('payment_status', $type);
    //         }
    //         $counts[] = $query->count();
    //     }
    //     return $counts;
    // }




}
