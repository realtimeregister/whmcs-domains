<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\App;

class DomainOverviewModuleWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Realtime Register - Domain statistics';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData()
    {
        try {
            $domainStatistics = App::client()->domains->list(limit: 1);
        } catch (\Exception) {
            return 0;
        }
        return $domainStatistics->pagination->total;
    }

    public function generateOutput($data): string
    {
        /** @var int $data */
        if ($data === 0) {
            $number = 'No';
        } else {
            $number = number_format($data);
        }
        return <<<EOF
<div class="panel panel-default">
    <div class="col-sm-12">
        <div class='item'>
            <div class='data'><h2>$number domains registered</h2></div>
        </div>
    </div>
</div>
EOF;
    }
}
