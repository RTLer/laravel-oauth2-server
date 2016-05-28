<?php
namespace Oauth2Tests\Grant;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use Oauth2Tests\OauthTestCase;
use RTLer\Oauth2\Entities\ClientEntity;
use Oauth2Tests\Stubs\CryptTraitStub;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use Zend\Diactoros\ServerRequest;

class ImplicitGrantTest extends OauthTestCase
{
    /**
     * CryptTrait stub
     */
    protected $cryptStub;

    public function setUp()
    {
        parent::setUp();
        $this->cryptStub = new CryptTraitStub();
    }

    public function testGetIdentifier()
    {
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $this->assertEquals('implicit', $grant->getIdentifier());
    }

    public function testCanRespondToAccessTokenRequest()
    {
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $this->assertFalse(
            $grant->canRespondToAccessTokenRequest(new ServerRequest())
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRespondToAccessTokenRequest()
    {
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $grant->respondToAccessTokenRequest(
            new ServerRequest(),
            new StubResponseType(),
            new \DateInterval('PT10M')
        );
    }

    public function testCanRespondToAuthorizationRequest()
    {
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'token',
                'client_id' => 'foo',
            ]
        );
        $this->assertTrue($grant->canRespondToAuthorizationRequest($request));
    }

    public function testValidateAuthorizationRequest()
    {
        $clientRepositoryMock = new ClientRepository();
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
                'client_id' => 'foo',
                'redirect_uri' => 'http://foo/bar',
            ]
        );
        $this->assertTrue($grant->validateAuthorizationRequest($request) instanceof AuthorizationRequest);
    }

    public function testValidateAuthorizationRequestRedirectUriArray()
    {
        $clientRepositoryMock = new ClientRepository();
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
                'client_id' => 'foo',
                'redirect_uri' => 'http://foo/bar',
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
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
                'client_id' => 'baz',
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
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
                'client_id' => 'foo',
                'redirect_uri' => 'http://bar',
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
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
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
                'client_id' => 'foo',
                'redirect_uri' => 'http://bar',
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
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $grant->setPrivateKey(new CryptKey('file://' . __DIR__ . '/../Stubs/private.key'));
        $grant->setPublicKey(new CryptKey('file://' . __DIR__ . '/../Stubs/public.key'));
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
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
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $grant = new ImplicitGrant(new \DateInterval('PT10M'));
        $grant->setPrivateKey(new CryptKey('file://' . __DIR__ . '/../Stubs/private.key'));
        $grant->setPublicKey(new CryptKey('file://' . __DIR__ . '/../Stubs/public.key'));
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->completeAuthorizationRequest($authRequest);
    }
}