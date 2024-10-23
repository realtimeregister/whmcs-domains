<?php

namespace RealtimeRegisterDomains\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Currencies;

class ShoppingCartService
{

    /**
     * @throws \Exception
     */
    private static function getExchangeRate(
        string $fromCurrency,
        string $toCurrency,
        string $defaultCurrency
    ): float|int {
        // Always try to use WHMCS exchange rate first
        if ($toCurrency == $defaultCurrency) {
            $c = Currencies::where('code', $fromCurrency)->first();
            if (!empty($c)) {
                return 1 / $c->rate;
            }
        } else {
            $c = Currencies::where('code', $toCurrency)->first();
            if (!empty($c)) {
                return (float)$c->rate;
            }
        }

        // RTR exchange rate fallback
        $exchangeRate = App::client()->financial->exchangeRates($toCurrency);
        foreach ($exchangeRate->exchangerates as $exchangeRate => $rate) {
            if ($exchangeRate == $fromCurrency) {
                return $rate;
            }
        }
        throw new \Exception('Currency ' . $fromCurrency . ' not found');
    }

    public static function getPremiumPricing($premiumPrice, $premiumCurrency, $shopCurrency): array|string
    {
        $price = $premiumPrice;

        if ($premiumCurrency != $shopCurrency) {
            try {
                // Exchange to default currency if necessary
                $defaultCurrency = Currencies::where('default', 1)->first()->code;

                if ($premiumCurrency != $defaultCurrency) {
                    $price *= self::getExchangeRate($premiumCurrency, $defaultCurrency, $defaultCurrency);
                }

                // Exchange to shop currency if necessary
                if ($defaultCurrency != $shopCurrency) {
                    $price *= self::getExchangeRate($defaultCurrency, $shopCurrency, $defaultCurrency);
                }
            } catch (\Exception $ex) {
                return [
                    'price' => '-1',
                    'currency' => [],
                    'error' => $ex->getMessage(),
                ];
            }
        }

        // Apply markup
        $markups = Capsule::select(
            'SELECT
                m.markup,
                dm.markup default_markup
            FROM tbldomainpricing_premium dm
            LEFT JOIN tbldomainpricing_premium m ON m.to_amount > ?
            WHERE
                dm.to_amount = -1
            ORDER BY m.to_amount
            LIMIT 1
            ',
            [$price]
        )[0];
        $markup = (float)($markups->markup != null ? $markups->markup : $markups->default_markup);
        $price *= (1 + $markup / 100);

        return number_format($price, 2, '.', '');
    }

    public static function updateCartPremiumPrices($shopCurrency): bool
    {
        $updated = false;
        if (empty($_SESSION['cart']['domains'])) {
            return $updated;
        }
        foreach ($_SESSION['cart']['domains'] as &$domain) {
            if ($domain['isPremium']) {
                // Recalculate price based on currency
                $price = ShoppingCartService::getPremiumPricing(
                    $domain['registrarCostPrice'],
                    strtoupper($_SESSION['PremiumDomains'][$domain['domain']]['cost']['CurrencyCode']),
                    $shopCurrency
                );

                $domain['domainpriceoverride'] = $price;
                $domain['domainrenewoverride'] = $price;

                $updated = true;
            }
        }
        return $updated;
    }
}
