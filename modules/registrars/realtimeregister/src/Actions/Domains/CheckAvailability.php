<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\IsProxyDomain;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\Config\Config;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
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

        $tldsToInclude = $request->params['tldsToInclude'];
        $tldPricing = App::localApi()->getTldPricing();
        $tlds = [];
        $tldPricing = array_keys($tldPricing);
        $results = new ResultsList();

        foreach ($tldsToInclude as $key => $tld) {
            if (!in_array(ltrim($tld, '.'), $tldPricing)) {
                $searchResult = new SearchResult($request->get('searchTerm'), $tld);
                $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
                $results->append($searchResult);
                continue;
            }
            if (Config::get('tldinfomapping.' . $tld) === 'centralnic') {
                $tlds[$key] = $tld . '.centralnic';
            } else {
                $tlds[$key] = $tld;
            }
        }

        $isProxyDomains = $isProxy->checkMany(
            $request->get('searchTerm'),
            array_map(fn(string $tld) => ltrim($tld, '.'), $tlds)
        );

        foreach ($isProxyDomains as $result) {
            $tld = trim(strstr($result->getDomain(), '.'), '.');

            $searchResult = new SearchResult($request->get('searchTerm'), $tld);

            $searchResult->setStatus(
                match ($result->getStatus()) {
                    IsProxyDomain::STATUS_AVAILABLE => SearchResult::STATUS_NOT_REGISTERED,
                    IsProxyDomain::STATUS_NOT_AVAILABLE => SearchResult::STATUS_REGISTERED,
                    default => SearchResult::STATUS_UNKNOWN
                }
            );

            $extra = $result->getExtras();

            if (isset($extra['type'], $extra['price'], $extra['currency']) && $extra['type'] === 'premium') {
                $searchResult->setPremiumDomain(true);
                $metadata = (new MetadataService($tld))->getMetadata();
                $premiumCostPricing = [
                    'register' => number_format(($extra['price'] / 100), 2, '.', ''),
                    'CurrencyCode' => $extra['currency']
                ];
                if ($metadata->premiumSupport === 'REGULAR') {
                    $premiumCostPricing['renew'] =
                        number_format(($extra['price'] / 100), 2, '.', '');
                }
                $searchResult->setPremiumCostPricing($premiumCostPricing);
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

    public static function handleException(\Throwable $exception, array $params): ResultsList
    {
        $resultList =  new ResultsList();

        $query = $_REQUEST['domain'];
        $searchTerm = $params['searchTerm'];

        if ($query !== $searchTerm) {
            $searchResult = new SearchResult($searchTerm, "." . MetadataService::getTld($query));
        } else {
            $searchResult = new SearchResult($searchTerm, $params['tldsToInclude'][0]);
        }

        $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
        $resultList->append($searchResult);

        LogService::logError($exception, "Error while checking domain " . $query);

        return $resultList;
    }
}
