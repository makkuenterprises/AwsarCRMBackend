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
    try {
        $key = env('ZOOM_API_KEY');
        $secret = env('ZOOM_API_SECRET');
        $algorithm = 'HS256';

        if (!$key || !$secret) {
            throw new \Exception('Zoom API credentials are missing or incorrect.');
        }

        $payload = [
            'iss' => $key,
            'exp' => Carbon::now()->addMinutes(15)->timestamp, // Example: JWT valid for 15 minutes
        ];

        $token = JWT::encode($payload, $secret, $algorithm);

        // Log the generated token for debugging
        \Log::info('Generated JWT token: ' . $token);

        return $token;
    } catch (\Exception $e) {
        // Log the error or handle it accordingly
        \Log::error('Error generating JWT token: ' . $e->getMessage());
        return null; // Or throw an exception depending on your error handling strategy
    }
}
 
 
public function createMeeting($data)
{
    try {
        $token = $this->generateJWT();

          if (!$token) {
            throw new \Exception('Failed to generate valid JWT token.');
        }


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
        // Log the error or handle it accordingly
        \Log::error('Error creating Zoom meeting: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}


} 