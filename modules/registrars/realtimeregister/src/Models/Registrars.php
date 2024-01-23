<?php

namespace RealtimeRegister\Models;

use Realtimeregister\Services\Config\Config;

class Registrars extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'tblregistrars';

    /**
     * Scope a query to only include the current addon.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRegistrar($query)
    {
        return $query->where('registrar', 'realtimeregister');
    }
}