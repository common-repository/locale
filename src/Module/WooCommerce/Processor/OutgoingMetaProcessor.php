<?php

# -*- coding: utf-8 -*-

namespace Locale\Module\WooCommerce\Processor;

use Locale\Module\Processor\OutgoingProcessor;
use Locale\Module\WooCommerce\Integrator;
use Locale\Translation;

/**
 * Class MetaProcessor
 *
 * @author Guido Scialfa <dev@guidoscialfa.com>
 */
class OutgoingMetaProcessor implements OutgoingProcessor
{
    /**
     * @inheritDoc
     */
    public function processOutgoing(Translation $translation)
    {
        $product = wc_get_product($translation->source_post_id());

        if (!$product) {
            return;
        }

        $translationData = [
            Integrator::PRODUCT_META_PURCHASE_NOTE => $product->get_purchase_note('edit'),
        ];

        foreach ($translationData as $metaKey => $metaValue) {
            $translation->set_value($metaKey, $metaValue, Integrator::DATA_NAMESPACE);
        }
    }
}
