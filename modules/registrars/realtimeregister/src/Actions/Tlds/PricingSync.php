<?php

namespace RealtimeRegister\Actions\Tlds;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\Price;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domain\TopLevel\ImportItem;

class PricingSync extends Action
{
    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $results = new ResultsList();

        $prices = [];
        $pricesSLD = [];

        if (App::registrarConfig()->customerHandle() !== '') {
            /** @var Price $priceItem */
            foreach (App::client()->customers->priceList(App::registrarConfig()->customerHandle()) as $priceItem) {
                if (!str_starts_with($priceItem->product, "domain_")) {
                    continue;
                }
                $explodeTld = explode('_', $priceItem->product);

                if (str_contains($priceItem->product, '_sld')) {
                    $pricesSLD[$explodeTld[1]][$priceItem->action] = [
                        'currency' => $priceItem->currency,
                        'price'    => $priceItem->price
                    ];

                    continue;
                }

                if (count($explodeTld) > 2 || count($explodeTld) < 2) {
                    continue;
                }

                $prices[$explodeTld[1]][$priceItem->action] = [
                    'currency' => $priceItem->currency,
                    'price'    => $priceItem->price
                ];
            }

            // Loop through the sld pricings and add every applicable tld that is not the main tld
            foreach ($pricesSLD as $tld => $priceInfo) {
                try {
                    $metadata = new MetadataService($tld, App::client());
                } catch (\Exception) {
                    continue;
                }

                foreach ($metadata->getApplicableFor() as $applicableTld) {
                    if (strpos($applicableTld, '.' . $tld) === false) {
                        continue;
                    }

                    $prices[$applicableTld] = $priceInfo;
                }
            }

            foreach ($prices as $tld => $priceInfo) {
                $item = new ImportItem();
                try {
                    $metadata = new MetadataService($tld, App::client());
                } catch (\Exception $e) {
                    continue;
                }

                $item->setExtension($tld);
                $item->setEppRequired($metadata->get('transferRequiresAuthcode'));
                if ($priceInfo['CREATE']) {
                    $item->setRegisterPrice(number_format($priceInfo['CREATE']['price'] / 100, 2, '.', ''));
                    $item->setCurrency($priceInfo['CREATE']['currency']);
                }

                if ($priceInfo['RENEW']) {
                    $item->setRenewPrice(number_format($priceInfo['RENEW']['price'] / 100, 2, '.', ''));
                }

                if ($priceInfo['TRANSFER']) {
                    $item->setTransferPrice(number_format($priceInfo['TRANSFER']['price'] / 100, 2, '.', ''));
                }

                if ($priceInfo['RESTORE']) {
                    if ($metadata->get('redemptionPeriod')) {
                        $item->setRedemptionFeePrice(number_format($priceInfo['RESTORE']['price'] / 100, 2, '.', ''));
                        $item->setRedemptionFeeDays($metadata->get('redemptionPeriod'));
                    }
                    if ($metadata->get('autoRenewGracePeriod')) {
                        $item->setGraceFeePrice(number_format($priceInfo['RENEW']['price'] / 100, 2, '.', ''));
                        $item->setGraceFeeDays($metadata->get('autoRenewGracePeriod'));
                    }
                }
                $results[] = $item;
            }
            return $results;
        } else {
            throw new \Exception("No Customer set in config");
        }
    }
}
