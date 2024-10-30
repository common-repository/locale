<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Installation
 */

namespace Locale\Activation;

use Pimple\Container;
use Locale\Service\IntegrableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Installation
 */
class ServiceProvider implements IntegrableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Activation.Activator'] = function (Container $container) {

            return new Activator($container['locale.plugin']);
        };
    }

    /**
     * @inheritdoc
     */
    public function integrate(Container $container)
    {
        $container['Activation.Activator']->store_version();
    }
}
