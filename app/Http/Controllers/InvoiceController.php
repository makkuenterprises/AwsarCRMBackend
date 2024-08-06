<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
// use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Student;
use App\Models\Course;
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
//         'invoice_id' => 'required|integer|exists:invoices,id',
//     ]);

//     try {
//         // Build query to fetch invoices for the specified student, course, and invoice ID using join
//         $invoices = DB::table('invoices')
//             ->join('courses_enrollements', 'invoices.enrollment_id', '=', 'courses_enrollements.id')
//             ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
//             ->where('invoices.student_id', $request->input('student_id'))
//             ->where('courses_enrollements.course_id', $request->input('course_id'))
//             ->where('invoices.id', $request->input('invoice_id'))
//             ->select(
//                 'invoices.*',
//                 'courses_enrollements.student_id',
//                 'courses_enrollements.course_id',
//                 'courses.name as course_name'
//             )
//             ->get();

//         // Check if any invoices are found
//         if ($invoices->isEmpty()) {
//             return response()->json([
//                 'status' => false,
//                 'code' => 404,
//                 'message' => 'No invoices found for the given criteria.',
//             ], 404);
//         }

//         // Fetch the student details
//         $student = Student::select('id', 'name', 'email', 'phone', 'street', 'postal_code', 'city', 'state', 'fname', 'fphone')
//                           ->findOrFail($request->input('student_id'));

//         // Generate PDF
//         // $pdf = PDF::loadView('invoices.pdf', [
//         //     'student' => $student,
//         //     'invoices' => $invoices,
//         // ]);

//           return response()->json([
//             'status' => true,
//             'code' => 200,
//             'data' => [
//                 'student' => $student,
//                 'invoices' => $invoices,
//             ],
//         ], 200);


//         // Download the PDF
//         return $pdf->download('invoices.pdf');

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
        'invoice_id' => 'required|integer|exists:invoices,id',
    ]);

    try {
        // Fetch invoices for the specified student, course, and invoice ID
        $invoices = DB::table('invoices')
            ->join('courses_enrollements', 'invoices.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $request->input('student_id'))
            ->where('courses_enrollements.course_id', $request->input('course_id'))
            ->where('invoices.id', $request->input('invoice_id'))
            ->select(
                'invoices.enrollment_id',
                DB::raw('GROUP_CONCAT(invoices.id) as invoice_ids'),
                DB::raw('GROUP_CONCAT(invoices.total_amount) as total_amounts'),
                DB::raw('GROUP_CONCAT(invoices.paid_amount) as paid_amounts'),
                DB::raw('GROUP_CONCAT(invoices.remaining_amount) as remaining_amounts'),
                DB::raw('GROUP_CONCAT(invoices.invoice_date) as invoice_dates'),
                'courses_enrollements.student_id',
                'courses_enrollements.course_id',
                'courses.name as course_name'
            )
            ->groupBy('invoices.enrollment_id', 'courses_enrollements.student_id', 'courses_enrollements.course_id', 'courses.name')
            ->get();

        // Check if any invoices are found
        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No invoices found for the given criteria.',
            ], 404);
        }

        // Fetch the student details
        $student = Student::select('id', 'name', 'email', 'phone', 'street', 'postal_code', 'city', 'state', 'fname', 'fphone')
                          ->findOrFail($request->input('student_id'));

        // Fetch all payment histories related to the specified criteria
        $paymentHistories = DB::table('payment_histories')
            ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $request->input('student_id'))
            ->where('courses_enrollements.course_id', $request->input('course_id'))
            ->select(
                'payment_histories.enrollment_id',
                DB::raw('GROUP_CONCAT(DISTINCT payment_histories.transaction_id) as transaction_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT payment_histories.payment_type) as payment_types'),
                DB::raw('GROUP_CONCAT(DISTINCT payment_histories.payment_status) as payment_statuses'),
                DB::raw('GROUP_CONCAT(payment_histories.paid_amount) as paid_amounts'),
                DB::raw('GROUP_CONCAT(payment_histories.payment_date) as payment_dates'),
                'courses_enrollements.student_id',
                'courses_enrollements.course_id',
                'courses.name as course_name'
            )
            ->groupBy('payment_histories.enrollment_id', 'courses_enrollements.student_id', 'courses_enrollements.course_id', 'courses.name')
            ->get();

        // Generate PDF
        // $pdf = PDF::loadView('invoices.pdf', [
        //     'student' => $student,
        //     'invoices' => $invoices,
        //     'paymentHistories' => $paymentHistories,
        // ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'student' => $student,
                'invoices' => $invoices,
                'paymentHistories' => $paymentHistories,
            ],
        ], 200);

        // Download the PDF
        // return $pdf->download('invoices.pdf');

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
