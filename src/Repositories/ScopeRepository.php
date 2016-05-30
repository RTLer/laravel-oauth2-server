<?php
namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use RTLer\Oauth2\Entities\ScopeEntity;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Oauth2Server;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var ModelResolver
     */
    protected $modelResolver;

    /**
     * AccessTokenRepository constructor.
     *
     */
    public function __construct()
    {
        $type = app()->make(Oauth2Server::class)
            ->getOptions()['database_type'];
        $this->modelResolver = new ModelResolver($type);
    }

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $scopeModel = $this->modelResolver->getModel('ScopeModel');

        $scopeModel = $scopeModel::where('_id', $identifier)->first();
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
        $scopeModel = $this->modelResolver->getModel('ScopeModel');
        $clientModel = $this->modelResolver->getModel('ClientModel');


        $clientModel = $clientModel::where('_id', $clientEntity->getIdentifier())->first();
        if (is_null($clientModel)) {
            return [];
        }

        $validScopes = collect($clientModel->scopes)->intersect($scopes);
        $validScopeModels = $scopeModel::whereIn('_id', $validScopes)->get();

        $validScopeEntities = [];
        foreach ($validScopeModels as $validScopeModel) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier($validScopeModel->_id);
            $validScopeEntities[] = $scopeEntity;
        }

        return $validScopeEntities;
    }
}