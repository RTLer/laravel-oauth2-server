<?php
namespace Oauth2Tests\Grant;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Oauth2Tests\OauthTestCase;
use RTLer\Oauth2\Entities\AccessTokenEntity;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Entities\RefreshTokenEntity;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use RTLer\Oauth2\Repositories\UserRepository;
use Zend\Diactoros\ServerRequest;

class PasswordGrantTest extends OauthTestCase
{
    public function testGetIdentifier()
    {
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $this->assertEquals('password', $grant->getIdentifier());
    }

    public function testRespondToRequest()
    {
        $clientRepositoryMock = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $scopeRepositoryMock = new ScopeRepository();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'foo',
                'client_secret' => 'bar',
                'username' => 'foo',
                'password' => 'bar',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
        $this->assertTrue($responseType->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertTrue($responseType->getRefreshToken() instanceof RefreshTokenEntityInterface);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function testRespondToRequestMissingUsername()
    {
        $clientRepositoryMock = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'foo',
                'client_secret' => 'bar',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function testRespondToRequestMissingPassword()
    {
        $clientRepositoryMock = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'foo',
                'client_secret' => 'bar',
                'username' => 'alex',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function testRespondToRequestBadCredentials()
    {
        $clientRepositoryMock = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id' => 'foo',
                'client_secret' => 'bar',
                'username' => 'alex',
                'password' => 'whisky',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }
}