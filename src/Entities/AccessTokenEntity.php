<?php

namespace RTLer\Oauth2\Entities;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use AccessTokenTrait, TokenEntityTrait, EntityTrait;

    protected $name = null;

    protected $publicIdentifier = null;

    /**
     * Set the name for the token.
     * it's use to name the token in personal access tokens.
     *
     * @param string $name The name of the token
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * get the name of the token.
     *
     * @return string
     **/
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name for the token.
     * it's use to name the token in personal access tokens.
     *
     * @param string $publicIdentifier The name of the token
     */
    public function setPublicIdentifier($publicIdentifier)
    {
        $this->publicIdentifier = (string) $publicIdentifier;
    }

    /**
     * get the name of the token.
     *
     * @return string
     **/
    public function getPublicIdentifier()
    {
        return $this->publicIdentifier;
    }
}
