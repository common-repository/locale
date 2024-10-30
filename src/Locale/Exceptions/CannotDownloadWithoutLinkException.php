<?php

namespace Locale\Locale\Exceptions;

use DomainException;

/**
 * Thrown when an attempt to download a translation job's file without a download
 * link is made.
 *
 * @package Locale\Locale\Exceptions
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class CannotDownloadWithoutLinkException extends DomainException
{
    public function __construct()
    {
        $message = "Unable to download a translation job's translations without a link.";

        parent::__construct($message);
    }
}
