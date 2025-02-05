<?php

/**
 * Storable Notice
 *
 * @since   1.0.0
 * @package Locale\Notice
 */

namespace Locale\Notice;

/**
 * Class StorableNotice
 *
 * @since   1.0.0
 * @package Locale\Notice
 */
interface StorableNotice extends Noticeable
{

    /**
     * Store Notice
     *
     * @param string $message The notice message to store.
     * @param string $severity The severity of the message.
     *
     * @return bool true if stored, false otherwise
     * @since 1.0.0
     */
    public function store($message, $severity);
}
