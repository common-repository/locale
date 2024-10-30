<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Setting
 */

namespace Locale\Setting;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Setting
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Settings.PluginSettings'] = static function () {

            return new PluginSettings();
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        add_action('admin_init', [$container['Settings.PluginSettings'], 'register_setting']);
    }
}
