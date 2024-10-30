<?php

namespace Locale\Locale\Support;

use Locale\Locale\Entities\JobTaxonomy;
use WP_Post;
use function get_term;
use function Locale\Functions\get_languages;
use function Locale\Functions\create_job_order;

/**
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class JobHandler extends \Locale\JobHandler
{
    /**
     * Creates a new job for the given post.
     *
     * @throws \Exception
     *
     * @param \WP_Post $post
     *
     * @return mixed
     */
    public static function orderJobFor(WP_Post $post)
    {
        $jobId = self::create_job_using_date();
        $jobTaxonomy = JobTaxonomy::incremental(get_term($jobId));

        foreach (get_languages() as $siteId => $language) {
            $jobTaxonomy->addTranslation($post->ID, $siteId);
        }

        return $jobTaxonomy->placeOrder();
    }
}
