<?php

# -*- coding: utf-8 -*-

namespace Locale\Module\WooCommerce;

use Locale\Module\Integrable;
use Locale\Module\Processor\ProcessorBusFactory;
use Locale\Module\WooCommerce\Processor\IncomingMetaProcessor;
use Locale\Module\WooCommerce\Processor\OutgoingMetaProcessor;
use Locale\Translation;

/**
 * Class Integrator
 *
 * @author Guido Scialfa <dev@guidoscialfa.com>
 */
class Integrator implements Integrable
{
    const DATA_NAMESPACE = 'woocommerce';

    const PRODUCT_META_PURCHASE_NOTE = 'purchase_note';

    /**
     * @var ProcessorBusFactory
     */
    private $processorBusFactory;

    /**
     * Integrator constructor
     * @param ProcessorBusFactory $processorBusFactory
     */
    public function __construct(ProcessorBusFactory $processorBusFactory)
    {
        $this->processorBusFactory = $processorBusFactory;
    }

    /**
     * @inheritDoc
     */
    public function integrate()
    {
        // TODO Temporary disabled until WooCommerce fields will be supported
        return;

        if (!function_exists('WC')) {
            return;
        }

        $processorBus = $this->processorBusFactory->create();
        $processorBus
            ->pushProcessor(new OutgoingMetaProcessor())
            ->pushProcessor(new IncomingMetaProcessor());

        add_action(
            'locale_outgoing_data',
            function (Translation $translation) use ($processorBus) {
                $processorBus->process($translation);
            }
        );
        add_action(
            'locale_updated_post',
            function (Translation $translation) use ($processorBus) {
                $processorBus->process($translation);
            }
        );
    }
}
