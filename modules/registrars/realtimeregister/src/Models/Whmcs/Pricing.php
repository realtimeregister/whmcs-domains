<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    public const TABLE_NAME = 'tblpricing';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    public $timestamps = false;

    public function currency()
    {
        return $this->hasOne(Currencies::class, 'id', 'currency');
    }

    public function getValutaAttribute()
    {
        return $this->currency()->first()->prefix;
    }

    public function getCurrencySuffixAttribute()
    {
        return $this->currency()->first()->suffix;
    }
}
