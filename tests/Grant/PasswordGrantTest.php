<?php

namespace Oauth2Tests\Grant;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Grant\PasswordGrant;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\UserRepository;
use Zend\Diactoros\ServerRequest;

class PasswordGrantTest extends OauthTestCase
{
    public function testGetIdentifier()
    {
        $userRepositoryMock = new UserRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        Oauth2Server::makeAuthorizationServer();
        $grant = new PasswordGrant($userRepositoryMock, $refreshTokenRepositoryMock);
        $this->assertEquals('password', $grant->getIdentifier());
    }

    public function testRespondToRequest()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enablePasswordGrant(Oauth2Server::getOptions('grants.password'));

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'username'      => 'foo',
                'password'      => 'bar',
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
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enablePasswordGrant(Oauth2Server::getOptions('grants.password'));
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
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
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enablePasswordGrant(Oauth2Server::getOptions('grants.password'));
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'username'      => 'alex',
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
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enablePasswordGrant(Oauth2Server::getOptions('grants.password'));
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'username'      => 'alex',
                'password'      => 'whisky',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }
}
