<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Exception;
use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Enums\WhmcsDomainStatus;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;

class Sync extends Action
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);
        $persist = $request->params['persist'];
        $values = [];

        try {
            $domain = $this->domainInfo($request);

            $expiryDate = $domain->expiryDate;

            if ($metadata->expiryDateOffset) {
                $expiryDate = $expiryDate->sub(new \DateInterval('PT' . $metadata->expiryDateOffset . 'S'));
            }
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            $whmcsDomain = Domain::query()->where('domain', $request->domain->unicodeDomain())->firstOrFail();
            if (self::checkForOutgoingTransfer($request)) {
                if ($persist) {
                    self::persist($request, $whmcsDomain->id, "Transferred Away");
                }
                return ["transferredAway" => true];
            }
            throw new DomainNotFoundException($exception);
        }

        $whmcsDomain = Domain::query()->where('domain', $request->domain->unicodeDomain())->firstOrFail();

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

        if ($domain->autoRenewPeriod < 12 && $domain->autoRenew) {
            if (strtotime($domain->expiryDate) >= strtotime($whmcsDomain->nextduedate)) {
                return [];
            }
        }

        if (strtotime($expiryDate->format('Y-m-d')) < strtotime($whmcsDomain->nextduedate)) {
            $values['expirydate'] = $this->syncDueDate($expiryDate->format('Y-m-d'));
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
        } else {
            $values[strtolower($status->value)] = true;
        }

        if ($persist) {
            self::persist(
                $request,
                $whmcsDomain->id,
                $status->value,
                $expiryDate,
                $this->syncDueDate($domain->expiryDate->format('Y-m-d'))
            );
        }

        try {
            if (function_exists('realtimeregister_after_Sync')) {
                return realtimeregister_after_Sync($request->params, $values);
            }
        } catch (\Exception $ex) {
            LogService::logError($ex);
            return [
                'error' => sprintf(
                    'Error while trying to execute the realtimeregister_after_Sync hook: %s.',
                    $ex->getMessage()
                )
            ];
        }

        return $values;
    }

    private static function checkForOutgoingTransfer(Request $request): bool
    {
        $domain = $request->domain->domainName();
        return App::client()->processes->list(parameters:
            ["identifier:eq" => $domain,
                "status" => "COMPLETED",
                "action:in" => "outgoingTransfer,outgoingInternalTransfer"
            ])->count() > 0;
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

    private static function persist(
        Request $request,
        int $domainId,
        string $status,
        ?\DateTime $newExpiry = null,
        string $nextDueDate = null
    ): void {
        $values = [
            'status' => $status,
        ];
        if ($newExpiry) {
            $values['expirydate'] = $newExpiry->format('Y-m-d');
        }
        if ($nextDueDate) {
            $values['nextduedate'] = $nextDueDate;
        }

        Domain::query()->where('id', $domainId)->update($values);
        $url = 'clientsdomains.php?userid=' . $request->params['userid']
            . '&id='
            . $request->params['domainid']
            . '&conf=success';

        // Refresh WHMCS because else you wont see the new status
        header("refresh: 0; url = " . $url);
    }
}
