<?php

namespace Locale\Locale\Exceptions;

use Locale\Api\ApiException;

/**
 * Thrown when an error has occurred while downloading a translation file, during
 * import.
 *
 * @package Locale\Locale\Exceptions
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslationResultDownloadErrorException extends ApiException
{
    public function __construct($message)
    {
        $message = "An error has occurred while downloading the translation: {$message}";

        parent::__construct($message);
    }
}
