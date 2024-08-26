<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
{
    public const TABLE_NAME = 'tblcurrencies';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];
    public $timestamps = false;
}
