<?php

if (!function_exists('array_all')) {
    /**
     * @param array<string|int, mixed> $array
     * @param callable $callback
     * @return bool
     */
    function array_all(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (!$callback($value, $key)) {
                return false;
            }
        }

        return true;
    }
}
