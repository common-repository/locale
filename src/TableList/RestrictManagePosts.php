<?php

/**
 * Restrict Manage Posts
 *
 * @since     1.0.0
 * @package   Locale
 */

namespace Locale\TableList;

use Locale\Plugin;

/**
 * Class RestrictManagePosts
 *
 * @since   1.0.0
 * @package Locale
 */
class RestrictManagePosts
{
    /**
     * Plugin
     *
     * @since 1.0.0
     *
     * @var \Locale\Plugin
     */
    private $plugin;

    /**
     * Capability
     *
     * @since 1.0.0
     *
     * @var string Capability needed by the user to perform actions and show elements
     */
    private static $capability = 'manage_options';

    /**
     * RestrictManagePosts constructor
     *
     * @param \Locale\Plugin $plugin The plugin instance.
     *
     * @since 1.0.0
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Enqueue Styles
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_styles()
    {
        wp_register_style(
            'locale-restrict-manage-posts',
            $this->plugin->url('/assets/css/restrict-manage-posts.css'),
            [],
            filemtime($this->plugin->dir('/assets/css/restrict-manage-posts.css')),
            'screen'
        );
    }

    /**
     * Enqueue Scripts
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_register_script(
            'locale-restrict-manage-posts',
            $this->plugin->url('/resources/js/restrict-manage-posts.js'),
            ['underscore', 'jquery'],
            filemtime($this->plugin->dir('/resources/js/restrict-manage-posts.js')),
            true
        );

        wp_localize_script(
            'locale-restrict-manage-posts',
            'strings',
            [
                'noElementsSelected' => esc_html__(
                    'You must select at least one element to translate.',
                    'locale'
                ),
            ]
        );
    }

    /**
     * Filter Bulk Action List
     *
     * @param array $actions The actions to filter.
     *
     * @return array The filtered actions
     * @since 1.0.0
     */
    public function filter_bulk_action_list($actions)
    {
        if (current_user_can(self::$capability)) {
            $actions['bulk_translate'] = esc_html__('Bulk Translate', 'locale');
        }

        return $actions;
    }

    /**
     * Restrict Manage Posts
     *
     * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
     *
     * @return void
     * @since 1.0.0
     */
    public function restrict_manage_posts($which)
    {
        if (!current_user_can(self::$capability)) {
            return;
        }

        if ('top' !== $which) {
            return;
        }

        wp_enqueue_style('locale-restrict-manage-posts');
        wp_enqueue_script('locale-restrict-manage-posts');

        require_once \Locale\Functions\get_template('/views/restrict-manage-posts.php');
    }
}
