<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
class DatewiseReport extends Controller
{

public function ReportDatewise(Request $request){
    
        $startDate = $request->start_date;
$endDate = $request->end_date;

$students = DB::table('courses_enrollements')
    ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
    ->join('payment_histories', 'courses_enrollements.id', '=', 'payment_histories.enrollment_id')
    ->whereBetween('courses_enrollements.enrollment_date', [$startDate, $endDate])
    ->select(
        'courses_enrollements.student_id',
        'courses.id as course_id',
        'courses.name',
        'courses.description',
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
            'description' => $coursePayments->first()->description,
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

}
