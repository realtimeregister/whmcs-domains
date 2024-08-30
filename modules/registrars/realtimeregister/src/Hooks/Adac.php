<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Enums\ScriptLocationType;
use RealtimeRegister\Models\Whmcs\Configuration;
use RealtimeRegister\Models\Whmcs\DomainPricing;
use RealtimeRegister\Models\Whmcs\Pricing;
use RealtimeRegister\Models\Whmcs\Registrars;
use Realtimeregister\Services\ShoppingCartService;

class Adac extends Hook
{
    public function __invoke(DataObject $vars)
    {
        global $smarty;
        $shopCurrency = strtoupper($vars['currency']['code']);

        if (
            !empty($smarty->tpl_vars['templatefile']->value)
            && in_array($smarty->tpl_vars['templatefile']->value, ['viewcart', 'domainregister'])
        ) {
            if ($_POST['currency']) {
                if (ShoppingCartService::updateCartPremiumPrices($shopCurrency)) {
                    header("Refresh: 0");
                    exit;
                }
            }
        }

        if (
            !empty($smarty->tpl_vars['templatefile']->value)
            && $smarty->tpl_vars['templatefile']->value == 'domainregister'
        ) {
            if (
                !empty($_POST['adacpremium']) && !empty($_POST['adacpremiumprice'])
                && !empty($_POST['adacpremiumcurrency'])
            ) {
                $premiumPrice = $_POST['adacpremiumprice'] / 100;
                $premiumCurrency = strtoupper($_POST['adacpremiumcurrency']);

                $_SESSION['PremiumDomains'][$_POST['adacpremium']]['cost'] = [
                    'register'     => number_format($premiumPrice, 2, '.', ''),
                    'renew'        => number_format($premiumPrice, 2, '.', ''),
                    'CurrencyCode' => $premiumCurrency
                ];

                $price = ShoppingCartService::getPremiumPricing($premiumPrice, $premiumCurrency, $shopCurrency);

                // Get currency ID
                $_SESSION['PremiumDomains'][$_POST['adacpremium']]['markupPrice'][1]['register']
                    = new \WHMCS\View\Formatter\Pricse($price, $vars['currency']);
                $_SESSION['PremiumDomains'][$_POST['adacpremium']]['markupPrice'][1]['renew']
                    = new \WHMCS\View\Formatter\Price($price, $vars['currency']);

                /**
                 * Counter-intuitive, but the markupPrice currency is set to the premium cost price currency in the
                 * default WHMCS xflow,we mimic this behavior.
                 */
                $whmcsCurrencies = [];
                foreach (localAPI('GetCurrencies', [])['currencies']['currency'] as $c) {
                    $whmcsCurrencies[strtoupper($c['code'])] = $c['id'];
                }
                $_SESSION['PremiumDomains'][$_POST['adacpremium']]['markupPrice'][1]['currency']
                    = $whmcsCurrencies[$premiumCurrency];

                echo json_encode(['price' => $price, 'currency' => $vars['currency']]);
                exit;
            }

            $adac_token = Registrars::select('value')
                ->where('setting', 'adac_token')
                ->registrar()
                ->first();

            $adac_token_value = false;
            if (!empty($adac_token->value)) {
                $adac_token_value = decrypt($adac_token->value, $GLOBALS['cc_encryption_hash']);
            }

            if (!empty($adac_token_value)) {
                $adac_key = Registrars::select('value')
                    ->where('setting', 'adac_key')
                    ->registrar()
                    ->first();

                $adac_key_value = decrypt($adac_key->value, $GLOBALS['cc_encryption_hash']);

                if (!empty($adac_key_value)) {
                    self::buildDomainPrices($vars);

                    $smarty->assign('adacTldToken', $adac_token_value);
                    $smarty->assign('adacApiKey', $adac_key_value);
                }
            }
        }
    }

    private static function buildDomainPrices($vars)
    {
        global $_LANG;
        $domainPrices = DomainPricing::select('id', 'group', 'extension')->get();

        $tldPricing = [];
        $tldGroup = [];
        foreach ($domainPrices->toArray() as $item) {
            $extensionStrip = str_replace('.', '', $item['extension']);
            $tldPricing[$item['id']] = $extensionStrip;
            $tldGroup[$extensionStrip]['type'] = $item['group'];
            $tldGroup[$extensionStrip]['title'] = $item['group'];

            if (!empty($item['group']) && !empty($_LANG['domainCheckerSalesGroup'][$item['group']])) {
                $tldGroup[$extensionStrip]['title'] = $_LANG['domainCheckerSalesGroup'][$item['group']];
            }
        }

        $currency = 1;

        if (!empty($vars['currency']['id'])) {
            $currency = $vars['currency']['id'];
        } elseif (!empty($vars['client']->currency)) {
            $currency = $vars['client']->currency;
        }

        $cartDomains = [];
        if (!empty($_SESSION['cart']['domains'])) {
            $domains = collect($_SESSION['cart']['domains']);

            foreach ($domains as $domain) {
                $cartDomains[] = $domain['domain'];
            }
        }

        /**
 * @var Pricing[] $prices
*/
        $prices = Pricing::where('currency', $currency)->where(
            function ($query) {
                $query->where('type', 'domainregister')
                    ->orWhere('type', 'domaintransfer')
                    ->orWhere('type', 'domainrenew');
            }
        )->get();

        if (!empty($prices)) {
            $build = [];

            foreach ($prices as $price) {
                if ($price['msetupfee'] < 0 && $price['qsetupfee'] < 0) {
                    continue;
                }

                if (!($price['msetupfee'] < 0)) {
                    $priceString = $price->getValutaAttribute();
                    $priceString .= $price['msetupfee'];
                    $priceString .= $price->getCurrencySuffixAttribute();

                    $build[$tldPricing[$price['relid']]]['group'] = $tldGroup[$tldPricing[$price['relid']]]['type'];
                    $build[$tldPricing[$price['relid']]]['group_title'] =
                        $tldGroup[$tldPricing[$price['relid']]]['title'];
                    $build[$tldPricing[$price['relid']]]['interval'] = '12';
                    $build[$tldPricing[$price['relid']]][$price['type']] = $priceString;
                } elseif (!($price['qsetupfee'] < 0)) {
                    $priceString = $price->getValutaAttribute();
                    $priceString .= $price['qsetupfee'];
                    $priceString .= $price->getCurrencySuffixAttribute();

                    $build[$tldPricing[$price['relid']]]['group'] = $tldGroup[$tldPricing[$price['relid']]]['type'];
                    $build[$tldPricing[$price['relid']]]['group_title'] =
                        $tldGroup[$tldPricing[$price['relid']]]['title'];
                    $build[$tldPricing[$price['relid']]]['interval'] = '24';
                    $build[$tldPricing[$price['relid']]][$price['type']] = $priceString;
                }
            }

            $ote = Registrars::select('value')
                ->where('setting', 'test_mode')
                ->registrar()
                ->first();

            $oteValue = false;

            if ($ote->value) {
                $oteValue = decrypt(
                    $ote->value,
                    $GLOBALS['cc_encryption_hash']
                );
            }

            $configPrem = Configuration::where('setting', 'PremiumDomains')->first();
            $premiumDomains = false;
            if (!empty($configPrem->value) && $configPrem->value == 1) {
                $premiumDomains = true;
            }

            global $_LANG;

            App::assets()->addStyle('adac.css');
            App::assets()->addScript('adac.js', ScriptLocationType::Footer);
            App::assets()->addToJavascriptVariables(
                'adac-js',
                [
                'adacLang'       => $_LANG['rtr']['adac'],
                'tldPrices'      => $build,
                'ote'            => $oteValue,
                'premiumDomains' => $premiumDomains,
                'cartDomains'    => $cartDomains
                ]
            );
        }

        return $prices;
    }
}
