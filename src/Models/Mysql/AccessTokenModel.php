<?php

namespace RTLer\Oauth2\Models\Mysql;


use Illuminate\Database\Eloquent\Model;

class AccessTokenModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_access_tokens';

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
