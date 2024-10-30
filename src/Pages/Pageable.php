<?php

/**
 * Pageable
 *
 * @since   1.0.0
 * @package Locale\Pages
 */

namespace Locale\Pages;

/**
 * Interface Pageable
 *
 * @since   1.0.0
 * @package Locale\Pages
 */
interface Pageable
{

    /**
     * Register Page
     *
     * @return void
     * @since 1.0.0
     */
    public function add_page();

    /**
     * Render Page Template
     *
     * @return void
     * @since 1.0.0
     */
    public function render_template();
}
