<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPricing extends Model
{
    protected $table = 'tbldomainpricing';

    protected $guarded = ['id'];

    public $timestamps = false;
}