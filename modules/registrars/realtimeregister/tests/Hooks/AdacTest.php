<?php

namespace Tests\Hooks;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\Hooks\Adac;

/**
 * ADAC must show the same registration price as the WHMCS cart when domain pricing slabs are used (#245)
 */
class AdacTest extends TestCase
{
    private const SLAB_GROUP_ID = 5;

    /**
     * tblpricing rows as WHMCS stores them: the default price (tsetupfee 0) and a slab for one client group
     */
    private function prices(): Collection
    {
        return collect([
            ['relid' => 1, 'type' => 'domainregister', 'msetupfee' => '34.00', 'tsetupfee' => '0.00'],
            ['relid' => 1, 'type' => 'domainregister', 'msetupfee' => '2.00', 'tsetupfee' => '5.00'],
            ['relid' => 1, 'type' => 'domainrenew', 'msetupfee' => '30.00', 'tsetupfee' => '0.00'],
            ['relid' => 2, 'type' => 'domainregister', 'msetupfee' => '10.00', 'tsetupfee' => '0.00'],
        ]);
    }

    private function registerPrice(int $clientGroupId, int $relid = 1): string
    {
        return Adac::pricesForClientGroup($this->prices(), $clientGroupId)
            ->first(fn($price) => $price['relid'] === $relid && $price['type'] === 'domainregister')['msetupfee'];
    }

    public function testVisitorWithoutClientGroupSeesDefaultPrice()
    {
        $this->assertSame('34.00', $this->registerPrice(0));
    }

    public function testClientWithoutSlabSeesDefaultPrice()
    {
        $this->assertSame('34.00', $this->registerPrice(9));
    }

    public function testClientInSlabGroupSeesSlabPrice()
    {
        $this->assertSame('2.00', $this->registerPrice(self::SLAB_GROUP_ID));
    }

    public function testTldsAndPriceTypesWithoutSlabKeepDefaultPrice()
    {
        $prices = Adac::pricesForClientGroup($this->prices(), self::SLAB_GROUP_ID);

        $this->assertCount(3, $prices);
        $this->assertSame('10.00', $this->registerPrice(self::SLAB_GROUP_ID, 2));
        $this->assertSame('30.00', $prices->firstWhere('type', 'domainrenew')['msetupfee']);
    }
}
