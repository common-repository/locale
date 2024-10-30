<?php

# -*- coding: utf-8 -*-

namespace Locale\Module\ACF\Processor;

use Locale\Exception\UnexpectedEntityException;
use Locale\Module\Processor\IncomingProcessor;
use Locale\Module\TranslationEntityAwareTrait;
use Locale\Module\ACF\Integrator;
use Locale\Utils\NetworkState;
use Locale\Translation;

/**
 * Class IncomingMetaProcessor
 *
 * Will receive the ACF data and will import
 *
 * @package Locale\Module\ACF\Processor
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
            $post = $this->post($translation);
        } catch (UnexpectedEntityException $exc) {
            $networkState->restore();
            return;
        }

        $translatedFieldsToImport = [];
        if ($translation->has_value(Integrator::ACF_FIELDS, Integrator::_NAMESPACE)) {
            $translatedFieldsToImport = $translation->get_value(
                Integrator::ACF_FIELDS,
                Integrator::_NAMESPACE
            );
        }

        $notTranslatedFieldsToImport = [];
        if ($translation->has_meta(Integrator::NOT_TRANSLATABE_ACF_FIELDS, Integrator::_NAMESPACE)) {
            $notTranslatedFieldsToImport = $translation->get_meta(
                Integrator::NOT_TRANSLATABE_ACF_FIELDS,
                Integrator::_NAMESPACE
            );
        }

        $fieldsToImport = array_merge($translatedFieldsToImport, $notTranslatedFieldsToImport);
        if (!empty($fieldsToImport)) {
            foreach ($fieldsToImport as $fieldKey => $fieldValue) {
                update_post_meta($post->ID, $fieldKey, $fieldValue);
            }
        }

        $networkState->restore();
    }
}
