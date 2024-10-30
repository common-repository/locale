<?php

namespace Locale\Functions;

use Locale\Api\ApiException;
use Locale\Locale\Entities\JobTaxonomy;
use Locale\Notice\TransientNoticeService;
use Locale\JobHandler;
use Locale\Job;
use function esc_html__;
use function get_term;
use function sprintf;

/**
 * Hide Term Slug Wrap
 *
 * @todo  Create unique css for the entire plugin to register and load when request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function job_hide_slug() {

    ?>
    <style>
        .form-field.term-slug-wrap, input[name=slug], span.title {
            display: none;
        }
    </style>
    <?php
}

/**
 * Bulk translate job
 *
 * @throws \Exception If isn't possible to create a job.
 *
 * @param string $redirect_to The redirect to string.
 * @param string $action      The currently action to take.
 * @param array  $post_ids    The posts ids list.
 *
 * @return string The redirect_to value
 */
function bulk_translate_jobs_by_request_posts( $redirect_to, $action, $post_ids ) {

    $languages = \filter_input( INPUT_GET, 'locale_bulk_languages', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
    $job   = \filter_input( INPUT_GET, 'locale_job_id', FILTER_SANITIZE_NUMBER_INT );

    // Do not perform anything if job hasn't been sent.
    if ( ! $job ) {
        TransientNoticeService::add_notice(
            esc_html__( 'You must select a job in order to translate items.', 'locale' ),
            'warning'
        );

        return wp_get_referer();
    }

    // Be sure we have only valid elements.
    $languages = array_filter( $languages );

    if ( 'bulk_translate' !== $action || empty( $post_ids ) || ! $languages ) {
        return $redirect_to;
    }

    // Isn't a number, don't try to convert to number -1.
    try {
        if ( '-1' === $job ) {
            $job = JobHandler::create_job_using_date();
        }
    } catch ( \Exception $e ) {
        TransientNoticeService::add_notice( $e->getMessage(), 'warning' );

        return wp_get_referer();
    }

    try {
        // Iterate translations.
        $jobTaxonomy = JobTaxonomy::synchronization(get_term($job));
        foreach ( $post_ids as $post_id ) {
            foreach ( $languages as $lang_id ) {
                $jobTaxonomy->addTranslation($post_id, $lang_id);
            }
        }
        $jobTaxonomy->placeOrder();
    } catch (ApiException $e) {
        TransientNoticeService::add_notice(
            sprintf(
                esc_html__('Locale: %s', 'locale'),
                $e->getMessage()
            ),
            'error'
        );
    }

    $redirect_to = Job\Taxonomy::get_job_link( $job );

    return $redirect_to;
}

/**
 * Retrieve Jobs
 *
 * @since 1.0.0
 *
 * @return array A list of jobs.
 */
function jobs() {

    $terms = get_terms(
        [
            'taxonomy'   => 'locale_job',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key'     => '_locale_order_id',
                    'compare' => 'NOT EXISTS',
                    'value'   => '',
                ],
            ],
        ]
    );

    $jobs = [];
    foreach ( $terms as $term ) {
        $jobs[ $term->term_id ] = $term->name;
    }

    return $jobs;
}
