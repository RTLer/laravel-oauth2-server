<?php

namespace RTLer\Oauth2\Providers;

use Illuminate\Support\ServiceProvider;
use RTLer\Oauth2\Authorizer;

class Oauth2ServiceProvider extends ServiceProvider
{
    protected $options;

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        $this->options = $app->make('config')->get('oauth2');

        parent::__construct($app);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->options['grants'] as $name => $options) {
            $name = camel_case('enable_'.$name.'_grant');

            $this->app[Authorizer::class]->{$name}($options);
        }

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Authorizer::class, function () {
            return new Authorizer($this->options['private_key'], $this->options['public_key']);
        });
    }
}
