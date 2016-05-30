# laravel-oauth [![Build Status](https://travis-ci.org/RTLer/laravel-oauth2.svg?branch=master)](https://travis-ci.org/RTLer/laravel-oauth2)
thephpleague oauth2 integration for laravel and mongo

## installation
require this package by running `composer require rtler/laravel-oauth2`

add `Oauth2ServiceProvider` into serviceProviders
* laravel in config\app.php add this
```php
'providers' => [
  ...
  RTLer\Oauth2\Providers\Oauth2ServiceProvider::class,
  ...
],
```
* lumen add this into bootstrap/app.php
```php
$app->register(RTLer\Oauth2\Providers\Oauth2ServiceProvider::class);
```
then publish the needed files using `php artisan vendor:publish`

it copy /vendor/rtler/laravel-oauth2/src/publish/config/oauth2.php to /config/oauth2.php

and /vendor/rtler/laravel-oauth2/src/publish/Oauth2/UserVerifier.php to /app/Oauth2/UserVerifier.php

config /app/Oauth2/UserVerifier.php class that use to login user and get user by identifier

then add middleware
* laravel in app/Http/Kernel.php add this
```php
protected $routeMiddleware = [
  ...
  'oauth2' => \RTLer\Oauth2\Middleware\ResourceServerMiddleware::class,
  ...
]
```
* lumen
```php
$app->routeMiddleware([
  ...
  'oauth' => \RTLer\Oauth2\Middleware\ResourceServerMiddleware::class,
  ...
]);
```

after that change the config/oauth2.php base on your needs

##usage
make route like getAccessToken and add this into it
```php
// ['password'] shows which grant is active
$response = Oauth2Server::makeAuthorizationServer(['password'])->respondToAccessTokenRequest($request, $response);

// this part use to chenge AuthorizationServer's response
// if you don't need to change it you can return $response
$oauthResponse = new JsonRespondManipulator($response);
$oauthResponse->editBody(function ($data) {
    $data['test'] = 'testing';
    return $data;
});

return $oauthResponse->getRespond();
```

and finally secure your routes with oauth middleware
** you can set scopes like this `'middleware' => 'oauth:scope1,scope2,...'`

```php
$app->group(['middleware' => 'oauth', 'prefix' => 'app', 'namespace' => 'App\Http\Controllers'], function ($app) {
    $app->get('init.json', [
        'as' => 'getInit', 'uses' => 'AppController@getInit',
    ]);
});
```

for choosing grants you can see [which grant?](https://oauth2.thephpleague.com/authorization-server/which-grant/)

##Contributing
please if you have any contribution that can help this package get better send PR
