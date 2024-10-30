<?php

namespace Locale\Locale\Entities;

use Locale\Locale\Support\JobHandler;
use WP_Term;
use function get_post;
use function get_term;
use function get_term_meta;
use function in_array;
use function Locale\Functions\create_job_order;
use function Locale\Functions\get_job_items;
use function Locale\Functions\set_unique_term_meta;

/**
 * Represents a WordPress job taxonomy.
 *
 * @package Locale\Locale\Entities
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class JobTaxonomy
{
    /**
     * @var string
     */
    const TRANSLATION_MODE_FIELD_NAME = '_locale_translation_mode';

    /**
     * The `_locale_translation_mode` WordPress term meta for jobs that will be
     * translated in incremental mode.
     *
     * @var string
     */
    const TRANSLATION_MODE_INCREMENTAL = 'incremental';

    /**
     * The `_locale_translation_mode` WordPress term meta for jobs that will be
     * translated in incremental synchronization.
     *
     * @var string
     */
    const TRANSLATION_MODE_SYNC = 'synchronization';

    /**
     * A list of humanized job translation modes, to be used for display to the
     * user.
     *
     * @var string[]
     */
    const HUMAN_READABLE_TRANSLATION_MODES = [
        self::TRANSLATION_MODE_SYNC => 'Synchronization',
        self::TRANSLATION_MODE_INCREMENTAL => 'Incremental',
    ];

    /**
     * @var \WP_Term
     */
    protected $job;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var \Locale\Locale\Support\JobHandler
     */
    protected $jobHandler;

    /**
     * @param \WP_Term $job
     * @param          $mode
     */
    protected function __construct(WP_Term $job, $mode)
    {
        $this->job = $job;
        $this->mode = $mode;
        $this->jobHandler = new JobHandler;

        set_unique_term_meta($job, self::TRANSLATION_MODE_FIELD_NAME, $mode);
    }

    /**
     * Prepares a job with the correct metadata for incremental translations
     *
     * @param \WP_Term $job
     *
     * @return static
     */
    public static function incremental(WP_Term $job)
    {
        return new static($job, self::TRANSLATION_MODE_INCREMENTAL);
    }

    /**
     * Prepares a job with the correct metadata for synchronization translations
     *
     * @param \WP_Term $job
     *
     * @return static
     */
    public static function synchronization(WP_Term $job)
    {
        return new static($job, self::TRANSLATION_MODE_SYNC);
    }

    /**
     * Determines whether the job submits its translations to Locale as
     * incremental
     *
     * @param \WP_Term $job
     *
     * @return bool
     */
    public static function isIncremental(WP_Term $job)
    {
        return self::getTranslationMode($job) === self::TRANSLATION_MODE_INCREMENTAL;
    }

    /**
     * Determines whether the job submits its translations to Locale as
     * synchronization
     *
     * @param \WP_Term $job
     *
     * @return bool
     */
    public static function isSynchronization(WP_Term $job)
    {
        return self::getTranslationMode($job) === self::TRANSLATION_MODE_SYNC;
    }

    /**
     * Returns the {@see JobTaxonomy::TRANSLATION_MODE_FIELD_NAME} of a job.
     *
     * @param \WP_Term $job
     *
     * @return mixed
     */
    public static function getTranslationMode(WP_Term $job)
    {
        return get_term_meta(
            $job->term_id, self::TRANSLATION_MODE_FIELD_NAME, true
        );
    }

    /**
     * Returns the job's items, including the original post at the end of the
     * list
     *
     * @todo Use self::getOrigPost() for getting the original post.
     *
     * @param \WP_Term $job
     *
     * @return array
     */
    public static function getItemsIncludingOrigPost(WP_Term $job)
    {
        $jobItems = get_job_items($job->term_id);
        $sourcePostIds = [];

        foreach ($jobItems as $jobItem) {
            $sourcePostId = $jobItem->_locale_post_id;

            if (! in_array($sourcePostId, $sourcePostIds)) {
                // Here, we are including each job item's source post ONCE so
                // that Locale can know from which post the translation should be
                // based on. This identification is done by Locale, by comparing the
                // locale
                $jobItems[] = get_post($sourcePostId);
                $sourcePostIds[] = $sourcePostId;
            }
        }

        return $jobItems;
    }

    /**
     * Returns the original post with which this translation job is based on.
     *
     * @param \WP_Term $job
     *
     * @return \WP_Post|null
     */
    public static function getOrigPost(WP_Term $job)
    {
        $jobItems = get_job_items($job->term_id);

        foreach ($jobItems as $jobItem) {
            if (! empty($jobItem->_locale_post_id)) {
                return get_post($jobItem->_locale_post_id);
            }
        }

        return null;
    }

    /**
     * Adds a translation to the current job taxonomy.
     *
     * @param int $postId
     * @param int $langId
     *
     * @return static
     */
    public function addTranslation($postId, $langId)
    {
        $this->jobHandler->add_translation(
            $this->job->term_id, $postId, $langId
        );

        return $this;
    }

    /**
     * Places a translation order for this job taxonomy.
     *
     * @return mixed
     */
    public function placeOrder()
    {
        return create_job_order(
            get_term($this->job->term_id, 'locale_job')
        );
    }
}
