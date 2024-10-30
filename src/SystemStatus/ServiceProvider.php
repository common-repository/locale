<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\SystemStatus
 */

namespace Locale\SystemStatus;

use Inpsyde\SystemStatus\Assets\Styles;
use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\SystemStatus
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['SystemStatus.Controller'] = function () {

            return new Controller();
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        (new Styles(
            $container['locale.plugin']->url('/assets/css/'),
            ''
        )
        )->init();
    }
}
