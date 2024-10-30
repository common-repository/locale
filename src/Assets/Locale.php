<?php

/**
 * Asset Locale
 *
 * @since   1.0.0
 * @package Locale\Assets
 */

namespace Locale\Assets;

use Locale\Plugin;

/**
 * Class Locale
 *
 * @since   1.0.0
 * @package Locale\Assets
 */
class Locale
{
    /**
     * Plugin
     *
     * @since 1.0.0
     *
     * @var \Locale\Plugin Instance of the class
     */
    private $plugin;

    /**
     * Locale constructor
     *
     * @param \Locale\Plugin $plugin Instance of the class.
     *
     * @since 1.0.0
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register Style
     *
     * @return void
     * @since 1.0.0
     */
    public function register_style()
    {
        wp_enqueue_style(
            'locale',
            $this->plugin->url('/assets/css/locale.css'),
            [],
            filemtime($this->plugin->dir('/assets/css/locale.css')),
            'screen'
        );
    }
}
