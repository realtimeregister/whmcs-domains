<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'tblpaymentgateways';

    protected $guarded = ['id'];

    public $timestamps = false;
}
