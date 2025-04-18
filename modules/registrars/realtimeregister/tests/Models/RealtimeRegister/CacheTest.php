<?php

namespace Tests\Models\RealtimeRegister;

use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;

class CacheTest extends TestCase
{
    public function testPutAndGet()
    {
        // Test with a string value
        Cache::put('test_string', 'Hello World', 60);
        $this->assertEquals('Hello World', Cache::get('test_string'));

        // Test with an array value
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        Cache::put('test_array', $array, 60);
        $this->assertEquals($array, Cache::get('test_array'));

        // Test with a boolean value
        Cache::put('test_bool', true, 60);
        $this->assertTrue(Cache::get('test_bool'));

        // Test with a numeric value
        Cache::put('test_number', 12345, 60);
        $this->assertEquals(12345, Cache::get('test_number'));
    }
}
