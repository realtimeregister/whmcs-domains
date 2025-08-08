<?php

namespace RealtimeRegisterDomains\Entities;

use Illuminate\Support\Arr;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class RegistrarConfig
{
    protected ?Request $request = null;

    protected ?array $registrarConfig = null;

    public function customerHandle(): ?string
    {
        return $this->get('customer_handle');
    }

    public function contactHandlePrefix(): ?string
    {
        return $this->get('contact_handle_prefix', 'srs_');
    }

    public function isTest(): bool
    {
        return $this->get('test_mode', true);
    }

    public function apiKey(): ?string
    {
        return $this->get('rtr_api_key');
    }

    public function hasDnsSupport(): bool
    {
        $dnsSupport = $this->get('dns_support', 'none');
        if ($dnsSupport === 'none') {
            return false;
        }
        return true;
    }

    public function get(string $key, $default = null)
    {
        if ($this->request) {
            return $this->request->get($key, fn() => $this->getRegistrarConfig($key, $default));
        }

        return $this->getRegistrarConfig($key, $default);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    protected function getRegistrarConfig(string $key, $default = null)
    {
        $this->loadRegistrarConfig();

        return Arr::get($this->registrarConfig, $key, $default);
    }

    protected function loadRegistrarConfig(): void
    {
        if ($this->registrarConfig === null) {
            /**
             * @noinspection PhpUndefinedConstantInspection
             */
            include_once ROOTDIR . "/includes/registrarfunctions.php";
            $this->registrarConfig = array_merge(
                [
                    'SyncStatus' => null,
                    'SyncExpireDate' => null,
                    'SyncDueDate' => null,
                    'SyncNextInvoiceDate' => null,
                    'DueDateDiff' => 0,
                    'NextInvoiceDateDiff' => 0,
                    'ipRestrict' => null,
                ],
                getRegistrarConfigOptions(App::NAME)
            );
        }
    }

    public function keepNameServers(): bool
    {
        return $this->get('transfer_keep_nameservers') === 'on';
    }
}
