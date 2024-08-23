<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class DomainPricing extends Model
{
    protected $table = 'tbldomainpricing';

    protected $guarded = ['id'];

    public $timestamps = false;
}
