<?php

namespace Locale\Locale\Exceptions;

use Locale\Api\ApiException;

/**
 * Thrown when an HTTP Request object is being instantiated without an API Key
 * configured.
 *
 * @package Locale\Locale\Exceptions
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class HttpRequestUnavailableWithoutApiKeyException extends ApiException
{
    public function __construct()
    {
        $message = "Unable to make HTTP Requests without Locale API Key.";

        parent::__construct($message);
    }
}
