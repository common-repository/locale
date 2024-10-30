<?php

/**
 *
 * @since   1.0.0
 * @package Locale\Service
 */

namespace Locale\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class BootstrappableServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Service
 */
interface BootstrappableServiceProvider extends ServiceProviderInterface
{

    /**
     * Boot
     *
     * @param \Pimple\Container $container
     *
     * @return mixed
     */
    public function boot(Container $container);
}
