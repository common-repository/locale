<?php

/**
 * MetaBox
 *
 * @since   1.0.0
 * @package Locale\MetaBox
 */

namespace Locale\MetaBox;

/**
 * Interface Metabox
 *
 * @since   1.0.0
 * @package Locale\MetaBox
 */
interface Boxable
{

    /**
     * Add Metabox
     *
     * @return void
     * @since 1.0.0
     */
    public function add_meta_box();

    /**
     * Render Meta Box Template
     *
     * @return void
     * @since 1.0.0
     */
    public function render_template();

    /**
     * Create Nonce Instance
     *
     * @return \Brain\Nonces\WpNonce The nonce instance
     * @since 1.0.0
     */
    public function nonce();
}
