<?php

namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Models\ModelResolver;
use RTLer\Oauth2\Oauth2Server;

class ClientRepository implements ClientRepositoryInterface
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
     * Get a client.
     *
     * @param string      $clientIdentifier   The client's identifier
     * @param string      $grantType          The grant type used
     * @param null|string $clientSecret       The client's secret (if sent)
     * @param bool        $mustValidateSecret If true the client must attempt to validate the secret unless the client
     *                                        is confidential
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $clientModel = $this->modelResolver->getModel('ClientModel');

        $driver = get_class($clientModel::getConnectionResolver()->connection());
        $idKey = 'id';
        if ($driver == 'Jenssegers\Mongodb\Connection') {
            $idKey = '_id';
        }

        $clintModelQuery = $clientModel::where($idKey, $clientIdentifier);

        if ($mustValidateSecret) {
            $clintModelQuery->where('secret', $clientSecret);
        }
        $clientModel = $clintModelQuery->first();

        if (is_null($clientModel)) {
            return;
        }

        if (!empty($clientModel->grant_type) &&
            $clientModel->grant_type != $grantType
        ) {
            return;
        }

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientIdentifier);
        $clientEntity->setName($clientModel->name);
        if (!is_null($clientModel->redirect_uri)) {
            $clientEntity->setRedirectUri($clientModel->redirect_uri);
        }

        return $clientEntity;
    }
}
