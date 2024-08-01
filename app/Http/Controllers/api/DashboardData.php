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
public function getStudentOverview(Request $request)
{
    try {
        $duration = $request->query('duration', 'month'); // default to monthly data
        $data = $this->fetchChartData($duration);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch student data',
            'message' => $e->getMessage(),
        ], 500);
    }
}
private function fetchChartData($duration)
{
    switch ($duration) {
        case 'week':
            return $this->getWeeklyData();
        case 'year':
            return $this->getYearlyData();
        case 'all_years':
            return $this->getAllYearlyData();
        default:
            return $this->getMonthlyData();
    }
}
private function getMonthlyData()
{
    $counts = [];
    $months = [];
    $currentYear = date('Y');

    for ($month = 1; $month <= 12; $month++) {
        $monthName = date('F', mktime(0, 0, 0, $month, 10)); // Get month name
        $months[] = $monthName;

        $counts['total'][] = Student::whereYear('created_at', $currentYear)
                                    ->whereMonth('created_at', $month)
                                    ->count();
        $counts['partial'][] = Student::whereYear('created_at', $currentYear)
                                      ->whereMonth('created_at', $month)
                                      ->where('payment_status', 'partial')
                                      ->count();
        $counts['full'][] = Student::whereYear('created_at', $currentYear)
                                   ->whereMonth('created_at', $month)
                                   ->where('payment_status', 'full')
                                   ->count();
        $counts['unpaid'][] = Student::whereYear('created_at', $currentYear)
                                     ->whereMonth('created_at', $month)
                                     ->where('payment_status', 'unpaid')
                                     ->count();
    }

    return [
        'labels' => $months,
        'counts' => $counts
    ];
}
private function getWeeklyData()
{
    $counts = [];
    $weeks = [];
    $currentYear = date('Y');

    for ($week = 1; $week <= 52; $week++) {
        $weekLabel = "Week $week, $currentYear";
        $weeks[] = $weekLabel;

        $counts['total'][] = Student::whereYear('created_at', $currentYear)
                                    ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                    ->count();
        $counts['partial'][] = Student::whereYear('created_at', $currentYear)
                                      ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                      ->where('payment_status', 'partial')
                                      ->count();
        $counts['full'][] = Student::whereYear('created_at', $currentYear)
                                   ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                   ->where('payment_status', 'full')
                                   ->count();
        $counts['unpaid'][] = Student::whereYear('created_at', $currentYear)
                                     ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                     ->where('payment_status', 'unpaid')
                                     ->count();
    }

    return [
        'labels' => $weeks,
        'counts' => $counts
    ];
}
private function getYearlyData()
{
    $counts = [];
    $years = [];
    $currentYear = date('Y');

    $years[] = (string)$currentYear;
    $counts['total'][] = Student::whereYear('created_at', $currentYear)->count();
    $counts['partial'][] = Student::whereYear('created_at', $currentYear)->where('payment_status', 'partial')->count();
    $counts['full'][] = Student::whereYear('created_at', $currentYear)->where('payment_status', 'full')->count();
    $counts['unpaid'][] = Student::whereYear('created_at', $currentYear)->where('payment_status', 'unpaid')->count();

    return [
        'labels' => $years,
        'counts' => $counts
    ];
}
private function getAllYearlyData()
{
    $counts = [];
    $years = [];

    $firstYear = Student::orderBy('created_at', 'asc')->first()->created_at->year;
    $lastYear = Student::orderBy('created_at', 'desc')->first()->created_at->year;

    for ($year = $firstYear; $year <= $lastYear; $year++) {
        $years[] = (string)$year;

        $counts['total'][] = Student::whereYear('created_at', $year)->count();
        $counts['partial'][] = Student::whereYear('created_at', $year)->where('payment_status', 'partial')->count();
        $counts['full'][] = Student::whereYear('created_at', $year)->where('payment_status', 'full')->count();
        $counts['unpaid'][] = Student::whereYear('created_at', $year)->where('payment_status', 'unpaid')->count();
    }

    return [
        'labels' => $years,
        'counts' => $counts
    ];
}



}
