<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Module
 */

namespace Locale\Module;

use CachingIterator;
use Pimple\Container;
use Locale\Module\ACF\Processor\IncomingMetaProcessor;
use Locale\Module\ACF\Processor\OutgoingMetaProcessor;
use Locale\Module\Mlp\DataProcessor;
use Locale\Module\Mlp\Integrator as MultilingualPressIntegrator;
use Locale\Module\Processor\ProcessorBusFactory;
use Locale\Module\WooCommerce\Integrator as WooCommerceIntegrator;
use Locale\Module\YoastSeo\Integrator as WordPressSeoByYoastIntegrator;
use Locale\Module\ACF\Integrator as ACFIntegrator;
use Locale\Service\IntegrableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Module
 */
class ServiceProvider implements IntegrableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[MultilingualPressIntegrator::class] = function () {
            return new MultilingualPressIntegrator();
        };
        $container[WordPressSeoByYoastIntegrator::class] = function () {
            return new WordPressSeoByYoastIntegrator();
        };
        $container[WooCommerceIntegrator::class] = function (Container $container) {
            return new WooCommerceIntegrator(
                $container[ProcessorBusFactory::class]
            );
        };

        $container[AcfIntegrator::class] = function (Container $container) {
            return new AcfIntegrator($container['tm/acf/processor_bus']);
        };

        $container['tm/acf/processor_bus'] = function (Container $container) {
            $outgoingMetaProcessor = $container['tm/acf/outgoing_meta_processor'];
            $incomingMetaProcessor = $container['tm/acf/incoming_meta_processor'];
            $processorBusFactory = $container[ProcessorBusFactory::class];
            $processorBus = $processorBusFactory->create();
            $processorBus
                ->pushProcessor($outgoingMetaProcessor)
                ->pushProcessor($incomingMetaProcessor);

            return $processorBus;
        };

        $container['tm/acf/outgoing_meta_processor'] = function () {
            return new OutgoingMetaProcessor();
        };

        $container['tm/acf/incoming_meta_processor'] = function () {
            return new IncomingMetaProcessor();
        };

        $container[ModulesProvider::class] = function (Container $container) {
            return new ModulesProvider([
                'wp-seo' => $container[WordPressSeoByYoastIntegrator::class],
                'multilingualpress' => $container[MultilingualPressIntegrator::class],
                'multilingual-press' => $container[MultilingualPressIntegrator::class],
                'woocommerce' => $container[WooCommerceIntegrator::class],
                'acf' => $container[ACFIntegrator::class],
            ]);
        };
        $container['Modules'] = function (Container $container) {
            return new CachingIterator(
                $container[ModulesProvider::class]->getIterator(),
                CachingIterator::FULL_CACHE
            );
        };
        $container[ModuleIntegrator::class] = function (Container $container) {
            return new ModuleIntegrator(
                $container['Modules']
            );
        };
        $container[ProcessorBusFactory::class] = function () {
            return new ProcessorBusFactory();
        };
    }

    /**
     * @inheritdoc
     */
    public function integrate(Container $container)
    {
        $container[ModuleIntegrator::class]->integrate();
    }
}
