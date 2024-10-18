<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\JSRouter;
use RealtimeRegisterDomains\Services\MetadataService;
use RealtimeRegister\Domain\DomainContact;

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
                $metaData = (new MetadataService($tld))->getMetadata();

                if (MetadataService::isRtr($tld) && !in_array('RENEW', $metaData->featuresAvailable)) {
                    if ($renewal['expiryDate']->timestamp > $dateToCheck) {
                        $renewal['eligibleForRenewal'] = false;
                    }
                }
            }
        }

        App::assets()->addScript("rtr.js");
        App::assets()->addScript("rtrClient.js");
        App::assets()->addStyle('style.css');

        if ($vars['action'] === 'domaincontacts') {
            self::initHandleMapping($_GET['domainid'] ?: $vars['domainid']);
        }

        $jsRouter = new JSRouter($vars);
        App::assets()->addToJavascriptVariables('rtr.js', ['rtr' => $jsRouter->getJson()]);
    }

    /**
     * @param int $domainid
     */
    public static function initHandleMapping($domainid): void
    {
        $whmcs_domain = localAPI('GetClientsDomains', ['domainid' => $domainid])['domains']['domain'][0];

        if (MetadataService::isRtr($whmcs_domain)) {
            try {
                $domain = App::client()->domains->get($whmcs_domain['domainname']);
                $contact_handles = ['Registrant' => self::getWhmcsCidFromHandle($domain->registrant)];
                foreach ($domain->contacts as $contact) {
                    /** @var DomainContact $contact */
                    $contact_handles[ucfirst(strtolower($contact->role))]
                        = self::getWhmcsCidFromHandle($contact->handle);
                }
                App::assets()->addScript('rtrHandleMapping.js');
                app::assets()->addToJavascriptVariables(
                    name: 'rtrHandleMapping.js',
                    data: ['contact_ids' => array_filter($contact_handles)]
                );
            } catch (\Exception) {
                // pass
            }
        }
    }

    public static function getWhmcsCidFromHandle(string $handle): ?string
    {
        $map = (new \RealtimeRegisterDomains\Services\ContactService())->fetchMappingByHandle($handle);

        if (!$map) {
            return null;
        }

        return !empty($map->contactid) ? sprintf("c%s", $map->contactid) : sprintf("u%s", $map->userid);
    }
}
