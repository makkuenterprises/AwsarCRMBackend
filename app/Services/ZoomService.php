<?php
namespace App\Services;

use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ZoomService
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => config('zoom.client_id'),
            'clientSecret'            => config('zoom.client_secret'),
            'redirectUri'             => config('zoom.redirect_uri'),
            'urlAuthorize'            => config('zoom.auth_url'),
            'urlAccessToken'          => config('zoom.token_url'),
            'urlResourceOwnerDetails' => ''
        ]);
    }

    public function getAuthorizationUrl()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        Session::put('oauth2state', $this->provider->getState());

        return $authorizationUrl;
    }

    public function getAccessToken($code)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
    }

    public function createMeeting($accessToken, $data)
    {
        $response = Http::withToken($accessToken)
            ->post(config('zoom.api_url') . '/users/me/meetings', $data);

        return $response->json();
    }
}
