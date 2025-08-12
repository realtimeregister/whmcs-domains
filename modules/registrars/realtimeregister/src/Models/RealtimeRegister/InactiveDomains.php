<?php

namespace RealtimeRegisterDomains\Models\RealtimeRegister;

use Illuminate\Database\Eloquent\Model;

class InactiveDomains extends Model
{
    public const TABLE_NAME = 'mod_realtimeregister_inactive_domains';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;
}
