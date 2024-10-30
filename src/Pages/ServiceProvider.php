<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Pages
 */

namespace Locale\Pages;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;
use Locale\Setting\PluginSettings;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Pages
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Page.Job'] = function () {

            return new \Locale\Pages\Job();
        };
        $container['Page.PluginMainPage'] = function (Container $container) {

            return new \Locale\Pages\PluginMainPage($container['locale.plugin']);
        };
        $container['Page.PageOptions'] = function (Container $container) {

            return new \Locale\Pages\PageOptions($container['locale.plugin']);
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        // Page Job.
        add_action('admin_menu', [$container['Page.Job'], 'add_page']);
        add_action(
            'admin_title',
            [
                $container['Page.Job'],
                'reintroduce_page_title_in_header',
            ]
        );

        // Main Page.
        add_action('admin_menu', [$container['Page.PluginMainPage'], 'add_page']);
        add_action(
            'admin_menu',
            [$container['Page.PluginMainPage'], 'make_menu_items_coherent']
        );

        // Page Options.
        add_action('admin_menu', [$container['Page.PageOptions'], 'add_page']);
        add_action('admin_head', [$container['Page.PageOptions'], 'enqueue_style']);
        add_action('admin_head', [$container['Page.PageOptions'], 'enqueue_script']);

        add_filter(
            'option_page_capability_' . PluginSettings::OPTION_GROUP,
            [$container['Page.PageOptions'], 'filter_capabilities']
        );
    }
}
