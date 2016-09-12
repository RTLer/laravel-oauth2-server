<?php

namespace RTLer\Oauth2\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class ScopeModel extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'id',
        'description',
    ];

    public static $canHandleArray = false;

    public static $identifierKey = 'id';

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
