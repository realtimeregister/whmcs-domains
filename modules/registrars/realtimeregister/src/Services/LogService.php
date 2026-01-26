<?php

namespace RealtimeRegisterDomains\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use RealtimeRegisterDomains\Models\Whmcs\ErrorLog;

class LogService
{
    private static int $pageSize = 25;

    public static function logError(\Throwable $e, string $message = ""): void
    {
        ErrorLog::query()->insert(
            [
                "severity" => "error",
                "exception_class" => get_class($e),
                "message" => "RealtimeRegister: " . $message . ": " . $e,
                "filename" => $e->getFile(),
                "line" => $e->getLine(),
                "details" => $e->getTraceAsString(),
                "created_at" => Carbon::now()->toDateTimeString()
            ]
        );
    }

    public static function getErrors(int $pageId = 1, string $searchTerm = ''): array | Collection
    {
        return ErrorLog::query()
            ->where("severity", "=", "error")
            ->where("message", "LIKE", "RealtimeRegister: %" . ($searchTerm ? $searchTerm . "%" : ''))
            ->orderBy("id", "desc")
            ->forPage($pageId, self::$pageSize)->get();
    }
}
