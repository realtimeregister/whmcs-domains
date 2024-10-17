<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Actions\Domains\SmartyTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Enums\WhmcsDomainStatus;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class AdminClientDomainsTabFields extends Hook
{
    use SmartyTrait;

    public function __invoke(DataObject $vars): array
    {
        $domainInfo = Domain::find($vars->get('id'));
        $domainName = $domainInfo->domain;

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
            $processes = App::client()->processes->export([
                'fields' => 'createdDate,action,status',
                'order' => '-createdDate',
                'identifier:eq' => $domainName
            ]);
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
            $rtrDomain = App::client()->domains->get($domainName);

            if (!empty($rtrDomain->keyData)) {
                $fields['DNSSec'] = $this->render(
                    __DIR__ . '/../Assets/Tpl/admin/keydata.tpl',
                    ['keyData' => $rtrDomain->keyData->entities]
                );
            }

            $fields['Status'] = $this->render(
                __DIR__ . '/../Assets/Tpl/admin/status.tpl',
                ['status' => $rtrDomain->status]
            );
        } catch (\Exception) {
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
            // ID protection button already visible at registrar commands
            $script = /** @lang JavaScript */
                '$(function(){
                    $("input[name=\'idprotection\']").parent("div").parent("div").parent("label").hide();
                });';
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
}
