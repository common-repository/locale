<?php

namespace Locale\Locale\Hooks\Posts;

use Locale\Locale\Hooks\Action;
use function get_post;

/**
 * Saves the revision id of a post when it was last published, so that it can be
 * retrieved later as a "previous" translation to be sent to Locale when the post is
 * translated.
 *
 * This works by looking at a saved post revision's source post, to see if its status
 * is published or not. If so, The `_locale_last_published_revision_id` post meta
 * will be set to the revision id.
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class SaveLastPublishedRevisionId extends Action
{
    /**
     * The meta name that is used to identify the last published revision id of a
     * post.
     *
     * @var string
     */
    const META_KEY = '_locale_last_published_revision_id';

    /**
     * @inheritdoc
     */
    protected $hookArgsNames = [
        'revisionId',
    ];

    /**
     * @var int
     */
    protected $revisionId;

    /**
     * @var array|\WP_Post|null
     */
    protected $revision;

    /**
     * @var array|\WP_Post|null
     */
    protected $post;

    /**
     * @inheritDoc
     */
    protected function shouldRun()
    {
        $this->revision = get_post($this->revisionId);
        $this->post = get_post($this->revision->post_parent);

        return $this->post->post_status === 'publish';
    }

    /**
     * @inheritDoc
     */
    protected function handle()
    {
        update_post_meta($this->post->ID, self::META_KEY, $this->revision->ID);
    }
}
