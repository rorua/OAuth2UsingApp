<?php

namespace App\Auth;
use App\Auth\Providers\AbstractProvider;
use Illuminate\Support\Str;

/**
 * Created by PhpStorm.
 * User: Programmer
 * Date: 17.04.2019
 * Time: 15:31
 */
class OAuthorize
{
    /**
     * @var AbstractProvider
     */
    private $provider;

    /**
     * OAuthorize constructor.
     * @param $providerName
     */
    public function __construct($providerName)
    {
        $this->provider = $this->getProvider($providerName);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        return $this->provider->redirect();
    }

    public function user()
    {
        return $this->provider->user();
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    private function getProvider($providerName)
    {
        $providerClass = 'App\Auth\Providers\\'.Str::studly($providerName).'Provider';

        return (new $providerClass);
    }

}