<?php

/**
 * Job Handler
 *
 * @since   1.0.0
 * @package Locale
 */

namespace Locale;

use Exception;
use function get_current_blog_id;

/**
 * Class JobHandler
 *
 * @since   1.0.0
 * @package Locale
 */
class JobHandler
{
    /**
     * Create Job
     *
     * @param string $title The title for the job.
     *
     * @return int The newly term ID
     * @throws \Exception In case the job cannot be created.
     *
     * @since 1.0.0
     */
    public function create_job($title)
    {
        // Check if job already exists.
        $ids = term_exists($title, 'locale_job');

        if (!$ids) {
            // Create if it does not exists.
            $ids = wp_insert_term($title, 'locale_job');
        }

        if (is_wp_error($ids)) {
            throw new Exception($ids->get_error_message());
        }

        return (int)$ids['term_id'];
    }

    /**
     * Add Translation
     *
     * @param int $job The job ID.
     * @param int $post_id The post associated to this job item.
     * @param int $lang_id The language id of the job item.
     *
     * @since 1.0.0
     */
    public function add_translation($job, $post_id, $lang_id)
    {
        $labels = get_post_type_labels(get_post_type_object(get_post_type($post_id)));

        $translation_id = wp_insert_post(
            [
                'post_type' => 'job_item',
                'post_title' => sprintf(
                    __('%1$s: "%2$s"', 'locale'),
                    esc_html($labels->singular_name),
                    get_the_title($post_id)
                ),
                'meta_input' => [
                    '_locale_target_id' => $lang_id,
                    '_locale_post_id' => $post_id,
                    '_locale_source_site_id' => get_current_blog_id(),
                ],
            ]
        );

        // Retrieve the slug of the term because we are dealing with non hierarchical terms.
        $job = get_term_field('slug', $job, 'locale_job');

        wp_set_post_terms($translation_id, [$job], 'locale_job');
    }

    /**
     * Create new Job by Date
     *
     * @return int The new job ID
     * @throws \Exception In case the job cannot be created.
     *
     * @since 1.0.0
     */
    public static function create_job_using_date()
    {
        return (new self())->create_job(
            sprintf(esc_html__('Job %s', 'locale'), date('Y-m-d H:i:s'))
        );
    }
}
