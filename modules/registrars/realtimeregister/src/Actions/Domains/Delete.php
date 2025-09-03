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
        App::client()->domains->delete(self::getDomainName($request->domain));
        $this->forgetDomainInfo($request);

        try {
            Manager::table(InactiveDomains::TABLE_NAME)->where(['domainName' => $request->domain])
                ->delete();
        } catch (\Exception $ignored) {
        }

        return ['success' => 'success'];
    }
}
