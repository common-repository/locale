<?php

# -*- coding: utf-8 -*-

namespace Locale\Module\WooCommerce\Processor;

use Locale\Exception\UnexpectedEntityException;
use Locale\Module\Processor\IncomingProcessor;
use Locale\Module\TranslationEntityAwareTrait;
use Locale\Module\WooCommerce\Integrator;
use Locale\Utils\NetworkState;
use Locale\Translation;
use WC_Product;

/**
 * Class IncomingMetaProcessor
 *
 * @author Guido Scialfa <dev@guidoscialfa.com>
 */
class IncomingMetaProcessor implements IncomingProcessor
{
    use TranslationEntityAwareTrait;

    /**
     * @inheritDoc
     */
    public function processIncoming(Translation $translation)
    {
        if (!$translation->is_valid()) {
            return null;
        }

        $networkState = NetworkState::create();
        $targetSiteId = $translation->target_site_id();

        $networkState->switch_to($targetSiteId);

        try {
            $product = $this->product($translation);
        } catch (UnexpectedEntityException $exc) {
            $networkState->restore();
            return;
        }

        $this->updatePurchaseNoteMeta($translation, $product);

        $product->save();

        $networkState->restore();
    }

    /**
     * Update Purchase Note Product Meta
     *
     * @param Translation $translation
     * @param WC_Product $product
     * @return $this
     */
    protected function updatePurchaseNoteMeta(Translation $translation, WC_Product $product)
    {
        $purchaseNote = (string)$translation->get_value(
            Integrator::PRODUCT_META_PURCHASE_NOTE,
            Integrator::DATA_NAMESPACE
        );
        $purchaseNote && $product->set_purchase_note($purchaseNote);

        return $this;
    }
}
