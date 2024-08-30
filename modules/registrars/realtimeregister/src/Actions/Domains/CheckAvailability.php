<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\Config\Config;
use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\IsProxyDomain;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;

class CheckAvailability extends Action
{
    public function __invoke(Request $request)
    {
        $isProxy = App::isProxy();

        if ($request->params['premiumEnabled']) {
            $isProxy->enable('premium');
        }

        $tlds = [];
        $originalQuery = $_REQUEST['domain'];

        if ($originalQuery != $request->params['searchTerm']) {
            $tld = MetadataService::getTld($originalQuery);
            if (Config::get('tldinfomapping.' . $tld) === 'centralnic') {
                $tlds[] = $tld;
            }
        } else {
            // Add centralnic tld, when needed
            $tldPricing = App::localApi()->getTldPricing();
            $tlds = array_keys($tldPricing);
            foreach ($tlds as $key => $tld) {
                if (Config::get('tldinfomapping.' . $tld) === 'centralnic') {
                    $tlds[$key] = $tld . '.centralnic';
                }
            }
        }
        $isProxyDomains = $isProxy->checkMany(
            $request->get('searchTerm'),
            array_map(fn(string $tld) => ltrim($tld, '.'), $tlds)
        );
        $results = new ResultsList();

        foreach ($isProxyDomains as $result) {
            $tld = trim(strstr($result->getDomain(), '.'), '.');

            $searchResult = new SearchResult($request->get('searchTerm'), $tld);

            $searchResult->setStatus(
                match ($result->getStatus()) {
                    IsProxyDomain::STATUS_AVAILABLE => SearchResult::STATUS_NOT_REGISTERED,
                    IsProxyDomain::STATUS_NOT_AVAILABLE => SearchResult::STATUS_REGISTERED,
                    default => SearchResult::STATUS_TLD_NOT_SUPPORTED
                }
            );

            $extra = $result->getExtras();

            if (isset($extra['type'], $extra['price'], $extra['currency']) && $extra['type'] === 'premium') {
                $searchResult->setPremiumDomain(true);
                $searchResult->setPremiumCostPricing(
                    [
                        'register' => number_format(($extra['price'] / 100), 2, '.', ''),
                        'renew' => number_format(($extra['price'] / 100), 2, '.', ''),
                        'CurrencyCode' => $extra['currency']
                    ]
                );
            }
            // clean centralnic from tlds
            if (str_ends_with($searchResult->getTopLevel(), '.centralnic')) {
                $searchResult->setTopLevel(str_replace('.centralnic', '', $searchResult->getTopLevel()));
                $searchResult->setPunycodeTopLevel(
                    str_replace('.centralnic', '', $searchResult->getPunycodeTopLevel())
                );
            }
            $results->append($searchResult);
        }
        return $results;
    }
}
