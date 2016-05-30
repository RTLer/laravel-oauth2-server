<?php

namespace App\Oauth2;

use RTLer\Oauth2\Authorize\UserVerifierInterface;

class UserVerifier implements UserVerifierInterface
{
    /**
     * get user identifier to login user by it.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType You can use the grant type to determine if the user is permitted to use the grant type.
     *
     * @return string|null user identifier or null in case of failure
     */
    public function getUserIdentifierByUserCredentials($username, $password, $grantType)
    {
        return 'testing';
    }

    /**
     * get user by identifier.
     *
     * @param string $identifier
     *
     * @return null|User
     */
    public function getUserByIdentifier($identifier)
    {
        // TODO: Implement getUserByIdentifier() method.
    }
}
