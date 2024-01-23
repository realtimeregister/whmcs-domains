<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Model;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

class Domain extends Model
{
    protected $table = 'tbldomains';
}
