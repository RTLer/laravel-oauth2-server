<?php
namespace RTLer\Oauth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use RTLer\Oauth2\Entities\UserEntity;

class UserRepository implements UserRepositoryInterface
{

    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     *
     * @return \League\OAuth2\Server\Entities\UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    )
    {

        $userEntity = new UserEntity();
        $userVerifier = \Oauth2::getOptions()['user_verifier'];
        $identifier = (new $userVerifier())
            ->getUserIdentifierByUserCredentials($username, $password, $grantType);
        
        if(is_null($identifier)){
            return null;
        }

        $userEntity->setIdentifier($identifier);
        return $userEntity;
    }
}