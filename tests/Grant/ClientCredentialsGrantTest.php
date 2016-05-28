<?php
namespace Oauth2Tests\Grant;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\StubResponseType;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use RTLer\Oauth2\Repositories\ClientRepository;
use RTLer\Oauth2\Repositories\ScopeRepository;
use Zend\Diactoros\ServerRequest;
class ClientCredentialsGrantTest extends OauthTestCase
{
    public function testGetIdentifier()
    {
        $grant = new ClientCredentialsGrant();
        $this->assertEquals('client_credentials', $grant->getIdentifier());
    }
    public function testRespondToRequest()
    {
        $clientRepositoryMock = new ClientRepository();
        $accessTokenRepositoryMock = new AccessTokenRepository();
        $scopeRepositoryMock = new ScopeRepository();
        $grant = new ClientCredentialsGrant();
        $grant->setClientRepository($clientRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(
            [
                'client_id'     => 'foo',
                'client_secret' => 'bar',
            ]
        );
        $responseType = new StubResponseType();
        $grant->respondToAccessTokenRequest($serverRequest, $responseType, new \DateInterval('PT5M'));
        $this->assertTrue($responseType->getAccessToken() instanceof AccessTokenEntityInterface);
    }
}
