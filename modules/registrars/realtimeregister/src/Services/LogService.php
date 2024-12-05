<?php

namespace RealtimeRegisterDomains\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
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

    public static function getErrors(int $pageId = 1, string $searchTerm = ''): Paginator
    {
        return ErrorLog::query()
            ->where("severity", "=", "error")
            ->where("message", "LIKE", "RealtimeRegister: %")
            ->orderBy("id", "desc")
            ->simplePaginate(
                self::$pageSize,
                ['id', 'severity', 'exception_class', 'message', 'filename', 'line', 'details', 'created_at'],
                'page',
                $pageId
            );
    }
}
