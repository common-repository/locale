<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Job
 */

namespace Locale\Job;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Job
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Job.Taxonomy'] = function () {

            return new Taxonomy();
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        // Taxonomy.
        add_action(
            'init',
            [$container['Job.Taxonomy'], 'register_taxonomy']
        );
        add_action(
            'manage_locale_job_custom_column',
            [$container['Job.Taxonomy'], 'print_column'],
            10,
            3
        );
        add_action(
            'admin_post_locale_job_info_save',
            [$container['Job.Taxonomy'], 'job_info_save']
        );
        add_action(
            'locale_job_item_table_views',
            [$container['Job.Taxonomy'], 'job_form']
        );
        add_action(
            'locale_job_item_table_views',
            [$container['Job.Taxonomy'], 'order_job_box_form']
        );

        add_filter(
            'manage_edit-locale_job_columns',
            [$container['Job.Taxonomy'], 'modify_columns']
        );
        add_filter(
            'locale_job_row_actions',
            [$container['Job.Taxonomy'], 'modify_row_actions'],
            10,
            2
        );
        add_filter(
            'get_edit_term_link',
            [$container['Job.Taxonomy'], 'edit_term_link'],
            10,
            3
        );
    }
}
