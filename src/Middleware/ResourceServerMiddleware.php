<?php

namespace RTLer\Oauth2\Middleware;

use Closure;
use League\OAuth2\Server\Exception\OAuthServerException;

class ResourceServerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $psrRequest = app()->make('Psr\Http\Message\ServerRequestInterface');
        $resourceServer = \Oauth2::makeResourceServer();

        $serverRequest = $resourceServer->validateAuthenticatedRequest($psrRequest);

        $environment = \Oauth2::getOptions()['environment'];
        $neededScopes = array_slice(func_get_args(), 2);
        $requestedScopes = $serverRequest->getAttribute('oauth_scopes');

        $this->validateScopes($neededScopes, $requestedScopes);

        $this->authUser($request, $serverRequest, $environment);

        return $next($request);
    }

    /**
     * check if client have right scopes to access the route
     *
     * @param $neededScopes
     * @param $requestedScopes
     * @throws OAuthServerException
     */
    protected function validateScopes($neededScopes, $requestedScopes)
    {
        $haveRightScope = true;
        foreach ($neededScopes as $neededScope) {
            if (!in_array($neededScope, $requestedScopes)) {
                $haveRightScope = false;
            }
        }
        if (!$haveRightScope) {
            throw OAuthServerException::accessDenied(
                'you need right scope to access this resource'
            );
        }
    }

    /**
     * auth user
     *
     * @param $request
     * @param $serverRequest
     * @param $environment
     */
    protected function authUser($request, $serverRequest, $environment)
    {
        $userVerifier = \Oauth2::getOptions()['user_verifier'];
        $user = (new $userVerifier())
            ->getUserByIdentifier($serverRequest->getAttribute('oauth_user_id'));
        if ($environment == 'laravel') {
            \Auth::setUser($user);
        }
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }
}
