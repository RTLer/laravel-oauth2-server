<?php
namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use RTLer\Oauth2\Entities\ScopeEntity;
use RTLer\Oauth2\Models\ClientModel;
use RTLer\Oauth2\Models\ScopeModel;

class ScopeRepository implements ScopeRepositoryInterface
{

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $scopeModel = ScopeModel::where('_id', $identifier)->first();
        if (is_null($scopeModel)) {
            return null;
        }
        $scopeEntity = new ScopeEntity();
        $scopeEntity->setIdentifier($scopeModel->_id);

        return $scopeEntity;
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param null|string $userIdentifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    )
    {
        $clientModel = ClientModel::where('_id', $clientEntity->getIdentifier())->first();
        if (is_null($clientModel)) {
            return [];
        }

        $validScopes = collect($clientModel->scopes)->intersect($scopes);
        $validScopeModels = ScopeModel::whereIn('_id', $validScopes)->get();

        $validScopeEntities = [];
        foreach ($validScopeModels as $validScopeModel) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier($validScopeModel->_id);
            $validScopeEntities[] = $scopeEntity;
        }

        return $validScopeEntities;
    }
}