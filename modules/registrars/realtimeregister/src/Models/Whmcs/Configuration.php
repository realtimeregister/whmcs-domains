<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    public const TABLE_NAME = 'tblconfiguration';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];
    public $timestamps = false;
}
