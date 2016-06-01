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

        $driver = get_class($scopeModel::getConnectionResolver()->connection());
        $idKey = 'id';
        if ($driver == 'Jenssegers\Mongodb\Connection') {
            $idKey = '_id';
        }

        $scopeModel = $scopeModel::where($idKey, $identifier)->first();
        if (is_null($scopeModel)) {
            return;
        }
        $scopeEntity = new ScopeEntity();
        $scopeEntity->setIdentifier($scopeModel->{$idKey});

        return $scopeEntity;
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[]                               $scopes
     * @param string                                               $grantType
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param null|string                                          $userIdentifier
     *
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $scopeModel = $this->modelResolver->getModel('ScopeModel');
        $clientModel = $this->modelResolver->getModel('ClientModel');

        $driver = get_class($clientModel::getConnectionResolver()->connection());
        $idKey = 'id';
        if ($driver == 'Jenssegers\Mongodb\Connection') {
            $idKey = '_id';
        }

        $clientModel = $clientModel::where($idKey, $clientEntity->getIdentifier())->first();
        if (is_null($clientModel)) {
            return [];
        }

        $scopes = array_map(function ($scopes) {
            return $scopes->getIdentifier();
        }, $scopes);

        $validScopes = $scopeModel::whereIn($idKey, $scopes)->get()->pluck($idKey);

        $validScopes = collect($validScopes);

        if (!empty($clientModel->scopes)) {
            $clientScopes = $clientModel->scopes;
            if ($driver != 'Jenssegers\Mongodb\Connection') {
                $clientScopes = json_decode($clientScopes);
            }
            $validScopes = $validScopes->intersect($clientScopes);
        }

        $validScopeEntities = [];
        foreach ($validScopes as $validScope) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier($validScope);
            $validScopeEntities[] = $scopeEntity;
        }

        return $validScopeEntities;
    }
}
