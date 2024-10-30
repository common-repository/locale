<?php

/**
 * Interface IntegrableServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Service
 */

namespace Locale\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Interface IntegrableServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Service
 */
interface IntegrableServiceProvider extends ServiceProviderInterface
{

    /**
     * Integrate service into plugin
     *
     * @param \Pimple\Container $container The container instance.
     *
     * @return void
     * @since 1.0.0
     */
    public function integrate(Container $container);
}
