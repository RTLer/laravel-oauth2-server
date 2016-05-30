<?php

namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use RTLer\Oauth2\Entities\RefreshTokenEntity;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Oauth2Server;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var ModelResolver
     */
    protected $modelResolver;

    /**
     * AccessTokenRepository constructor.
     */
    public function __construct()
    {
        $type = app()->make(Oauth2Server::class)
            ->getOptions()['database_type'];
        $this->modelResolver = new ModelResolver($type);
    }

    /**
     * Creates a new refresh token.
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
        $refreshTokenModel = $this->modelResolver->getModel('RefreshTokenModel');

        $refreshToken = [
            'token'           => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'expire_time'     => $refreshTokenEntity->getExpiryDateTime(),

        ];

        $refreshTokenModel::create($refreshToken);
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $refreshTokenModel = $this->modelResolver->getModel('RefreshTokenModel');

        $refreshTokenModel::where('token', $tokenId)->delete();
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
        $refreshTokenModel = $this->modelResolver->getModel('RefreshTokenModel');

        return !(bool) $refreshTokenModel::where('token', $tokenId)->exists();
    }
}
