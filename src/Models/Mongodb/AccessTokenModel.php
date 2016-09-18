<?php

namespace RTLer\Oauth2\Models\Mongodb;

use Jenssegers\Mongodb\Eloquent\Model;

class AccessTokenModel extends Model
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
    protected $collection = 'oauth_access_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'name',
        'client_id',
        'session_id',
        'expire_time',
        'user_id',
        'scopes',
    ];

    public static $canHandleArray = true;
    public static $identifierKey = '_id';
}
