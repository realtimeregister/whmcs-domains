<?php

namespace RealtimeRegister\Actions\Domains;

use Exception;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\Enums\WhmcsDomainStatus;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

class Sync extends Action
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);

        $domain = $this->domainInfo($request);

        $expiryDate = $domain->expiryDate;

        if ($metadata->expiryDateOffset) {
            $expiryDate = $expiryDate->add(new \DateInterval('PT' . $metadata->expiryDateOffset . 'S'));
        }

        $values = [];

        if ($domain->autoRenewPeriod < 12 && $domain->autoRenew) {
            $whmcsDomain = Domain::query()->where('domain', $request->domain->name)->firstOrFail();

            if (strtotime($whmcsDomain->expirydate) >= strtotime($whmcsDomain->nextduedate)) {
                return [];
            }

            if (strtotime($expiryDate->format('Y-m-d')) < strtotime($whmcsDomain->nextduedate)) {
                return ['expirydate' => $this->syncDueDate($whmcsDomain->nextduedate)];
            }

            return ['expirydate' => $expiryDate->format('Y-m-d')];
        }

        if ($expiryDate->format('Y-m-d') != '0000-00-00') {
            $values['expirydate'] = $expiryDate->format('Y-m-d');
        }

        $status = WhmcsDomainStatus::fromDomainDetails($domain);

        if (!in_array($status, [WhmcsDomainStatus::Active, WhmcsDomainStatus::Expired, WhmcsDomainStatus::Redemption])
        ) {
            throw new Exception(sprintf("Domain status %s", $status->value));
        }

        $values[$status->value] = true;

        return $values;
    }

    protected function syncDueDate(string $date): string
    {
        $syncDueOffset = (int)$this->config('DomainSyncNextDueDate', 0);

        if (!$syncDueOffset) {
            return $date;
        }

        return date("Y-m-d", strtotime($date . ($syncDueOffset * -1) . ' days'));
    }

    protected function parseDomainStatus(array $statuses): string
    {
        if (array_intersect([DomainStatusEnum::STATUS_SERVER_HOLD, DomainStatusEnum::STATUS_REGISTRAR_HOLD], $statuses)
        ) {
            return 'Fraud';
        }

        if (in_array(DomainStatusEnum::STATUS_REDEMPTION_PERIOD, $statuses)) {
            return 'Redemption';
        }

        if (in_array(DomainStatusEnum::STATUS_PENDING_TRANSFER, $statuses)) {
            return 'Pending Transfer';
        }

        if (in_array(DomainStatusEnum::STATUS_EXPIRED, $statuses)) {
            return 'Expired';
        }

        return 'Active';
    }
}
