<?php

namespace RealtimeRegisterDomains\Enums;

use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegisterDomainsRealtimeRegister\Domain\Enum\DomainStatusEnum;

enum WhmcsDomainStatus: string
{
    case Fraud = 'Fraud';
    case Redemption = 'Redemption';
    case PendingTransfer = 'Pending Transfer';
    case Expired = 'Expired';
    case Active = 'Active';
    case Pending = 'Pending';
    case Inactive = 'Inactive';

    public static function fromDomainDetails(DomainDetails $domain): WhmcsDomainStatus
    {
        if (
            array_intersect(
                [DomainStatusEnum::STATUS_SERVER_HOLD, DomainStatusEnum::STATUS_REGISTRAR_HOLD],
                $domain->status
            )
        ) {
            return self::Fraud;
        }

        if (in_array(DomainStatusEnum::STATUS_REDEMPTION_PERIOD, $domain->status)) {
            return self::Redemption;
        }

        if (in_array(DomainStatusEnum::STATUS_PENDING_TRANSFER, $domain->status)) {
            return self::PendingTransfer;
        }

        if (in_array(DomainStatusEnum::STATUS_EXPIRED, $domain->status)) {
            return self::Expired;
        }

        if (in_array(DomainStatusEnum::STATUS_PENDING_VALIDATION, $domain->status)) {
            return self::Pending;
        }

        if (in_array(DomainStatusEnum::STATUS_INACTIVE, $domain->status)) {
            return self::Inactive;
        }

        return self::Active;
    }
}
