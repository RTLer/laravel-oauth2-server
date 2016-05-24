<?php

namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use RTLer\Oauth2\Entities\AccessTokenEntity;
use RTLer\Oauth2\Models\AccessTokenModel;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    /**
     * Create a new access token
     *
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     * @param mixed $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity();
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param \League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $newAccessToken = [
            'token' => $accessTokenEntity->getIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'expire_time' => $accessTokenEntity->getExpiryDateTime(),
        ];

        if (!is_null($accessTokenEntity->getUserIdentifier())) {
            $newAccessToken['user_id'] = $accessTokenEntity->getUserIdentifier();
        }

        if ($accessTokenEntity->getScopes() !== []) {
            $newAccessToken['scopes'] = $accessTokenEntity->getScopes();
        }
        AccessTokenModel::create($newAccessToken);
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        AccessTokenModel::where('token', $tokenId)->delete();
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return !(boolean)AccessTokenModel::where('token', $tokenId)->exists();
    }
}