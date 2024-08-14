<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public const TABLE_NAME = 'tblcontacts';
    protected $table = self::TABLE_NAME;

    public const ROLE_REGISTRANT = 'Registrant';
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_TECH = 'Technical';
    public const ROLE_BILLING = 'Billing';
}
