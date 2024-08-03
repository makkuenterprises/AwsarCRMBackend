<?php
namespace App\Services;

use App\Models\ZoomToken;
use Illuminate\Support\Facades\Http;

class ZoomService
{
    private $clientId;
    private $clientSecret; 
    private $accountId;

    public function __construct()
    {
        // Load credentials from config or environment
        // $this->clientId = '2sRe8kjIRHi3m0gqmDqMrQ';
        // $this->clientSecret = 'eOkWtXLchTSEHg8jV1ywqISRTDzLb34p';
        // $this->accountId = 'w_RHU_PQSMu_EoUd_ke9bA';

    $this->clientId = env('ZOOM_CLIENT_ID');
    $this->clientSecret = env('ZOOM_CLIENT_SECRET');
    $this->accountId = env('ZOOM_ACCOUNT_ID');
    }

    public function generateToken()
    {
        $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Host' => 'zoom.us',
        ])->asForm()->post('https://zoom.us/oauth/token', [
            'grant_type' => 'account_credentials',
            'account_id' => $this->accountId,
        ]);

        if ($response->ok()) {
            $data = $response->json();
            $this->storeToken($data['access_token'], $data['expires_in']);
            return $data;
        }

        return null;
    }
 
    private function storeToken($accessToken, $expiresIn)
    {
        $expiresAt = now()->addSeconds($expiresIn);
        ZoomToken::updateOrCreate(
            ['id' => 1], // Assuming only one record is stored
            ['access_token' => $accessToken, 'expires_at' => $expiresAt]
        );
    }

    public function getAccessToken()
    {
        $token = ZoomToken::find(1); // Assuming only one record is stored
        if ($token && $token->expires_at > now()) {
            return $token->access_token;
        }

        // Token is expired or not present; generate a new one
        $this->generateToken();
        return ZoomToken::find(1)->access_token;
    }
}
