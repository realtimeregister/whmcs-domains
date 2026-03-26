<?php

namespace Tests\Hooks\Client;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Client\ClientDetailsValidation;
use PHPUnit\Framework\TestCase;

class ClientDetailsValidationTest extends TestCase
{
    public function testDutchPostalcode()
    {
        $clientDetailsValidation = new ClientDetailsValidation(App::instance());
        $this->assertEmpty($clientDetailsValidation(new DataObject(['postcode' => '8000 AA', 'country' => 'NL'])));
        $this->assertEmpty($clientDetailsValidation(new DataObject(['postcode' => '1741 VM', 'country' => 'NL'])));
        $this->assertEquals(
            'Invalid postcode, we expect "' . $clientDetailsValidation->postalcodePatterns['NL'] . '"',
            $clientDetailsValidation(new DataObject(['postcode' => '8000 11', 'country' => 'NL']))
        );
    }

    public function testBelgianPostalcode()
    {
        $clientDetailsValidation = new ClientDetailsValidation(App::instance());

        $this->assertEmpty($clientDetailsValidation(new DataObject(['postcode' => '1000', 'country' => 'BE'])));
        $this->assertEquals(
            'Invalid postcode, we expect "' . $clientDetailsValidation->postalcodePatterns['BE'] . '"',
            $clientDetailsValidation(new DataObject(['postcode' => 'AAAA', 'country' => 'BE']))
        );
    }

    public function testBurundianPostalcode()
    {
        $clientDetailsValidation = new ClientDetailsValidation(App::instance());
        $this->assertEmpty($clientDetailsValidation(new DataObject(['postcode' => 'BP2755', 'country' => 'BI'])));
    }
}
