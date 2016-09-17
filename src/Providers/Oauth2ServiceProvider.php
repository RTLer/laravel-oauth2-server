<?php

namespace RTLer\Oauth2\Providers;

use Illuminate\Support\ServiceProvider;
use RTLer\Oauth2\Oauth2Server;

class Oauth2ServiceProvider extends ServiceProvider
{
    protected $options;

    public function __construct($app)
    {
        if (method_exists($app, 'configure')) {
            // @codeCoverageIgnoreStart
            $app->configure('oauth2');
            // @codeCoverageIgnoreEnd
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
            __DIR__.'/../publish/config/oauth2.php' => base_path('config/oauth2.php'),
        ]);
        $this->publishes([
            __DIR__.'/../publish/Oauth2/UserVerifier.php' => base_path('app/Oauth2/UserVerifier.php'),
        ]);
        $this->publishes([
            __DIR__.'/../publish/migrations/2016_05_30_155444_CreateOauth2Tables.php' => base_path('database/migrations/2016_05_30_155444_CreateOauth2Tables.php'),
        ]);

        $this->commands([
            \RTLer\Oauth2\Commands\PersonalAccessClientCommand::class,
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
