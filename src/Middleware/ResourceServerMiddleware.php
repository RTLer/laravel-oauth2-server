<?php

namespace RTLer\Oauth2\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use RTLer\Oauth2\Oauth2Server;

class ResourceServerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $psrRequest = app()->make('Psr\Http\Message\ServerRequestInterface');

        /** @var \League\OAuth2\Server\ResourceServer $resourceServer */
        $resourceServer = app()->make(Oauth2Server::class)
            ->makeResourceServer();

        $serverRequest = $resourceServer->validateAuthenticatedRequest($psrRequest);

        $neededScopes = array_slice(func_get_args(), 2);
        $requestedScopes = $serverRequest->getAttribute('oauth_scopes');

        $this->validateScopes($neededScopes, $requestedScopes);

        if (!empty($serverRequest->getAttribute('oauth_user_id'))) {
            $this->authUser($request, $serverRequest);
        }

        app()->make(Oauth2Server::class)->setAuthInfo([
            'access_token_id' => $serverRequest->getAttribute('oauth_access_token_id'),
            'client_id'       => $serverRequest->getAttribute('oauth_client_id'),
            'user_id'         => $serverRequest->getAttribute('oauth_user_id'),
            'scopes'          => $serverRequest->getAttribute('oauth_scopes'),
        ]);

        return $next($request);
    }

    /**
     * check if client have right scopes to access the route.
     *
     * @param $neededScopes
     * @param $requestedScopes
     *
     * @return bool
     * @throws OAuthServerException
     */
    protected function validateScopes($neededScopes, $requestedScopes)
    {
        foreach ($requestedScopes as $requestedScope) {

            if(in_array($requestedScope->getIdentifier(), $neededScopes)){
                return true;
            }
        }
        throw OAuthServerException::accessDenied(
            'you need right scope to access this resource'
        );
    }

    /**
     * auth user.
     *
     * @param Request                $request
     * @param ServerRequestInterface $serverRequest
     */
    protected function authUser(Request $request, ServerRequestInterface $serverRequest)
    {
        $userVerifier = app()->make(Oauth2Server::class)
            ->getOptions()['user_verifier'];
        /** @var \RTLer\Oauth2\Authorize\UserVerifierInterface $userVerifierObj */
        $userVerifierObj = new $userVerifier();
        $user = $userVerifierObj
            ->getUserByIdentifier($serverRequest->getAttribute('oauth_user_id'));
        Auth::setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }
}
