<?php

namespace RTLer\Oauth2\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class SessionModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_sessions';

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
