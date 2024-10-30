<?php

/**
 * Locale Status
 *
 * @since   1.0.0
 * @package Locale\SystemStatus
 */

namespace Locale\SystemStatus;

use Inpsyde\SystemStatus\Data\Information;
use Inpsyde\SystemStatus\Item\Item;
use Locale\Plugin;

use function Locale\Functions\get_languages;
use function Locale\Functions\locale_api;

/**
 * Class Locale
 *
 * @since   1.0.0
 * @package Locale\SystemStatus
 */
class Locale implements Information
{
    /**
     * The collection of information
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $collection = [];

    /**
     * System Information Title
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $title;

    /**
     * Locale constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->title = esc_html__('Locale', 'locale');
    }

    /**
     * System Information Title
     *
     * @return string
     * @since 1.0.0
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Collection
     *
     * @return array The collection of information
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * Plugin Version
     *
     * @return void
     */
    public function pluginVersion()
    {
        $plugin = new Plugin();
        $this->collection['plugin_version'] = new Item(
            esc_html__('Plugin Version', 'locale'),
            $plugin->version()
        );
    }

    /**
     * Activated Languages
     *
     * @return void
     * @since 1.0.0
     */
    public function activatedLanguages()
    {
        $languages = get_languages();
        $lang_list = '';

        foreach ($languages as $language) {
            $lang_list .= $language->get_label() . ', ';
        }

        $lang_list = trim($lang_list, ', ');

        $this->collection['activated_languages'] = new Item(
            esc_html__('Activated Languages', 'locale'),
            $lang_list
        );
    }
}
