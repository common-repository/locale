<?php

namespace Locale\Locale\Entities;

use Locale\Locale\Hooks\Posts\SaveLastPublishedRevisionId;
use Locale\Utils\NetworkState;
use WP_Post;
use function get_post;
use function is_null;
use Locale\Locale\Support\Language;
use function Locale\Functions\current_lang_code;
use function Locale\Functions\get_languages;

/**
 * Represents a WordPress post.
 *
 * @package Locale\Locale\Entities
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class WordpressPost
{
    /**
     * The fields in a WordPress post that we can translate.
     *
     * @var array
     */
    const TRANSLATABLE_FIELDS = [
        'post_content',
        'post_title',
        'post_excerpt',
    ];

    /**
     * @var \WP_Post
     */
    protected $post;

    /**
     * The source language with which we're translating from.
     *
     * @var \Locale\Locale\Support\Language
     */
    protected $sourceLang;

    /**
     * @var \Locale\Utils\NetworkState
     */
    protected $networkState;

    public function __construct(WP_Post $post)
    {
        $this->sourceLang = new Language(
            empty($post->_locale_target_id)
                // When the _locale_target_id is empty, that means that we're
                // given the source post. It doesn't necessarily have the lang
                // metadata unlike the `job_item` posts, and since this is
                // the source, we can just use the current site's language.
                ? current_lang_code()
                : get_languages()[$post->_locale_target_id]->get_lang_code()
        );
        $this->post = $post;
        $this->networkState = NetworkState::create();
    }

    /**
     * Determines whether a post is a job item or not.
     *
     * @param \WP_Post $post
     *
     * @return bool
     */
    public static function isJobItem(WP_Post $post)
    {
        $languages = get_languages();

        return $post->post_type === 'job_item'
            && ! empty($post->_locale_post_id)
            && isset($languages[$post->_locale_target_id]);
    }

    /**
     * Determines whether a post is a revision or not.
     *
     * @param \WP_Post $post
     *
     * @return bool
     */
    public static function isRevision(WP_Post $post)
    {
        return wp_is_post_revision($post);
    }

    /**
     * Determines whether a post is neither a revision, nor a job item.
     *
     * @param \WP_Post $post
     *
     * @return bool
     */
    public static function isSource(WP_Post $post)
    {
        return ! self::isJobItem($post) && ! self::isRevision($post);
    }

    /**
     * Creates a new WordpressPost instance from the last published revision of the
     * given post.
     *
     * @param \WP_Post $post
     *
     * @return static|null
     */
    public static function fromLastPublishedRevision(WP_Post $post)
    {
        $revision = get_post($post->{SaveLastPublishedRevisionId::META_KEY});

        if (empty($revision)) {
            // TODO: Investigate why there are times when revision ids attributed to
            //  the posts do not match an actual post.
            return null;
        }

        return new static($revision);
    }

    /**
     * Returns the parent of the post identified by the `post_parent` field.
     *
     * @return array|\WP_Post|null
     */
    public function getPostParent()
    {
        return get_post($this->post->post_parent);
    }

    /**
     * Returns the source post of the job item, even if it is from a different
     * site.
     *
     * @return array|\WP_Post|null
     */
    public function getJobItemSourcePost()
    {
        $linkedPosts = apply_filters(
            'locale_relations',
            $this->post->_locale_source_site_id,
            $this->post->_locale_post_id
            // Not sure why, but it seems that we don't need to pass "page" here if
            // the target posts are pages. When setting to "page", the filter returns
            // nothing. Otherwise, it returns the pages.
        );
        $postId = $linkedPosts[$this->post->_locale_target_id];

        $this->networkState->switch_to($this->post->_locale_target_id);

        $post = get_post($postId);

        $this->networkState->restore();

        return $post;
    }

    /**
     * Extracts the fields that are supposed to be translated from a WordPress post
     * or page, then creates Translation objects from those.
     *
     * @see \Locale\Module\Mlp\Processor\PostDataBuilder::processIncoming()
     *
     * @return \Locale\Locale\Entities\Translation[]
     */
    public function getTranslatableFields()
    {
        $post = self::isJobItem($this->post) || self::isSource($this->post)
            // While job item's source post and regular posts should have their
            // content sent to Locale, revisions send its own content.
            ? $this->getSourcePost()
            : $this->post;
        if (self::isJobItem($this->post)) {
            // For job items, the id of the source post should be used to
            // identify the translation.
            $identifiedAs = get_post($this->post->_locale_post_id);
        } else if (self::isRevision($this->post)) {
            // However, for revisions, we'll use the post parent since it is the
            // source post
            $identifiedAs = get_post($this->post->post_parent);
        } else {
            $identifiedAs = $this->post;
        }

        if (is_null($post)) {
            return [];
        }

        $translations = [];

        foreach (self::TRANSLATABLE_FIELDS as $field) {
            if (empty($post->{$field})) {
                continue;
            }

            $translation = new Translation($this->sourceLang, $post, $field);
            $translation->identifiedAs($identifiedAs);

            $translations[] = $translation;
        }

        return $translations;
    }

    /**
     * Returns the source if the post is a job item. Otherwise, just the original
     * post is returned.
     *
     * @return array|\WP_Post|null
     */
    public function getSourcePost()
    {
        if (self::isSource($this->post)) {
            return $this->post;
        } else if (self::isRevision($this->post)) {
            return $this->getPostParent();
        }

        return $this->getJobItemSourcePost();
    }
}
