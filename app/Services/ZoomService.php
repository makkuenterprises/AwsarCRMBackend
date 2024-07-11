<?php
namespace App\Services;

use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;

class ZoomService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); 
    }

private function generateJWT()
{
    $key = env('ZOOM_API_KEY');
    $secret = env('ZOOM_API_SECRET');
    $algorithm = 'HS256'; 

    $payload = [
        'iss' => $key,
        'exp' => Carbon::now()->addMinutes(15)->timestamp, // Example: JWT valid for 15 minutes
    ];

    return JWT::encode($payload, $secret, $algorithm);
}



 public function createMeeting($data)
{
    try {
        $token = $this->generateJWT();
        $response = $this->client->post('https://api.zoom.us/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'topic'      => $data['topic'],
                'type'       => 2,
                'start_time' => $data['start_time'],
                'duration'   => $data['duration'],  // Duration in minutes
                'agenda'     => $data['agenda'],
                'settings'   => [
                    'host_video'        => false,
                    'participant_video' => false,
                    'waiting_room'      => true,
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    } catch (\Exception $e) {
        // Handle exceptions, e.g., log error messages, retry token generation, etc.
        return ['error' => $e->getMessage()];
    }
}


} 