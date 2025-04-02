<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Actions\Domains\SmartyTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Enums\WhmcsDomainStatus;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Services\Config\Config;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class AdminClientDomainsTabFields extends Hook
{
    use SmartyTrait;

    public function __invoke(DataObject $vars): array
    {
        $domainInfo = Domain::find($vars->get('id'));
        $tld = MetadataService::getTld($domainInfo->domain);
        $domainName = $domainInfo->domain . Config::getPseudoTld($tld);

        $fields = [];
        try {
            $result = App::client()->domains->transferInfo($domainName);
            if (!empty($result->log)) {
                $fields['Transfer Logs'] = $this->render(__DIR__ . '/../Assets/Tpl/admin/transfer_log.tpl', [
                    'type' => $result->type,
                    'logs' => array_reverse($result->log->entities)
                ]);
            }
        } catch (\Exception) {
            # ignore
        }

        try {
            $processes = array_map(
                fn($process) => [...$process, 'link' => App::portalUrl() . '/app/process/' . $process['id']],
                App::client()->processes->export([
                    'fields' => 'createdDate,action,status,id',
                    'order' => '-createdDate',
                    'identifier:eq' => $domainName
                ])
            );

            if (!empty($processes)) {
                $fields['Processes'] = $this->render(__DIR__ . '/../Assets/Tpl/admin/processes_log.tpl', [
                    'processes' => $processes,
                ]);
            }
        } catch (\Exception) {
            # ignore
        }

        $rtrDomain = null;
        try {
            $rtrDomain = App::client()->domains->get(App::toPunyCode($domainName));

            if (!empty($rtrDomain->keyData)) {
                $fields['DNSSec'] = $this->render(
                    __DIR__ . '/../Assets/Tpl/admin/keydata.tpl',
                    ['keyData' => $rtrDomain->keyData->entities]
                );
            }

            $fields['Status'] = $this->render(
                __DIR__ . '/../Assets/Tpl/admin/status.tpl',
                ['status' => array_map(fn($status) => self::getStatusDescription($status), $rtrDomain->status)]
            );
        } catch (\Exception $e) {
            # ignore
        }

        if (!empty($fields)) {
            $fields = array_merge(['' => '<h1>Information from Realtime Register:</h1>'], $fields);
        }

        $metaData = null;
        try {
            $metaData = (new MetadataService($domainInfo->domain))->getMetadata();
        } catch (\Exception $e) {
            LogService::logError($e);
        }

        // Some special features, which can only be done by using javascript
        if (
            $domainInfo->registrar === 'realtimeregister'
        ) {
            $hasTransferLock = in_array(
                DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED,
                $rtrDomain?->status ?? []
            );

            // ID protection button already visible at registrar commands
            $script =
                '$(function(){
                    $("input[name=\'idprotection\']").parent("div").parent("div").parent("label").hide();';

            if ($_SESSION['currentError']) {
                $currentError = $_SESSION['currentError'];
                $script .= '$(".successbox").removeClass("successbox").addClass("errorbox").html("' . htmlentities($currentError) . '");';
                session_start();
                unset($_SESSION['currentError']);
                session_write_close();
            }

            if ($rtrDomain) {
                $script .= 'if($("form").find("tr:contains(\'Language Code\')").length > 1) {
                                $("select[name=\'domainfield[0]\'] option:not([selected=\'selected\'])").remove();
                            }';
            }

            $script .=
                     '$("input[name=\'lockstatus\']").prop("checked", ' . ($hasTransferLock ? 'true' : 'false') . ');
                     $("input[name=\'oldlockstatus\']").val(' . ($hasTransferLock ? '"locked"' : '"unlocked"') . ')' .
                    '});';

            if ($metaData && $domainInfo->status === 'Active' && $metaData->expiryDateOffset > 0 && $rtrDomain) {
                $script = /** @lang JavaScript */
                    '
                let newElm = document.createElement("i");
                newElm.classList.add("fas","fa-info-circle");
                newElm.style.marginLeft = "0.5em";
                newElm.id = "expiryOffsetInformation";
                newElm.setAttribute("data-toggle", "tooltip");
                newElm.title = "Expiry offset is ' . number_format($metaData->expiryDateOffset)
                    . ' seconds, which translates to '
                    . \Carbon\CarbonInterval::seconds($metaData->expiryDateOffset)->cascade()->forHumans()
                    . '. The renewal/delete window is '
                    . \Carbon\Carbon::parse(
                        ($rtrDomain->expiryDate->getTimestamp() - $metaData->expiryDateOffset)
                    )->toDateTimeString()
                    . ', the registry expirydate is '
                    . \Carbon\Carbon::parse($rtrDomain->expiryDate->getTimestamp())->toDateTimeString() . '";
                let elm = document.getElementById("inputExpiryDate");
                elm.style.display = "inherit";
                elm.after(newElm);
                
                $(function () {
                    $("#expiryOffsetInformation").tooltip();
                });';
            } elseif (
                /*
                 * See if we need the IDProtect button at all, there is no other way to hide this button via WHMCS
                 */
                in_array(
                    $domainInfo->status,
                    [
                        WhmcsDomainStatus::Expired->value,
                        WhmcsDomainStatus::PendingTransfer->value,
                        WhmcsDomainStatus::Pending->value
                    ]
                )
            ) {
                $script .= /** @lang JavaScript */
                    'let elm = document.querySelector(\'[data-target="#modalIdProtectToggle"]\');
                elm.style.display = "none";';
            }

            if ($script) {
                $fields[''] = $fields[''] . '<script>' . $script . '</script>';
            }
        }
        return $fields;
    }

    private static function getStatusDescription(string $status): string
    {
        if ($status === 'PENDING_VALIDATION') {
            return "PENDING VALIDATION: Domain registrant needs to verify contact details through email. 
            To resend the contact validation email to the registrant email address, 
            click the registrar command 'resend validation emails' button above.";
        }
        return $status;
    }
}
