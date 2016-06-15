<?php

namespace LeagueTests;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Oauth2Tests\OauthTestCase;
use RTLer\Oauth2\Facade\Oauth2Server;
use RTLer\Oauth2\Repositories\AccessTokenRepository;
use Zend\Diactoros\ServerRequestFactory;

class ResourceServerTest extends OauthTestCase
{
    public function testValidateAuthenticatedRequest()
    {
        $server = new ResourceServer(
            new AccessTokenRepository(),
            'file://'.__DIR__.'/Stubs/public.key'
        );

        try {
            $server->validateAuthenticatedRequest(ServerRequestFactory::fromGlobals());
        } catch (OAuthServerException $e) {
            $this->assertEquals('Missing "Authorization" header', $e->getHint());
        }
    }
    public function testMakeResourceServer()
    {
        $server = Oauth2Server::makeResourceServer();
        $this->assertInstanceOf('League\OAuth2\Server\ResourceServer', $server);
    }
}
