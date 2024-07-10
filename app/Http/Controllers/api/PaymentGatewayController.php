<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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
            'data' => PaymentGateway::first()
        ], 200);
    }
}
