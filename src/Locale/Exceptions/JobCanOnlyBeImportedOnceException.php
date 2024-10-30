<?php

namespace Locale\Locale\Exceptions;

use DomainException;

/**
 * Thrown when an attempt to re-import a job after it's imported.
 *
 * @package Locale\Locale\Exceptions
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class JobsCanOnlyBeImportedOnceException extends DomainException
{
    public function __construct()
    {
        $message = 'Unable to update a job after it has been imported.';

        parent::__construct($message);
    }
}
