<?php
namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;
use Firebase\JWT\JWT;

trait ZoomMeetingTrait
{
    protected $client;
    protected $jwt;
    protected $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->jwt,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    protected function generateZoomToken()
    {
        $key = env('ZOOM_API_KEY', '');
        $secret = env('ZOOM_API_SECRET', '');
        $payload = [
            'iss' => $key,
            'exp' => time() + 3600, // Token valid for 1 hour
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    private function retrieveZoomUrl()
    {
        // Ensure the URL ends with a slash
        return rtrim(env('ZOOM_API_URL', 'https://api.zoom.us/v2/'), '/') . '/';
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomMeetingTrait->toZoomTimeFormat : ' . $e->getMessage());
            return '';
        }
    }

    public function create($data)
    {
        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl() . $path;

        $body = [
            'json' => [
                'topic'      => $data['topic'],
                'type'       => 2, // 2 for scheduled meeting
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => $data['agenda'] ?? null,
                'timezone'   => 'Asia/Kolkata',
                'settings'   => [
                    'host_video'        => $data['host_video'],
                    'participant_video' => $data['participant_video'],
                    'waiting_room'      => true,
                ],
            ],
            'headers' => $this->headers,
        ];

        try {
            $response = $this->client->post($url, $body);
            $data = json_decode($response->getBody(), true);
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => $data,
            ];
        } catch (RequestException $e) {
            Log::error('Error creating Zoom meeting: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create meeting',
                'error'   => $e->getMessage(),
            ];
        }
    }

    // Other methods (update, get, delete) would follow a similar pattern...
}







