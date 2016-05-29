<?php

namespace RTLer\Oauth2\Models;

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
        'session_id',
        'expire_time'
    ];
}
