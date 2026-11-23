<?php

namespace Tests\Hooks\Client;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Client\ContactDetailsValidation;
use PHPUnit\Framework\TestCase;

class ClientDetailsValidationTest extends TestCase
{
    private function validate(array $vars): array
    {
        return (new ContactDetailsValidation(App::instance()))(
            new DataObject($vars + ['phonenumber' => '+31.612345678'])
        );
    }

    private function postcodeError(string $country): array
    {
        $patterns = (new ContactDetailsValidation(App::instance()))->postalcodePatterns;
        return ['Invalid postcode, we expect "' . $patterns[$country] . '"'];
    }

    public function testDutchPostalcode()
    {
        $this->assertEmpty($this->validate(['postcode' => '8000 AA', 'country' => 'NL']));
        $this->assertEmpty($this->validate(['postcode' => '1741 VM', 'country' => 'NL']));
        $this->assertEquals($this->postcodeError('NL'), $this->validate(['postcode' => '8000 11', 'country' => 'NL']));
    }

    public function testBelgianPostalcode()
    {
        $this->assertEmpty($this->validate(['postcode' => '1000', 'country' => 'BE']));
        $this->assertEquals($this->postcodeError('BE'), $this->validate(['postcode' => 'AAAA', 'country' => 'BE']));
    }

    public function testBurundianPostalcode()
    {
        $this->assertEmpty($this->validate(['postcode' => 'BP2755', 'country' => 'BI']));
    }

    /**
     * Contacts can be used as registrant, so a missing or invalid phone number must block the save (#251)
     */
    public function testPhoneNumber()
    {
        $this->assertEmpty($this->validate(['country' => 'NL', 'phonenumber' => '0612345678']));
        $this->assertEquals(['A phone number is required'], $this->validate(['country' => 'NL', 'phonenumber' => '']));
        $this->assertEquals(['A phone number is required'], $this->validate(['country' => 'NL', 'phonenumber' => ' ']));
        $this->assertEquals(['Invalid phone number'], $this->validate(['country' => 'NL', 'phonenumber' => 'nope']));
    }
}
