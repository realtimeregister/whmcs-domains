<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public const TABLE_NAME = 'tblclients';
    protected $table = self::TABLE_NAME;
}