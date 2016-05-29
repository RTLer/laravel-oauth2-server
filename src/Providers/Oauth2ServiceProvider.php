<?php

namespace RTLer\Oauth2\Providers;

use Illuminate\Support\ServiceProvider;
use RTLer\Oauth2\Oauth2Server;

class Oauth2ServiceProvider extends ServiceProvider
{
    protected $options;

    public function __construct($app)
    {
        try {
            $app->configure('oauth2');
        } catch (\Exception $e) {
        }
        
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
            __DIR__ . '../publish/config/oauth2.php' => base_path('config/oauth2.php'),
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
