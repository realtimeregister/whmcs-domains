<?php

namespace RealtimeRegisterDomains\Services;

use Carbon\Carbon;
use RealtimeRegisterDomains\Models\Whmcs\ErrorLog;

class LogService
{
    public static function logError(\Throwable $e, string $message = ""): void
    {
        ErrorLog::query()->insert(
            ["severity" => "error",
                "exception_class" => get_class($e),
                "message" => "RealtimeRegister: " . $message . ": " . $e,
                "filename" => $e->getFile(),
                "line" => $e->getLine(),
                "details" => $e->getTraceAsString(),
                "created_at" => Carbon::now()->toDateTimeString()
            ]
        );
    }

    public static function getErrors(int $limit = 500): array
    {
        return ErrorLog::query()
            ->where("severity", "=", "error")
            ->where("message", "LIKE", "RealtimeRegister: %")
            ->orderBy("id", "desc")
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
