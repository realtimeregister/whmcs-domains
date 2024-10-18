<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Exception;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Enums\WhmcsDomainStatus;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;
use Illuminate\Database\Capsule\Manager as Capsule;

class Sync extends Action
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);

        try {
            $domain = $this->domainInfo($request);

            $expiryDate = $domain->expiryDate;

            if ($metadata->expiryDateOffset) {
                $expiryDate = $expiryDate->add(new \DateInterval('PT' . $metadata->expiryDateOffset . 'S'));
            }

            $values = [];
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            throw new DomainNotFoundException($exception);
        }

        $whmcsDomain = Domain::query()->where('domain', $request->domain->domainName())->firstOrFail();

        if ($domain->autoRenewPeriod < 12 && $domain->autoRenew) {
            if (strtotime($whmcsDomain->expirydate) >= strtotime($whmcsDomain->nextduedate)) {
                return [];
            }

            try {
                if (function_exists('realtimeregister_before_Sync')) {
                    $values = realtimeregister_before_Sync($values, $request->params, $metadata, $domain);
                }
            } catch (\Exception $ex) {
                return [
                    'error' => sprintf(
                        'Error while trying to execute the realtimeregister_before_Sync hook: %s.',
                        $ex->getMessage()
                    )
                ];
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

        if (
            !in_array(
                $status,
                [
                    WhmcsDomainStatus::Active,
                    WhmcsDomainStatus::Expired,
                    WhmcsDomainStatus::Redemption,
                    WhmcsDomainStatus::Pending
                ]
            )
        ) {
            throw new Exception(sprintf("Domain status %s", $status->value));
        }

        if ($status->value === WhmcsDomainStatus::Pending->value) {
            $values['active'] = false;
            $values['cancelled'] = false;
            $values['transferredAway'] = false;

            Capsule::table('tbldomains')->where('id', $whmcsDomain->id)->update(['status' => 'Pending']);
        } else {
            $values[strtolower($status->value)] = true;
        }

        try {
            if (function_exists('realtimeregister_after_Sync')) {
                return realtimeregister_after_Sync($request->params, $values);
            }
        } catch (\Exception $ex) {
            return [
                'error' => sprintf(
                    'Error while trying to execute the realtimeregister_after_Sync hook: %s.',
                    $ex->getMessage()
                )
            ];
        }

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
        if (
            array_intersect([DomainStatusEnum::STATUS_SERVER_HOLD, DomainStatusEnum::STATUS_REGISTRAR_HOLD], $statuses)
        ) {
            return 'Fraud';
        }

        if (in_array(DomainStatusEnum::STATUS_REDEMPTION_PERIOD, $statuses)) {
            return 'Redemption';
        }

        if (in_array(DomainStatusEnum::STATUS_PENDING_TRANSFER, $statuses)) {
            return 'Pending Transfer';
        }

        if (
            array_intersect(
                [DomainStatusEnum::STATUS_PENDING_VALIDATION, DomainStatusEnum::STATUS_PENDING_UPDATE],
                $statuses
            )
        ) {
            return 'Pending';
        }

        if (in_array(DomainStatusEnum::STATUS_EXPIRED, $statuses)) {
            return 'Expired';
        }

        return 'Active';
    }
}
