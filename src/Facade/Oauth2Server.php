<?php
namespace RTLer\Oauth2\Facade;

use Illuminate\Support\Facades\Facade;
use RTLer\Oauth2\Oauth2Server as Oauth2ServerClass;

class Oauth2Server extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Oauth2ServerClass::class;
    }
}