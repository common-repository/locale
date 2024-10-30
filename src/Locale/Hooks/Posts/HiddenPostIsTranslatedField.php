<?php

namespace Locale\Locale\Hooks\Posts;

use Locale\Locale\Hooks\Action;

/**
 * Inserts a hidden input named `post_is_translated` into the UI just before the
 * original checkbox with the same name, to serve as a work-around for
 * MultilingualPress' bug with the "Translation Completed" checkbox within a post's
 * meta-box, not allowing them to untick it, causing the post to always be marked as
 * "Translation Completed"
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class HiddenPostIsTranslatedField extends Action
{
    /**
     * @inheritDoc
     */
    protected function shouldRun()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function handle()
    {
        echo '<input type="hidden" name="post_is_translated" value="0">';
    }
}
