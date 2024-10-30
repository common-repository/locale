<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\JobItem
 */

namespace Locale\JobItem;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\JobItem
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $c)
    {
        $c['JobItem.PostType'] = function ($c) {

            return new PostType($c['locale.plugin']);
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        add_action('init', [$container['JobItem.PostType'], 'register_post_type']);
        add_filter(
            'locale_job_item_row_actions',
            [$container['JobItem.PostType'], 'filter_row_actions'],
            10,
            2
        );
    }
}
