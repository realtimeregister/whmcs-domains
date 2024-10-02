<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Whmcs\DomainPricing;
use RealtimeRegister\Request;

class RenewDomain extends Action
{
    use DomainTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
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

        /** @var DomainPricing $pricing */
        $price = DomainPricing::query()->where(['extension' => '.' . $domain->tldPunyCode])->first();

        if ($domain->isInRedemptionGracePeriod === true && (int)$price->redemption_grace_period_fee > -1) {
            $renewal = App::client()->domains->restore(
                domain: $domain->domainName(),
                reason: 'Renewal requested of this domain by WHMCS user'
            );
        } else {
            $renewal = App::client()->domains->renew(
                domain: $domain->domainName(),
                period: $period,
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
    }
}
