<?php

namespace Locale\Locale\Exceptions;

use Locale\Api\ApiException;

/**
 * Thrown when Locale has deemed our request to create a project to be unauthorized.
 *
 * @package Locale\Locale\Exceptions
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class UnauthorizedProjectCreationException extends ApiException
{
    public function __construct()
    {
        $message = "We were unable to create a project in Locale. Please check your API key \u{1F600}";

        parent::__construct($message);
    }
}
