<?php

namespace Locale\Locale\Hooks\Posts;

use Locale\Locale\Hooks\Filter;
use function array_filter;
use Locale\Locale\Entities\WordpressPost;

/**
 * Filters the provided set of WordPress posts to make sure that only job items
 * are being returned.
 *
 * @package Locale\Locale\Hooks\Posts
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class JobItemsOnly implements Filter
{
    /**
     * @param \WP_Post[] $posts
     *
     * @return \WP_Post[]
     */
    public function __invoke(array $posts)
    {
        return array_filter($posts, function ($post) {
            return WordpressPost::isJobItem($post);
        });
    }
}
