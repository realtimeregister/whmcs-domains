<?php

declare(strict_types=1);

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use Illuminate\Database\Capsule\Manager as Capsule;

class DtsIntegration extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        if (App::registrarConfig()->get('dts_enable') == 'yes') {
            $invoiceId = $vars['invoiceid'];

            $invoice = Capsule::table('tblinvoices')
                ->where('id', $invoiceId)
                ->first();

            if (!$invoice) {
                return;
            }

            $items = Capsule::table('tblinvoiceitems')
                ->where('invoiceid', $invoiceId)
                ->get();

            $domains = [];

            foreach ($items as $item) {
                if (
                    in_array($item->type, [
                        'DomainTransfer',
                        'DomainRenew',
                        'Domain'
                    ])
                ) {
                    $domain = null;

                    if (!empty($item->relid)) {
                        $domain = Capsule::table('tbldomains')
                            ->where('id', $item->relid)
                            ->value('domain');
                    } elseif (preg_match('/([a-z0-9-]+\.[a-z]{2,})/i', $item->description, $matches)) {
                        $domain = $matches[1];
                    }

                    if ($domain) {
                        $domain = strtolower(trim($domain));
                        $domains[] = $domain;
                    }
                }
            }

            $domains = array_unique($domains);

            if (empty($domains)) {
                return;
            }

            $dtsEmailTemplate = App::registrarConfig()->get('dts_email_template');

            // Send email using WHMCS internal API if one is selected in the admin
            if ($dtsEmailTemplate !== 'none' && $dtsEmailTemplate !== null) {
                $postData = [
                    'messagename' => $dtsEmailTemplate,
                    'id' => $invoiceId,
                    'customvars' => base64_encode(serialize([
                        'domains' => implode(', ', $domains),
                        'invoiceid' => $invoiceId,
                    ])),
                ];

                localAPI('SendEmail', $postData);
            }

            $dtsKeys = explode(',', trim(App::registrarConfig()->get('dts_api_key')));

            if (is_array($dtsKeys)) {
                foreach ($dtsKeys as $accessToken) {
                    $apiUrl = 'https://dm.realtimeregister.com/dts/api/add-domains';

                    // Build payload for Realtime Register DTS api
                    $payload = json_encode([
                        'domains' => array_values($domains)
                    ]);

                    $ch = curl_init($apiUrl);

                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_HTTPHEADER => [
                            'Content-Type: application/json',
                            'Authorization: ' . $accessToken
                        ],
                        CURLOPT_TIMEOUT => 30,
                    ]);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if ($response === false) {
                        logActivity('RealtimeRegister API error: ' . curl_error($ch));
                    } else {
                        $result = json_decode($response, true);

                        if ($httpCode !== 200 || empty($result['success'])) {
                            logActivity('RealtimeRegister API failed: ' . $response);
                        } else {
                            logActivity('RealtimeRegister: domains sent successfully');

                            foreach ($items as $item) {
                                if (
                                    in_array($item->type, [
                                        'DomainTransfer',
                                        'DomainRenew',
                                        'Domain'
                                    ]) && !empty($item->relid)
                                ) {
                                    Capsule::table('tbldomains')
                                        ->where('id', $item->relid)
                                        ->update([
                                            'registrar' => 'realtimeregister'
                                        ]);
                                }
                            }
                        }
                    }
                    curl_close($ch);
                }
            }

            logActivity(
                'Added domain to DTS domains(' . implode(',', array_filter($domains))
                . ') because of invoice ' . $invoiceId,
                $invoice->userid
            );
        }
    }
}
