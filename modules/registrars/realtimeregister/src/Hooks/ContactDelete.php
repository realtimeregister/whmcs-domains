<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;
use RealtimeRegisterDomains\Services\LogService;

class ContactDelete extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $handles = ContactMapping::query()->where(
            ['userid' => $vars->userid],
            ['contactid', $vars->get('contactid')]
        )->pluck('handle');

        foreach ($handles as $handle) {
            try {
                App::client()->contacts->delete(App::registrarConfig()->customerHandle(), $handle);
                ContactMapping::query()->where(
                    ['userid', $vars->get('userid')],
                    ['contactid', $vars->get('contactid')],
                    ['handle', $handle]
                )->delete();
            } catch (BadRequestException $exception) {
                // Most of the time a validation error, because the contact is still in use
                LogService::logError($exception);
                throw $exception;
            }
        }
    }
}
