<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use function RealtimeRegister\dd;

class ContactEdit extends Hook
{

    public function __invoke(DataObject $vars)
    {
        // Check if the contact id is mapped to a rtr contact
        App::contacts()->fetchMappingByHandle()


        // Map contact info to understandable object.
    }
}