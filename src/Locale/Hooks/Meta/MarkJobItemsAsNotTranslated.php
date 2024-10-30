<?php

namespace Locale\Locale\Hooks\Meta;

use function array_key_exists;
use function get_term;
use function delete_post_meta;
use Locale\Locale\Entities\WordpressPost;
use Locale\Locale\Entities\JobTaxonomy;

/**
 * When the `_locale_order_id` meta of a job gets set with a value, each of the
 * posts under it will have their `_post_is_translated` meta deleted.
 *
 * @package Locale\Locale\Hooks\Meta
 *
 * @author Peter Cortez <peter@locale.to>
 */
class MarkJobItemsAsNotTranslated extends MarkJobItemsAsTranslated
{
    /**
     * @inheritDoc
     */
    protected function shouldRun()
    {
        return $this->metaKey === '_locale_order_id'
            && $this->metaValue !== null;
    }

    /**
     * @inheritDoc
     */
    protected function handle()
    {
        $jobItems = JobTaxonomy::getItemsIncludingOrigPost(
            get_term($this->objectId)
        );

        foreach ($jobItems as $jobItem) {
            $post = (new WordpressPost($jobItem))->getSourcePost();

            if (WordpressPost::isJobItem($jobItem)) {
                $this->networkState->switch_to($jobItem->_locale_target_id);
                delete_post_meta($post->ID, '_post_is_translated');
                $this->networkState->restore();
            } else {
                delete_post_meta($post->ID, '_post_is_translated');
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * A work-around for overriding MultilingualPress' automatic setting of
     * `_post_is_translated` by checking whether the post `post_is_translated`
     * parameter is set to 1. To make our changes persist over theirs, we're going
     * to remove the post data instead.
     */
    protected function postHandlerActions()
    {
        parent::postHandlerActions();

        if (
            $this->shouldRun() && array_key_exists('post_is_translated', $_POST)
        ) {
            $_POST['post_is_translated'] = 0;
        }
    }
}
