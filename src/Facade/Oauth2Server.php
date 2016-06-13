<?php

namespace RTLer\Oauth2\Facade;

use Illuminate\Support\Facades\Facade;
use RTLer\Oauth2\Oauth2Server as Oauth2ServerClass;

/**
 * Class Oauth2Server
 *
 * @package RTLer\Oauth2\Facade
 * @method static bool makeAuthorizationServer($grantNames = [])
 * @method static bool makeResourceServer()
 * @method static bool enableAuthorizationGrants($grantNames = null)
 * @method static bool enableGrant($name)
 * @method static bool enableClientCredentialsGrant($options)
 * @method static bool enableAuthCodeGrant($options)
 * @method static bool enablePasswordGrant($options)
 * @method static bool enableImplicitGrant($options)
 * @method static bool enableRefreshTokenGrant($options)
 * @method static bool getAuthorizationServer()
 * @method static bool getDateInterval()
 * @method static bool getResourceServer()
 * @method static bool getOptions()
 * @method static bool getAuthInfo()
 * @method static bool setAuthInfo($authInfo)
 * @method static bool revokeAccessToken()
 * @method static bool getBearerTokenResponse()
 * @method static bool getBearerTokenValidator(\RTLer\Oauth2\Repositories\AccessTokenRepository $accessTokenRepository)
 */
class Oauth2Server extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Oauth2ServerClass::class;
    }
}
