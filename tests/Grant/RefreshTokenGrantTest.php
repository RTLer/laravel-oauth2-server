<?php

namespace Oauth2Tests\Grant;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\CryptTraitStub;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use Zend\Diactoros\ServerRequest;

class RefreshTokenGrantTest extends OauthTestCase
{
    /**
     * CryptTrait stub.
     *
     * @var CryptTraitStub
     */
    protected $cryptStub;

    public function setUp()
    {
        parent::setUp();
        $this->cryptStub = new CryptTraitStub();
    }

    public function testGetIdentifier()
    {
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        $grant = new RefreshTokenGrant($refreshTokenRepositoryMock);
        $this->assertEquals('refresh_token', $grant->getIdentifier());
    }

    public function testRespondToRequest()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'foo',
                    'refresh_token_id' => 'RefreshTokenFoo',
                    'access_token_id'  => 'AccessTokenFoo',
                    'scopes'           => ['foo'],
                    'user_id'          => 123,
                    'expire_time'      => time() + 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
        $this->assertTrue($responseType->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertTrue($responseType->getRefreshToken() instanceof RefreshTokenEntityInterface);
    }

    public function testRespondToReducedScopes()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'foo',
                    'refresh_token_id' => 'RefreshTokenFoo',
                    'access_token_id'  => 'AccessTokenFoo',
                    'scopes'           => ['foo', 'bar'],
                    'user_id'          => 123,
                    'expire_time'      => time() + 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
                'scope'         => 'foo',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
        $this->assertTrue($responseType->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertTrue($responseType->getRefreshToken() instanceof RefreshTokenEntityInterface);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 5
     */
    public function testRespondToUnexpectedScope()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'foo',
                    'refresh_token_id' => 'RefreshTokenFoo',
                    'access_token_id'  => 'AccessTokenFoo',
                    'scopes'           => ['foo', 'bar'],
                    'user_id'          => 123,
                    'expire_time'      => time() + 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
                'scope'         => 'foobar',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function testRespondToRequestMissingOldToken()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
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
     * @expectedExceptionCode 8
     */
    public function testRespondToRequestInvalidOldToken()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = 'foobar';
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 8
     */
    public function testRespondToRequestClientMismatch()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'bar',
                    'refresh_token_id' => 'zyxwvu',
                    'access_token_id'  => 'abcdef',
                    'scopes'           => ['foo'],
                    'user_id'          => 123,
                    'expire_time'      => time() + 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 8
     */
    public function testRespondToRequestExpiredToken()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'foo',
                    'refresh_token_id' => 'zyxwvu',
                    'access_token_id'  => 'abcdef',
                    'scopes'           => ['foo'],
                    'user_id'          => 123,
                    'expire_time'      => time() - 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 8
     */
    public function testRespondToRequestRevokedToken()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableRefreshTokenGrant(Oauth2Server::getOptions('grants.refresh_token'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $oldRefreshToken = $this->cryptStub->doEncrypt(
            json_encode(
                [
                    'client_id'        => 'foo',
                    'refresh_token_id' => 'zyxwvu',
                    'access_token_id'  => 'abcdef',
                    'scopes'           => ['foo'],
                    'user_id'          => 123,
                    'expire_time'      => time() + 3600,
                ]
            )
        );
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'refresh_token' => $oldRefreshToken,
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
    }
}
