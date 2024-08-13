<?php

namespace RealtimeRegister\Services;

class Assets
{
    protected array $head = [];

    protected array $footer = [];

    public function prependHead(string $content): self
    {
        array_unshift($this->head, $content);

        return $this;
    }

    public function head(string $content): self
    {
        $this->head[] = $content;

        return $this;
    }

    public function addScript(string $name): self
    {
        $path = self::getPath(self::getSystemUrl() . __DIR__ . '/../Assets/Js/' . $name);

        $this->head[] = '<script type="text/javascript">' . file_get_contents($path) . '</script>';
        return $this;
    }

    private static function getSystemUrl(): string
    {
        global $whmcs;
        return parse_url($whmcs->getSystemURL(), PHP_URL_PATH);
    }

    private static function getPath(string $path): string
    {
        return str_replace(
            ['//', '\\\\', '/' . '\\'],
            DIRECTORY_SEPARATOR,
            $path
        );
    }

    public function footer(string $content): self
    {
        $this->footer[] = $content;

        return $this;
    }

    public function renderHead(): string
    {
        $content = implode("\n", $this->head);

        $this->head = [];

        return $content;
    }

    public function renderFooter(): string
    {
        $content = implode("\n", $this->footer);

        $this->footer = [];

        return $content;
    }
}
