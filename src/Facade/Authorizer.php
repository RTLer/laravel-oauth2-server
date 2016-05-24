<?php
namespace RTLer\Oauth2\Facade;

use Illuminate\Support\Facades\Facade;
use RTLer\Oauth2\Authorizer as OauthAuthorizer;

class Authorizer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return OauthAuthorizer::class; }
}