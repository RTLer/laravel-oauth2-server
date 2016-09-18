<?php

namespace RTLer\Oauth2\Repositories;

use Illuminate\Database\Eloquent\Collection;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use RTLer\Oauth2\Entities\AccessTokenEntity;
use RTLer\Oauth2\Entities\ScopeEntity;
use RTLer\Oauth2\Entities\UserEntity;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Oauth2Server;

class AccessTokenRepository implements AccessTokenRepositoryInterface
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
     * Create a new access token.
     *
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface  $clientEntity
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     * @param mixed                                                 $userIdentifier
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
     * @param \League\OAuth2\Server\Entities\AccessTokenEntityInterface|AccessTokenEntity $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        $newAccessToken = [
            'token'       => $accessTokenEntity->getIdentifier(),
            'client_id'   => $accessTokenEntity->getClient()->getIdentifier(),
            'expire_time' => $accessTokenEntity->getExpiryDateTime(),
        ];

        if (!is_null($accessTokenEntity->getUserIdentifier())) {
            $newAccessToken['user_id'] = $accessTokenEntity->getUserIdentifier();
        }
        if (!is_null($accessTokenEntity->getName())) {
            $newAccessToken['name'] = $accessTokenEntity->getName();
        }
        if ($accessTokenEntity->getScopes() !== []) {
            $scopes = array_map(function ($Scope) {
                /* @var ScopeEntity $Scope */
                return $Scope->getIdentifier();
            }, $accessTokenEntity->getScopes());

            if ($accessTokenModel::$canHandleArray) {
                $newAccessToken['scopes'] = $scopes;
            } else {
                $newAccessToken['scopes'] = json_encode($scopes);
            }
        }
        $accessTokenModel::create($newAccessToken);
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        $accessTokenModel::where('token', $tokenId)->delete();
    }

    /**
     * find an access token.
     *
     * @param string $tokenId
     *
     * @return AccessTokenEntity
     */
    public function findAccessToken($tokenId)
    {
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        $accessToken = $accessTokenModel::where('token', $tokenId)->first();

        if (is_null($accessToken)) {
            return;
        }

        return $this->getAccessTokenEntity($accessToken);
    }

    /**
     * find an access token.
     *
     * @param UserEntity $user
     *
     * @return array|null
     */
    public function findAccessTokensByUser(UserEntity $user)
    {
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        /** @var Collection $accessTokens */
        $accessTokens = $accessTokenModel::where('user_id', $user->getIdentifier())->get();

        if ($accessTokens->isEmpty()) {
            return;
        }

        $accessTokens->map(function ($accessToken) {
            return $this->getAccessTokenEntity($accessToken);
        });

        return $accessTokens->toArray();
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
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        return !(bool) $accessTokenModel::where('token', $tokenId)->exists();
    }

    /**
     * @param $accessToken
     *
     * @return AccessTokenEntity
     */
    protected function getAccessTokenEntity($accessToken)
    {
        $accessTokenModel = $this->modelResolver->getModel('AccessTokenModel');

        $accessTokenEntity = new AccessTokenEntity();


        $clientRepository = new ClientRepository();
        $client = $clientRepository->findClientEntity($accessToken->client_id, null, null, false);
        $accessTokenEntity->setClient($client);
        $accessTokenEntity->setUserIdentifier($accessToken->user_id);
        $accessTokenEntity->setIdentifier($accessToken->token);
        $accessTokenEntity->setExpiryDateTime($accessToken->expire_time);

        $scopes = $accessToken->scopes;
        if (!$accessTokenModel::$canHandleArray) {
            $scopes = json_decode($scopes);
        }
        if (!empty($scopes)) {
            $clientRepository = new ScopeRepository();

            foreach ($scopes as $scope) {
                $accessTokenEntity->addScope(
                    $clientRepository->getScopeEntityByIdentifier($scope)
                );
            }
        }

        return $accessTokenEntity;
    }
}
