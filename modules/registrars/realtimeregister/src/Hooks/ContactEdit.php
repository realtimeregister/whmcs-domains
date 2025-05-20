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
                    $errorMessage = json_decode(str_replace('Bad Request: ', '', $exception->getMessage()), true);

                    // Split the contact if the error is a validation error, because we can't update the contact
                    if (is_array($errorMessage) && $errorMessage['type'] == 'ContactUpdateValidationError') {
                        $newHandle = uniqid(App::registrarConfig()->contactHandlePrefix() ?: '', true);
                        App::client()->contacts->split(
                            App::registrarConfig()->customerHandle(),
                            $mapping->handle,
                            $newHandle
                        );

                        App::client()->contacts->update(
                            App::registrarConfig()->customerHandle(),
                            $newHandle,
                            ...$diff
                        );

                        LogService::logError(
                            $exception,
                            sprintf("Splitting contact from %s to %s", $mapping->handle, $newHandle)
                        );
                    } else {
                        LogService::logError($exception, json_encode($diff));
                        throw $exception;
                    }
                }
            }
        }
    }
}
