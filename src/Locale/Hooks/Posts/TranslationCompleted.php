<?php

namespace Locale\Locale\Hooks\Posts;

use function get_post;
use Locale\Locale\Hooks\ListColumn;

/**
 * Represents the "Translation Completed" column in the posts and pages list, and
 * will have a value of "Yes" if the `_post_is_translated` meta of a post is equal to
 * a (string) 1, otherwise, "No".
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslationCompleted extends ListColumn
{
    /**
     * @inheritdoc
     */
    protected $hookArgsNames = [
        'arg1',
        'arg2',
    ];

    /**
     * When cast as a column, this will be equivalent to the post type of a post.
     * Otherwise, it will be equivalent to the id of the post.
     *
     * @var string|int
     */
    protected $arg2;

    /**
     * @inheritDoc
     */
    protected function getName()
    {
        return '_post_is_translated';
    }

    /**
     * @inheritDoc
     */
    protected function getDisplayName()
    {
        return 'Translation Completed';
    }

    /**
     * @inheritDoc
     */
    protected function getCellValue()
    {
        $post = get_post($this->arg2);

        return $post !== null && (int) $post->_post_is_translated === 1
            ? 'Yes'
            : 'No';
    }
}
