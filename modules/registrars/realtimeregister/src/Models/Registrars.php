<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Registrars extends Model
{
    public const TABLE_NAME = 'tblregistrars';
    protected $table = self::TABLE_NAME;

    /**
     * Scope a query to only include the current addon.
     */
    public function scopeRegistrar(Builder $query): Builder
    {
        return $query->where('registrar', 'realtimeregister');
    }
}
