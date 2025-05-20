<?php

namespace RealtimeRegisterDomains\Widget;

abstract class BaseWidget extends \WHMCS\Module\AbstractWidget
{
    public function getId()
    {
        return 'realtimeregister-' . $this->getClassname();
    }

    private function getClassname(): string
    {
        $classname = get_called_class();
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }

        return $pos;
    }
}
