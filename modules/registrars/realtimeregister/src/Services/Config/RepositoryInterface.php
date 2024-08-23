<?php

namespace RealtimeRegister\Services\Config;

interface RepositoryInterface
{
    /**
     * Determine if the given configuration value exists.
     */
    public function has(string $key): bool;

    /**
     * Get the specified configuration value.
     *
     * @param  mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Get all of the configuration items for the application.
     */
    public function all(): array;

    /**
     * Set a given configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $value
     * @return void
     */
    public function set($key, $value = null);

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  mixed $value
     * @return void
     */
    public function prepend(string $key, $value);

    /**
     * Push a value onto an array configuration value.
     *
     * @param  mixed $value
     * @return void
     */
    public function push(string $key, $value);
}
