<?php

namespace RealtimeRegisterDomains\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class DomainPricing extends Model
{
    public const TABLE_NAME = 'tbldomainpricing';
    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    public $timestamps = false;
}
