<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ZoomController extends Controller
{

public function createMeeting(Request $request): array
{
    // Validate input
    $validated = $this->validate($request, [
        'title' => 'required',
        'start_date_time' => 'required|date',
        'duration_in_minute' => 'required|numeric'
    ]);

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateToken(),
            'Content-Type' => 'application/json',
        ])->post("https://api.zoom.us/v2/users/me/meetings", [
            'topic' => $validated['title'],
            'type' => 2, // 2 for scheduled meeting
            'start_time' => Carbon::parse($validated['start_date_time'])->toIso8601String(),
            'duration' => $validated['duration_in_minute'],
        ]);
        
        return $response->json();

    } catch (\Throwable $th) {
        Log::error('Failed to create meeting: ' . $th->getMessage());
        return [
            'status' => false,
            'message' => 'Failed to create meeting',
            'error' => $th->getMessage()
        ];
    }
}
protected function generateToken(): string
{
    try {
        $base64String = base64_encode(env('ZOOM_CLIENT_ID') . ':' . env('ZOOM_CLIENT_SECRET'));

        $responseToken = Http::withHeaders([
            "Content-Type" => "application/x-www-form-urlencoded",
            "Authorization" => "Basic {$base64String}"
        ])->post("https://zoom.us/oauth/token", [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ]
        ]);

        // Log the full response body for debugging
        $responseBody = $responseToken->json();
        Log::info('Token response: ' . json_encode($responseBody));

        if (isset($responseBody['access_token'])) {
            return $responseBody['access_token'];
        } else {
            Log::error('Error generating token. Response: ' . json_encode($responseBody));
            throw new \Exception('Access token not found in response');
        }

    } catch (\Throwable $th) {
        Log::error('Error generating token: ' . $th->getMessage());
        throw $th;
    }
}

}