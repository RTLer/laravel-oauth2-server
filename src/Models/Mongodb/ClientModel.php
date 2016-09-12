<?php

namespace RTLer\Oauth2\Models\Mongodb;

use Jenssegers\Mongodb\Eloquent\Model;

class ClientModel extends Model
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
    protected $collection = 'oauth_clients';

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

    public static $canHandleArray = true;

    public static $identifierKey = '_id';

    /**
     * Scope a query.
     *
     * @param $query
     * @param $identifier
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where(self::$identifierKey, $identifier);
    }
}
