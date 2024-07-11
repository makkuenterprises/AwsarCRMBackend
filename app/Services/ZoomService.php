<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ZoomController extends Controller
{
   public function redirectToZoom()
{
    // Encode client_id and client_secret to Base64
    $base64Credentials = base64_encode(env('ZOOM_CLIENT_ID') . ':' . env('ZOOM_CLIENT_SECRET'));

    // Build the authorization header
    $authorizationHeader = 'Basic ' . $base64Credentials;

    // Redirect URL construction
    $query = http_build_query([
        'response_type' => 'code',
        'client_id' => env('ZOOM_CLIENT_ID'),
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
        'scope' => 'meeting:write meeting:read'
    ]);

    // Construct the full authorization URL
    $authorizationUrl = 'https://zoom.us/oauth/authorize?' . $query;

    // Redirect the user with the authorization header
    return redirect()->away($authorizationUrl)->header('Authorization', $authorizationHeader);
}

    //  'client_id' => "iG8rs9fKS5qO9nUh2xkvQ",
    //     'client_secret' => "WjuzwgKObaToMSGPgD5APb9TZFLCSxPR"
public function handleZoomCallback(Request $request)
{
    $code = $request->input('code');

    $response = Http::asForm()->post('https://zoom.us/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET')
    ]);

    if ($response->successful()) {
        $data = $response->json();
        Session::put('zoom_access_token', $data['access_token']);
        return response()->json(['message' => 'Zoom authorization successful', 'access_token' => $data['access_token']], 200);
    }

    return response()->json(['error' => 'Failed to authenticate with Zoom'], $response->status());
}


    public function createMeeting(Request $request)
    {
        $accessToken = Session::get('zoom_access_token');

        $response = Http::withToken($accessToken)->post('https://api.zoom.us/v2/users/me/meetings', [
            'topic' => $request->input('topic'),
            'type' => 2,
            'start_time' => $request->input('start_time'),
            'duration' => $request->input('duration'),  // Duration in minutes
            'agenda' => $request->input('agenda'),
            'settings' => [
                'host_video' => false,
                'participant_video' => false,
                'waiting_room' => true,
            ],
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Meeting created successfully', 'data' => $response->json()]);
        }

        return response()->json(['message' => 'Failed to create meeting', 'error' => $response->json()], $response->status());
    }
}