<?php

namespace Locale\Functions;

use DateTime;
use Exception;
use Locale\Api;
use Locale\Api\ApiException;
use Locale\Domain\Job;
use Locale\Locale\Entities\JobTaxonomy;
use Locale\Plugin;
use Locale\Setting\PluginSettings;
use Locale\Translation;
use Locale\Utils\TimeZone;
use WP_Term;
use function apply_filters;
use function do_action;
use Locale\Locale;
use function esc_html__;
use function get_post;
use function get_term_meta;
use function in_array;
use function is_callable;

/**
 * Update Job
 *
 * @param WP_Term $job The job term to use to retrieve the info to update the post.
 *
 * @return void
 * @throws Exception In case the job ID cannot be retrieved.
 */
function job_update(WP_Term $job)
{
    $job_id = get_term_meta($job->term_id, '_locale_order_id', true);
    $jobStatus = get_term_meta($job->term_id, '_locale_order_status', true);

    if (!$job_id) {
        throw new Exception(
            esc_html__('Invalid Job ID, impossible to update the job', 'locale')
        );
    } else if ($jobStatus === Locale\Entities\TranslationJob::STATUS_IMPORTED) {
        throw new Locale\Exceptions\JobsCanOnlyBeImportedOnceException;
    }

    $translationJob = Locale\Entities\TranslationJob::find($job_id);
    $download = new Locale\Support\TranslationImport($translationJob);

    foreach ($download->getTranslations() as $incoming_translation) {
        $translation = Translation::for_incoming((array)$incoming_translation);

        /**
         * Fires for each item or translation received from the API.
         *
         * @param Translation $translation Translation data built from data received from API
         */
        do_action('locale_incoming_data', $translation);

        /**
         * Filters the updater that executed have to return the updated post
         */
        $updater = apply_filters('locale_post_updater', null, $translation);
        is_callable($updater) and $updater($translation);

        /**
         * Fires after the updater has updated the post.
         *
         * @param Translation $translation Translation data built from data received from API
         */
        do_action('locale_updated_post', $translation);
    }

    set_unique_term_meta(
        $job,
        '_locale_order_status',
        Locale\Entities\TranslationJob::STATUS_IMPORTED
    );
    set_unique_term_meta(
        $job,
        '_locale_order_imported_at',
        (new DateTime('now', (new TimeZone())->value()))->getTimestamp()
    );
}

/**
 * Retrieve job items status
 *
 * @param WP_Term $job_term The term instance to retrieve the job data.
 *
 * @return string The status of the job
 *
 * @throws ApiException If something went wrong during retrieve the job data.
 */
function job_items_status(WP_Term $job_term)
{
    $job_id = get_term_meta(
        $job_term->term_id,
        '_locale_order_id',
        true
    );

    if (!$job_id) {
        return null;
    }

    $translationJob = new Locale\Entities\TranslationJob($job_id);

    return $translationJob->hydrate()->getStatus();
}

/**
 * Get Global Job status
 *
 * @param \WP_Term $job_term The term instance to retrieve the job data.
 *
 * @return string The translation status label
 * @throws ApiException If something went wrong during retrieve the job data.
 */
function job_global_status(WP_Term $job_term)
{
    return job_items_status($job_term);
}

/**
 * Job Order
 *
 * @throws ApiException In case the job cannot be created.
 *
 * @param WP_Term $job_term The job term associated.
 *
 * @return mixed Whatever the update_term_meta returns
 * @since 1.0.0
 *
 * @api
 */
function create_job_order(WP_Term $job_term)
{
    $jobItems = [JobTaxonomy::getOrigPost($job_term)];
    $translationJobRequest = new Locale\Support\TranslationJobRequest($job_term);

    foreach ($jobItems as $jobItem) {
        $current = new Locale\Entities\WordpressPost($jobItem);
        $previous = Locale\Entities\WordpressPost::fromLastPublishedRevision($jobItem);

        foreach ($current->getTranslatableFields() as $field) {
            $translationJobRequest->add($field);
        }

        if (
            Locale\Entities\JobTaxonomy::isIncremental($job_term)
            && $previous !== null
        ) {
            foreach ($previous->getTranslatableFields() as $field) {
                $translationJobRequest->addPrevious($field);
            }
        }
    }

    $job = $translationJobRequest->submit();

    // Set the order ID of this job.
    set_unique_term_meta($job_term, '_locale_order_id', $job->getId());

    // Set the default order status.
    return set_unique_term_meta(
        $job_term,
        '_locale_order_status',
        esc_html__($job->getStatus(), 'locale')
    );
}
