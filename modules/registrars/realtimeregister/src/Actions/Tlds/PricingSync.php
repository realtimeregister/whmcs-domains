<?php

namespace RealtimeRegister\Actions\Tlds;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;
use WHMCS\Domain\TopLevel\ImportItem;
use WHMCS\Domains\DomainLookup\ResultsList;

class PricingSync extends Action
{
    use GetPricesTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $results = new ResultsList();

        if (App::registrarConfig()->customerHandle() !== '') {
            // Then, get the pricing
            $prices = $this->getPrices(App::client()->customers->priceList(App::registrarConfig()->customerHandle()));

            foreach ($prices as $tld => $priceInfo) {
                $item = new ImportItem();
                try {
                    $metadata = (new MetadataService($tld))->getMetadata();
                } catch (\Exception $e) {
                    continue;
                }

                $item->setExtension($tld);
                $item->setEppRequired($metadata->transferRequiresAuthcode);
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
                    if ($metadata->redemptionPeriod) {
                        $item->setRedemptionFeePrice(number_format($priceInfo['RESTORE']['price'] / 100, 2, '.', ''));
                        $item->setRedemptionFeeDays($metadata->redemptionPeriod);
                    }
                    if ($metadata->autoRenewGracePeriod) {
                        $item->setGraceFeePrice(number_format($priceInfo['RENEW']['price'] / 100, 2, '.', ''));
                        $item->setGraceFeeDays($metadata->autoRenewGracePeriod);
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
