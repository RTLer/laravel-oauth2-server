<?php

namespace RTLer\Oauth2\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;

class SessionModel extends Model
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
    protected $collection = 'oauth_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'client_id',
        'owner_type',
        'owner_id',
        'client_redirect_uri',
    ];
}
