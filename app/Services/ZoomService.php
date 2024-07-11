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
        $payload = [
            'iss' => $key,
            'exp' => Carbon::now()->addMinute()->timestamp,
        ];

        return JWT::encode($payload, $secret);
    }

    public function createMeeting($data)
    {
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
                    'host_video'      => false,
                    'participant_video' => false,
                    'waiting_room'    => true,
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}