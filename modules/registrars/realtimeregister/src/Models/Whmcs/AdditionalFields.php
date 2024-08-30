<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class AdditionalFields extends Model
{
    protected $table = 'tbldomainsadditionalfields';

    protected $guarded = ['id'];

    public $timestamps = false;
}
