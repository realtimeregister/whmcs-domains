<?php

namespace RealtimeRegisterDomains\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    public const TABLE_NAME = 'tblorders';
    protected $table = self::TABLE_NAME;
}
