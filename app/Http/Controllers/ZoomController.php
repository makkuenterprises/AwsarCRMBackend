<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ZoomToken;
use App\Services\ZoomService; 
use Illuminate\Support\Facades\Http; 
use App\Models\ZoomMeeting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZoomController extends Controller
{

  private $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function createMeeting(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
            'agenda' => 'required|string',
        ]);

        $accessToken = $this->zoomService->getAccessToken();
        $meetingConfig = [
            'topic' => $request->input('topic'),
            'start_time' => $request->input('start_time'),
            'agenda' => $request->input('agenda'),
        ];

        $zoomDetails = $this->create_a_zoom_meeting($meetingConfig, $accessToken);

        return response()->json($zoomDetails);
    }

private function create_a_zoom_meeting($meetingConfig, $accessToken)
{
    $requestBody = [
        'topic'      => $meetingConfig['topic'] ?? 'New Meeting',
        'type'       => 2, // Scheduled meeting
        'start_time' => $meetingConfig['start_time'],
        'duration'   => 30, // Default duration in minutes
        'password'   => mt_rand(), // Random password
        'timezone'   => 'Asia/Kolkata',
        'agenda'     => $meetingConfig['agenda'],
        'settings'   => [
            'host_video'        => false,
            'participant_video' => true,
            'join_before_host'  => true,
            'mute_upon_entry'   => true,
            'waiting_room'      => false,
            'auto_recording'    => 'none',
        ],
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://api.zoom.us/v2/users/me/meetings",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json",
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($requestBody),
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return [
            'success'  => false,
            'msg'      => 'cURL Error #:' . $error,
            'response' => null,
        ];
    } else {
        $zoomData = json_decode($response, true);

        // Save meeting details in the database
        \App\Models\ZoomMeeting::create([
            'uuid'         => $zoomData['uuid'] ?? null,
            'meeting_id'   => $zoomData['id'],
            'host_id'      => $zoomData['host_id'],
            'host_email'   => $zoomData['host_email'],
            'topic'        => $zoomData['topic'],
            'type'         => $zoomData['type'],
            'status'       => $zoomData['status'],
            'start_time'   => $zoomData['start_time'],
            'duration'     => $zoomData['duration'],
            'timezone'     => $zoomData['timezone'],
            'agenda'       => $zoomData['agenda'],
            'start_url'    => $zoomData['start_url'] ?? null,
            'join_url'     => $zoomData['join_url'] ?? null,
            'password'     => $zoomData['password'] ?? null,
        ]);

        return [
            'success'  => true,
            'msg'      => 'Meeting created and saved successfully',
            'response' => $zoomData,
        ];
    }
}

  public function getAllMeetings()
    {
        try {
            // Fetch all records from the zoom_meetings table
            $meetings = ZoomMeeting::all();

            return response()->json([
                'success' => true,
                'data' => $meetings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meetings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
  

   public function deleteMeeting($meetingId)
    {
        $accessToken = $this->zoomService->getAccessToken();
        
        // Step 1: Delete the meeting from Zoom
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api.zoom.us/v2/meetings/$meetingId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer " . $accessToken,
            ],
            CURLOPT_CUSTOMREQUEST  => "DELETE",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return response()->json([
                'success'  => false,
                'msg'      => 'cURL Error #:' . $error,
            ]);
        } else {
            // Step 2: Delete the meeting record from the database
            try {
                $zoomMeeting = ZoomMeeting::where('meeting_id', $meetingId)->firstOrFail();
                $zoomMeeting->delete();

                return response()->json([
                    'success'  => true,
                    'msg'      => 'Meeting deleted successfully from Zoom and database',
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success'  => false,
                    'msg'      => 'Meeting not found in the database',
                    'error'    => $e->getMessage()
                ], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'success'  => false,
                    'msg'      => 'Failed to delete meeting from the database',
                    'error'    => $e->getMessage()
                ], 500);
            }
        }
    }
public function updateMeeting(Request $request, $meetingId)
{
    $request->validate([
        'topic' => 'nullable|string',
        'start_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
        'agenda' => 'nullable|string',
        'duration' => 'nullable|integer|min:1',
        'host_video' => 'nullable|boolean',
        'participant_video' => 'nullable|boolean',
        'join_before_host' => 'nullable|boolean',
        'mute_upon_entry' => 'nullable|boolean',
        'waiting_room' => 'nullable|boolean',
        'auto_recording' => 'nullable|string|in:none,local,cloud',
    ]);

    $accessToken = $this->zoomService->getAccessToken();

    $meetingConfig = [
        'topic'      => $request->input('topic'),
        'start_time' => $request->input('start_time'),
        'agenda'     => $request->input('agenda'),
        'duration'   => $request->input('duration'),
        'timezone'   => 'Asia/Kolkata',
        'settings'   => [
            'host_video'        => $request->input('host_video', false),
            'participant_video' => $request->input('participant_video', true),
            'join_before_host'  => $request->input('join_before_host', true),
            'mute_upon_entry'   => $request->input('mute_upon_entry', true),
            'waiting_room'      => $request->input('waiting_room', false),
            'auto_recording'    => $request->input('auto_recording', 'none'),
        ],
    ];


    $client = new Client();

    try {
        $response = $client->request('PATCH', "https://api.zoom.us/v2/meetings/$meetingId", [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type'  => 'application/json',
            ],
            'json' => $meetingConfig,
        ]);

        $body = $response->getBody();
        $zoomData = json_decode($body, true);

        return response()->json([
            'success'  => true,
            'msg'      => 'Meeting updated successfully',
            'response' => $zoomData,
        ]);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        return response()->json([
            'success'  => false,
            'msg'      => 'Failed to update meeting',
            'error'    => $e->getMessage(),
        ], $e->getResponse()->getStatusCode());
    } catch (\Exception $e) {
        return response()->json([
            'success'  => false,
            'msg'      => 'Failed to update meeting',
            'error'    => $e->getMessage(),
        ], 500);
    }
}


} 