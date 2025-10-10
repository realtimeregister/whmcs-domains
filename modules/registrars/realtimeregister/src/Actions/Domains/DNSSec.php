<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\KeyDataCollection;

class DNSSec extends Action
{
    use SmartyTrait;

    public function __invoke(Request $request)
    {
        $domainid = $request->params['domainid'];
        $domainName = $request->params['domainname'];
        $message = '';

        $tldInfo = $this->tldInfo($request);
        $metadata = $tldInfo->metadata;
        $keyData = [];
        $saved = false;

        if ($metadata->allowedDnssecRecords) {
            try {
                $domain = $this->domainInfo($request);

                if ($domain->keyData) {
                    $keyData = $domain->keyData;
                }
            } catch (\Exception $e) {
                $message = sprintf("Error while creating or getting the domain: %s", $e->getMessage());
            }

            if (!empty($_POST['dnssecAction']) && $_POST['dnssecAction'] === 'update') {
                $DNSSecBuild = [];
                if ($_POST['flags']) {
                    foreach ($_POST['flags'] as $key => $value) {
                        $DNSSecBuild[] = [
                            'flags' => (int)$value,
                            'protocol' => 3,
                            'algorithm' => (int)$_POST['algorithm'][$key],
                            'publicKey' => $_POST['publicKey'][$key]
                        ];
                    }
                }


                try {
                    App::client()->domains->update(
                        domainName: $domainName,
                        keyData: KeyDataCollection::fromArray($DNSSecBuild)
                    );
                    $saved = true;
                } catch (\Exception $ex) {
                    $keyData = json_decode(json_encode($DNSSecBuild), false);
                    $message = sprintf("Error while updating the DNS Sec: %s", $ex->getMessage());
                }
            }

            return [
                'templatefile' => 'load_template',
                'breadcrumb' => [
                    'clientarea.php?action=domaindetails&id=' . $domainid . '&modop=custom&a=DNSSec'
                    => 'DNSSec Management'
                ],
                'vars' => [
                    'content' => $this->render(__DIR__ . '/../../Assets/Tpl/dns_sec_form.tpl', [
                        'keyData' => $keyData,
                        'error' => $message,
                        'saved' => $saved,
                        'domainName' => $domainName,
                        'domainId' => $domainid,
                    ]),
                ]
            ];
        } else {
            throw new \Exception('DNSSec records are not allowed on this TLD');
        }
    }
}
