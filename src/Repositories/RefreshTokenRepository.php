<?php
namespace RTLer\Oauth2\Repositories;


use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use RTLer\Oauth2\Entities\RefreshTokenEntity;
use RTLer\Oauth2\Models\RefreshTokenModel;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }

    /**
     * Create a new refresh token_name.
     *
     * @param \League\OAuth2\Server\Entities\RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshToken = [
            'token' => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'expire_time' => $refreshTokenEntity->getExpiryDateTime(),

        ];

        RefreshTokenModel::create($refreshToken);
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        RefreshTokenModel::where('token', $tokenId)->delete();
    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return !(boolean)RefreshTokenModel::where('token', $tokenId)->exists();
    }
}