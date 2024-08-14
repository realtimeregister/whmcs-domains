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
    public const TABLE_NAME = 'mod_realtimeregister_contact_mapping';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;
    protected $casts = [
        'org_allowed' => 'bool'
    ];
}
