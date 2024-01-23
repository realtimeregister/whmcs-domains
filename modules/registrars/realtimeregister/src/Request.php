<?php

namespace RealtimeRegister;

use Illuminate\Support\Arr;
use RealtimeRegister\Entities\Domain;

class Request
{
    public readonly ?Domain $domain;

    /**
     * @var ?int In Years
     */
    public readonly ?int $registrationPeriod;

    public readonly ?string $eppCode;

    public readonly bool $hasPremiumEnabled;

    public readonly array $params;

    public function __construct(array $params = [])
    {
        if (isset($params['tld'], $params['sld'])) {
            $this->domain = Domain::fromWhmcs($params);
        }
        $this->registrationPeriod = $params['regperiod'] ?? null;
        $this->eppCode = $params['eppcode'] ?? null;

        $this->hasPremiumEnabled = $params['premiumEnabled'] ?? false;
        $this->params = $params;
    }

    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? value($default);
    }

    public function input(string $key = null, $default = null)
    {
        return Arr::get($_POST ?? [], $key, $default);
    }
}