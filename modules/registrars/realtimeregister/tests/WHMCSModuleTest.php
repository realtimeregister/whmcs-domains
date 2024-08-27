<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Realtime Register WHMCS Provisioning Module Test
 *
 * Sample PHPUnit test that asserts the fundamental requirements of a WHMCS
 * module, ensuring that the required ConfigOptions function is defined, and
 * that all defined functions return the appropriate data type.
 **/
class WHMCSModuleTest extends TestCase
{
    /** @var string $moduleName */
    protected string $moduleName = 'realtimeregister';

    /**
     * Asserts the required config options function is defined.
     */
    public function testRequiredConfigOptionsFunctionExists()
    {
        $this->assertTrue(function_exists($this->moduleName . '_getConfigArray'));
    }

    /**
     * Data provider of module function return data types.
     *
     * Used in verifying module functions return data of the correct type.
     *
     * @return array
     *
     */
    public static function providerFunctionReturnTypes(): array
    {
        return [
            'Version' => ['version', 'string'],
            'Check Availability' => ['CheckAvailability', 'array'],
            'Get Domain Information' => ['GetDomainInformation', 'array'],
            'Save Nameservers' => ['SaveNameservers', 'array'],
            'Save Registrar Lock' => ['SaveRegistrarLock', 'array'],
            'Get Contact Details' => ['GetContactDetails', 'array'],
            'Save Contact Details' => ['SaveContactDetails', 'array'],
            'Sync' => ['Sync', 'array'],
            'Admin Area Custom Button Array' => ['AdminCustomButtonArray', 'array'],
        ];
    }

    /**
     * Test module functions return appropriate data types.
     *
     * @param string $function
     * @param string $returnType
     *
     * @dataProvider providerFunctionReturnTypes
     */
    public function testFunctionsReturnAppropriateDataType(string $function, string $returnType)
    {
        if (function_exists($this->moduleName . '_' . $function)) {
            $result = call_user_func($this->moduleName . '_' . $function, []);

            if ($returnType == 'array') {
                $this->assertTrue(is_array($result));
            } elseif ($returnType == 'null') {
                $this->assertTrue(is_null($result));
            } else {
                $this->assertTrue(is_string($result));
            }
        }
    }
}
