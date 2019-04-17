<?php

namespace App\Auth\Providers;


use App\Auth\Contracts\Provider;
use App\Auth\Contracts\User;
use GuzzleHttp\Client;

class FusionauthProvider extends AbstractProvider implements Provider
{
    /**
     * FusionauthProvider constructor.
     */
    public function __construct()
    {
        $this->clientId = config('services.fusionauth.client_id');
        $this->redirectUrl = urlencode(config('services.fusionauth.callback_url'));
        $this->clientSecret = config('services.fusionauth.client_secret');;
        $this->providerUrl = config('services.fusionauth.provider_url');;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        $client_id = $this->clientId;
        $callback_url = $this->redirectUrl;
        $authUrl = $this->getAuthUrl(null);

        return redirect("{$authUrl}?client_id={$client_id}&redirect_uri={$callback_url}&response_type=code");
    }

    /**
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        $provider_url = $this->providerUrl;

        return "{$provider_url}/oauth2/authorize";
    }


    /**
     * @return string
     */
    protected function getTokenUrl()
    {
        $provider_url = $this->providerUrl;
        $token_url = $provider_url . '/oauth2/token';

        return $token_url;
    }


    /**
     * @param string $token
     * @return mixed
     */
    protected function getUserByToken($token)
    {
        $userinfo_url = $this->providerUrl . '/oauth2/userinfo';

        $guzzle = new Client();

        $response = $guzzle->request('GET', $userinfo_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @return User
     */
    public function user()
    {
        $request = request();

        $token_url = $this->getTokenUrl();

        $params = $this->getTokenFields($request->input('code'));

        $query_params = $this->getQueryParams($params);

        $guzzle = new Client();
        $response = $guzzle->request('POST', $token_url . $query_params, [
            'auth' => [$this->clientId, $this->clientSecret]
        ]);

        if ($response->getStatusCode() == 200) {

            $res = json_decode($response->getBody()->getContents());

            $access_token = $res->access_token;
            $expires_in = $res->expires_in;
            $token_type = $res->token_type;

            $userFromProvider = $this->getUserByToken($access_token);

            return $userFromProvider;
        }

        //todo
        //return $response->getBody();

    }

}