<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class RenewDomain extends Action
{
    use DomainTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): array
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

        /*
         * Because we can't be sure the correct price is imported (because it can be manually changed, or isn't
         * imported yet, the following call will throw an exception, if there is a price for the restore. This should
         * be handled by the admin.
         */
        if ($domain->isInRedemptionGracePeriod === true) {
            $renewal = App::client()->domains->restore(
                domain: $domain->domainName(),
                reason: 'Restore requested of this domain by WHMCS user'
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

        return ['success' => true,  'message' => 'The domain has been renewed'];
    }
}
