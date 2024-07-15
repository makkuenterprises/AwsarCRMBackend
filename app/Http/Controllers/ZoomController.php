<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Jubaer\Zoom\Facades\Zoom;

class ZoomController extends Controller
{ 
 
public function createMeeting(Request $request)
{
    $validated = $request->validate([
        'agenda' => 'required|string',
        'topic' => 'required|string',
        'duration' => 'required|integer',
        'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
        'password' => 'nullable|string',
    ]);
 
    $meetingData = [
        'agenda' => $validated['agenda'],
        'topic' => $validated['topic'],
        'type' => 2, // Scheduled meeting
        'duration' => $validated['duration'],
        'timezone' => 'Asia/Dhaka',
        'password' => $validated['password'] ?? '',
        'start_time' => $validated['start_time'],
        'settings' => [
            'join_before_host' => false,
            'host_video' => true,
            'participant_video' => true,
            'mute_upon_entry' => true,
            'waiting_room' => true,
            'audio' => 'both',
            'auto_recording' => 'cloud',
            'approval_type' => 0,
        ],
    ];

    // Create a new meeting instance
    $meeting = Zoom::meeting()->make($meetingData);

    // Save the meeting
    $response = $meeting->save();

    return response()->json($response);
}}