<?php

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Request
 */

namespace Locale\Request;

use Pimple\Container;
use Locale\Service\BootstrappableServiceProvider;
use Locale\Request\Api;
use Locale\Auth;
use Brain\Nonces;
use Locale\JobHandler;

/**
 * Class ServiceProvider
 *
 * @since   1.0.0
 * @package Locale\Request
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['Request.AddTranslation'] = function () {

            return new Api\AddTranslation(
                new Auth\Validator(),
                new Nonces\WpNonce('add_translation'),
                new JobHandler()
            );
        };
        $container['Request.OrderJob'] = function () {

            return new Api\OrderJob(
                new Auth\Validator(),
                new Nonces\WpNonce('order_job')
            );
        };
        $container['Request.UpdateJobOrderStatus'] = function () {

            return new Api\UpdateJobOrderStatus(
                new Auth\Validator(),
                new Nonces\WpNonce('update_job')
            );
        };
        $container['Request.ImportJob'] = function () {

            return new Api\ImportJob(
                new Auth\Validator(),
                new Nonces\WpNonce('import_job')
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        // Add Translation.
        add_action('load-edit.php', [$container['Request.AddTranslation'], 'handle']);
        add_action('load-post.php', [$container['Request.AddTranslation'], 'handle']);

        // Import Translation.
        add_action(
            'admin_post_locale_import_job',
            [$container['Request.ImportJob'], 'handle']
        );

        // Order Job.
        add_action(
            'admin_post_locale_order_job',
            [
                $container['Request.OrderJob'],
                'handle',
            ]
        );

        // Update Job Order Status.
        add_action(
            'admin_post_locale_update_job',
            [$container['Request.UpdateJobOrderStatus'], 'handle']
        );
    }
}
