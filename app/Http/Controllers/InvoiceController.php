<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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

}
