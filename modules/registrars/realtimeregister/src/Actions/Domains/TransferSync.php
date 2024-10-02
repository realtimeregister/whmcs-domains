<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;

class TransferSync extends Action
{
    use DomainTrait;

    // Transfer error statuses
    private array $transferErrorStatuses = [
        'cancelled',
        'rejected',
        'failed',
    ];

    // Transfer status => message.
    private array $transferStatusMessages = [
        'pendingwhois' => 'Transfer is pending for whois information',
        'pendingfoa' => 'Transfer is pending approval from autorized contact',
        'pending' => 'Transfer has is pending at the registry',
        'approved' => 'Transfer has been approved by autorized contact',
        'cancelled' => 'Transfer has been cancelled by customer',
        'rejected' => 'Transfer has been rejected by registry or registrant',
        'failed' => 'Transfer has failed',
        'completed' => 'Transfer is completed',
    ];

    public function __invoke(Request $request): array
    {
        $values = [];

        try {
            $metadata = $this->metadata($request);
        } catch (\Exception $ex) {
            return [
                'error' =>
                    sprintf(
                        'trying connect to server: %s.',
                        $ex->getMessage()
                    )
            ];
        }

        // Lets check transfer information.
        try {
            $transferInfo = App::client()->domains->transferInfo(
                $this->checkForPunyCode($request->domain)
            );
            $message = $this->transferStatusMessages[$transferInfo->status];
            // Lets process COMPLETED transfer status.
            if ($transferInfo->status == 'completed') {
                $values['completed'] = true;
                $offset = $metadata->expiryDateOffset;
                $expirydate = date("Y-m-d", $transferInfo->expiryDate->getTimestamp() - ((int)$offset));
                $values['expirydate'] = $expirydate;
            } elseif (in_array($transferInfo->status, $this->transferErrorStatuses)) {
                // Lets allow domain transfer failure and provide the message.
                $values['failed'] = true;
                $values['reason'] = $message;
            } else {
                // This should be one of the pending domain transfer statuses.
                return ['error' => $message];
            }
        } catch (\Exception $ex) {
            return ['error' => sprintf('Error retrieving information about domain transfer: %s', $ex->getMessage())];
        }

        return $values;
    }
}
