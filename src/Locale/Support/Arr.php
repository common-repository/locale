<?php

namespace Locale\Locale\Support;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;
use function call_user_func;
use function explode;
use function is_null;
use function strpos;
use const ARRAY_FILTER_USE_BOTH;

/**
 * A fork of Laravel's \Illuminate\Support\Arr for working with arrays.
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (! is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            // A slight modification from the core Laravel Arr::get method. Instead
            // of returning the array when the key is null, return the default
            // instead.
            return $default;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Similar to Laravel's Arr::where helper, but resets the array keys before
     * returning the resulting array.
     */
    public static function where($array, callable $callback = null): array
    {
        $filtered = is_null($callback)
            ? array_filter($array)
            : array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);

        return static::isAssoc($array) ? $filtered : array_values($filtered);
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys
     * beginning with zero.
     *
     * @param  array  $array
     *
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}
