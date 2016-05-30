<?php

namespace RTLer\Oauth2\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;

class AuthCodeModel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

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
        'token',
        'client_id',
        'expire_time'
    ];
}
