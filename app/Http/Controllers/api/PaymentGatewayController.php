<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Teacher;  
use App\Models\StaffModel;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Auth;


// 
class PaymentGatewayController extends Controller
{
    //
    public function index() 
    {
        $gateway = PaymentGateway::first();
        if ($gateway) {
            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Payment gateway retrieved successfully.',
                'data' => $gateway
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'No payment gateway found.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([ 
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
            // 'description' => 'nullable|string',
        ]);

        $gateway = PaymentGateway::first();
        if ($gateway) {
            $gateway->update($validated);
            $message = 'Payment gateway updated successfully.';
        } else {
            PaymentGateway::create($validated);
            $message = 'Payment gateway created successfully.';
        }

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => $message,
           'data' => [
                'name' => $gateway->name,
                'api_key' => $gateway->api_key,  
                'api_secret' => $gateway->api_secret,
                // 'description' => $gateway->description,
            ]
        ], 200);
    }


 
    public function dashboardaData()
    {
        try {
            $staff = Auth::guard('staff')->user();
         $student = Auth::guard('student')->user();
         $teacher = Auth::guard('teacher')->user();
        $admin = Auth::guard('admin')->user();

          if ($student || $admin || $staff || $teacher) {
            $teachersCount = Teacher::count();
            $studentsCount = Student::count();
            $staffCount = StaffModel::count();
            $coursesCount = Course::count();

       $partialPaymentStudentsCount = DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->where('students.payment_status', 'partial')
    ->distinct('students.id')
    ->count();
            // $notEnrollStudentsCount = Student::where('course_name', 'Not Enrolled')->count();

    $notEnrollStudentsCount = DB::table('students')
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('courses_enrollements')
              ->whereRaw('courses_enrollements.student_id = students.id');
    }) 
    ->count();
    $enrollStudentsCount = DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->distinct('students.id')
    ->count('students.id');
          $fullPaymentStudentsCount = DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->where('students.payment_status', 'full')
    ->distinct('students.id') 
    ->count('students.id'); 
$otherPaymentStatusStudentsCount = DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->whereNotIn('students.payment_status', ['partial', 'full'])
    ->distinct('students.id')
    ->count();
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
                    'enrollStudentsCount' => $enrollStudentsCount,
                    'unpaidPaymentStatusStudentsCount' => $otherPaymentStatusStudentsCount,
                ], 
            ], 200);
            }else{
             return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch counts',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


public function fetchChartData(Request $request)
{
    try {
           $staff = Auth::guard('staff')->user();
         $student = Auth::guard('student')->user();
         $teacher = Auth::guard('teacher')->user();
        $admin = Auth::guard('admin')->user();

          if ($student || $admin || $staff || $teacher) {
        $duration = $request->query('duration', 'month'); // default to monthly data
        $data = $this->getChartData($duration);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
        }else{
             return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch data',
            'message' => $e->getMessage(),
        ], 500);
    }
}

private function getChartData($duration)
{
    switch ($duration) {
        case 'week':
            return ['week' => $this->getWeeklyData()];
        case 'year':
            return ['year' => $this->getYearlyData()];
        default:
            return ['month' => $this->getMonthlyData()];
    }
}

private function getMonthlyData()
{
    $counts = [];
    $months = [];
    $currentYear = date('Y');

    for ($month = 1; $month <= 12; $month++) {
        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $months[] = $monthName;

        $counts['studentsCount'][] = Student::whereYear('created_at', $currentYear)
                                            ->whereMonth('created_at', $month)
                                            ->count();
        $counts['paidPayments'][] = Student::whereYear('created_at', $currentYear)
                                           ->whereMonth('created_at', $month)
                                           ->where('payment_status', 'paid')
                                           ->count();
        $counts['pendingPayments'][] = Student::whereYear('created_at', $currentYear)
                                              ->whereMonth('created_at', $month)
                                              ->where('payment_status', 'pending')
                                              ->count();
    }

    return [
        'labels' => $months,
        'studentsCount' => $counts['studentsCount'],
        'paidPayments' => $counts['paidPayments'],
        'pendingPayments' => $counts['pendingPayments']
    ];
}

private function getWeeklyData()
{
    $counts = [];
    $weeks = [];
    $currentYear = date('Y');

    for ($week = 1; $week <= 52; $week++) {
        $weekLabel = sprintf('%02d, %s', $week, $currentYear);
        $weeks[] = $weekLabel;

        $counts['studentsCount'][] = Student::whereYear('created_at', $currentYear)
                                            ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                            ->count();
        $counts['paidPayments'][] = Student::whereYear('created_at', $currentYear)
                                           ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                           ->where('payment_status', 'paid')
                                           ->count();
        $counts['pendingPayments'][] = Student::whereYear('created_at', $currentYear)
                                              ->whereRaw('WEEKOFYEAR(created_at) = ?', [$week])
                                              ->where('payment_status', 'pending')
                                              ->count();
    }

    return [
        'labels' => $weeks,
        'studentsCount' => $counts['studentsCount'],
        'paidPayments' => $counts['paidPayments'],
        'pendingPayments' => $counts['pendingPayments']
    ];
}


private function getYearlyData()
{
    $counts = [
        'studentsCount' => [],
        'paidPayments' => [],
        'pendingPayments' => []
    ];
    $years = [];
    $currentYear = date('Y');

    // Get all years from the data
    $yearsRange = Student::selectRaw('YEAR(created_at) as year')
                         ->distinct()
                         ->orderBy('year')
                         ->pluck('year')
                         ->toArray();

    // If no years are found, fallback to current year
    if (empty($yearsRange)) {
        $yearsRange = [$currentYear];
    }

    foreach ($yearsRange as $year) {
        $years[] = (string)$year;

        // Fetch data for each year
        $studentsCount = Student::whereYear('created_at', $year)->count();
        $paidPayments = Student::whereYear('created_at', $year)
                                ->where('payment_status', 'paid')
                                ->count();
        $pendingPayments = Student::whereYear('created_at', $year)
                                   ->where('payment_status', 'pending')
                                   ->count();

        // Collect counts
        $counts['studentsCount'][] = $studentsCount;
        $counts['paidPayments'][] = $paidPayments;
        $counts['pendingPayments'][] = $pendingPayments;
    }

    return [
        'labels' => $years,
        'studentsCount' => $counts['studentsCount'],
        'paidPayments' => $counts['paidPayments'],
        'pendingPayments' => $counts['pendingPayments']
    ];
}



    
}