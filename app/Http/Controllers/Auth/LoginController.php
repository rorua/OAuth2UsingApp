<?php

namespace App\Http\Controllers\Auth;

use App\Auth\OAuthorize;
use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        return (new OAuthorize($provider))->redirect();
    }

    /**
     * @param string $provider
     * @return array
     */
    public function handleProviderCallback($provider)
    {
        $userFromProvider = (new OAuthorize($provider))->user();

        if ($provider == 'fusionauth') {

            $user = $this->findOrCreateFusionAuthUser($userFromProvider);

            auth()->login($user);

        } elseif ($provider == 'keycloak') {

            $user = $this->findOrCreateKeycloakUser($userFromProvider);

            auth()->login($user);
        }

        return redirect('/home');

    }

    /**
     * @param $userFromProvider
     * @return User
     */
    private function findOrCreateFusionAuthUser($userFromProvider)
    {
        $user = User::firstOrNew([
            'provider_id' => $userFromProvider['sub'],
            'provider' => 'fusionauth',
        ]);

        if ($user->exists) return $user;

        $user->fill([
            'email' => $userFromProvider['email'],
            'name' => $userFromProvider['given_name'],
            'surname' => $userFromProvider['family_name'],
            'avatar' => $userFromProvider['picture'],
            'phone_number' => $userFromProvider['phone_number'],
            'birthday' => $userFromProvider['birthdate'],
        ])->save();

        return $user;
    }

    /**
     * @param $userFromProvider
     * @return User
     */
    private function findOrCreateKeycloakUser($userFromProvider)
    {
        $user = User::firstOrNew([
            'provider_id' => $userFromProvider['sub'],
            'provider' => 'keycloak',
        ]);

        if ($user->exists) return $user;

        $user->fill([
            'email' => $userFromProvider['email'],
            'name' => $userFromProvider['given_name'],
            'surname' => $userFromProvider['family_name'],
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
        auth()->logout();

        return redirect('/');

    }
}
