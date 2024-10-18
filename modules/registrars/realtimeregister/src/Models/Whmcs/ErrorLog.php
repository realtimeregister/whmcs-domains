<?php

namespace RealtimeRegisterDomains\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    public const TABLE_NAME = 'tblerrorlog';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    public $timestamps = false;
}
