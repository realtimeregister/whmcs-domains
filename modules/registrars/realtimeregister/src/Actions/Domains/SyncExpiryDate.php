<?php

namespace RealtimeRegister\Actions\Domains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;

class SyncExpiryDate extends Action
{
    public function __invoke(Request $request)
    {
        try {
            $metadata = new MetadataService($request->params['tld']);
        } catch (\Exception $ex) {
            return sprintf('Error while trying connect to server: %s.', $ex->getMessage());
        }

        // Sync expire date from realtimeregister
        try {
            $offset = $metadata->get("expiryDateOffset");
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
