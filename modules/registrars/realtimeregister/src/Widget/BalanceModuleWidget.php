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
        try {
            $credits = App::client()->customers->credits(App::registrarConfig()->customerHandle());
            return $credits->entities;
        } catch (\Exception) {
            return 0;
        }
    }

    public function generateOutput($data): string
    {
        $content = '';

        if (is_numeric($data)) {
            $content .= "<div class='col-12' style='padding: 15px'>Something went wrong fetching your balance</div>";
        } else {
            foreach ($data as $item) {
                $content .= "<div class='col-sm-6'>
                            <div class='item'>
                                <div class='data'>" . number_format($item->balance) . "</div>
                                <div class='note'>" . $item->currency . "</div>
                            </div>
                        </div>";
            }
        }
        return <<<EOF
<div class="panel panel-default widget-billing">
    $content
</div>
EOF;
    }
}
