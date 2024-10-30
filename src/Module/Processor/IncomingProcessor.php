<?php

// -*- coding: utf-8 -*-

namespace Locale\Module\Processor;

use Locale\Translation;

/**
 * Interface IncomingProcessor
 * @package Locale\Module\Processor
 */
interface IncomingProcessor extends Processor
{
    /**
     * Process Incoming
     *
     * @param Translation $translation
     *
     * @return void
     */
    public function processIncoming(Translation $translation);
}
