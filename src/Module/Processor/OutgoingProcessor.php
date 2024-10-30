<?php

// -*- coding: utf-8 -*-

namespace Locale\Module\Processor;

use Locale\Translation;

/**
 * Interface OutgoingProcessor
 * @package Locale\Module\Processor
 */
interface OutgoingProcessor extends Processor
{
    /**
     * Process Outgoing
     *
     * @param Translation $translation
     *
     * @return void
     */
    public function processOutgoing(Translation $translation);
}
