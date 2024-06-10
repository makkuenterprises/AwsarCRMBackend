<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class MeetingCreateController extends Controller
{

public function create(Request $request)
// {
//     // dd("kjskjsk");
//     // Validate form input
//     $request->validate([
//         'api_key' => 'required',
//         'api_secret' => 'required',
//         'topic' => 'required',
//         'start_time' => 'required|date',
//         'duration' => 'required|numeric',
//     ]);

//     // Create Zoom meeting using Zoom API
//     $response = Http::withHeaders([
//         'Authorization' => 'Bearer ' . $request->api_key,
//     ])->post('https://api.zoom.us/v2/users/me/meetings', [
//         'topic' => $request->topic,
//         'start_time' => $request->start_time,
//         'duration' => $request->duration,
//         // Add more meeting parameters as needed
//     ]);
// // Handle Zoom API response
// if ($response->successful()) {
//     $responseData = $response->json();
//     $joinUrl = $responseData['join_url']; // Get the join URL from the response
//     // You may also log or process other response data here
//     return response()->json([
//         'success' => true,
//         'message' => 'Meeting created successfully.',
//         'join_url' => $joinUrl,
//     ]);
// } else {
//     $errorMessage = $response->body(); // Capture the error message from the response body
//     Log::error('Failed to create Zoom meeting: ' . $response->status() . ' ' . $errorMessage);
//     return response()->json([
//         'success' => false,
//         'message' => 'Failed to create Zoom meeting. Please try again.',
//         'error' => $errorMessage, // Include the error message in the response
//     ], 500);
// }
//     // Handle Zoom API response
//     if ($response->successful()) {
//         $responseData = $response->json();
//         $joinUrl = $responseData['join_url']; // Get the join URL from the response
//         // You may also log or process other response data here
//         return response()->json([
//             'success' => true,
//             'message' => 'Meeting created successfully.',
//             'join_url' => $joinUrl,
//         ]);
//     } else {
//         Log::error('Failed to create Zoom meeting: ' . $response->status() . ' ' . $response->body());
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to create Zoom meeting. Please try again.',
//         ], 500);
//     }
// }
{

// Generate access token
$response = Http::asForm()->post('https://zoom.us/oauth/token', [
    'grant_type' => 'client_credentials',
    'client_id' => $request->api_key,
    'client_secret' => $request->api_secret,
]);

if ($response->successful()) {
    $responseData = $response->json();
    $accessToken = $responseData['access_token'];

    // Create Zoom meeting using access token
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
    ])->post('https://api.zoom.us/v2/users/me/meetings', [
        'topic' => $request->topic,
        'start_time' => $request->start_time,
        'duration' => $request->duration,
        // Add more meeting parameters as needed
    ]);

    // Handle Zoom API response
    if ($response->successful()) {
        $responseData = $response->json();
        $joinUrl = $responseData['join_url']; // Get the join URL from the response
        // You may also log or process other response data here
        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully.',
            'join_url' => $joinUrl,
        ]);
    } else {
        $errorMessage = $response->body(); // Capture the error message from the response body
        Log::error('Failed to create Zoom meeting: ' . $response->status() . ' ' . $errorMessage);
        return response()->json([
            'success' => false,
            'message' => 'Failed to create Zoom meeting. Please try again.',
            'error' => $errorMessage, // Include the error message in the response
        ], 500);
    }
} else {
    // Failed to obtain access token
    $errorMessage = $response->body(); // Capture the error message from the response body
    Log::error('Failed to obtain access token: ' . $response->status() . ' ' . $errorMessage);
    return response()->json([
        'success' => false,
        'message' => 'Failed to obtain access token. Please try again.',
        'error' => $errorMessage, // Include the error message in the response
    ], 500);
}
}
}
