<?php

/**
 * Job
 *
 * @since   1.0.0
 * @package Locale\Job
 */

namespace Locale\Job;

use Brain\Nonces\WpNonce;
use Closure;
use Locale\Functions;
use Locale\Notice\TransientNoticeService;
use Locale\View\Job\OrderInfo;
use WP_Term;

/**
 * Class Taxonomy
 *
 * @since   1.0.0
 * @package Locale\Job
 */
class Taxonomy
{
    /**
     * @since 1.0.0
     */
    const COL_STATUS = 'locale_order_status';

    /**
     * @since 1.0.0
     */
    const COL_ACTIONS = 'locale_order_action';

    /**
     * Job Title and Description Form in edit page.
     *
     * @param string $value The views link. Untouched.
     *
     * @return string The untouched parameter
     * @todo  This is hooked in a filter, may create confusion about the value passed in.
     *        Is there a way to move into an action?
     *
     * @since 1.0.0
     */
    public function job_form($value)
    {
        $job = filter_input(
            INPUT_GET,
            'locale_job_id',
            FILTER_SANITIZE_NUMBER_INT
        );

        $job = get_term($job, 'locale_job');
        if (!$job instanceof WP_Term) {
            return $value;
        }

        $bind = (object)[
            'job' => $job,
            'nonce' => $this->nonce(),
        ];

        $closure = Closure::bind(
            function () {

                // @todo Make it a View.
                require Functions\get_template('/views/job/form-title-description.php');
            },
            $bind
        );

        $closure();

        return $value;
    }

    /**
     * Job Box in Edit Page
     *
     * @param string $value The views link. Untouched.
     *
     * @return string The untouched parameter
     * @todo  This is hooked in a filter, may create confusion about the value passed in.
     *        Is there a way to move into an action?
     *
     * @since 1.0.0
     */
    public function order_job_box_form($value)
    {
        $job = filter_input(
            INPUT_GET,
            'locale_job_id',
            FILTER_SANITIZE_NUMBER_INT
        );

        $job = get_term($job, 'locale_job');
        if (!$job instanceof WP_Term) {
            return $value;
        }

        (new OrderInfo($job->term_id))->render();

        return $value;
    }

    /**
     * Nonce
     *
     * @return \Brain\Nonces\WpNonce The nonce instance
     * @since 1.0.0
     */
    public function nonce()
    {
        return new WpNonce('update_job_info');
    }

    /**
     * Save Job Info based on request
     *
     * @return void
     * @since 1.0.0
     */
    public function job_info_save()
    {
        // Check Action and auth.
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
        if ('locale_job_info_save' !== $action) {
            return;
        }

        if (!$this->nonce()->validate() || !current_user_can('manage_options')) {
            wp_die('Cheating Uh?');
        }

        $job_id = (int)filter_input(
            INPUT_POST,
            'locale_job_id',
            FILTER_SANITIZE_NUMBER_INT
        );
        $job = get_term($job_id, 'locale_job');

        if ($job instanceof WP_Term) {
            $update = wp_update_term(
                $job->term_id,
                'locale_job',
                [
                    'name' => sanitize_text_field(
                        filter_input(
                            INPUT_POST,
                            'tag-name',
                            FILTER_SANITIZE_STRING
                        )
                    ),
                    'description' => filter_input(
                        INPUT_POST,
                        'description',
                        FILTER_SANITIZE_STRING
                    ),
                ]
            );

            if (is_wp_error($update)) {
                TransientNoticeService::add_notice(
                    esc_html__(
                        'Something went wrong. Please try again.',
                        'locale'
                    ),
                    'warning'
                );
            } else {
                TransientNoticeService::add_notice(
                    sprintf(
                        esc_html__(
                            'Job %s updated.',
                            'locale'
                        ),
                        '<strong>' . get_term_field(
                            'name',
                            $job_id,
                            'locale_job'
                        ) . '</strong>'
                    ),
                    'success'
                );
            }
        }

        if (!$job instanceof WP_Term) {
            TransientNoticeService::add_notice(
                esc_html__(
                    'Invalid job ID, the information could not be updated.',
                    'locale'
                ),
                'warning'
            );
        }

        wp_safe_redirect(wp_get_referer());

        die;
    }

    /**
     * Register Taxonomy
     *
     * @return void
     * @since 1.0.0
     */
    public function register_taxonomy()
    {
        register_taxonomy(
            'locale_job',
            'job_item',
            [
                'label' => esc_html__('Jobs', 'locale'),
                'labels' => [
                    'add_new_item' => esc_html__('Create new job', 'locale'),
                ],
                'public' => true,
                'capabilities' => [
                    'manage_terms' => 'manage_options',
                    'edit_terms' => 'do_not_allow',
                    'delete_terms' => 'manage_options',
                    'assign_terms' => 'manage_options',
                ],
            ]
        );
    }

    /**
     * Register Status for Post
     *
     * @since 1.0.0
     */
    public static function register_post_status()
    {
    }

    /**
     * Edit Row Actions
     *
     * @param string[] $columns The columns contain the values for the row.
     * @param \WP_Term $term The term instance related to the columns.
     *
     * @return array The columns content
     * @since 1.0.0
     */
    public static function modify_row_actions($columns, $term)
    {
        $new_columns = [
            'delete' => $columns['delete'],
            'view' => sprintf(
                '<a href="%s">%s</a>',
                self::get_job_link($term->term_id),
                esc_html__('View', 'locale')
            ),
        ];

        return $new_columns;
    }

    /**
     * Job Link
     *
     * @param int $job The job from which retrieve the term indetifier.
     *
     * @return string
     * @since 1.0.0
     */
    public static function get_job_link($job)
    {
        return get_admin_url(
            null,
            add_query_arg(
                [
                    'page' => 'locale-job',
                    'locale_job_id' => $job,
                    'post_type' => 'job_item',
                ],
                'admin.php'
            )
        );
    }

    /**
     * @param $columns
     *
     * @return array
     * @since 1.0.0
     */
    public static function modify_columns($columns)
    {
        unset($columns['cb']);
        unset($columns['slug']);
        unset($columns['posts']);

        // Add status ad second place.
        $columns = array_slice($columns, 0, 1)
            + [static::COL_STATUS => esc_html__('Status', 'locale')]
            + array_slice($columns, 1);

        $columns[static::COL_ACTIONS] = '';

        return $columns;
    }

    /**
     * @param $value
     * @param $column_name
     * @param $term_id
     *
     * @return string
     * @since 1.0.0
     */
    public static function print_column($value, $column_name, $term_id)
    {
        switch ($column_name) {
            case static::COL_STATUS:
                if (!get_term_meta($term_id, '_locale_order_id', true)) {
                    return esc_html__('New', 'locale');
                }

                $orderInfo = new OrderInfo($term_id);

                return sprintf(
                    esc_html($orderInfo->get_status_label())
                );
                break;
            case static::COL_ACTIONS:
                return sprintf(
                    '<a href="%s" class="button">%s</a>',
                    self::get_job_link($term_id),
                    esc_html__('Show job', 'locale')
                );
        }

        return $value;
    }

    /**
     * Edit Term Link for Job Taxonomy
     *
     * @param string $location The location link.
     * @param int $term_id The term id.
     * @param string $taxonomy The taxonomy name associated to the term.
     *
     * @return string The filtered location
     * @since 1.0.0
     */
    public function edit_term_link($location, $term_id, $taxonomy)
    {
        if ('locale_job' === $taxonomy) {
            $location = self::get_job_link($term_id);
        }

        return $location;
    }
}
