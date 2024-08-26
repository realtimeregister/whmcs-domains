<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\App;

class BalanceModuleWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Realtime Register - Balance';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData()
    {
        $credits = App::client()->customers->credits(App::registrarConfig()->customerHandle());
        return $credits->entities;
    }

    public function generateOutput($data): string
    {
        $content = '';
        foreach ($data as $item) {
            $content .= "<div class='col-sm-6'>
                            <div class='item'>
                                <div class='data'>" . number_format($item->balance) . "</div>
                                <div class='note'>" . $item->currency . "</div>
                            </div>
                        </div>";
        }
        return <<<EOF
<div class="panel panel-default widget-billing">
    $content
</div>
EOF;
    }
}
