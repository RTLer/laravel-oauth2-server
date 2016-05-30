<?php

namespace RTLer\Oauth2\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'grant_type',
        'secret',
        'name',
        'redirect_uri',
        'scopes',
    ];
}
