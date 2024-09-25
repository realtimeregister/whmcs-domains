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

    /**
     * @param array|string $content If you use this function directly, you can also add random pieces of content to the
     *                              header. If you just want to add a file, use addScript or addStyle instead
     */
    public function addToHeader($content): self
    {
        if (is_array($content)) {
            self::$head[] = $content;
        } else {
            $payload = [];
            $payload['name'] = uniqid(more_entropy: true);
            $payload['location'] = ScriptLocationType::Header;
            $payload['type'] = 'inline';
            $payload['content'] = $content;
            self::$head[] = $payload;
        }
        return $this;
    }

    public function addScript(string $name, ScriptLocationType $scriptLocationType = ScriptLocationType::Header): self
    {
        $payload = [];
        $payload['name'] = $name;
        $payload['location'] = $scriptLocationType;
        $payload['type'] = 'script';

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

    private function addToFooter(array $content): void
    {
        self::$footer[] = $content;
    }

    public function renderHead(): string
    {
        $content = $this->render(self::$head);
        self::$head = [];

        return $content;
    }

    public function renderFooter(): string
    {
        $content = $this->render(self::$footer);
        self::$footer = [];

        return $content;
    }

    /**
     * Render function, adds App::VERSION to each file to combat caching
     */
    private function render(array $assets): string
    {
        $content = '';

        foreach ($assets as $asset) {
            if (is_array($asset)) {
                if ($asset['type'] === 'script') {
                    if (array_key_exists($asset['name'], self::$javascriptVariables)) {
                        $content .= $this->renderJavascriptVariables($asset['name']);
                    }
                    $content .= '<script src="' . self::getPath($this->getBasePath('/Assets/Js/' . $asset['name']))
                        . '?' . App::VERSION . '"></script>';
                } elseif ($asset['type'] === 'style') {
                    $content .= '<link href="' . self::getPath($this->getBasePath('/Assets/Css/' . $asset['name']))
                        . '?' . App::VERSION . '" rel="stylesheet">';
                } elseif ($asset['type'] === 'inline') {
                    $content .= $asset['content'];
                }
            } else {
                $content .= $asset;
            }
        }

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
        $payload = [];
        $payload['name'] = $name;
        $payload['type'] = 'style';

        $this->addToHeader($payload);

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
