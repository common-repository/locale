<?php

namespace Locale\Locale\Hooks\Posts;

use function in_array;
use Locale\Locale\Hooks\Posts\Support\SendToLocaleOnce;

/**
 * Creates a Locale job when a post transitions from `draft` to `published`, then
 * sends it to Locale for translation.
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class SendToLocaleAfterPublish extends SendToLocaleOnce
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
            && $this->post->post_status === 'publish'
            && $this->hasNotBeenSent();
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    protected function handle()
    {
        \Locale\Locale\Support\JobHandler::orderJobFor($this->post);
    }

    /**
     * @inheritDoc
     */
    protected function getPost()
    {
        return $this->post;
    }
}
