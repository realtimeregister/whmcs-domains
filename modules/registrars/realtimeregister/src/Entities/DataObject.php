<?php

namespace RealtimeRegister\Entities;

use Illuminate\Support\Arr;

class DataObject extends \ArrayObject
{
    public function get(string $key, $default = null)
    {
        return Arr::get($this, $key, $default);
    }
}