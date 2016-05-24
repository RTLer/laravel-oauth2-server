<?php

namespace RTLer\Oauth2\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AuthCodeModel extends Model
{

    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'oauth_auth_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'redirect_uri',
        'expire_time'
    ];
}
