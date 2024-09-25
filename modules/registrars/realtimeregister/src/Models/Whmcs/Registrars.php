<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Registrars extends Model
{
    public const TABLE_NAME = 'tblregistrars';
    protected $table = self::TABLE_NAME;

    protected $fillable = ['registrar', 'setting', 'value'];

    public $timestamps = false;

    /**
     * Scope a query to only include the current addon.
     */
    public function scopeRegistrar(Builder $query): Builder
    {
        return $query->where('registrar', 'realtimeregister');
    }

    public static function getRegistrarConfig($settingParams): array
    {
        $tblregistrars = Capsule::table('tblregistrars')
            ->where('registrar', 'realtimeregister')
            ->whereIn('setting', $settingParams)
            ->get();

        collect($tblregistrars)->filter(
            function ($value) {
                $value->value = decrypt(
                    $value->value,
                    $GLOBALS['cc_encryption_hash']
                );
            }
        );

        $params = [];
        foreach ($tblregistrars as $param) {
            $params[$param->setting] = $param->value;
        }

        return $params;
    }
}
