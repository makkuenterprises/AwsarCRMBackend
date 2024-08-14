<?php

namespace App\Http\Controllers;

use App\Services\GoogleMeetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;


class GoogleController extends Controller
{
    protected $googleService;

    public function __construct(GoogleMeetService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function redirectToGoogle()
    {
        return redirect()->to($this->googleService->client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $token = $this->googleService->authenticate($request->get('code'));
        Session::put('google_access_token', $token);

        return redirect()->route('home');
    }

    public function createMeeting(Request $request)
    {
        $this->googleService->setAccessToken(Session::get('google_access_token'));

        $event = $this->googleService->createEvent(
            $request->input('summary'),
            $request->input('description'),
            $request->input('start_datetime'),
            $request->input('end_datetime')
        );

        return response()->json(['event' => $event]);
    }
}

