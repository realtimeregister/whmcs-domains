<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;

class ContactDelete extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $handles = ContactMapping::query()->where(
            ['userid' => $vars->userid],
            ['contactid', $vars->get('contact_id')]
        )->pluck('handle');

        foreach ($handles as $handle) {
            App::client()->contacts->delete($vars->get('customer_handle'), $handle);
            ContactMapping::query()->where(
                ['userid', $vars->get('userid')],
                ['contactid', $vars->get('contact_id')],
                ['handle', $handle]
            )->delete();
        }
    }
}
