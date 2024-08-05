<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ZoomToken;
use App\Services\ZoomService; 
use Illuminate\Support\Facades\Http; 
use App\Models\ZoomMeeting;
use App\Models\Teacher;
use App\Models\Student;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZoomController extends Controller
{

  private $zoomService; 

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

//     public function createMeeting(Request $request)
//     {
//         $request->validate([
//             'topic' => 'required|string',
//             'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
//             'agenda' => 'required|string',
//               'batch_id' => 'required|exists:courses,id',
//         ]);

//         $accessToken = $this->zoomService->getAccessToken();
//         $meetingConfig = [
//             'topic' => $request->input('topic'),
//             'start_time' => $request->input('start_time'),
//             'agenda' => $request->input('agenda'),
//             'batch_id' => $request->input('batch_id'),
//         ];

//         $zoomDetails = $this->create_a_zoom_meeting($meetingConfig, $accessToken);

//         return response()->json($zoomDetails);
//     }

// private function create_a_zoom_meeting($meetingConfig, $accessToken)
// {
//     $requestBody = [
//         'topic'      => $meetingConfig['topic'] ?? 'New Meeting',
//         'type'       => 2, // Scheduled meeting
//         'start_time' => $meetingConfig['start_time'],
//         'duration'   => 30, // Default duration in minutes
//         'password'   => mt_rand(), // Random password
//         'timezone'   => 'Asia/Kolkata',
//         'agenda'     => $meetingConfig['agenda'],
//         'settings'   => [
//             'host_video'        => false,
//             'participant_video' => true,
//             'join_before_host'  => true, 
//             'mute_upon_entry'   => true,
//             'waiting_room'      => false,
//             'auto_recording'    => 'none',
//         ],
//     ];

//     $curl = curl_init();
//     curl_setopt_array($curl, [
//         CURLOPT_URL            => "https://api.zoom.us/v2/users/me/meetings",
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_SSL_VERIFYPEER => false,
//         CURLOPT_HTTPHEADER     => [
//             "Authorization: Bearer " . $accessToken,
//             "Content-Type: application/json",
//         ],
//         CURLOPT_POST           => true,
//         CURLOPT_POSTFIELDS     => json_encode($requestBody),
//     ]);

//     $response = curl_exec($curl);
//     $error = curl_error($curl);
//     curl_close($curl);

//     if ($error) {
//         return [
//             'success'  => false,
//             'msg'      => 'cURL Error #:' . $error,
//             'response' => null,
//         ];
//     } else {
//         $zoomData = json_decode($response, true);
//         dd($zoomData);

//         // Save meeting details in the database
//         \App\Models\ZoomMeeting::create([
//             'uuid'         => $zoomData['uuid'] ?? null,
//             'meeting_id'   => $zoomData['id'],
//             'host_id'      => $zoomData['host_id'],
//             'host_email'   => $zoomData['host_email'],
//             'topic'        => $zoomData['topic'],
//             'type'         => $zoomData['type'],
//             'status'       => $zoomData['status'],
//             'start_time'   => $zoomData['start_time'],
//             'duration'     => $zoomData['duration'],
//             'timezone'     => $zoomData['timezone'],
//             'agenda'       => $zoomData['agenda'],
//             'start_url'    => $zoomData['start_url'] ?? null,
//             'join_url'     => $zoomData['join_url'] ?? null,
//             'password'     => $zoomData['password'] ?? null,
//             'batch_id'     => $meetingConfig['agenda'] ?? null,
            
//         ]);

//         return [
//             'success'  => true,
//             'msg'      => 'Meeting created and saved successfully',
//             'response' => $zoomData,
//         ];
//     }
// }

public function createMeeting(Request $request)
{
    $request->validate([
        'topic' => 'required|string',
        'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
        'agenda' => 'required|string',
        'batch_id' => 'required|exists:courses,id',
        'duration' => 'required',
    ]);

    $accessToken = $this->zoomService->getAccessToken();
    $meetingConfig = [
        'topic' => $request->input('topic'),
        'start_time' => $request->input('start_time'),
        'agenda' => $request->input('agenda'),
        'batch_id' => $request->input('batch_id'),
        'duration' => $request->input('duration'),
    ];

    $zoomDetails = $this->create_a_zoom_meeting($meetingConfig, $accessToken);

    return response()->json($zoomDetails);
}

private function create_a_zoom_meeting($meetingConfig, $accessToken)
{
    // dd($meetingConfig);
    $requestBody = [
        'topic'      => $meetingConfig['topic'] ?? 'New Meeting',
        'type'       => 2, // Scheduled meeting
        'start_time' => $meetingConfig['start_time'],
        'duration'   =>  $meetingConfig['duration'], // Default duration in minutes
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
            //   dd($meetingConfig['batch_id']);
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
            'batch_id'     => $meetingConfig['batch_id'],
        ]);

        return [
            'code'  => 200,
            'success'  => true,
            'msg'      => 'Meeting created and saved successfully',
            // 'response' => $zoomData,
        ];
    }
}


  public function getAllMeetings()
    {
        try {
            // Fetch all records from the zoom_meetings table
            $meetings = ZoomMeeting::orderBy('created_at', 'desc')->get();
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

    public function viewMeeting($meetingId)
{
    // Retrieve the meeting data from the database using the meeting ID 
    $zoomMeeting = \App\Models\ZoomMeeting::where('meeting_id', $meetingId)->first();

    // Check if the meeting exists
    if (!$zoomMeeting) {
        return response()->json([
            'success' => false,
            'msg'     => 'Meeting not found',
        ], 404);
    }

    // Return the meeting data
    return response()->json([
        'success' => true,
        'data'    => [
            'topic'      => $zoomMeeting->topic,
            'agenda'     => $zoomMeeting->agenda,
            'start_time' => $zoomMeeting->start_time,
            'duration'   => $zoomMeeting->duration,
            'batch_id'   => $zoomMeeting->batch_id,
           
        ],
    ]);
}

// public function updateMeeting(Request $request, $meetingId)
// {
//     $request->validate([
//         'topic' => 'nullable|string',
//         'start_time' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
//         'agenda' => 'nullable|string',
//         'duration' => 'nullable|integer|min:1',
//         'host_video' => 'nullable|boolean',
//         'participant_video' => 'nullable|boolean',
//         'join_before_host' => 'nullable|boolean',
//         'mute_upon_entry' => 'nullable|boolean',
//         'waiting_room' => 'nullable|boolean',
//         'auto_recording' => 'nullable|string|in:none,local,cloud',
//         'batch_id' => 'required|exists:courses,id',
        
//     ]);

//     $accessToken = $this->zoomService->getAccessToken();

//     $meetingConfig = [
//         'topic'      => $request->input('topic'),
//         'start_time' => $request->input('start_time'),
//         'agenda'     => $request->input('agenda'),
//         'duration'   => $request->input('duration'),
//         'batch_id' => $request->input('batch_id'),
//         'timezone'   => 'Asia/Kolkata',
//         'settings'   => [
//             'host_video'        => $request->input('host_video', false),
//             'participant_video' => $request->input('participant_video', true),
//             'join_before_host'  => $request->input('join_before_host', true),
//             'mute_upon_entry'   => $request->input('mute_upon_entry', true),
//             'waiting_room'      => $request->input('waiting_room', false),
//             'auto_recording'    => $request->input('auto_recording', 'none'),
//         ],
//     ];

//     $client = new \GuzzleHttp\Client();

//     try {
//         $response = $client->request('PATCH', "https://api.zoom.us/v2/meetings/$meetingId", [
//             'headers' => [
//                 'Authorization' => "Bearer $accessToken",
//                 'Content-Type'  => 'application/json',
//             ],
//             'json' => $meetingConfig,
//         ]);

//         $body = $response->getBody()->getContents();
//         $zoomData = json_decode($body, true);

//         $zoomMeeting = \App\Models\ZoomMeeting::where('meeting_id', $meetingId)->first();
//         if ($zoomMeeting) {
//             $zoomMeeting->update([
//                 'topic'      => $request->input('topic', $zoomMeeting->topic),
//                 'agenda'     => $request->input('agenda', $zoomMeeting->agenda),
//                 'start_time' => $request->input('start_time', $zoomMeeting->start_time),
//                 'duration'   => $request->input('duration', $zoomMeeting->duration),
//                 'batch_id' => $request->input('batch_id',$zoomMeeting->batch_id),
//                 'settings'   => json_encode($meetingConfig['settings']),
//             ]);
//         }

//         return response()->json([
//             'success'  => true,
//             'msg'      => 'Meeting updated successfully',
//         ]);
//     } catch (\GuzzleHttp\Exception\ClientException $e) {
//         $responseBody = $e->getResponse()->getBody()->getContents();
//         \Log::error('Client error from Zoom API', ['response' => $responseBody]);

//         return response()->json([
//             'success'  => false,
//             'msg'      => 'Failed to update meeting',
//             'error'    => 'Client error: ' . $e->getMessage(),
//         ], $e->getResponse()->getStatusCode());
//     } catch (\Exception $e) {
//         \Log::error('General error while updating Zoom meeting', ['error' => $e->getMessage()]);

//         return response()->json([
//             'success'  => false,
//             'msg'      => 'Failed to update meeting',
//             'error'    => 'General error: ' . $e->getMessage(),
//         ], 500);
//     }
// }


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
        'batch_id' => 'required|exists:courses,id',
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

    $client = new \GuzzleHttp\Client();

    try {
        $response = $client->request('PATCH', "https://api.zoom.us/v2/meetings/$meetingId", [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type'  => 'application/json',
            ],
            'json' => $meetingConfig,
        ]);

        $body = $response->getBody()->getContents();
        $zoomData = json_decode($body, true);

        // Update meeting details in the database
        $zoomMeeting = \App\Models\ZoomMeeting::where('meeting_id', $meetingId)->first();
        if ($zoomMeeting) {
            $zoomMeeting->update([
                'topic'        => $request->input('topic', $zoomMeeting->topic),
                'agenda'       => $request->input('agenda', $zoomMeeting->agenda),
                'start_time'   => $request->input('start_time', $zoomMeeting->start_time),
                'duration'     => $request->input('duration', $zoomMeeting->duration),
                'batch_id'     => $request->input('batch_id', $zoomMeeting->batch_id),
                'settings'     => json_encode($meetingConfig['settings']),
            ]);
        }

        return response()->json([
            'success'  => true,
            'msg'      => 'Meeting updated successfully',
        ]);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $responseBody = $e->getResponse()->getBody()->getContents();
        \Log::error('Client error from Zoom API', ['response' => $responseBody]);

        return response()->json([
            'success'  => false,
            'msg'      => 'Failed to update meeting',
            'error'    => 'Client error: ' . $e->getMessage(),
        ], $e->getResponse()->getStatusCode());
    } catch (\Exception $e) {
        \Log::error('General error while updating Zoom meeting', ['error' => $e->getMessage()]);

        return response()->json([
            'success'  => false,
            'msg'      => 'Failed to update meeting',
            'error'    => 'General error: ' . $e->getMessage(),
        ], 500);
    }
} 
  
public function getUserMeetings(Request $request)
    {
        // Validate the request data
        
        try {
            $request->validate([
            'role' => 'required|string|in:teacher,student',
            'user_id' => 'required|integer',
        ]);

            $courseIds = [];

            $role = $request->input('role');
            $userId = $request->input('user_id');

            if ($role === 'teacher') {
                // Fetch the teacher
                $teacher = Teacher::findOrFail($userId);

                // Get all courses the teacher is assigned to
                $courseIds = DB::table('courses')
                    ->join('course_teacher', 'courses.id', '=', 'course_teacher.course_id')
                    ->where('course_teacher.teacher_id', $userId)
                    ->pluck('courses.id')
                    ->toArray();
            } elseif ($role === 'student') {
                // Fetch the student
                $student = Student::findOrFail($userId);

                // Get all courses the student is enrolled in
                $courseIds = DB::table('courses_enrollments')
                    ->where('student_id', $userId)
                    ->pluck('course_id')
                    ->toArray();
            } else {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Invalid role specified'
                ], 400);
            }

            // Retrieve all meetings associated with these courses
            $meetings = ZoomMeeting::whereIn('batch_id', $courseIds)->get();

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $meetings,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
                'message' => 'Failed to retrieve meetings',
                'error' => $e->getMessage(),
            ], 500);
        }
    } 
} 


