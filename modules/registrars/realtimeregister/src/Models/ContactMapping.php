<?php

namespace RealtimeRegister\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $handle
 * @property int $contactid
 * @property bool $org_allowed
 */
class ContactMapping extends Model
{
    protected $table = 'mod_realtimeregister_contact_mapping';
    public $timestamps = false;
    protected $casts = [
        'org_allowed' => 'bool'
    ];
}
