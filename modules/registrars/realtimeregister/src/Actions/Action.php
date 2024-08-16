<?php

namespace RealtimeRegister\Actions;

use Illuminate\Database\Capsule\Manager;
use RealtimeRegister\App;
use RealtimeRegister\Contracts\InvokableAction;
use RealtimeRegister\Models\Cache;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\Contact;
use SandwaveIo\RealtimeRegister\Domain\DomainDetails;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;
use SandwaveIo\RealtimeRegister\Domain\TLDMetaData;

abstract class Action implements InvokableAction
{
    public function __construct(protected App $app)
    {
    }

    protected function domainInfo(Request $request): DomainDetails
    {
        return Cache::request()->rememberForever(
            'domain-info:' . $request->domain->domainName(),
            function () use ($request) {
                return App::client()->domains->get($request->domain->domainName());
            }
        );
    }

    protected function forgetDomainInfo(Request $request): bool
    {
        return Cache::request()->forget('domain-info:' . $request->domain->domainName());
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
        return (new MetadataService($request->domain->tld))->getMetadata();
    }

    protected function info(Request $request) : TLDInfo {
        return (new MetadataService($request->domain->tld))->getAll();
    }


    public function config(string $key, $default = null)
    {
        return Manager::table('tblconfiguration')->where('setting', $key)->value('value') ?: $default;
    }
}
