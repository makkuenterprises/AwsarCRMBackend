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

$coursesWithPaymentHistory = DB::table('courses_enrollements')
    ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
    ->join('payment_histories', 'courses_enrollements.id', '=', 'payment_histories.enrollment_id')
    ->whereBetween('courses_enrollements.enrollment_date', [$startDate, $endDate]) // Filter by enrollment date range
    ->select(
        'courses.*', 
        'courses_enrollements.student_id', // Include student ID
        'courses_enrollements.enrollment_date',
        'payment_histories.transaction_id', 
        'payment_histories.payment_type', 
        'payment_histories.payment_status', 
        'payment_histories.paid_amount', 
        'payment_histories.payment_date'
    )
    ->get();

return response()->json($coursesWithPaymentHistory);
}

}
