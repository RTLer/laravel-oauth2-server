<?php

namespace RTLer\Oauth2\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class ScopeModel extends Model
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
    protected $collection = 'oauth_scopes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'description'
    ];

    /**
     * .
     */
    public function oauthClient()
    {
        return $this->hasMany('RTLer\Oauth2\Models\OauthClient', 'scope_id');
    }

    /**
     * .
     */
    public function oauthGrant()
    {
        return $this->hasOne('RTLer\Oauth2\Models\OauthGrant', 'grant_id');
    }

}
