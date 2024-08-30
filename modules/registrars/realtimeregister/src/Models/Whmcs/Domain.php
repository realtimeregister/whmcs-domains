<?php

namespace RealtimeRegister\Models\Whmcs;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    public const TABLE_NAME = 'tbldomains';
    protected $table = self::TABLE_NAME;

    public static function exists(string $domainName): bool
    {
        return self::query()->select(["id"])->where('domain', '=', $domainName)->count() > 0;
    }
}
