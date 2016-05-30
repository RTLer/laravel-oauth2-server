<?php

namespace RTLer\Oauth2\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class ScopeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_scopes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'description',
    ];
}
