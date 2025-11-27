<?php

namespace RealtimeRegisterDomains\Actions;

use Illuminate\Database\Capsule\Manager;
use RealtimeRegister\Domain\Contact;
use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Contracts\InvokableAction;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\MetadataService;

abstract class Action implements InvokableAction
{
    use DomainTrait;

    public function __construct(protected App $app)
    {
    }

    protected function domainInfo(Request $request): DomainDetails
    {
        $domainName = self::getDomainName($request->domain);
        return Cache::request()->remember(
            'domain-info:' . $domainName,
            MetadataService::DAY_MINUTES,
            function () use ($request, $domainName) {
                return App::client()->domains->get($domainName);
            }
        );
    }

    protected function forgetDomainInfo(Request $request): bool
    {
        return Cache::request()->forget('domain-info:' . self::getDomainName($request->domain));
    }

    public function contactInfo(string $handle): Contact
    {
        $customerHandle = App::registrarConfig()->customerHandle();

        return Cache::request()->remember(
            'contact-info:' . $customerHandle . ':' . $handle,
            MetadataService::DAY_MINUTES,
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
