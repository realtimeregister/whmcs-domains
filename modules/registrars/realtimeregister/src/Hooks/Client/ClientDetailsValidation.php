<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class ClientDetailsValidation extends Hook
{
    /**
     * These patterns purely given as an indication and give no guarantee the submitted postalcode is also allowed by
     * registries. Some will, for example, disallow PO Boxes
     *
     * @var array|string[]
     */
    public array $postalcodePatterns = [
        'AF' => '([1-3][0-9]|[4][0-3])\d{2}',
        'AX' => '(AX-)?22\d{3}',
        'AR' => '[A-Z]?\d{4}([A-Z]{3})?',
        'AM' => '\d{4}',
        'AU' => '\d{4}',
        'AT' => '\d{4}',
        'BE' => '\d{4}',
        'BA' => '\d{5}',
        'BR' => '\d{5}-?\d{3}',
        'BG' => '\d{4}',
        'CA' => '[A-Z]\d[A-Z]\s?\d[A-Z]\d',
        'CL' => '\d{7}',
        'CN' => '\d{6}',
        'HR' => '(HR-)?\d{5}',
        'CU' => '(CP\s)?\d{5}',
        'CY' => '\d{4}',
        'CZ' => '\d{3}\s?\d{2}',
        'DK' => '\d{4}',
        'DO' => '\d{5}',
        'EC' => '(EC)?\d{6}',
        'EG' => '\d{5}',
        'EE' => '\d{5}',
        'FI' => '(FI-)?\d{5}',
        'FR' => '\d{5}',
        'PF' => '9[78]7\d{2}',
        'DE' => '\d{5}',
        'GB' => 'GIR\s?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT' .
            '|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN' .
            '|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR' .
            '|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\d[\dA-Z]?\s?\d[A-Z]{2}))' .
            '|BFPO\s?\d{1,4}',
        'GR' => '\d{3}\s?\d{2}',
        'HU' => '\d{4}',
        'IN' => '\d{6}',
        'ID' => '\d{5}',
        'IR' => '\d{5}(-?[0-9]{3,5})?',
        'IQ' => '\d{5}',
        'IL' => '\d{7}|\d{5}',
        'IT' => '\d{5}',
        'JP' => '\d{3}-?\d{4}',
        'JO' => '\d{5}',
        'KR' => '\d{5,6}',
        'LV' => '(LV)?(\s|-)?\d{4}',
        'LB' => '\d{4}(\s\d{4})?',
        'LT' => '(LT-)?\d{5}',
        'LU' => '((L|LU)(\s|-)?)?\d{4}',
        'MY' => '\d{5}',
        'MT' => '[A-Z]{3}\s?\d{4}',
        'MX' => '\d{5}',
        'MA' => '\d{5}',
        'NL' => '\d{4}\s?[A-Z]{2}',
        'NZ' => '\d{4}',
        'NO' => '(NO-)?\d{4}',
        'PK' => '\d{5}',
        'PE' => '((callo|lima)(\s|-)?|\d{3})\d{2}',
        'PH' => '\d{4}',
        'PL' => '\d{2}-?\d{3}',
        'PR' => '00(60[1-9]|6[1-9]\d|7\d{2}|90[1-9]|9[1-9]\d)(-\d{4})?',
        'PT' => '\d{4}(-\d{3})?',
        'RO' => '\d{6}',
        'RU' => '\d{6}',
        'SA' => '\d{5}(-\d{4})?',
        'SG' => '\d{6}',
        'SK' => '\d{3}\s?\d{2}',
        'SI' => '(SI-)?\d{4}',
        'ZA' => '\d{4}',
        'ES' => '\d{5}',
        'LK' => '\d{5}',
        'SD' => '\d{5}',
        'SE' => '(SE)?(\s|-)?\d{3}\s?\d{2}',
        'CH' => '\d{4}',
        'TH' => '\d{5}',
        'TN' => '\d{4}',
        'TR' => '\d{5}',
        'UA' => '\d{5}',
        'US' => '\d{5}(-\d{4})?',
        'UY' => '\d{5}',
        'VE' => '\d{4}'
    ];

    public function __invoke(DataObject $vars)
    {
        $country = strtoupper(trim($vars['country'] ?? ''));
        $postalcode = trim($vars['postcode'] ?? '');
        if (isset($this->postalcodePatterns[$country]) && $postalcode !== '') {
            if (!preg_match('/' . $this->postalcodePatterns[$country] . '/', $postalcode)) {
                return 'Invalid postcode, we expect "' . $this->postalcodePatterns[$country] . '"';
            }
        }
    }
}
