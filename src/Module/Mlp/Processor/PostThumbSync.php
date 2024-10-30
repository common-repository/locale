<?php

// -*- coding: utf-8 -*-

namespace Locale\Module\Mlp\Processor;

use Locale\Module\Mlp\Adapter;
use Locale\Module\Mlp\Connector;
use Locale\Module\ModuleIntegrator;
use Locale\Utils\NetworkState;
use Locale\Module\Processor\IncomingProcessor;
use Locale\Translation;

/**
 * Class PostThumbSync
 *
 * @package Locale\Module\Mlp\Processor
 */
class PostThumbSync implements IncomingProcessor
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * TaxonomiesSync constructor
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function processIncoming(Translation $translation)
    {
        $saved_post = $translation->get_meta(
            PostSaver::SAVED_POST_KEY,
            ModuleIntegrator::POST_DATA_NAMESPACE
        );

        if (!$saved_post || !post_type_supports($saved_post->post_type, 'thumbnail')) {
            return;
        }

        $sync_on_update = true;
        $isUpdateKey = $translation->get_meta(
            PostDataBuilder::IS_UPDATE_KEY,
            ModuleIntegrator::POST_DATA_NAMESPACE
        );

        if ($isUpdateKey) {
            $sync_on_update = apply_filters(
                'locale_mlp_module_sync_post_thumb_on_update',
                true,
                $translation
            );
        }

        if (!$sync_on_update) {
            return;
        }

        $source_site_id = $translation->source_site_id();
        $networkState = NetworkState::create();

        $networkState->switch_to($source_site_id);
        $source_thumb_id = get_post_thumbnail_id($translation->source_post_id());
        $networkState->restore();

        $target_thumb_id = 0;

        if ($source_thumb_id) {
            $image_sync = Connector::utils()->image_sync($this->adapter);
            $target_thumb_id = $image_sync->copy_image(
                $source_thumb_id,
                $source_site_id,
                $translation->target_site_id()
            );
        }

        if ($target_thumb_id) {
            switch_to_blog($translation->target_site_id());
            set_post_thumbnail($saved_post, $target_thumb_id);
            restore_current_blog();
        }
    }
}
