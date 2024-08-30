<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'tbladmins';

    protected $guarded = ['id'];

    public $timestamps = false;
}
