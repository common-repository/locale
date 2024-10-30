<?php

/**
 * Containing class that handles pixxio options page.
 *
 * @package Locale
 */

namespace Locale\Pages;

use Brain\Nonces\WpNonce;
use Locale\Auth;
use Locale\Functions;
use Locale\Plugin;
use Locale\Setting;

/**
 * Controller / Model for locale options page.
 *
 * @package Locale\Admin
 */
class PageOptions implements Pageable
{
    const USERNAME = 'locale_api_username';
    const PASSWORD = 'locale_api_password';

    const TRANSIENT_CATEGORIES = 'locale_categories';
    const SELECTED_CATEGORIES = 'locale_sync_categories';
    const SLUG = 'locale_settings';

    /**
     * Allowed actions
     *
     * If this is a field in the post data,
     * then the equally names function will be called with the post data.
     * Usually those is a list of buttons that can be pressed on the options page.
     *
     * @var string[]
     */
    private $actions = [
        'fetch_files',
        'save',
        'save_categories',
        'update_categories',
    ];

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * PageOptions constructor
     *
     * @param Plugin $plugin The plugin instance.
     *
     * @since 1.0.0
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @inheritdoc
     */
    public function add_page()
    {
        add_submenu_page(
            'locale',
            esc_html__('Settings', 'locale'),
            esc_html__('Settings', 'locale'),
            'manage_options',
            self::SLUG,
            [$this, 'render_template']
        );
    }

    /**
     * @inheritdoc
     */
    public function render_template()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__(
                'You do not have sufficient permissions to access this page.',
                'locale'
            ));
        }

        wp_enqueue_style('locale-options-page');

        // Render the template.
        require_once Functions\get_template('/views/options-page/layout.php');
    }

    /**
     * Fetch token.
     *
     * Only / Best current point to hook in settings update process it the option page cap filter.
     *
     * @param string[] $capabilities A list of capabilities.
     *
     * @return \string[]
     */
    public function filter_capabilities($capabilities)
    {
        if (!check_admin_referer(Setting\PluginSettings::OPTION_GROUP . '-options')) {
            // Seems like some other page so we won't do stuff.
            return $capabilities;
        }

        $login_data = $_REQUEST; // input var okay

        // Buttons the user can press.
        $chosen_action = array_intersect(array_keys($login_data), $this->actions);

        if ($chosen_action) {
            $chosen_action = current($chosen_action) . '_action';
            $this->$chosen_action($login_data);
        }

        return $capabilities;
    }

    /**
     * Enqueue Style
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_style()
    {
        wp_register_style(
            'locale-options-page',
            $this->plugin->url('/assets/css/settings.css'),
            [],
            filemtime((new Plugin())->dir('/assets/css/settings.css')),
            'screen'
        );
    }

    /**
     * Enqueue Script
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_script()
    {
        wp_enqueue_script(
            'locale-options-page',
            $this->plugin->url('/resources/js/options-page.js'),
            ['jquery', 'jquery-ui-tabs'],
            filemtime((new Plugin())->dir('/resources/js/options-page.js')),
            true
        );
    }
}
