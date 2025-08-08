<?php

namespace RealtimeRegisterDomains\Models\RealtimeRegister;

use Illuminate\Database\Eloquent\Model;

class ProblematicDomains extends Model
{
    public const TABLE_NAME = 'mod_realtimeregister_problematic_domains';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;
}
