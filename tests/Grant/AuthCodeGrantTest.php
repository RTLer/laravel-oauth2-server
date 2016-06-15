<?php

namespace Oauth2Tests\Grant;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\CryptTraitStub;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\AuthCodeRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\RefreshTokenRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use Zend\Diactoros\ServerRequest;

class AuthCodeGrantTest extends OauthTestCase
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
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));

        $this->assertEquals('authorization_code', $grant->getIdentifier());
    }

    public function testCanRespondToAuthorizationRequest()
    {
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
            ]
        );

        $this->assertTrue($grant->canRespondToAuthorizationRequest($request));
    }

    public function testValidateAuthorizationRequest()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
                'redirect_uri'  => 'http://foo/bar',
            ]
        );

        $this->assertTrue($grant->validateAuthorizationRequest($request) instanceof AuthorizationRequest);
    }

    public function testValidateAuthorizationRequestRedirectUriArray()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
                'redirect_uri'  => 'http://foo/bar',
            ]
        );

        $this->assertTrue($grant->validateAuthorizationRequest($request) instanceof AuthorizationRequest);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function testValidateAuthorizationRequestMissingClientId()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
            ]
        );

        $grant->validateAuthorizationRequest($request);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 4
     */
    public function testValidateAuthorizationRequestInvalidClientId()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'baz',
            ]
        );

        $grant->validateAuthorizationRequest($request);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 4
     */
    public function testValidateAuthorizationRequestBadRedirectUriString()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
                'redirect_uri'  => 'http://bar',
            ]
        );

        $grant->validateAuthorizationRequest($request);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 4
     */
    public function testValidateAuthorizationRequestBadRedirectUriArray()
    {
        $clientRepositoryMock = new ClientRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
                'redirect_uri'  => 'http://bar',
            ]
        );

        $grant->validateAuthorizationRequest($request);
    }

    public function testCompleteAuthorizationRequest()
    {
        $authRequest = new AuthorizationRequest();
        $authRequest->setAuthorizationApproved(true);
        $authRequest->setClient(new ClientEntity());
        $authRequest->setGrantTypeId('authorization_code');
        $authRequest->setUser(new UserEntity());

        $authCodeRepository = new AuthCodeRepository();

        $grant = new AuthCodeGrant(
            $authCodeRepository,
            new RefreshTokenRepository(),
            new \DateInterval('PT10M')
        );

        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));

        $this->assertTrue($grant->completeAuthorizationRequest($authRequest) instanceof RedirectResponse);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 9
     */
    public function testCompleteAuthorizationRequestDenied()
    {
        $authRequest = new AuthorizationRequest();
        $authRequest->setAuthorizationApproved(false);
        $authRequest->setClient(new ClientEntity());
        $authRequest->setGrantTypeId('authorization_code');
        $authRequest->setUser(new UserEntity());

        $authCodeRepository = new AuthCodeRepository();

        $grant = new AuthCodeGrant(
            $authCodeRepository,
            new RefreshTokenRepository(),
            new \DateInterval('PT10M')
        );

        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));

        $grant->completeAuthorizationRequest($authRequest);
    }

    public function testRespondToAccessTokenRequest()
    {
        $clientRepository = new ClientRepository();
        $scopeRepositoryMock = new ScopeRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();
        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepository);
        $grant->setScopeRepository($scopeRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));
        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
                'code'          => $this->cryptStub->doEncrypt(
                    json_encode(
                        [
                            'auth_code_id' => 'testAuthCode',
                            'expire_time'  => time() + 3600,
                            'client_id'    => 'foo',
                            'user_id'      => 123,
                            'scopes'       => ['foo'],
                            'redirect_uri' => 'http://foo/bar',
                        ]
                    )
                ),
            ]
        );
        /** @var StubResponseType $response */
        $response = $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
        $this->assertTrue($response->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertTrue($response->getRefreshToken() instanceof RefreshTokenEntityInterface);
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function testRespondToAccessTokenRequestMissingRedirectUri()
    {
        $clientRepository = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();


        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepository);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type' => 'authorization_code',
            ]
        );

        /* @var StubResponseType $response */
        $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
    }

    /**
     * @expectedException \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function testRespondToAccessTokenRequestMissingCode()
    {
        $client = new ClientEntity();
        $client->setRedirectUri('http://foo/bar');
        $clientRepositoryMock = new ClientRepository();

        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
            ]
        );

        /* @var StubResponseType $response */
        $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
    }

    public function testRespondToAccessTokenRequestExpiredCode()
    {
        $client = new ClientEntity();
        $client->setIdentifier('foo');
        $client->setRedirectUri('http://foo/bar');
        $clientRepositoryMock = new ClientRepository();

        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
                'code'          => $this->cryptStub->doEncrypt(
                    json_encode(
                        [
                            'auth_code_id' => 'testAuthCodeExpired',
                            'expire_time'  => time() - 3600,
                            'client_id'    => 'foo',
                            'user_id'      => 123,
                            'scopes'       => ['foo'],
                            'redirect_uri' => 'http://foo/bar',
                        ]
                    )
                ),
            ]
        );

        try {
            /* @var StubResponseType $response */
            $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Authorization code has expired');
        }
    }

    public function testRespondToAccessTokenRequestRevokedCode()
    {
        $client = new ClientEntity();
        $client->setIdentifier('foo');
        $client->setRedirectUri('http://foo/bar');
        $clientRepositoryMock = new ClientRepository();

        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();

        $authCodeRepositoryMock = new AuthCodeRepository();

        $grant = new AuthCodeGrant(
            $authCodeRepositoryMock,
            new RefreshTokenRepository(),
            new \DateInterval('PT10M')
        );
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
                'code'          => $this->cryptStub->doEncrypt(
                    json_encode(
                        [
                            'auth_code_id' => uniqid(),
                            'expire_time'  => time() + 3600,
                            'client_id'    => 'foo',
                            'user_id'      => 123,
                            'scopes'       => ['foo'],
                            'redirect_uri' => 'http://foo/bar',
                        ]
                    )
                ),
            ]
        );

        try {
            /* @var StubResponseType $response */
            $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Authorization code has been revoked');
        }
    }

    public function testRespondToAccessTokenRequestClientMismatch()
    {
        $clientRepositoryMock = new ClientRepository();

        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
                'code'          => $this->cryptStub->doEncrypt(
                    json_encode(
                        [
                            'auth_code_id' => 'testAuthCodeForBaz',
                            'expire_time'  => time() + 3600,
                            'client_id'    => 'bar',
                            'user_id'      => 123,
                            'scopes'       => ['foo'],
                            'redirect_uri' => 'http://foo/bar',
                        ]
                    )
                ),
            ]
        );

        try {
            /* @var StubResponseType $response */
            $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Authorization code was not issued to this client');
        }
    }

    public function testRespondToAccessTokenRequestBadCodeEncryption()
    {
        $client = new ClientEntity();
        $client->setIdentifier('foo');
        $client->setRedirectUri('http://foo/bar');
        $clientRepositoryMock = new ClientRepository();

        $accessTokenRepositoryMock = new AccessTokenRepository();
        $refreshTokenRepositoryMock = new RefreshTokenRepository();

        Oauth2Server::makeAuthorizationServer();
        $grant = Oauth2Server::enableAuthCodeGrant(Oauth2Server::getOptions('grants.auth_code'));
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setPublicKey(new CryptKey('file://'.__DIR__.'/../Stubs/public.key'));
        $grant->setPrivateKey(new CryptKey('file://'.__DIR__.'/../Stubs/private.key'));

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            [],
            [],
            [],
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => 'foo',
                'client_secret' => 'bar',
                'redirect_uri'  => 'http://foo/bar',
                'code'          => 'sdfsfsd',
            ]
        );

        try {
            /* @var StubResponseType $response */
            $grant->respondToAccessTokenRequest($request, new StubResponseType(), new \DateInterval('PT10M'));
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Cannot decrypt the authorization code');
        }
    }
}
