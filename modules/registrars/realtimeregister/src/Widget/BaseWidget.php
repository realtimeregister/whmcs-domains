<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\Whmcs\Admin;

abstract class BaseWidget extends \WHMCS\Module\AbstractWidget
{
    protected DataObject $vars;

    public function __construct(DataObject $vars)
    {
        $this->vars = $vars;
    }

    public function getId(): string
    {
        $classname = get_called_class();
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }

        return $pos;
    }

    protected function isVisible(): bool
    {
        $admin = Admin::query()->find($this->vars->get('adminid'));

        $hiddenWidgets = $admin->hidden_widgets;

        if ($hiddenWidgets) {
            if (in_array($this->getId(), explode(',', $hiddenWidgets))) {
                return false;
            }
        }
        return true;
    }
}
