<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
// use Barryvdh\DomPDF\Facade as PDF;
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
      

        try {
              $request->validate([
            'student_id' => 'required|integer',
        ]);
            // Fetch all invoices for the specified student
            $invoices = Invoice::where('student_id', $request->input('student_id'))
                ->get();

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $invoices,
            ], 200);
        }  catch (\Illuminate\Validation\ValidationException $e) {
    return response()->json([
        'status' => false,
        'code' => 422,
        'message' => 'Validation failed',
        'errors' => $e->errors(),
    ], 422);
}catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to retrieve ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



public function getAllInvoicesByStudentDownload(Request $request)
{
    // Validate the request
    $request->validate([
        'student_id' => 'required|integer|exists:students,id',
        'course_id' => 'required|integer|exists:courses,id',
        'invoice_id' => 'required|integer|exists:invoices,id',
    ]);

    try {
        // Build query to fetch invoices for the specified student, course, and invoice ID
        $invoices = Invoice::where('student_id', $request->input('student_id'))
            ->where('course_id', $request->input('course_id'))
            ->where('id', $request->input('invoice_id'))
            ->get();

        // Check if any invoices are found
        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No invoices found for the given criteria.',
            ], 404);
        }
        return response()->json(['data'=> $invoices]);

        // Generate PDF
        // $pdf = PDF::loadView('invoices.pdf', ['invoices' => $invoices]);

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
