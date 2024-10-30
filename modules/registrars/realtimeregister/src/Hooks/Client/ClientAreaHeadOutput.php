<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Services\JSRouter;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class ClientAreaHeadOutput extends Hook
{
    public function __invoke(DataObject $vars)
    {
        global $smarty;

        // Allow standard_cart template
        if (!empty($smarty->tpl_vars['renewalsData']->value)) {
            $dateToCheck = strtotime('+1 month', time());
            foreach ($smarty->tpl_vars['renewalsData']->value as &$renewal) {
                $tld = substr($renewal['tld'], 1);
                try {
                    $metaData = (new MetadataService($tld))->getMetadata();
                    if (MetadataService::isRtr($tld) && !in_array('RENEW', $metaData->featuresAvailable)) {
                        if ($renewal['expiryDate']->timestamp > $dateToCheck) {
                            $renewal['eligibleForRenewal'] = false;
                        }
                    }
                } catch (\Exception $e) {
                    LogService::logError($e);
                }
            }
        }

        App::assets()->addScript("rtr.js");
        App::assets()->addScript("rtrClient.js");
        App::assets()->addStyle('style.css');

        $jsRouter = new JSRouter($vars);
        App::assets()->addToJavascriptVariables('rtr.js', ['rtr' => $jsRouter->json]);
    }
}
