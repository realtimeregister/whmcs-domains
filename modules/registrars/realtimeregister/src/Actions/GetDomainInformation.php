<?php

namespace RealtimeRegister\Actions;

use RealtimeRegister\App;
use RealtimeRegister\Request;
use WHMCS\Domain\Registrar\Domain;

class GetDomainInformation extends Action
{
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);
        $domain = $this->domainInfo($request);

        $registrant = $this->contactInfo($domain->registrant);

        $nameservers = [];
        foreach ($domain->ns as $key => $server) {
            $nameservers['ns' . ($key + 1)] = $server;
        }

        $expiryDate = $domain->expiryDate;

        if ($metadata->expiryDateOffset) {
            $expiryDate = $expiryDate->add(new \DateInterval('PT' . $metadata->expiryDateOffset . 'S'));
        }

        return (new Domain())
            ->setDomain($domain->domainName)
            ->setNameservers($nameservers)
            ->setRegistrationStatus($this->getWhmcsDomainStatus($domain))
            ->setTransferLock(in_array('CLIENT_TRANSFER_PROHIBITED', $domain->status))
            ->setExpiryDate(new \WHMCS\Carbon($expiryDate))
            ->setRestorable(in_array('REDEMPTION_PERIOD', $domain->status))
            ->setIdProtectionStatus($domain->privacyProtect)
            ->setIsIrtpEnabled($metadata->registrantChangeApprovalRequired)
            ->setIrtpTransferLock(in_array('IRTPC_TRANSFER_PROHIBITED', $domain->status))
            ->setDomainContactChangePending($this->hasPendingChanges($domain->registrant))
            ->setRegistrantEmailAddress($registrant->email)
            ->setIrtpVerificationTriggerFields(
                ['Registrant' => ['First Name', 'Last Name', 'Organization Name', 'Email']]
            );
    }

    /**
     * @param \SandwaveIo\RealtimeRegister\Domain\DomainDetails $domain
     * @return string
     */
    public function getWhmcsDomainStatus(\SandwaveIo\RealtimeRegister\Domain\DomainDetails $domain): string
    {
        $status = 'STATUS_INACTIVE';

        if (in_array('OK', $domain->status)) {
            $status = 'STATUS_ACTIVE';
        } elseif (in_array('EXPIRED', $domain->status)) {
            $status = 'STATUS_EXPIRED';
        } elseif (in_array('REDEMPTION_PERIOD', $domain->status)) {
            $status = 'STATUS_SUSPENDED';
        } elseif (in_array('PENDING_DELETE', $domain->status)) {
            $status = 'STATUS_PENDING_DELETE';
        }
        return $status;
    }

    /**
     * @param string $handle
     * @return bool
     */
    public function hasPendingChanges(string $handle): bool
    {
        return App::client()->processes->list(
            limit: 1,
            parameters: [
                'type:eq' => 'contact',
                'action:eq' => 'update',
                'identifier' => $handle,
                'status' => 'SUSPENDED',
            ]
        )->count() > 0;
    }
}
