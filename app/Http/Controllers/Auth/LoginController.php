<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    const CLIENT_ID = 'a00c5abb-84d8-441c-ab73-db744334c6a7';

    const CLIENT_SECRET = 'irkjzhbGVolA6iFDf4VJClMNP2jI_VDXo1kwYQWvaKo';

    const REDIRECT_URL = 'http://auth.loc/login/callback';

    const PROVIDER_URL = 'http://localhost:9011';

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToProvider()
    {
        $client_id = self::CLIENT_ID;
        $redirect_url = self::REDIRECT_URL;
        $provider_url = self::PROVIDER_URL;

        return redirect("{$provider_url}/oauth2/authorize?client_id={$client_id}&redirect_uri={$redirect_url}&response_type=code");
    }

    /**
     * @param Request $request
     * @return array
     */
    public function handleProviderCallback(Request $request)
    {
        $provider_url = self::PROVIDER_URL;
        $token_url = $provider_url . '/oauth2/token';

        $params = [
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::REDIRECT_URL,
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
        ];

        $query_params = '?';
        foreach ($params as $key => $param) {
            $query_params .= $key . '=' . $param . '&';
        }

        $guzzle = new Client();

        $header = base64_encode(self::CLIENT_ID . ':' . self::CLIENT_SECRET);

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

        //return redirect('/');
        return compact('access_token', 'expires_in', 'token_type', 'user_id');

    }

    /**
     * @param $token
     * @return mixed
     */
    private function getUserByToken($token)
    {
        $userinfo_url = self::PROVIDER_URL . '/oauth2/userinfo';

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
        $logout_url = self::PROVIDER_URL . '/oauth2/logout?client_id=' . self::CLIENT_ID;

        $guzzle = new Client();

        $response = $guzzle->request('GET', $logout_url);

        auth()->logout();

        return redirect('/');

    }
}
