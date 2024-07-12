<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use MacsiDigital\Zoom\Facades\Zoom;
use GuzzleHttp\Client;

class ZoomController extends Controller
{

// Redirect to Zoom for authorization
public function redirectToZoom()
{
    $query = http_build_query([
        'response_type' => 'code',
        'client_id' => env('ZOOM_API_KEY'),
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
        'scope' => 'meeting:write meeting:read'
    ]);

    return redirect('https://zoom.us/oauth/authorize?' . $query);
}

// Handle the Zoom callback
public function handleZoomCallback(Request $request)
{
    $code = $request->input('code');

    $response = Http::asForm()->post('https://zoom.us/oauth/token', [
        'grant_type' => 'account_credentials', 
        'code' => $code, 
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
          'account_id' => env('ZOOM_ACCOUNT_ID'),
        'client_id' => env('ZOOM_API_KEY'),
        'client_secret' => env('ZOOM_API_SECRET')
    ]);

    if ($response->successful()) {
        $data = $response->json();
        Session::put('zoom_access_token', $data['access_token']);
        Session::put('zoom_refresh_token', $data['refresh_token']);
        return redirect('/home')->with('success', 'Zoom authorization successful');
    }

    return redirect('/')->with('error', 'Failed to authenticate with Zoom');
}


// Create a Zoom meeting
public function createZoomMeeting()
{
    $accessToken = Session::get('zoom_access_token');

    $zoomUser = Zoom::user()->first();
    $meeting = $zoomUser->meetings()->create([
        'topic' => 'Test Meeting',
        'type' => 2, // Scheduled meeting
        'start_time' => now()->addDay()->format('Y-m-d\TH:i:s'),
        'duration' => 30, // 30 minutes
        'timezone' => 'UTC',
        'agenda' => 'Test meeting description',
        'settings' => [
            'host_video' => false,
            'participant_video' => false,
            'join_before_host' => true,
            'mute_upon_entry' => true,
            'watermark' => false,
            'use_pmi' => false
        ]
    ]);

    return response()->json($meeting);
}

 public function createeMeeting(Request $request)
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

        $responseBody = $response->json();
        \Log::info('Zoom Create Meeting Response: ' . json_encode($responseBody));

        return $responseBody;

    } catch (\Throwable $th) {
        \Log::error('Zoom API Create Meeting Error: ' . $th->getMessage());
        throw $th;
    }
}


    private function generateToken()
{
    $client = new Client();

    //====== get Auth token =======
    $response = $client->post('https://zoom.us/oauth/token', [
        'form_params' => [
            'grant_type' => 'account_credentials',
          'account_id' => env('ZOOM_ACCOUNT_ID'),
        'client_id' => env('ZOOM_API_KEY'),
        'client_secret' => env('ZOOM_API_SECRET')
        ],
    ]);
    dd($response);
    $accessToken = json_decode($response->getBody())?->access_token ;

    return $accessToken;
}


}