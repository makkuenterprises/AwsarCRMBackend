<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZoomController extends Controller
{

    public function __construct()
    {

    }
    //++++++++++++++++++++++++++++++++++++++++++++++++
    //++++++++++++++++++++++++++++++++++++++++++++++++
    public function index(Request $request)
    {
        if (!$request->code) {
            $this->get_oauth_step_1();
        } else {
            $getToken         = $this->get_oauth_step_2($request->code);
            $get_zoom_details = $this->create_a_zoom_meeting([
                'topic'      => 'Interview With Code-180',
                'start_time' => date('Y-m-dTh:i:00') . 'Z',
                'agenda'     => "We are having interview with @code-180",
                'jwtToken'   => 'eyJzdiI6IjAwMDAwMSIsImFsZyI6IkhTNTEyIiwidiI6IjIuMCIsImtpZCI6ImU5ODdiMThiLWNlZmUtNGQxOS1iMGFlLWIwOTFjYTlhYWFjNCJ9.eyJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiJidHB1R0hkMFJSV1NTUXFEaWYyZVFRIiwidmVyIjo5LCJhdWlkIjoiOWE3OWNhN2RlNjg4NzE2OTlkOWNjYTk4MzZiZjcwNGMiLCJuYmYiOjE3MjIwNTYxNzksImNvZGUiOiJZX2tSMFppTFN0Q1hjVWp2QXhROGt3eE1TZ1Jra2FWblgiLCJpc3MiOiJ6bTpjaWQ6YnJhb19vdFhRczZrSktZWE9kZkZUUSIsImdubyI6MCwiZXhwIjoxNzIyMDU5Nzc5LCJ0eXBlIjozLCJpYXQiOjE3MjIwNTYxNzksImFpZCI6IndfUkhVX1BRU011X0VvVWRfa2U5YkEifQ.8pobZYzw7RPCIr2fz1Oiho2BvzMXmtLOyy_qWoWu4pY6ADWXlOpdVYqbHZmyhZ5Mc0ZgSzMfoEOTdyG4rc6iJA',
            ]);
            //dd($get_zoom_details);
            return view('welcome')->with('respond', json_encode($get_zoom_details));
        }
    }
    //++++++++++++++++++++++++++++++++++++++++++++++++
    //++++++++++++++++++++++++++++++++++++++++++++++++
    private function get_oauth_step_1()
    {
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $redirectURL  = 'http://zoommeetingoauth.test/zoom-meeting-create';
        $authorizeURL = 'https://zoom.us/oauth/authorize';
        //++++++++++++++++++++++++++++++++++++++++++++++++++
        $clientID     = '585Iz_bTqC2T4U7WN7mLA';
        // dd($clientID);
        $clientSecret = 'XL6voQIn3ScAwpj14fFs6KZkoB7rpEpw';
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $authURL = $authorizeURL . '?client_id=' . $clientID . '&redirect_uri=' . $redirectURL . '&response_type=code&scope=&state=xyz';
        header('Location: ' . $authURL);
        exit;
    }
    //++++++++++++++++++++++++++++++++++++++++++++++++
    //++++++++++++++++++++++++++++++++++++++++++++++++
    private function get_oauth_step_2($code)
    {
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $tokenURL    = 'https://zoom.us/oauth/token';
        $redirectURL = 'http://zoommeetingoauth.test/zoom-meeting-create';
        //++++++++++++++++++++++++++++++++++++++++++++++++++
          $clientID     = '585Iz_bTqC2T4U7WN7mLA';
        // dd($clientID);
        $clientSecret = 'XL6voQIn3ScAwpj14fFs6KZkoB7rpEpw';
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $curl   = curl_init();
        $params = array(CURLOPT_URL => $tokenURL . "?"
            . "code=" . $code
            . "&grant_type=authorization_code"
            . "&client_id=" . $clientID
            . "&client_secret=" . $clientSecret
            . "&redirect_uri=" . $redirectURL,
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_MAXREDIRS           => 10,
            CURLOPT_TIMEOUT             => 30,
            CURLOPT_HTTP_VERSION        => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST       => "POST",
            CURLOPT_NOBODY              => false,
            CURLOPT_HTTPHEADER          => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "accept: *",
            ),
        );
        curl_setopt_array($curl, $params);
        $response = curl_exec($curl);
        //++++++++++++++++++++++++++++++++++++++++++++++++++
        $err = curl_error($curl);
        curl_close($curl);
        //++++++++++++++++++++++++++++++++++++++++++++++++++
        $response = json_decode($response, true);
        return $response;
    }
    //++++++++++++++++++++++++++++++++++++++++++++++++
    //++++++++++++++++++++++++++++++++++++++++++++++++
    private function create_a_zoom_meetingh($meetingConfig = [])
    {
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $requestBody = [
            'topic'      => $meetingConfig['topic'] ?? 'New Meeting General Talk',
            'type'       => $meetingConfig['type'] ?? 2,
            'start_time' => $meetingConfig['start_time'] ?? date('Y-m-dTh:i:00') . 'Z',
            'duration'   => $meetingConfig['duration'] ?? 30,
            'password'   => $meetingConfig['password'] ?? mt_rand(),
            'timezone'   => 'Asia/Kolkata',
            'agenda'     => $meetingConfig['agenda'] ?? 'Interview Meeting',
            'settings'   => [
                'host_video'        => false,
                'participant_video' => true,
                'cn_meeting'        => false,
                'in_meeting'        => false,
                'join_before_host'  => true,
                'mute_upon_entry'   => true,
                'watermark'         => false,
                'use_pmi'           => false,
                'approval_type'     => 0,
                'registration_type' => 0,
                'audio'             => 'voip',
                'auto_recording'    => 'none',
                'waiting_room'      => false,
            ],
        ];
        //++++++++++++++++++++++++++++++++++++++++++++++++
        //++++++++++++++++++++++++++++++++++++++++++++++++
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://api.zoom.us/v2/users/me/meetings",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($requestBody),
            CURLOPT_HTTPHEADER     => array(
                "Authorization: Bearer " . $meetingConfig['jwtToken'],
                "Content-Type: application/json",
                "cache-control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
        //++++++++++++++++++++++++++++++++++++++++++++++++
        if ($err) {
            return [
                'success'  => false,
                'msg'      => 'cURL Error #:' . $err,
                'response' => null,
            ];
        } else {
            return [
                'success'  => true,
                'msg'      => 'success',
                'response' => json_decode($response, true),
            ];
        }
    }


    public function createMeeting(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'access_token' => 'required|string',
            'topic' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
            'agenda' => 'required|string',
        ]);

        // Extract the access token and other meeting details from the request
        $accessToken = $request->input('access_token');
        $meetingConfig = [
            'topic' => $request->input('topic'),
            'start_time' => $request->input('start_time'),
            'agenda' => $request->input('agenda'),
        ];

        // Call the function to create a Zoom meeting
        $zoomDetails = $this->create_a_zoom_meeting($meetingConfig, $accessToken);

        // Return the result as JSON
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
            return [
                'success'  => true,
                'msg'      => 'Meeting created successfully',
                'response' => json_decode($response, true),
            ];
        }
    }


}