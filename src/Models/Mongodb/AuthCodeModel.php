<?php

namespace RTLer\Oauth2\Models\Mongodb;

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expire_time',
    ];

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
        'expire_time',
        'user_id',
    ];
}
