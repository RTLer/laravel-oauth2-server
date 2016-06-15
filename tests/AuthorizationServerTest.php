<?php

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Oauth2Tests\OauthTestCase;
use Oauth2Tests\Stubs\StubResponseType;
use Psr\Http\Message\ResponseInterface;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\JsonRespondManipulator;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class AuthorizationServerTest extends OauthTestCase
{
    public function testRespondToRequestInvalidGrantType()
    {
        try {
            Oauth2::makeAuthorizationServer()
                ->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response());
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
            ->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response());
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
            $authorizationServer->completeAuthorizationRequest($authRequest, new Response()) instanceof ResponseInterface
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

    public function testRevokeAccessToken()
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


        Oauth2Server::setAuthInfo([
            'access_token_id' => $responseType->getAccessToken()->getIdentifier(),
        ]);

        Oauth2Server::revokeAccessToken();
    }

    public function testJsonRespondManipulator()
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
        $response = app('\Psr\Http\Message\ResponseInterface');
        $responseEditor = new JsonRespondManipulator($responseType->generateHttpResponse($response));

        $responseEditor->editBody(function ($data) {
            $data['test'] = 'testing';
        });
        $responseEditor->editResponse(function ($res) {
            /* @var ResponseInterface $res */
            return $res->withHeader('test', 'testing');
        });

        $this->assertInstanceOf('Zend\Diactoros\Response', $responseEditor->getResponse());
    }
}
