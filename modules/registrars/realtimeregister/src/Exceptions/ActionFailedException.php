<?php

namespace RealtimeRegisterDomains\Exceptions;

class ActionFailedException extends \RuntimeException
{
    protected string $format = 'Error performing %s: %s';

    protected ?array $response = null;

    public static function withResponse(array $response): static
    {
        $exception = (new static());
        $exception->response = $response;

        return $exception;
    }

    public function response(string $action): array
    {
        if ($this->response) {
            return $this->response;
        }

        return [
            'success' => false,
            'error' => sprintf($this->format, $action, $this->getMessage())
        ];
    }

    public static function forException(\Throwable $exception): static
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }
}
