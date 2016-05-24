<?php
namespace RTLer\Oauth2;

use Carbon\CarbonInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\AuthCodeRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use RTLer\Oauth2\Repositories\UserRepository;

class Authorizer
{

    protected $server;

    public function __construct($privateKey, $publicKey)
    {
        $this->server = $this->getAuthorizationServer($privateKey, $publicKey);
    }

    public function getAuthorizationServer($privateKey, $publicKey)
    {
        // Init our repositories
        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        // Setup the authorization server
        return new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $publicKey
        );
    }

    public function enableClientCredentialsGrant($options)
    {
        // Enable the client credentials grant on the server
        $this->server->enableGrantType(
            new ClientCredentialsGrant(),
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    public function enableAuthCodeGrant($options)
    {
        // Init our repositories
        $authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface
        $refreshTokenRepository = new RefreshTokenRepository();
        $grant = new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            $this->getDateInterval($options['auth_code_ttl']) // authorization codes will expire after 10 minutes
        );

        $grant->setRefreshTokenTTL(
            $this->getDateInterval($options['refresh_token_ttl'])
        ); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->server->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    public function enablePasswordGrant($options)
    {
        // Init our repositories
        $userRepository = new UserRepository(); // instance of UserRepositoryInterface
        $refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

        $grant = new PasswordGrant(
            $userRepository,
            $refreshTokenRepository
        );

        $grant->setRefreshTokenTTL(
            $this->getDateInterval($options['refresh_token_ttl'])
        ); // refresh tokens will expire after 1 month

        // Enable the password grant on the server
        $this->server->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );


    }

    public function enableImplicitGrant($options)
    {
        $grant = new ImplicitGrant(
            $this->getDateInterval($options['access_token_ttl'])
        );

        // Enable the implicit grant on the server
        $this->server->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    public function enableRefreshTokenGrant($options)
    {
        // Init our repositories
        $refreshTokenRepository = new RefreshTokenRepository();

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(
            $this->getDateInterval($options['refresh_token_ttl'])
        ); // new refresh tokens will expire after 1 month

        // Enable the refresh token grant on the server
        $this->server->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // new access tokens will expire after an hour
        );
    }

    /**
     * @return AuthorizationServer
     */
    public function getServer()
    {
        return $this->server;
    }

    protected function getDateInterval($minutes)
    {
        return CarbonInterval::minutes($minutes);
    }
}