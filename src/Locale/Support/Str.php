<?php

namespace Locale\Locale\Support;

/**
 * A fork of Laravel's \Illuminate\Support\Str for working with strings.
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class Str
{
    /**
     * Determines whether the given subject is a valid JSON string.
     *
     * @see https://stackoverflow.com/a/59849065/8449155 - For performance benchmarks
     *
     * @param mixed $subject
     *
     * @return boolean
     */
    public static function isJson($subject): bool
    {
        return is_string($subject)
            && json_decode($subject, true) !== null;
    }
}
