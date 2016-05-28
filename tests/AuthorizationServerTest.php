<?php
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use LeagueTests\Stubs\AuthCodeEntity;
use Oauth2Tests\OauthTestCase;
use Psr\Http\Message\ResponseInterface;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Entities\UserEntity;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class AuthorizationServerTest extends OauthTestCase
{
    public function testRespondToRequestInvalidGrantType()
    {
        try {
            Oauth2::makeAuthorizationServer()
                ->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response);
        } catch (OAuthServerException $e) {
            $this->assertEquals('unsupported_grant_type', $e->getErrorType());
            $this->assertEquals(400, $e->getHttpStatusCode());
        }
    }

    public function testRespondToRequest()
    {


        $_POST['grant_type'] = 'client_credentials';
        $_POST['client_id'] = 'foo';
        $_POST['client_secret'] = 'bar';
        $response = Oauth2::makeAuthorizationServer(['client_credentials'])
            ->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetResponseType()
    {
        $abstractGrantReflection = new \ReflectionClass(Oauth2::makeAuthorizationServer());
        $method = $abstractGrantReflection->getMethod('getResponseType');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(Oauth2::makeAuthorizationServer()) instanceof BearerTokenResponse);
    }

    public function testCompleteAuthorizationRequest()
    {
        $authorizationServer = Oauth2::makeAuthorizationServer(['auth_code']);

        $authRequest = new AuthorizationRequest();
        $authRequest->setAuthorizationApproved(true);
        $authRequest->setClient(new ClientEntity());
        $authRequest->setGrantTypeId('authorization_code');
        $authRequest->setUser(new UserEntity());

        $this->assertTrue(
            $authorizationServer->completeAuthorizationRequest($authRequest, new Response) instanceof ResponseInterface
        );
    }

    public function testValidateAuthorizationRequest()
    {


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

        $oauth2Request = Oauth2::makeAuthorizationServer(['auth_code'])
            ->validateAuthorizationRequest($request);


        $this->assertTrue($oauth2Request instanceof AuthorizationRequest);
    }

    /**
     * @expectedException  \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 2
     */
    public function testValidateAuthorizationRequestUnregistered()
    {
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

        Oauth2::makeAuthorizationServer()
            ->validateAuthorizationRequest($request);

    }
}
