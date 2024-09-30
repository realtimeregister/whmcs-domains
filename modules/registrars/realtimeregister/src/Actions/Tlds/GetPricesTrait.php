<?php

namespace RealtimeRegister\Actions\Tlds;

use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\Price;
use SandwaveIo\RealtimeRegister\Domain\PriceCollection;
use SandwaveIo\RealtimeRegister\Domain\Promo;
use SandwaveIo\RealtimeRegister\Domain\PromoCollection;

trait GetPricesTrait
{
    public function getPrices(PromoCollection|PriceCollection $priceList): array
    {
        $prices = [];
        $pricesSLD = [];
        /** @var Price|Promo $priceItem */
        foreach ($priceList as $priceItem) {
            if (!str_starts_with($priceItem->product, "domain_")) {
                continue;
            }
            $explodeTld = explode('_', $priceItem->product);

            if (str_contains($priceItem->product, '_sld')) {
                $pricesSLD[$explodeTld[1]][$priceItem->action] = [
                    'currency' => $priceItem->currency,
                    'price' => $priceItem->price
                ];
                continue;
            }

            if (count($explodeTld) > 2 || count($explodeTld) < 2) {
                continue;
            }

            $prices[$explodeTld[1]][$priceItem->action] = [
                'currency' => $priceItem->currency,
                'price' => $priceItem->price
            ];
        }

        // Loop through the sld pricings and add every applicable tld that is not the main tld
        foreach ($pricesSLD as $tld => $priceInfo) {
            try {
                $metadata = (new MetadataService($tld))->getAll();
            } catch (\Exception) {
                continue;
            }

            foreach ($metadata->applicableFor as $applicableTld) {
                if (!str_contains($applicableTld, '.' . $tld)) {
                    continue;
                }
                $prices[$applicableTld] = $priceInfo;
            }
        }
        return $prices;
    }
}
