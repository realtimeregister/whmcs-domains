<?php

declare(strict_types=1);

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Entities\WhmcsContact;
use RealtimeRegisterDomains\Services\LogService;

class ContactEdit extends Hook
{
    use DomainTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(DataObject $vars): void
    {
        $mappings = App::contacts()->fetchMappingByContactId((int)$vars->get('userid'), (int)$vars->get('contactid'));

        if ($mappings->isEmpty()) {
            // No mapping found, so we create one from the whmcs contact, if possible
            try {
                $this->getOrCreateContact(
                    (int)$vars->get('userid'),
                    (int)$vars->get('contactid'),
                    (bool)$vars->get('companyname')
                );

                $mappings = App::contacts()->fetchMappingByContactId(
                    (int)$vars->get('userid'),
                    (int)$vars->get('contactid')
                );
            } catch (\Exception $exception) {
                LogService::logError($exception);
                throw $exception;
            }
        }

        $contact = WhmcsContact::make($vars);

        foreach ($mappings as $mapping) {
            $rtrContact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $mapping->handle);

            $diff = $contact->diff($rtrContact, $contact->toRtrArray($mapping->org_allowed));

            if (!empty($diff)) {
                try {
                    App::client()->contacts->update(
                        App::registrarConfig()->customerHandle(),
                        $mapping->handle,
                        ...$diff
                    );
                } catch (\Exception $exception) {
                    LogService::logError($exception, $diff['addressLine']);
                    throw $exception;
                }
            }
        }
    }
}
