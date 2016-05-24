<?php

namespace RTLer\Oauth2\Repositories;


use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use RTLer\Oauth2\Entities\AuthCodeEntity;
use RTLer\Oauth2\Models\AuthCodeModel;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{

    /**
     * Creates a new AuthCode
     *
     * @return \League\OAuth2\Server\Entities\AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param \League\OAuth2\Server\Entities\AuthCodeEntityInterface $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $newAuthCode = [
            'token' => $authCodeEntity->getIdentifier(),
            'expire_time' => $authCodeEntity->getExpiryDateTime(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
        ];

        if (!is_null($authCodeEntity->getUserIdentifier())) {
            $newAuthCode['user_id'] = $authCodeEntity->getUserIdentifier();
        }

        AuthCodeModel::create($newAuthCode);
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {
        AuthCodeModel::where('token', $codeId)->delete();

    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {
        return !(boolean)AuthCodeModel::where('token', $codeId)->exists();
    }
}