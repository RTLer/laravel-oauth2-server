<?php

namespace RTLer\Oauth2\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class GrantModel extends Model
{

    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'oauth_grants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id'
    ];
}
