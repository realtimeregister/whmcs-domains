<?php

namespace Tests\Hooks;

use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\Hooks\AdminClientDomainsTabFieldsSave;

/**
 * Admin domain field values must map to the right property by form index, also with gaps (#257)
 */
class AdminClientDomainsTabFieldsSaveTest extends TestCase
{
    public function testRemovedDefaultFieldsLeaveGapInIndexes()
    {
        $fields = [3 => ['Name' => 'identificationNumber'], 4 => ['Name' => 'entityType']];

        $this->assertSame(
            ['identificationNumber' => '12345678Z', 'entityType' => '1'],
            AdminClientDomainsTabFieldsSave::mapDomainFields($fields, [3 => '12345678Z', 4 => '1'])
        );
    }

    public function testUncheckedCheckboxIsNotSubmitted()
    {
        $fields = [0 => ['Name' => 'a'], 1 => ['Name' => 'tick'], 2 => ['Name' => 'lastTick']];

        $this->assertSame(
            ['a' => 'x', 'tick' => '', 'lastTick' => ''],
            AdminClientDomainsTabFieldsSave::mapDomainFields($fields, [0 => 'x'])
        );
    }

    public function testConsecutiveIndexesSkipRemovedAndLanguageCode()
    {
        $fields = [
            0 => ['Name' => 'a'],
            1 => ['Name' => 'old', 'Remove' => true],
            2 => ['Name' => 'languageCode'],
            3 => ['Name' => 'b'],
        ];

        $this->assertSame(
            ['a' => '1', 'b' => '2'],
            AdminClientDomainsTabFieldsSave::mapDomainFields($fields, [0 => '1', 2 => 'nl', 3 => '2'])
        );
    }

    public function testUnknownIndexIsNotShiftedToAnotherProperty()
    {
        $fields = [3 => ['Name' => 'identificationNumber'], 4 => ['Name' => 'entityType']];

        $this->assertNull(AdminClientDomainsTabFieldsSave::mapDomainFields($fields, [3 => 'x', 7 => 'y']));
    }
}
