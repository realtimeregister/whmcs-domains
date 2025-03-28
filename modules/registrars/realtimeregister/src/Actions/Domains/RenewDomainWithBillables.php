<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\DomainPricing;
use RealtimeRegisterDomains\Request;

class RenewDomainWithBillables extends Action
{
    use DomainTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): array|string
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;

        try {
            if (function_exists('realtimeregister_before_RenewDomain')) {
                realtimeregister_before_RenewDomain($request->params);
            }
        } catch (\Exception $ex) {
            return [
                'error' =>
                    sprintf(
                        'Error while trying to execute the realtimeregister_before_RenewDomain hook: %s.',
                        $ex->getMessage()
                    )
            ];
        }

        $period = $request->get('regperiod') * 12;
        if (!in_array($period, $metadata->createDomainPeriods)) {
            throw new \Exception(
                sprintf('It is not possible to register/transfer .%s domains for that period.', $domain->tld)
            );
        }

        $domainName = self::getDomainName($domain);

        /** @var DomainPricing $pricing */
        $price = DomainPricing::query()->where(['extension' => '.' . $domain->tldPunyCode])->first();

        if ($domain->isInRedemptionGracePeriod === true && (int)$price->redemption_grace_period_fee > -1) {
            $billables = $this->buildBillables(App::client()->domains->restore(
                domain: $domainName,
                reason: 'Renewal requested of this domain by WHMCS user',
                isQuote: true
            ));

            $renewal = App::client()->domains->restore(
                domain: $domainName,
                reason: 'Renewal requested of this domain by WHMCS user',
                billables: BillableCollection::fromArray($billables)
            );
        } else {
            $billables = $this->buildBillables(App::client()->domains->renew(
                domain: $domainName,
                period: $period,
                isQuote: true
            ));

            $renewal = App::client()->domains->renew(
                domain: $domainName,
                period: $period,
                billables: BillableCollection::fromArray($billables)
            );
        }

        try {
            if (function_exists('realtimeregister_after_RenewDomain')) {
                realtimeregister_after_RenewDomain($request->params, $renewal);
            }
        } catch (\Exception $ex) {
            return [
                'error' => sprintf(
                    'Error while trying to execute the realtimeregister_after_RenewDomain hook: %s.',
                    $ex->getMessage()
                )
            ];
        }

        return ['success' => true];
    }
}
