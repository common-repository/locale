<?php

namespace Locale\Locale\Hooks\Posts;

use function in_array;
use Locale\Locale\Hooks\Action;

/**
 * Forcefully creates a new revision of a post when it gets published, even if there
 * are no changes to the post's contents.
 *
 * The forced revision creation is done by using _wp_put_post_revision() instead of
 * wp_save_post_revision() which checks whether the fields of the last revision and
 * the current post matches. In our case, we don't want that because we always want
 * to keep track of the last published version.
 *
 * @see \Locale\Locale\Hooks\Posts\SaveLastPublishedRevisionId To find out more about
 * why we are forcing the revision creation.
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class CreateRevisionAfterPublish extends Action
{
    /**
     * @inheritdoc
     */
    protected $hookArgsNames = [
        'postId',
        'post',
        'oldStatus',
    ];

    /**
     * @var int
     */
    protected $postId;

    /**
     * @var \WP_Post
     */
    protected $post;

    /**
     * @var string
     */
    protected $oldStatus;

    /**
     * @inheritDoc
     */
    protected function shouldRun()
    {
        return in_array($this->oldStatus, ['pending', 'draft'])
            && $this->post->post_status === 'publish';
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    protected function handle()
    {
        _wp_put_post_revision($this->postId);
    }
}
