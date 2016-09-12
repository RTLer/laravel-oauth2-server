<?php

namespace RTLer\Oauth2\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
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
    protected $table = 'oauth_clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'grant_type',
        'secret',
        'name',
        'redirect_uri',
        'scopes',
    ];

    public static $canHandleArray = false;

    public static $identifierKey = 'id';

    /**
     * Scope a query to only include users of a given type.
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
