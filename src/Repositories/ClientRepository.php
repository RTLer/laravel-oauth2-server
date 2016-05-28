<?php

namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use RTLer\Oauth2\Entities\ClientEntity;
use RTLer\Oauth2\Models\ClientModel;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     * @param bool $mustValidateSecret If true the client must attempt to validate the secret unless the client
     *                                        is confidential
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $clintModelQuery = ClientModel::where('_id', $clientIdentifier);

        if ($mustValidateSecret) {
            $clintModelQuery->where('secret', $clientSecret);
        }
        $clientModel = $clintModelQuery->first();

        if (is_null($clientModel)) {
            return null;
        }

        if (!is_null($clientModel->grant_type) &&
            $clientModel->grant_type != $grantType
        ) {
            return null;
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