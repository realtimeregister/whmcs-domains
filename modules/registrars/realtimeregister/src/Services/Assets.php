<?php

namespace RealtimeRegister\Services;

use RealtimeRegister\App;
use RealtimeRegister\Enums\ScriptLocationType;

final class Assets
{
    protected static array $head = [];
    protected static array $footer = [];
    protected static array $javascriptVariables = [];

    public function prependHead(string $content): self
    {
        array_unshift(self::$head, $content);

        return $this;
    }

    public function addToHeader(string $content): self
    {
        self::$head[] = $content;
        return $this;
    }

    public function addScript(string $name, ScriptLocationType $scriptLocationType = ScriptLocationType::Header): self
    {
        $payload = '<script src="' . self::getPath($this->getBasePath('/Assets/Js/' . $name))
            . '?' . App::VERSION . '"></script>';
        if ($scriptLocationType === ScriptLocationType::Header) {
            $this->addToHeader($payload);
        } else {
            $this->addToFooter($payload);
        }
        return $this;
    }

    private static function getPath(string $path): string
    {
        return str_replace(
            ['//', '\\\\', '/' . '\\'],
            DIRECTORY_SEPARATOR,
            $path
        );
    }

    public function addToFooter(string $content): self
    {
        self::$footer[] = $content;

        return $this;
    }

    public function renderHead(): string
    {
        $content = implode("\n", self::$head);
        self::$head = [];

        foreach (self::$javascriptVariables as $key => $foo) {
            $content .= $this->renderJavascriptVariables($key);
        }

        return $content;
    }

    public function renderFooter(): string
    {
        $content = implode("\n", self::$footer);

        self::$footer = [];

        return $content;
    }

    private function getBasePath(string $assetUrl): string
    {
        $basePath = realpath(__DIR__ . '/../../../../../');
        $baseModulePath = realpath(__DIR__ . '/../');
        $basePath = str_replace($basePath, '', $baseModulePath);

        return $basePath . $assetUrl;
    }

    public function addStyle(string $name): self
    {
        $this->addToHeader(
            '<link href="' . self::getPath($this->getBasePath('/Assets/Css/' . $name))
            . '?' . App::VERSION . '" rel="stylesheet">'
        );

        return $this;
    }

    public function addToJavascriptVariables(string $name, array $data): void
    {
        self::$javascriptVariables[$name] = $data;
    }

    private function renderJavascriptVariables(string $asset): string
    {
        $output = '';
        if (!empty(self::$javascriptVariables[$asset])) {
            $output .= '<script>';
            foreach (self::$javascriptVariables[$asset] as $key => $data) {
                if (is_array($data)) {
                    $output .= 'var ' . $key . ' = ' . json_encode($data) . ';';
                } else {
                    $output .= 'var ' . $key . ' = "' . $data . '";';
                }
            }
            $output .= '</script>';
        }
        return $output;
    }
}
