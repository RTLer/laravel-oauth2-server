<?php

namespace RTLer\Oauth2\Grants;

use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;

class PersonalAccessGrant extends AbstractGrant
{
    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));

        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        $userIdentifier = $this->getRequestParameter('user_id', $request);
        if (is_null($userIdentifier)) {
            throw OAuthServerException::invalidRequest('user_id');
        }


        // Issue and persist access token
        $accessToken = $this->issueAccessToken(
            new DateInterval('P5Y'), $client,
            $userIdentifier, $scopes
        );

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'personal_access';
    }
}
