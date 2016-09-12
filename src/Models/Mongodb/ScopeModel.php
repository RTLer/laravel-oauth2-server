<?php

namespace RTLer\Oauth2\Models\Mongodb;

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
        'description',
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

    /**
     * Scope a query.
     *
     * @param $query
     * @param $identifier
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByIdentifierIn($query, $identifier)
    {
        return $query->whereIn(self::$identifierKey, $identifier);
    }
}
