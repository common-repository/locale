<?php

// -*- coding: utf-8 -*-

/**
 * Bridge between the translation data and the MLP API
 */

namespace Locale\Module\Mlp;

use Locale\Domain\Language;
use Locale\Module\Processor\ProcessorBus;
use Locale\Translation;
use WP_Post;

/**
 * Class Connector
 *
 * A connector between Locale and MLP through the Adapter
 *
 * @package Locale\Module\Mlp
 */
class Connector
{
    /**
     * @var Utils\Registry;
     */
    private static $utils;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ProcessorBus
     */
    private $processorBus;

    /**
     * @return Utils\Registry
     */
    public static function utils()
    {
        self::$utils or self::$utils = new Utils\Registry();

        return self::$utils;
    }

    /**
     * Connector constructor
     *
     * @param Adapter $adapter
     * @param ProcessorBus $processorBus
     */
    public function __construct(Adapter $adapter, ProcessorBus $processorBus)
    {
        $this->adapter = $adapter;
        $this->processorBus = $processorBus;
    }

    /**
     * @wp-hook locale_outgoing_data
     *
     * @param Translation $data
     */
    public function prepare_outgoing(Translation $data)
    {
        $this->processorBus->process($data);
    }

    /**
     * @wp-hook locale_post_updater
     *
     * @return callable
     */
    public function prepare_updater()
    {
        return [$this, 'update_translations'];
    }

    /**
     * @param Translation $data
     *
     * @return null|WP_Post
     */
    public function update_translations(Translation $data)
    {
        if (!$data->is_valid()) {
            return null;
        }

        $this->processorBus->process($data);
    }

    /**
     * @wp-hook locale_current_language
     *
     * @return Language
     */
    public function current_language()
    {
        $site_id = get_current_blog_id();
        $lang_iso = $this->adapter->blog_language($site_id);
        $lang_name = $this->adapter->lang_by_iso($lang_iso);

        return new Language($lang_iso, $lang_name);
    }

    /**
     * @wp-hook locale_languages
     *
     * @param array $languages
     * @param int $site_id
     *
     * @return Language[]
     */
    public function related_sites($languages, $site_id)
    {
        $sites = $this->adapter->related_sites($site_id);

        foreach ($sites as $site) {
            $lang_iso = $this->adapter->blog_language($site);

            $languages[$site] = new Language($lang_iso, $this->adapter->lang_by_iso($lang_iso));
        }

        return $languages;
    }
}
