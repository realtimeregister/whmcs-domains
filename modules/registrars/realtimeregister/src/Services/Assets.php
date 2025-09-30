<?php

namespace RealtimeRegisterDomains\Services;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Enums\ScriptLocationType;
use WHMCS\Config\Setting;

final class Assets
{
    protected static array $head = [];
    protected static array $footer = [];
    protected static array $javascriptVariables = [];

    public function prependHead(string $content): self
    {
        array_unshift(self::$head, $this->createPayload($content));

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
            self::$head[] = $this->createPayload($content);
        }
        return $this;
    }

    /**
     * Helper function to create payloads
     * @param string $content
     * @param ScriptLocationType $scriptLocationType
     * @return array
     */
    private function createPayload(
        string $content,
        ScriptLocationType $scriptLocationType = ScriptLocationType::Header
    ): array {
        $payload = [];
        $payload['name'] = uniqid(more_entropy: true);
        $payload['location'] = $scriptLocationType;
        $payload['type'] = 'inline';
        $payload['content'] = $content;

        return $payload;
    }

    public function addScript(string $name, ScriptLocationType $scriptLocationType = ScriptLocationType::Header): self
    {
        $payload = [];
        $payload['name'] = $name;
        $payload['location'] = $scriptLocationType;
        $payload['type'] = 'script';

        if ($scriptLocationType === ScriptLocationType::Header) {
            $this->addItemIfNotExists(self::$head, $name, $payload, [$this, 'addToHeader']);
        } else {
            $this->addItemIfNotExists(self::$footer, $name, $payload, [$this, 'addToFooter']);
        }
        return $this;
    }

    private function addItemIfNotExists(array $list, string $name, array $payload, callable $addMethod): void
    {
        foreach ($list as $item) {
            if ($item['name'] === $name) {
                return;
            }
        }
        $addMethod($payload);
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
        // Filesystem: WHMCS root and this module's /src folder
        $fsWhmcsRoot = realpath(__DIR__ . '/../../../../../');
        $fsModuleSrc = realpath(__DIR__ . '/../');

        // Relative web path to the module's /src (e.g. /modules/registrars/realtimeregister/src)
        $relative = str_replace($fsWhmcsRoot, '', $fsModuleSrc);
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

        // Prepend the SystemURL *path* (handles installs under /clients, /billing, etc.)
        $systemPath = parse_url(Setting::getValue('SystemURL'), PHP_URL_PATH) ?? '';
        $systemPath = rtrim($systemPath, '/');

        // Final absolute path for href/src (no scheme/host so getPath() won’t break https://)
        return $systemPath . $relative . $assetUrl;
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
