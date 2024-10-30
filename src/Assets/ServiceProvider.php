<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Assets
 */

namespace Locale\Assets;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Assets
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Assets.Locale'] = function (Container $container) {

            return new Locale($container['locale.plugin']);
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        add_action('admin_head', [$container['Assets.Locale'], 'register_style']);
    }
}
