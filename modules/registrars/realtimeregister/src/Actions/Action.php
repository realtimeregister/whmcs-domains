<?php

namespace RealtimeRegisterDomains\Actions;

use Illuminate\Database\Capsule\Manager;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Contracts\InvokableAction;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\MetadataService;
use RealtimeRegister\Domain\Contact;
use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Domain\TLDMetaData;

abstract class Action implements InvokableAction
{
    use DomainTrait;

    public function __construct(protected App $app)
    {
    }

    protected function domainInfo(Request $request): DomainDetails
    {
        $domainName = $this->checkForPunyCode($request->domain);
        return Cache::request()->rememberForever(
            'domain-info:' . $domainName,
            function () use ($request, $domainName) {
                return App::client()->domains->get($domainName);
            }
        );
    }

    protected function forgetDomainInfo(Request $request): bool
    {
        return Cache::request()->forget('domain-info:' . $this->checkForPunyCode($request->domain));
    }

    public function contactInfo(string $handle): Contact
    {
        $customerHandle = App::registrarConfig()->customerHandle();

        return Cache::request()->rememberForever(
            'contact-info:' . $customerHandle . ':' . $handle,
            function () use ($customerHandle, $handle) {
                return App::client()->contacts->get($customerHandle, $handle);
            }
        );
    }

    protected function metadata(Request $request): TLDMetaData
    {
        return (new MetadataService($request->domain->tldPunyCode))->getMetadata();
    }

    protected function tldInfo(Request $request): TLDInfo
    {
        return (new MetadataService($request->domain->tldPunyCode))->getAll();
    }

    public function config(string $key, $default = null)
    {
        return Manager::table('tblconfiguration')->where('setting', $key)->value('value') ?: $default;
    }
}
