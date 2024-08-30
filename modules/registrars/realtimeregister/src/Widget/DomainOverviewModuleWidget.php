<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\App;
use SandwaveIo\RealtimeRegister\Domain\Pagination;

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
        $domainStatistics = App::client()->domains->list(limit:1);
        return $domainStatistics->pagination;
    }

    public function generateOutput($data): string
    {
        /**
 * @var Pagination $data
*/

        $number = number_format($data->total);
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
