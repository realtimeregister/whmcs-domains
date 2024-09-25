<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AddonModules extends Model
{
    protected $table = 'tbladdonmodules';

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Scope a query to only include the current addon.
     */
    public function scopeAddon(Builder $query): Builder
    {
        return $query->where('module', 'realtimeregister');
    }
}
