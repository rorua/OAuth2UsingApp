<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private $CLIENT_ID;

    private $CLIENT_SECRET;

    private $CALLBACK_URL;

    private $PROVIDER_URL;

    public function __construct()
    {
        $this->CLIENT_ID = config('services.fusionauth.client_id');
        $this->CLIENT_SECRET = config('services.fusionauth.client_secret');
        $this->CALLBACK_URL = config('services.fusionauth.callback_url');
        $this->PROVIDER_URL = config('services.fusionauth.provider_url');
    }


    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToProvider()
    {
        $client_id = $this->CLIENT_ID;
        $callback_url = $this->CALLBACK_URL;
        $provider_url = $this->PROVIDER_URL;

        return redirect("{$provider_url}/oauth2/authorize?client_id={$client_id}&redirect_uri={$callback_url}&response_type=code");
    }

    /**
     * @param Request $request
     * @return array
     */
    public function handleProviderCallback(Request $request)
    {
        $provider_url = $this->PROVIDER_URL;
        $token_url = $provider_url . '/oauth2/token';

        $params = [
            'client_id' => $this->CLIENT_ID,
            'redirect_uri' => $this->CALLBACK_URL,
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
        ];

        $query_params = '?';
        foreach ($params as $key => $param) {
            $query_params .= $key . '=' . $param . '&';
        }

        $header = base64_encode($this->CLIENT_ID . ':' . $this->CLIENT_SECRET);

        $guzzle = new Client();
        $response = $guzzle->request('POST', $token_url . $query_params, [
            'headers' => ['Authorization' => 'Basic ' . $header],
        ]);

        if ($response->getStatusCode() == 200) {

            $res = json_decode($response->getBody()->getContents());

            $access_token = $res->access_token;
            $expires_in = $res->expires_in;
            $token_type = $res->token_type;
            $user_id = $res->userId;

            $userFromProvider = $this->getUserByToken($access_token);

            $user = $this->findOrCreateUser($userFromProvider);

            auth()->login($user);

            return redirect('/home');

        }

        //todo
        return redirect('/');
        //return compact('access_token', 'expires_in', 'token_type', 'user_id');

    }

    /**
     * @param $token
     * @return mixed
     */
    private function getUserByToken($token)
    {
        $userinfo_url = $this->PROVIDER_URL . '/oauth2/userinfo';

        $guzzle = new Client();

        $response = $guzzle->request('GET', $userinfo_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $userFromProvider
     * @return User
     */
    private function findOrCreateUser($userFromProvider)
    {
        $user = User::firstOrNew(['email' => $userFromProvider['email']]);

        if ($user->exists) return $user;

        $user->fill([
            'name' => $userFromProvider['given_name'],
            'surname' => $userFromProvider['family_name'],
            'avatar' => $userFromProvider['picture'],
            'phone_number' => $userFromProvider['phone_number'],
            'birthday' => $userFromProvider['birthdate'],
        ])->save();

        return $user;
    }

    /**
     * Logout user from session
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        $logout_url = $this->PROVIDER_URL . '/oauth2/logout?client_id=' . $this->CLIENT_ID;

        $guzzle = new Client();

        $response = $guzzle->request('GET', $logout_url);

        auth()->logout();

        return redirect('/');

    }
}
