<?php

namespace RTLer\Oauth2\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SessionModel extends Model
{

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
        'client_redirect_uri'
    ];

    /**
     * oauthScope.
     */
    public function oauthClient()
    {
        return $this->belongsTo('RTLer\Oauth2\Models\OauthClient', 'client_id');
    }

}
