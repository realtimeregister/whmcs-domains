<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\Config\Config;
use RealtimeRegister\Services\LogService;
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
        $tldPricing = App::localApi()->getTldPricing();

        if ($originalQuery != $request->params['searchTerm']) {
            $tld = MetadataService::getTld($originalQuery);

            if (!in_array($tld, array_keys($tldPricing))) {
                $results = new ResultsList();
                $searchResult = new SearchResult($request->get('searchTerm'), $tld);

                $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
                $results->append($searchResult);
                return $results;
            }

            if (Config::get('tldinfomapping.' . $tld) === 'centralnic') {
                $tlds[] = $tld . '.centralnic';
            } else {
                $tlds[] = $tld;
            }
        } else {
            // Add centralnic tld, when needed
            $tlds = array_keys($tldPricing);
            foreach ($tlds as $key => $tld) {
                if (Config::get('tldinfomapping.' . $tld) === 'centralnic') {
                    $tlds[$key] = $tld . '.centralnic';
                } else {
                    $tlds[$key] = $tld;
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
                    default => SearchResult::STATUS_UNKNOWN
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

    public static function handleException(\Throwable $exception, array $params): ResultsList
    {
        $resultList =  new ResultsList();

        $query = $_REQUEST['domain'];
        $searchTerm = $params['searchTerm'];

        if ($query !== $searchTerm) {
            $searchResult = new SearchResult($searchTerm, "." . MetadataService::getTld($query));
        } else {
            $searchResult = new SearchResult($searchTerm, array_keys(App::localApi()->getTldPricing())[0]);
        }

        $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
        $resultList->append($searchResult);

        LogService::logError($exception, "Error while checking domain " . $query);

        return $resultList;
    }
}
