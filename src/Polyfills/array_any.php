<?php

if (!function_exists('array_any')) {

    /**
     *
     * @param array<string|int, mixed> $array
     * @param callable $callback
     * @return bool
     */
    function array_any(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
