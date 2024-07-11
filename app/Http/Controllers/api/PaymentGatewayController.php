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

            $partialPaymentStudentsCount = Student::where('payment_status', 'partial')->count();
            // $notEnrollStudentsCount = Student::where('course_name', 'Not Enrolled')->count();

           $notEnrollStudentsCount = DB::table('students')
    ->leftJoin('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->whereNull('courses_enrollements.student_id')
    ->count();

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
