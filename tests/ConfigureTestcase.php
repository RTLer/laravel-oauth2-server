<?php
namespace Oauth2Tests;

trait ConfigureTestCase
{
    protected function getPackageProviders($app)
    {
        $app['config']->set('oauth2', [
            'private_key' => __DIR__ . '/Stubs/private.key',
            'private_key_phrase' => '',
            'public_key' => __DIR__ . '/Stubs/private.key',
            'user_verifier' => \App\Oauth2\UserVerifier::class,
            'grants'=>[
                'client_credentials' => [
                    'access_token_ttl' => 10,
                ],
                'auth_code' => [
                    'auth_code_ttl' => 60,
                    'refresh_token_ttl' => 60,
                    'access_token_ttl' => 10,
                ],
                'password' => [
                    'refresh_token_ttl' => 60,
                    'access_token_ttl' => 10,
                ],
                'implicit' => [
                    'access_token_ttl' => 10,
                ],
                'refresh_token' => [
                    'refresh_token_ttl' => 60,
                    'access_token_ttl' => 10,
                ],
            ],
        ]);

        return [
            \RTLer\Oauth2\Providers\Oauth2ServiceProvider::class,
            \Jenssegers\Mongodb\MongodbServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Oauth2' => \RTLer\Oauth2\Facade\Authorizer::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mongodb');
        $app['config']->set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' =>'127.0.0.1',
            'port' => 27017,
            'database' => 'testbench',
            'username' => '',
            'password' => '',
            'options' => [
                'db' => 'admin', // sets the authentication database required by mongo 3
                //'replicaSet' => 'rs01',
                'socketTimeoutMS' => '90000',
                /*'connectTimeoutMS' => 40000*/
            ],
            'use_mongo_id' => true,
        ]);

    }

}