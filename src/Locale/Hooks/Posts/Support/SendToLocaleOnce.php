<?php

namespace Locale\Locale\Hooks\Posts\Support;

use Locale\Locale\Hooks\Action;
use function in_array;

/**
 * A base action class that orders a post translation to Locale, once.
 *
 * Since we are utilizing multiple hooks that orders translations to Locale, it is
 * important that they are only triggered once for a given WordPress post. This base
 * class ensures that that does happen.
 *
 * @package Locale\Locale\Hooks\Posts\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
abstract class SendToLocaleOnce extends Action
{
    /**
     * Returns the post that will be sent to Locale
     *
     * @return \WP_Post
     */
    abstract protected function getPost();

    /**
     * An array of WordPress post ids that has been sent to Locale for translation
     * already.
     *
     * @var int[]
     */
    protected static $sent = [];

    /**
     * Determines whether the post has already been sent to Locale earlier by another
     * action that extends this base class.
     *
     * @return bool
     */
    protected function hasNotBeenSent()
    {
        return ! in_array($this->getPost()->ID, self::$sent);
    }

    /**
     * @inheritDoc
     */
    protected function postHandlerActions()
    {
        self::$sent[] = $this->getPost()->ID;
    }
}
