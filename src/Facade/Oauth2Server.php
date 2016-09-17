<?php

namespace RTLer\Oauth2\Facade;

use Illuminate\Support\Facades\Facade;
use RTLer\Oauth2\Oauth2Server as Oauth2ServerClass;

/**
 * Class Oauth2Server.
 *
 * @method static \League\OAuth2\Server\AuthorizationServer makeAuthorizationServer($grantNames = [])
 * @method static \League\OAuth2\Server\ResourceServer makeResourceServer()
 * @method static void enableAuthorizationGrants($grantNames = null)
 * @method static void enableGrant($name)
 * @method static \League\OAuth2\Server\Grant\ClientCredentialsGrant enableClientCredentialsGrant($options)
 * @method static \League\OAuth2\Server\Grant\AuthCodeGrant enableAuthCodeGrant($options)
 * @method static \League\OAuth2\Server\Grant\PasswordGrant enablePasswordGrant($options)
 * @method static \RTLer\Oauth2\Grants\PersonalAccessGrant enablePersonalAccessGrant($options)
 * @method static \League\OAuth2\Server\Grant\ImplicitGrant enableImplicitGrant($options)
 * @method static \League\OAuth2\Server\Grant\RefreshTokenGrant enableRefreshTokenGrant($options)
 * @method static \League\OAuth2\Server\AuthorizationServer getAuthorizationServer()
 * @method static \Carbon\CarbonInterval getDateInterval()
 * @method static \League\OAuth2\Server\ResourceServer getResourceServer()
 * @method static mixed getOptions($key = null)
 * @method static mixed getAuthInfo()
 * @method static void setAuthInfo($authInfo)
 * @method static bool revokeAccessToken()
 * @method static \League\OAuth2\Server\ResponseTypes\BearerTokenResponse getBearerTokenResponse()
 * @method static \League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator getBearerTokenValidator(\RTLer\Oauth2\Repositories\AccessTokenRepository $accessTokenRepository)
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
