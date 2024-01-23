<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Model;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

class Contact extends Model
{
    public const ROLE_REGISTRANT = 'Registrant';
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_TECH = 'Technical';
    public const ROLE_BILLING = 'Billing';

    protected $table = 'tblcontacts';
}
