<?php

// GoogleMeetController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;
use Google_Service_Calendar;
use Google\Client;
use Google\Service\Calendar;
use Illuminate\Http\Request;

class GoogleMeetController extends Controller
{
    //
    public function redirectToGoogle()
    {

           return Socialite::driver('google')
        ->scopes(['https://www.googleapis.com/auth/calendar.events'])
        ->redirect();
    }

    public function handleGoogleCallback()
    {
        // $user = Socialite::driver('google')->stateless()->user();
            $user = Socialite::driver('google')->user();
            // dd($user);
        $token = $user->token;

        // Save the token and other necessary data in the database for future use
        // ...

        return redirect('/')->with('success', 'Logged in with Google successfully');
    }

    public function createGoogleMeetEvent(Request $request)
{
    $token = auth()->user()->google_token; // Retrieve the stored token from the database
    
    $googleCalendarService = new \App\Services\GoogleCalendarService($token);

    $event = $googleCalendarService->createEvent(
        'Meeting Title',
        'Meeting Description',
        '2024-07-12T10:00:00-07:00',
        '2024-07-12T11:00:00-07:00',
        [
            ['email' => 'attendee1@example.com'],
            ['email' => 'attendee2@example.com'],
        ]
    );

    return response()->json(['event' => $event]);
}
}
