<?php

namespace Tests\Entities;

use libphonenumber\NumberParseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\Entities\WhmcsContact;

/**
 * Saving a contact without a phone number must not throw (#251)
 */
class WhmcsContactTest extends TestCase
{
    public static function emptyNumbers(): array
    {
        return ['null' => [null], 'empty string' => ['']];
    }

    #[DataProvider('emptyNumbers')]
    public function testEmptyNumberReturnsNull(?string $number)
    {
        $this->assertNull(WhmcsContact::formatE164a($number, 'NL'));
    }

    public function testNumberIsFormatted()
    {
        $this->assertSame('+31.612345678', WhmcsContact::formatE164a('0612345678', 'NL'));
        $this->assertSame('+31.612345678', WhmcsContact::formatE164a('+31 6 12345678'));
    }

    public function testInvalidNumberStillThrows()
    {
        $this->expectException(NumberParseException::class);
        WhmcsContact::formatE164a('not a number', 'NL');
    }

    public function testPhoneNumberFormattedWithoutPhoneNumber()
    {
        $this->assertNull((new WhmcsContact(id: 1, countryCode: 'NL'))->phoneNumberFormatted());
    }
}
