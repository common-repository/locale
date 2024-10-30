<?php

/**
 * Plugin Settings
 *
 * @since   1.0.0
 * @package Locale\Setting
 */

namespace Locale\Setting;

use Locale\Functions;
use Locale\Notice\TransientNoticeService;

/**
 * Class PluginSettings
 *
 * @since   1.0.0
 * @package Locale\Setting
 */
class PluginSettings
{
    /**
     * Options Group
     *
     * @since 1.0.0
     *
     * @var string
     */
    const OPTION_GROUP = 'locale_api';

    /**
     * Section Credentials
     *
     * @since 1.0.0
     *
     * @var string The section ID for the API Credentials
     */
    const SECTION_CREDENTIALS = 'locale_api_credentials';

    /**
     * The API url
     *
     * @since 1.0.0
     *
     * @var string The api url setting value
     */
    const URL = 'locale_api_url';

    /**
     * The refresh api token
     *
     * @todo  May be this should be called TOKEN since it's the option not the time for the
     *        refresh?.
     *
     * @since 1.0.0
     *
     * @var string The value for when the token must be refreshed
     */
    const API_KEY = 'locale_api_token';

    /**
     * Register all settings.
     *
     * @return void
     * @since 1.0.0
     */
    public function register_setting()
    {
        add_settings_section(
            self::SECTION_CREDENTIALS,
            esc_html__('Credentials', 'locale'),
            '__return_false',
            self::OPTION_GROUP
        );

        // Token.
        $this->add_settings_field(
            self::API_KEY,
            esc_html__('API Key', 'locale'),
            [$this, 'dispatch_input_text'],
            self::OPTION_GROUP,
            self::SECTION_CREDENTIALS,
            [
                'value' => get_option(
                    self::API_KEY,
                    // Context: User is in the backend, did not yet fetched a token and finds instructions below.
                    ''
                ),
                'placeholder' => esc_html__('Not set', 'locale'),
                'description' => sprintf(
                    __(
                        'If you do not have an API Key, please contact us: %s',
                        'locale'
                    ),
                    '<a href="https://www.locale.to/">https://www.locale.to/</a>'
                ),
            ]
        );
    }

    /**
     * Create input field for option.
     *
     * @param array $field Must have a "name" key with the actual option name/id as its value.
     *
     * @since 1.0.0
     */
    public function dispatch_input_text($field)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $bind = (object)$field;

        unset($field);

        require Functions\get_template('views/type/default.php');
    }

    /**
     * Simplify adding setting fields.
     *
     * @param string $id Slug-name to identify the field. Used in the 'id' attribute of
     *                            tags.
     * @param string $title Formatted title of the field. Shown as the label for the field
     *                            during output.
     * @param callable $callback Function that fills the field with the desired form inputs. The
     *                            function should echo its output.
     * @param string $page The slug-name of the settings page on which to show the section
     *                            (general, reading, writing, ...).
     * @param string $section Optional. The slug-name of the section of the settings page
     *                            in which to show the box. Default 'default'.
     * @param array $args {
     *                            Optional. Extra arguments used when outputting the field.
     *
     * @type string $label_for When supplied, the setting title will be wrapped
     *                             in a `<label>` element, its `for` attribute populated
     *                             with this value.
     * @type string $class CSS Class to be added to the `<tr>` element when the
     *                             field is output.
     * }
     * @since 1.0.0
     */
    private function add_settings_field($id, $title, $callback, $page, $section, $args = [])
    {
        if (!isset($args['name'])) {
            $args['name'] = $id;
        }

        if (!isset($args['type'])) {
            $args['type'] = 'text';
        }

        if (!isset($args['value'])) {
            $args['value'] = get_option($args['name']);
        }

        register_setting($page, $args['name']);

        add_settings_field($id, $title, $callback, $page, $section, $args);
    }
}
