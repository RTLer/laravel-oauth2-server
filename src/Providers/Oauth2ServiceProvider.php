<?php

namespace RTLer\Oauth2\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RTLer\Oauth2\Oauth2Server;

class Oauth2ServiceProvider extends ServiceProvider
{
    protected $options;

    public function __construct(Application $app)
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
        $this->publishes([
            __DIR__ . '../publish/config/oauth2.php' => config_path('oauth2.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Oauth2Server::class, function () {
            return new Oauth2Server($this->options);
        });
    }
}
