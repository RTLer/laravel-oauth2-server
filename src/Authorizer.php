<?php
namespace RTLer\Oauth2;

use Carbon\CarbonInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\AuthCodeRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use RTLer\Oauth2\Repositories\UserRepository;

class Authorizer
{

    protected $authorizationServer;
    protected $resourceServer;
    protected $options;

    /**
     * Authorizer constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;

    }

    /**
     * setup AuthorizationServer.
     *
     * @return AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        // Init our repositories
        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        // Setup the authorization server
        $this->authorizationServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $this->options['private_key'],
            $this->options['public_key']
        );

        $this->enableAuthorizationGrants($this->options);

        return $this->authorizationServer;
    }

    /**
     * setup ResourceServer.
     *
     * @return ResourceServer
     */
    public function makeResourceServer()
    {
        // Init our repositories
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        // Setup the authorization server
        $this->resourceServer = new ResourceServer(
            $accessTokenRepository,
            $this->options['public_key']
        );

        return $this->resourceServer;
    }

    /**
     * enable AuthorizationGrants
     *
     * @param $options
     */
    public function enableAuthorizationGrants($options){
        foreach ($options['grants'] as $name => $grantOptions) {
            $name = camel_case('enable_' . $name . '_grant');

            $this->{$name}($grantOptions);
        }
    }

    /**
     * enable ClientCredentialsGrant
     *
     * @param $options
     */
    public function enableClientCredentialsGrant($options)
    {
        // Enable the client credentials grant on the server
        $this->authorizationServer->enableGrantType(
            new ClientCredentialsGrant(),
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    /**
     * enable AuthCodeGrant
     *
     * @param $options
     */
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
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    /**
     * enable PasswordGrant
     *
     * @param $options
     */
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
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );


    }

    /**
     * enable ImplicitGrant
     *
     * @param $options
     */
    public function enableImplicitGrant($options)
    {
        $grant = new ImplicitGrant(
            $this->getDateInterval($options['access_token_ttl'])
        );

        // Enable the implicit grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );
    }

    /**
     * enable RefreshTokenGrant
     *
     * @param $options
     */
    public function enableRefreshTokenGrant($options)
    {
        // Init our repositories
        $refreshTokenRepository = new RefreshTokenRepository();

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(
            $this->getDateInterval($options['refresh_token_ttl'])
        ); // new refresh tokens will expire after 1 month

        // Enable the refresh token grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // new access tokens will expire after an hour
        );
    }

    /**
     * get AuthorizationServer
     *
     * @return AuthorizationServer
     */
    public function getAuthorizationServer()
    {
        return $this->authorizationServer;
    }

    protected function getDateInterval($minutes)
    {
        return CarbonInterval::minutes($minutes);
    }

    /**
     * get ResourceServer
     *
     * @return mixed
     */
    public function getResourceServer()
    {
        return $this->resourceServer;
    }

    /**
     * get Options (configs)
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }
}