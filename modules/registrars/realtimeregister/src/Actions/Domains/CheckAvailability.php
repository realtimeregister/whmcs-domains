<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
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

        $isProxyDomains = $isProxy->checkMany(
            $request->get('searchTerm'),
            array_map(fn(string $tld) => ltrim($tld, '.'), $request->get('tldsToInclude'))
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

            $results->append($searchResult);
        }
        return $results;
    }
}
