<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DatewiseReport extends Controller
{

public function ReportDatewise(Request $request)
{
    // Validate that start_date and end_date are present in the request
    $validatedData = $request->validate([
        'start_date' => 'required',
        'end_date' => 'required',
    ]);

    $startDate = $validatedData['start_date'];
    $endDate = $validatedData['end_date'];

    $students = DB::table('courses_enrollements')
        ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
        ->join('payment_histories', 'courses_enrollements.id', '=', 'payment_histories.enrollment_id')
        ->whereBetween('courses_enrollements.enrollment_date', [$startDate, $endDate])
        ->select(
            'courses_enrollements.student_id',
            'courses.id as course_id',
            'courses.name',
            'courses_enrollements.enrollment_date',
            'payment_histories.transaction_id',
            'payment_histories.payment_type',
            'payment_histories.payment_status',
            'payment_histories.paid_amount',
            'payment_histories.payment_date'
        )
        ->get()
        ->groupBy('student_id');

    $response = [];

    foreach ($students as $studentId => $studentCourses) {
        $studentData = [
            'student_id' => $studentId,
            'courses' => []
        ];

        $coursesGrouped = $studentCourses->groupBy('course_id');

        foreach ($coursesGrouped as $courseId => $coursePayments) {
            $courseData = [
                'course_id' => $courseId,
                'course_name' => $coursePayments->first()->name,
                'enrollment_date' => $coursePayments->first()->enrollment_date,
                'payments' => $coursePayments->map(function ($payment) {
                    return [
                        'transaction_id' => $payment->transaction_id,
                        'payment_type' => $payment->payment_type,
                        'payment_status' => $payment->payment_status,
                        'paid_amount' => $payment->paid_amount,
                        'payment_date' => $payment->payment_date,
                    ];
                })->toArray()
            ];

            $studentData['courses'][] = $courseData;
        }

        $response[] = $studentData;
    }

    return response()->json($response);
}


public function ReportToday(Request $request)
{
    // Get today's date
    $today = date('Y-m-d');

    $students = DB::table('courses_enrollements')
        ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
        ->join('payment_histories', 'courses_enrollements.id', '=', 'payment_histories.enrollment_id')
        ->whereDate('courses_enrollements.enrollment_date', '=', $today) // Filter for today's date
        ->select(
            'courses_enrollements.student_id',
            'courses.id as course_id',
            'courses.name as course_name', // Ensure this matches the actual column name
            'courses_enrollements.enrollment_date',
            'payment_histories.transaction_id',
            'payment_histories.payment_type',
            'payment_histories.payment_status',
            'payment_histories.paid_amount',
            'payment_histories.payment_date'
        )
        ->get()
        ->groupBy('student_id');

    $response = [];

    foreach ($students as $studentId => $studentCourses) {
        $studentData = [
            'student_id' => $studentId,
            'courses' => []
        ];

        $coursesGrouped = $studentCourses->groupBy('course_id');
        
        foreach ($coursesGrouped as $courseId => $coursePayments) {
            $courseData = [ 
                'course_id' => $courseId,
                'course_name' => $coursePayments->first()->course_name,
                'enrollment_date' => $coursePayments->first()->enrollment_date,
                'payments' => $coursePayments->map(function ($payment) {
                    return [
                        'transaction_id' => $payment->transaction_id,
                        'payment_type' => $payment->payment_type,
                        'payment_status' => $payment->payment_status,
                        'paid_amount' => $payment->paid_amount,
                        'payment_date' => $payment->payment_date,
                    ];
                })->toArray()
            ];
            
            $studentData['courses'][] = $courseData;
        }

        $response[] = $studentData;
    }

    return response()->json($response);
}

public function DownloadReportToday(Request $request)
{
    // Get today's date
    $today = date('Y-m-d');

    $students = DB::table('courses_enrollements')
        ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
        ->join('payment_histories', 'courses_enrollements.id', '=', 'payment_histories.enrollment_id')
        ->whereDate('courses_enrollements.enrollment_date', '=', $today)
        ->select(
            'courses_enrollements.student_id',
            'courses.id as course_id',
            'courses.name as course_name',
            'courses_enrollements.enrollment_date',
            'payment_histories.transaction_id',
            'payment_histories.payment_type',
            'payment_histories.payment_status',
            'payment_histories.paid_amount',
            'payment_histories.payment_date'
        )
        ->get()
        ->groupBy('student_id');

    $response = [];

    foreach ($students as $studentId => $studentCourses) {
        $studentData = [
            'student_id' => $studentId,
            'courses' => []
        ];

        $coursesGrouped = $studentCourses->groupBy('course_id');
        
        foreach ($coursesGrouped as $courseId => $coursePayments) {
            $courseData = [ 
                'course_id' => $courseId,
                'course_name' => $coursePayments->first()->course_name,
                'enrollment_date' => $coursePayments->first()->enrollment_date,
                'payments' => $coursePayments->map(function ($payment) {
                    return [
                        'transaction_id' => $payment->transaction_id,
                        'payment_type' => $payment->payment_type,
                        'payment_status' => $payment->payment_status,
                        'paid_amount' => $payment->paid_amount,
                        'payment_date' => $payment->payment_date,
                    ];
                })->toArray()
            ];
            
            $studentData['courses'][] = $courseData;
        }

        $response[] = $studentData;
    }
    dd( $response);

    // Generate the PDF from the Blade view
    $pdf = Pdf::loadView('today_report', ['students' => $response]);
    // Return the PDF as a download
    return $pdf->download('today_report_' . $today . '.pdf');
}


}
