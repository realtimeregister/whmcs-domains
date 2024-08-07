<?php

namespace RealtimeRegister\Enums;

use SandwaveIo\RealtimeRegister\Domain\DomainDetails;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

enum WhmcsDomainStatus: string
{
    case Fraud = 'Fraud';
    case Redemption = 'Redemption';
    case PendingTransfer = 'Pending Transfer';
    case Expired = 'Expired';
    case Active = 'Active';

    public static function fromDomainDetails(DomainDetails $domain): WhmcsDomainStatus
    {
        if (array_intersect([DomainStatusEnum::STATUS_SERVER_HOLD, DomainStatusEnum::STATUS_REGISTRAR_HOLD], $domain->status)) {
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

        return self::Active;
    }
}
