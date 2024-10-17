<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\DnsHostAddressCollection;

class ChildHosts extends Action
{
    use SmartyTrait;

    public function __invoke(Request $request)
    {
        $domainid = $request->params['domainid'];
        $domainname = $request->params['domainname'];
        $message = '';
        try {
            $domainInfo = $this->domainInfo($request);
            $domain = $domainInfo->domainName;
        } catch (\Exception $e) {
            $message = sprintf("Error while creating or deleting the host: %s", $e->getMessage());
        }

        if (!empty($domainInfo)) {
            try {
                if (isset($_POST['hostAction']) && isset($_POST['hostName']) && is_string($_POST['hostName'])) {
                    if ($_POST['hostAction'] == "delete") {
                        if (!in_array($_POST['hostName'], $domainInfo->childHosts)) {
                            throw new \Exception(
                                sprintf("Host '%s' is not a subordinate host of '%s'", $_POST['hostName'], $domainname)
                            );
                        }
                        App::client()->hosts->delete($_POST['hostName']);
                        exit;
                    }

                    if ($_POST['hostAction'] == "create") {
                        $hostName = $_POST['hostName'] . '.' . $domainname;

                        if (!$this->isSubordinateHost($hostName, $domainname)) {
                            throw new \Exception(
                                sprintf("Host '%s' is not a subordinate host of '%s'", $hostName, $domainname)
                            );
                        }

                        if (
                            isset($_POST['ipAddress']) && is_string($_POST['ipAddress'])
                            && isset($_POST['ipVersion']) && in_array($_POST['ipVersion'], ["V4", "V6"])
                        ) {
                            App::client()->hosts->create(
                                $hostName,
                                DnsHostAddressCollection::fromArray(
                                    [
                                        [
                                            'ipVersion' => $_POST['ipVersion'],
                                            'address' => $_POST['ipAddress'],
                                        ],
                                    ]
                                )
                            );
                            $domainInfo['childHosts'][] = $hostName;
                        } else {
                            throw new \Exception("Invalid IP address information provided.");
                        }
                    }
                }
            } catch (\Exception $ex) {
                $message = sprintf("Error while creating or deleting the host: %s", $ex->getMessage());
            }

            try {
                if (isset($_POST['hostAction']) && $_POST['hostAction'] == "update") {
                    if (
                        !isset($_POST['host']) || !is_array($_POST['host']) || !isset($_POST['ipVersion'])
                        || !is_array($_POST['ipVersion'])
                    ) {
                        throw new \Exception("Invalid host information provided.");
                    }

                    $addresses = [];
                    foreach ($_POST['host'] as $key => $address) {
                        if (
                            !isset($_POST['ipVersion'][$key])
                            || !in_array($_POST['ipVersion'][$key], haystack: ["V4", "V6"])
                        ) {
                            throw new \Exception("Invalid host information provided.");
                        }

                        if ($address) {
                            $addresses[] = ["address" => $address, "ipVersion" => $_POST['ipVersion'][$key]];
                        }
                    }
                    App::client()->hosts->update($_POST['hostName'], DnsHostAddressCollection::fromArray($addresses));
                    header('Location: ' . html_entity_decode($_SERVER['REQUEST_URI']));
                    exit;
                }
            } catch (\Exception $ex) {
                $message = sprintf("Error while updating the host: %s", $ex->getMessage());
            }

            $hosts = [];
            foreach ($domainInfo->childHosts as $host) {
                if (!empty($host)) {
                    $hosts[] = App::client()->hosts->get($host);
                }
            }
        }

        return [
            'templatefile' => 'load_template',
            'breadcrumb' => [
                'clientarea.php?action=domaindetails&id=' . $domainid . '&modop=custom&a=ChildHosts' => 'Child Hosts'
            ],
            'vars' => [
                'content' => $this->render(__DIR__ . '/../../Assets/Tpl/child_hosts_form.tpl', [
                    'hosts' => $hosts,
                    'error' => $message,
                    'domainName' => $domain,
                    'domainId' => $domainid,
                ]),
            ],
        ];
    }

    private function isSubordinateHost($hostname, $domainname): bool
    {
        $name = substr($hostname, -strlen($domainname) - 1);
        return ($name === $domainname) || ($name === ".$domainname");
    }
}
