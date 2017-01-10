<?php

namespace RTLer\Oauth2;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Psr\Http\Message\ResponseInterface;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Grants\PersonalAccessGrant;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\AuthCodeRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use RTLer\Oauth2\Repositories\UserRepository;
use Zend\Diactoros\ServerRequest;

class Oauth2Server
{
    /**
     * @var AuthorizationServer
     */
    protected $authorizationServer;
    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var array configs
     */
    protected $options;

    /**
     * @var array auth info
     */
    protected $authInfo;

    /**
     * Oauth2Server constructor.
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
     * @param array $grantNames
     *
     * @return AuthorizationServer
     */
    public function makeAuthorizationServer($grantNames = [])
    {
        // Init our repositories
        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        // init bearer
        $bearerTokenResponse = $this->getBearerTokenResponse();

        // Setup the authorization server
        $this->authorizationServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $this->options['private_key'],
            $this->options['public_key'],
            $bearerTokenResponse
        );

        $this->enableAuthorizationGrants($grantNames);

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

        // init bearer
        $bearerTokenValidator = $this->getBearerTokenValidator($accessTokenRepository);

        // Setup the authorization server
        $this->resourceServer = new ResourceServer(
            $accessTokenRepository,
            $this->options['public_key'],
            $bearerTokenValidator
        );

        return $this->resourceServer;
    }

    /**
     * enable all configured Authorization Grants.
     *
     * @param array|null $grantNames
     *
     * @internal param $options
     */
    public function enableAuthorizationGrants($grantNames = null)
    {
        $activeGrants = array_keys($this->options['grants']);
        foreach ($activeGrants as $name) {
            if (is_null($grantNames) || in_array($name, $grantNames)) {
                $this->enableGrant($name);
            }
        }
    }

    /**
     * enable Authorization Grant by name.
     *
     * @param $name
     */
    public function enableGrant($name)
    {
        $methodName = camel_case('enable_'.$name.'_grant');

        $this->{$methodName}($this->options['grants'][$name]);
    }

    /**
     * enable ClientCredentialsGrant.
     *
     * @param $options
     *
     * @return ClientCredentialsGrant
     */
    public function enableClientCredentialsGrant($options)
    {
        $grant = new ClientCredentialsGrant();
        // Enable the client credentials grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );

        return $grant;
    }

    /**
     * enable AuthCodeGrant.
     *
     * @param $options
     *
     * @return AuthCodeGrant
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
        );

        // Enable the authentication code grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl']) // access tokens will expire after 1 hour
        );

        return $grant;
    }

    /**
     * enable PasswordGrant.
     *
     * @param $options
     *
     * @return PasswordGrant
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
        );

        // Enable the password grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl'])
        );

        return $grant;
    }

    /**
     * enable PasswordGrant.
     *
     * @return PersonalAccessGrant
     */
    public function enablePersonalAccessGrant()
    {
        $grant = new PersonalAccessGrant();

        // Enable the password grant on the server
        $this->authorizationServer->enableGrantType($grant);

        return $grant;
    }

    /**
     * enable ImplicitGrant.
     *
     * @param $options
     *
     * @return ImplicitGrant
     */
    public function enableImplicitGrant($options)
    {
        $grant = new ImplicitGrant(
            $this->getDateInterval($options['access_token_ttl'])
        );

        // Enable the implicit grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl'])
        );

        return $grant;
    }

    /**
     * enable RefreshTokenGrant.
     *
     * @param $options
     *
     * @return RefreshTokenGrant
     */
    public function enableRefreshTokenGrant($options)
    {
        // Init our repositories
        $refreshTokenRepository = new RefreshTokenRepository();

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(
            $this->getDateInterval($options['refresh_token_ttl'])
        );

        // Enable the refresh token grant on the server
        $this->authorizationServer->enableGrantType(
            $grant,
            $this->getDateInterval($options['access_token_ttl'])
        );

        return $grant;
    }

    /**
     * get AuthorizationServer.
     *
     * @return AuthorizationServer
     */
    public function getAuthorizationServer()
    {
        return $this->authorizationServer;
    }

    /**
     * get DateInterval.
     *
     * @param $minutes
     *
     * @return CarbonInterval
     */
    protected function getDateInterval($minutes)
    {
        return CarbonInterval::minutes($minutes);
    }

    /**
     * get ResourceServer.
     *
     * @return mixed
     */
    public function getResourceServer()
    {
        return $this->resourceServer;
    }

    /**
     * get Options (configs).
     *
     * @param null $key
     *
     * @return mixed
     */
    public function getOptions($key = null)
    {
        if (!is_null($key)) {
            return Arr::get($this->options, $key, null);
        }

        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * get auth info.
     *
     * @return mixed
     */
    public function getAuthInfo()
    {
        return $this->authInfo;
    }

    /**
     * set auth info.
     *
     * @param $authInfo
     */
    public function setAuthInfo($authInfo)
    {
        $this->authInfo = $authInfo;
    }

    /**
     * revoke access token.
     *
     * @return bool
     */
    public function revokeAccessToken()
    {
        if (isset($this->getAuthInfo()['access_token_id'])) {
            $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
            $accessTokenRepository->revokeAccessToken(
                $this->getAuthInfo()['access_token_id']
            );

            return true;
        }

        return false;
    }

    /**
     * revoke access token.
     *
     * @param $identifier
     *
     * @return bool
     */
    public function revokeAccessTokenByPublicIdentifier($identifier)
    {
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $accessTokenRepository->revokeAccessTokenByPublicIdentifier($identifier);

        return true;
    }

    /**
     * get BearerTokenResponse.
     *
     * @return BearerTokenResponse
     */
    protected function getBearerTokenResponse()
    {
        if (empty($this->options['bearer_token_response'])) {
            return new BearerTokenResponse();
        }

        return new $this->options['bearer_token_response']();
    }

    /**
     * get BearerTokenValidator.
     *
     * @param AccessTokenRepository $accessTokenRepository
     *
     * @return BearerTokenValidator
     */
    protected function getBearerTokenValidator(AccessTokenRepository $accessTokenRepository)
    {
        if (empty($this->options['bearer_token_validator'])) {
            return new BearerTokenValidator($accessTokenRepository);
        }

        return new $this->options['bearer_token_validator']($accessTokenRepository);
    }

    /**
     * get getPersonalAccessToken.
     *
     * @param $userId
     * @param $tokenName
     * @param array  $scopes
     * @param string $personalClientId
     * @param string $personalClientSecret
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getPersonalAccessToken($userId, $tokenName, array $scopes = [], $personalClientId = 'personal_access_client', $personalClientSecret = 'secret')
    {
        $request = (new ServerRequest())->withParsedBody([
            'grant_type'    => 'personal_access',
            'client_id'     => $personalClientId,
            'client_secret' => $personalClientSecret,
            'token_name'    => $tokenName,
            'user_id'       => $userId,
            'scope'         => implode(' ', $scopes),
        ]);

        $response = self::makeAuthorizationServer(['personal_access'])
            ->respondToAccessTokenRequest($request, app(ResponseInterface::class));
        $accessTokenData = json_decode((string) $response->getBody(), true);

        return $accessTokenData;
    }

    /**
     * get getAccessTokensForUser.
     *
     * @param $userId
     *
     * @return array|null
     */
    public function getAccessTokensForUser($userId)
    {
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $user = new UserEntity(); // instance of AccessTokenRepositoryInterface
        $user->setIdentifier($userId);

        return $accessTokenRepository->findAccessTokensByUser($user);
    }
}
