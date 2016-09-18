<?php

namespace Oauth2Tests\Grant;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Grant\PasswordGrant;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\UserRepository;
use Zend\Diactoros\ServerRequest;

class PersonalAccessGrantTest extends OauthTestCase
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
        $grant = Oauth2Server::enablePersonalAccessGrant(Oauth2Server::getOptions('grants.personal_access'));

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'token_name' => 'baz',
                'user_id'       => 1,
            ]
        );

        $responseType = new StubResponseType();

        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
        $this->assertTrue($responseType->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertEquals(1, $responseType->getAccessToken()->getUserIdentifier());
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function testRespondToRequestMissingUserIdentifier()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enablePersonalAccessGrant(Oauth2Server::getOptions('grants.personal_access'));
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
}
