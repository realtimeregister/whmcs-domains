<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    public const TABLE_NAME = 'tbldomains';
    protected $table = self::TABLE_NAME;
}
