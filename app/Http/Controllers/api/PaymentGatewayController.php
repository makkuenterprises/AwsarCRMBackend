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
            'description' => 'nullable|string',
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
                'description' => $gateway->description,
            ]
        ], 200);
    }

     public function dashboardaData()
    {
        try {
            $teachersCount = Teacher::count();
            $studentsCount = Student::count();
            $staffCount = StaffModel::count();
            $coursesCount = Course::count();

            $partialPaymentStudentsCount =  DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->where('students.payment_status', 'partial')
    ->distinct('students.id')
    ->count('students.id');
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
        if ($duration === 'week') {
            return [
                'numberOfStudents' => $this->getWeeklyCounts('total'),
                'partialPayment' => $this->getWeeklyCounts('partial'),
                'fullPayment' => $this->getWeeklyCounts('full'),
                'unpaid' => $this->getWeeklyCounts('deactive')
            ];
        } elseif ($duration === 'year') {
            return [
                'numberOfStudents' => $this->getYearlyCounts('total'),
                'partialPayment' => $this->getYearlyCounts('partial'),
                'fullPayment' => $this->getYearlyCounts('full'),
                'unpaid' => $this->getYearlyCounts('deactive')
            ];
        } else {
            return [
                'numberOfStudents' => $this->getMonthlyCounts('total'),
                'partialPayment' => $this->getMonthlyCounts('partial'),
                'fullPayment' => $this->getMonthlyCounts('full'),
                'unpaid' => $this->getMonthlyCounts('deactive')
            ];
        }
    }

    private function getMonthlyCounts($type)
    {
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $query = DB::table('students')
                ->whereMonth('students.created_at', $month);

            if ($type !== 'total') {
                $query->where('students.payment_status', $type);
            }

            if ($type === 'full') {
                $query->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
                    ->distinct('students.id');
            }

            $counts[] = $query->count('students.id');
        }
        return $counts;
    }

    private function getWeeklyCounts($type)
    {
        $counts = [];
        for ($week = 1; $week <= 52; $week++) {
            $query = DB::table('students')
                ->whereRaw('WEEKOFYEAR(students.created_at) = ?', [$week]);

            if ($type !== 'total') {
                $query->where('students.payment_status', $type);
            }

            if ($type === 'full') {
                $query->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
                    ->distinct('students.id');
            }

            $counts[] = $query->count('students.id');
        }
        return $counts;
    }

    private function getYearlyCounts($type)
    {
        $counts = [];
        $currentYear = date('Y');
        for ($year = $currentYear - 4; $year <= $currentYear; $year++) {
            $query = DB::table('students')
                ->whereYear('students.created_at', $year);

            if ($type !== 'total') {
                $query->where('students.payment_status', $type);
            }

            if ($type === 'full') {
                $query->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
                    ->distinct('students.id');
            }

            $counts[] = $query->count('students.id');
        }
        return $counts;
    
    }
}
