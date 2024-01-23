<?php

namespace RealtimeRegister\Services;

class Assets
{
    protected array $head = [];

    protected array $footer = [];

    public function prependHead(string $content)
    {
        array_unshift($this->head, $content);

        return $this;
    }

    public function head(string $content)
    {
        $this->head[] = $content;

        return $this;
    }

    public function footer(string $content)
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