<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\App;

class InformationModuleWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Realtime Register';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = true;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData()
    {
//        $credits = App::client()->customers->credits(App::registrarConfig()->customerHandle());
        return [];
    }

    public function generateOutput($data)
    {
        return <<<EOF
<div class="widget-content-padded">
    There should be information here about your domains
</div>
EOF;
    }
}
