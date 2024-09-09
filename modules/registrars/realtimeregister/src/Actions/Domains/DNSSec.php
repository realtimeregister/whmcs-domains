<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\Language;
use SandwaveIo\RealtimeRegister\Domain\KeyDataCollection;
use Smarty;

class DNSSec extends Action
{
    public function __invoke(Request $request)
    {
        $domainid = $request->params['domainid'];
        $message = '';

        try {
            $domain = $this->domainInfo($request);

            $keyData = [];
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
                    domainName: $domain->domainName,
                    keyData: KeyDataCollection::fromArray($DNSSecBuild)
                );

                header('Location: ' . html_entity_decode($_SERVER['REQUEST_URI']));
                exit;
            } catch (\Exception $ex) {
                $keyData = $DNSSecBuild;
                $message = sprintf("Error while updating the DNS Sec: %s", $ex->getMessage());
            }
        }

        $smarty = new Smarty();

        global $_LANG;
        new Language(); // Load translations

        $smarty->assign([
            'LANG' => $_LANG,
            'keyData' => $keyData,
            'error' => $message,
            'domainName' => $domain->domainName,
            'domainId' => $domainid,
        ]);

        return [
            'templatefile' => 'load_template',
            'breadcrumb' => [
                'clientarea.php?action=domaindetails&id=' . $domainid . '&modop=custom&a=ChildHosts' => 'Child Hosts'
            ],
            'vars' => [
                'content' => $smarty->fetch(__DIR__ . '/../../Assets/Tpl/dns_sec_form.tpl'),
            ]
        ];
    }
}
