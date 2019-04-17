<?php

namespace App\Auth;


use App\Auth\Providers\KeycloakProvider;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * TODO not finished
 */
class OAuthorizeManager extends Manager implements Contracts\Factory
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Build an OAuth 2 provider instance.
     * @param $provider
     * @param $config
     * @return mixed
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'], $config['client_id'],
            $config['client_secret'], $this->formatRedirectUrl($config),
            Arr::get($config, 'guzzle', [])
        );
    }

    /**
     * Format the callback URL, resolving a relative URI if needed.
     *
     * @param  array  $config
     * @return string
     */
    protected function formatRedirectUrl(array $config)
    {
        $redirect = value($config['redirect']);

        return Str::startsWith($redirect, '/')
            ? $this->app['url']->to($redirect)
            : $redirect;
    }


    protected function createFuaionauthDriver()
    {
        $config = $this->app['config']['services.fusionauth'];

        return $this->buildProvider(
            KeycloakProvider::class, $config
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No OAUTH driver was specified.');
    }
}