<?php

namespace RealtimeRegister\Actions;

use Illuminate\Database\Capsule\Manager;
use RealtimeRegister\App;
use RealtimeRegister\Cache;
use RealtimeRegister\Contracts\InvokableAction;
use RealtimeRegister\Request;
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
        return Cache::request()->rememberForever('domain-info:' . $request->domain->domainName(), function () use ($request) {
            return App::client()->domains->get($request->domain->domainName());
        });
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
        return $this->tldInfo($request)->metadata;
    }

    protected function tldInfo(Request $request): TLDInfo
    {
        return TLDInfo::fromArray(Cache::db()->remember('tld-info:' . $request->domain->tld, 14400, function () use ($request) {
            $info = App::client()->tlds->info($request->domain->tld);


            foreach ($info->applicableFor as $applicableTld) {
                if ($applicableTld === $request->domain->tld) {
                    continue;
                }

                Cache::db()->put('tld-info:' . $applicableTld, $info->toArray(), 14400);
            }

            return $info->toArray();
        }));
    }

    protected function additionalFields(Request $request)
    {
    }

    public function config(string $key, $default = null)
    {
        return Manager::table('tblconfiguration')->where('setting', $key)->value('value') ?: $default;
    }
}
