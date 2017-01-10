<?php

namespace RTLer\Oauth2\Grants;

use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use RTLer\Oauth2\Entities\AccessTokenEntity;

class PersonalAccessGrant extends AbstractGrant
{
    protected $tokenName;

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

        $this->tokenName = $this->getRequestParameter('token_name', $request);
        if (is_null($this->tokenName)) {
            throw OAuthServerException::invalidRequest('token_name');
        }

        // Issue and persist access token
        $accessToken = $this->issueAccessToken(
            new DateInterval('P5Y'),
            $client,
            $userIdentifier,
            $scopes
        );

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * Issue an access token.
     *
     * @param \DateInterval                                         $accessTokenTTL
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface  $client
     * @param string                                                $userIdentifier
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     * @param $name
     *
     * @return \League\OAuth2\Server\Entities\AccessTokenEntityInterface
     */
    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ) {
        /** @var AccessTokenEntity $accessToken */
        $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $userIdentifier);
        $accessToken->setClient($client);
        $accessToken->setName($this->tokenName);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setIdentifier($this->generateUniqueIdentifier());
        $accessToken->setExpiryDateTime((new \DateTime())->add($accessTokenTTL));

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        $this->accessTokenRepository->persistNewAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'personal_access';
    }
}
