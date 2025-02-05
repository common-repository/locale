<?php

/**
 * Job Updater
 *
 * @package Locale\Admin
 */

namespace Locale;

use Locale\Locale\Entities\JobTaxonomy;
use function get_term;

/**
 * @since   1.0.0
 * @package Locale\Admin
 */
class JobUpdater
{
    /**
     * Append to Title
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $append_to_title = '';

    /**
     * Set Hooks
     *
     * @since 1.0.0
     *
     * Add action and filters to make the thing work
     */
    public function init()
    {
        add_action(
            'locale_action_job_add_translation',
            [$this, 'force_ancestors_in_job'],
            10,
            4
        );
    }

    /**
     * Run after all the jobs items referring a post have been created and assigned to a job, extracts the
     * ancestor ids of the post being translated and add those ancestors to cart as well, ith they are not there
     * already.
     *
     * @wp-hook locale_action_job_add_translation
     *
     * @param int $job The job ID.
     * @param int $post_id The post ID.
     * @param \Locale\Domain\Language[] $languages A list of languages.
     *
     * @return int The job ID
     * @since   1.0.0
     */
    public function force_ancestors_in_job($job, $post_id, $languages)
    {
        $post = get_post($post_id);

        if (!$post || !apply_filters('locale_force_add_parent_translations', false, $post)) {
            return $job;
        }

        $ancestors = wp_parse_id_list(get_post_ancestors($post));

        if (!$ancestors) {
            return $job;
        }

        $job_items = get_posts(
            [
                'fields' => 'ids',
                'post_type' => 'job_item',
                'nopaging' => true,
                'tax_query' => [
                    [
                        'taxonomy' => 'locale_job',
                        'terms' => [$job],
                        'field' => 'term_id',
                    ],
                ],
            ]
        );

        $already_in_job = [];
        foreach ($job_items as $job_item_id) {
            $lang = get_post_meta($job_item_id, '_locale_target_id', true);
            if (!$lang || !in_array($lang, $languages, true)) {
                continue;
            }

            $added_ancestor_id = (int)get_post_meta(
                $job_item_id,
                '_locale_post_id',
                true
            );
            if ($added_ancestor_id && in_array($added_ancestor_id, $ancestors, true)) {
                empty($already_in_job[$lang]) and $already_in_job[$lang] = [];
                $already_in_job[$lang][$added_ancestor_id] = true;
            }
        }

        $original_title = get_the_title($post);
        $ancestor_hint = esc_html__('ancestor of: "%s"', 'locale');
        $this->append_to_title = '(' . sprintf($ancestor_hint, $original_title) . ')';

        $jobTaxonomy = JobTaxonomy::incremental(get_term($job));

        add_filter('wp_insert_post_data', [$this, 'update_job_item_title'], 10);

        foreach ($languages as $lang_id) {
            foreach ($ancestors as $ancestor_id) {
                if (empty($already_in_job[$lang_id][$ancestor_id])) {
                    $jobTaxonomy->addTranslation($ancestor_id, $lang_id);
                }
            }
        }

        $this->append_to_title = '';
        remove_filter('wp_insert_post_data', [$this, 'update_job_item_title'], 10);

        return $job;
    }

    /**
     * Filter the cart item post data being added, appending to title an hint that post was added automatically because
     * ancestor of another post.
     *
     * @param array $data Data to update.
     *
     * @return array data updated
     * @since 1.0.0
     */
    public function update_job_item_title(array $data)
    {
        if ($this->append_to_title & !empty($data['post_type']) & $data['post_type'] === 'job_item') {
            empty($data['post_title']) and $data['post_title'] = '';
            $data['post_title'] and $data['post_title'] .= ' ';

            $data['post_title'] .= $this->append_to_title;
        }

        return $data;
    }
}
