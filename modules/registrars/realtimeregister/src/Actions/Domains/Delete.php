<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Illuminate\Database\Capsule\Manager;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\RealtimeRegister\InactiveDomains;
use RealtimeRegisterDomains\Request;

class Delete extends Action
{
    public function __invoke(Request $request): array
    {
        $domainName = self::getDomainName($request->domain);
        App::client()->domains->delete($domainName);
        $this->forgetDomainInfo($request);

        try {
            Manager::table(InactiveDomains::TABLE_NAME)->where(['domainName' => $domainName])
                ->delete();
        } catch (\Exception $ignored) {
        }

        return ['success' => 'success'];
    }
}
