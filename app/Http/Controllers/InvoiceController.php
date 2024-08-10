<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use PDF;
use App\Models\Student;
use App\Models\Course;
use App\Models\Details;
use Illuminate\Support\Facades\Storage;

use DB;
use Illuminate\Http\Request;

class InvoiceController extends Controller 
{
    public function getAllInvoices()
    {
        try {
            // Retrieve all invoices from the database
            $invoices = Invoice::all();

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $invoices,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to retrieve invoices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
public function getAllInvoicesByStudent(Request $request)
{
    // Validate the request
    $request->validate([
        'student_id' => 'required|integer|exists:students,id',
    ]);

    try {
        // Fetch the student with specific fields
        $student = Student::select('id', 'name', 'email', 'phone', 'street', 'postal_code', 'city', 'state', 'fname', 'fphone')
                          ->findOrFail($request->input('student_id'));

        // Fetch all invoices for the specified student with course and enrollment details using join
        $invoices = DB::table('invoices')
            ->join('courses_enrollements', 'invoices.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->select(
                'invoices.*',
                'courses_enrollements.student_id',
                'courses_enrollements.course_id',
                'courses.name as course_name'
            )
            ->where('invoices.student_id', $request->input('student_id'))
            ->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'student' => $student,
                'invoices' => $invoices,
            ],
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to retrieve invoices',
            'error' => $e->getMessage(),
        ], 500);
    } 
}


// public function getAllInvoicesByStudentDownload(Request $request)
// {
//     // Validate the request 
//     $request->validate([ 
//         'student_id' => 'required|integer|exists:students,id',
//         'course_id' => 'required|integer|exists:courses,id',
//         'transaction_id' => 'required|string|exists:invoices,transaction_id', // Validate the transaction ID
//     ]);

//     try {
//         // Fetch invoices for the specified student, course, and transaction ID
//         $invoices = DB::table('invoices')
//             ->join('courses_enrollements', 'invoices.enrollment_id', '=', 'courses_enrollements.id')
//             ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
//             ->where('courses_enrollements.student_id', $request->input('student_id'))
//             ->where('courses_enrollements.course_id', $request->input('course_id'))
//             ->where('invoices.transaction_id', $request->input('transaction_id')) // Filter by transaction ID
//             ->select(
//                 'invoices.*',
//                 'courses_enrollements.student_id',
//                 'courses_enrollements.course_id',
//                 'courses.name as course_name'
//             )
//             ->get();

//         if ($invoices->isEmpty()) {
//             return response()->json([
//                 'status' => false,
//                 'code' => 404,
//                 'message' => 'Invoice not found'
//             ], 404);
//         }

//         // Calculate totals from the invoices
//         $totalAmount = $invoices->sum('total_amount');

//         // Fetch the student details 
//         $student = Student::select('id', 'name', 'email', 'phone', 'street', 'postal_code', 'city', 'state', 'fname', 'fphone')
//                           ->findOrFail($request->input('student_id'));

//         // Fetch all payment histories related to the specified criteria
//         $paymentHistories = DB::table('payment_histories')
//             ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
//             ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
//             ->where('courses_enrollements.student_id', $request->input('student_id'))
//             ->where('courses_enrollements.course_id', $request->input('course_id'))
//             ->where('payment_histories.transaction_id', $request->input('transaction_id')) // Filter by transaction ID
//             ->select('payment_histories.*')
//             ->get();

//         // Calculate total paid amount from payment histories
//         $paidAmount = $paymentHistories->sum('paid_amount');

//         // Calculate outstanding amount
//         $outstandingAmount = $totalAmount - $paidAmount;

//         // Format amounts
//         $totalAmountFormatted = number_format($totalAmount, 2, '.', ',');
//         $paidAmountFormatted = number_format($paidAmount, 2, '.', ',');
//         $outstandingAmountFormatted = number_format($outstandingAmount, 2, '.', ',');

//         // Format paid_amount in paymentHistories
//         $formattedPaymentHistories = $paymentHistories->map(function($payment) {
//             $payment->paid_amount = number_format($payment->paid_amount, 2, '.', ',');  
//             return $payment;
//         }); 

//         $details = Details::first(); 

//         if ($details->side_logo) {
//             if (filter_var($details->side_logo, FILTER_VALIDATE_URL)) {
//                 // It's a URL, use it directly
//                 $details->side_logo = $details->side_logo; 
//             } else {
//                 // Generate a URL for the stored file
//                 $details->side_logo = url(Storage::url($details->side_logo));
//             }
//         }

//         // Generate PDF
//         $pdf = PDF::loadView('invoice', [
//             'details' => $details,
//             'student' => $student,
//             'invoices' => $invoices,
//             'paymentHistories' => $formattedPaymentHistories,
//             'totalAmount' => $totalAmountFormatted,
//             'paidAmount' => $paidAmountFormatted,
//             'outstandingAmount' => $outstandingAmountFormatted,
//         ]);

//         // Stream the PDF to the browser
//         return $pdf->stream('invoice.pdf');

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status' => false,
//             'code' => 422,
//             'message' => 'Validation failed',
//             'errors' => $e->errors(),
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'code' => 500,
//             'message' => 'Failed to retrieve invoices',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }

public function getAllInvoicesByStudentDownload(Request $request)
{
    // Validate the request 
    $request->validate([ 
        'student_id' => 'required|integer|exists:students,id',
        'course_id' => 'required|integer|exists:courses,id',
        'transaction_id' => 'required|string|exists:invoices,transaction_id', // Validate the transaction ID
    ]);

    try {
        // Fetch invoices for the specified student, course, and transaction ID
        $invoices = DB::table('invoices')
            ->join('courses_enrollements', 'invoices.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $request->input('student_id'))
            ->where('courses_enrollements.course_id', $request->input('course_id'))
            ->where('invoices.transaction_id', $request->input('transaction_id')) // Filter by transaction ID
            ->select(
                'invoices.*',
                'courses_enrollements.student_id',
                'courses_enrollements.course_id',
                'courses.name as course_name'
            )
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404, 
                'message' => 'Invoice not found'
            ], 404);
        }

        // Calculate totals from the invoices
         $course = Course::find($request->course_id);
          $totalAmount=$course->fee; 

        // Fetch the student details 
        $student = Student::select('id', 'name', 'email', 'phone', 'street', 'postal_code', 'city', 'state', 'fname', 'fphone')
                          ->findOrFail($request->input('student_id'));

        // Fetch all payment histories related to the specified criteria (current transaction)
        $paymentHistories = DB::table('payment_histories')
            ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $request->input('student_id'))
            ->where('courses_enrollements.course_id', $request->input('course_id'))
            ->where('payment_histories.transaction_id', $request->input('transaction_id')) // Filter by transaction ID
            ->select('payment_histories.*')
            ->get();

        // Calculate total paid amount from payment histories of current transaction
        $paidAmount = $paymentHistories->sum('paid_amount');

        // Fetch all previous payments for the course and student (excluding the current transaction)
          // Fetch all previous payments for the same transaction ID, excluding the current one
       // Fetch all previous payments up to and including the specified transaction ID
        $previousPayments = DB::table('payment_histories')
            ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $request->input('student_id'))
            ->where('courses_enrollements.course_id', $request->input('course_id'))
            ->where('payment_histories.transaction_id', '<=', $request->input('transaction_id')) // Include up to and including the specified transaction ID
            ->select('payment_histories.*')
            ->get();

       // Calculate the total amount of previous payments
        $previousPaymentsTotal = $previousPayments->sum('paid_amount');

        // Total paid amount including previous payments
        $totalPaidAmount =  $previousPaymentsTotal;
        
        $totalOT = $invoices->sum('total_amount');

        $PaymentsAlreadyMade = $previousPaymentsTotal - $totalOT;

        // Calculate outstanding amount considering all previous payments

        $outstandingAmount = $totalAmount - $totalPaidAmount;

        // Format amounts
        $totalAmountFormatted = number_format($totalAmount, 2, '.', ',');
        $paidAmountFormatted = number_format($paidAmount, 2, '.', ',');
        $previousPaymentsTotalFormatted = number_format($PaymentsAlreadyMade, 2, '.', ',');
        $totalPaidAmountFormatted = number_format($totalPaidAmount, 2, '.', ',');
        $outstandingAmountFormatted = number_format($outstandingAmount, 2, '.', ',');

        // Format paid_amount in paymentHistories
        $formattedPaymentHistories = $paymentHistories->map(function($payment) {
            $payment->paid_amount = number_format($payment->paid_amount, 2, '.', ',');  
            return $payment;
        }); 

        $details = Details::first(); 

        if ($details->side_logo) {
            if (filter_var($details->side_logo, FILTER_VALIDATE_URL)) {
                // It's a URL, use it directly
                $details->side_logo = $details->side_logo; 
            } else {
                // Generate a URL for the stored file
                $details->side_logo = url(Storage::url($details->side_logo));
            }
        }

        // Generate PDF
        $pdf = PDF::loadView('invoice', [
            'details' => $details,
            'student' => $student,
            'invoices' => $invoices,
            'paymentHistories' => $formattedPaymentHistories,
            'totalAmount' => $totalAmountFormatted,
            'paidAmount' => $paidAmountFormatted, // Current transaction paid amount
            'totalPaidAmount' => $totalPaidAmountFormatted, // All previous payments included
            'outstandingAmount' => $outstandingAmountFormatted,
            'privious_all_transaction' => $previousPaymentsTotalFormatted,
        ]);

        // Stream the PDF to the browser
        return $pdf->stream('invoice.pdf');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to retrieve invoices',
            'error' => $e->getMessage(),
        ], 500);
    }
}







}
