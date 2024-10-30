<?php

namespace Locale\Locale\Hooks\Posts;

use function get_post;
use Locale\Locale\Hooks\Posts\Support\SendToLocaleOnce;

/**
 * Sends the published post to Locale after it has been updated.
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class SendToLocalePublishedUpdate extends SendToLocaleOnce
{
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

        return $this->post->post_status === 'publish' && $this->hasNotBeenSent();
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
