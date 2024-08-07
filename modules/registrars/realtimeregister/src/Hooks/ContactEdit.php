<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Entities\WhmcsContact;

class ContactEdit extends Hook
{
    public function __invoke(DataObject $vars)
    {
        $mappings = App::contacts()->fetchMappingByContactId($vars->get('userid'), $vars->get('contactid'));

        if ($mappings->isEmpty()) {
            // No mapping found, so we do nothing
            return;
        }

        $contact = WhmcsContact::make($vars);

        foreach ($mappings as $mapping) {
            $rtrContact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $mapping->handle);

            $diff = $contact->diff($rtrContact, $contact->toRtrArray($mapping->org_allowed));

            if (!empty($diff)) {
                try {
                    App::client()->contacts->update(App::registrarConfig()->customerHandle(), $mapping->handle, ...$diff);
                } catch (\Exception $exception) {
                    // @todo: Handle bad requests
                    throw $exception;
                }
            }
        }
        // @todo: return
    }
}
