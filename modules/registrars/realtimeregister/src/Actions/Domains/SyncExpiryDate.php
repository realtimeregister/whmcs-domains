<?php

namespace RealtimeRegister\Actions\Domains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;

class SyncExpiryDate extends Action
{
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);

        // Sync expire date from realtimeregister
        try {
            $offset = $metadata->expiryDateOffset;
            $domainInformation = App::client()->domains->get(domainName: $request->domain->domainName());
            $fields['expirydate'] = date("Y-m-d", $domainInformation->expiryDate->getTimestamp() - ((int)$offset));

            Capsule::table("tbldomains")->where(
                'id',
                $request->params['id'] ?: $request->params['domainid']
            )->update($fields);
        } catch (\Exception $e) {
            logActivity(
                sprintf("ERROR (Realtime Register): Failed to sync ExpiryDate: %s.", $e->getMessage())
            );
        }

        $url = 'clientsdomains.php?userid=' . $request->params['userid'] . '&id=' . $request->params['domainid'];

        // Refresh WHMCS because else you wont see the new expiry date
        header("refresh: 0; url = " . $url);
    }
}
