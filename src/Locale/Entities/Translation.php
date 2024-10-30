<?php

namespace Locale\Locale\Entities;

use WP_Post;
use Locale\Locale\Support\Language;
use Locale\Locale\Support\TranslationId;
use Locale\Locale\Support\Contracts\Arrayable;
use function is_null;

/**
 * Represents a transformation of a WordPress post into a Locale translation entity.
 *
 * @package Locale\Locale\Entities
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class Translation implements Arrayable
{
    /**
     * The identifier used to determine whether a WordPress post is a post.
     *
     * @var string
     */
    const POST_TYPE_POST = 'post';

    /**
     * The identifier used to determine whether a WordPress post is a page.
     *
     * @var string
     */
    const POST_TYPE_PAGE = 'page';

    /**
     * The source language with which we're translating from.
     *
     * @var \Locale\Locale\Support\Language
     */
    protected $sourceLang;

    /**
     * The WordPress post from whom this Locale Translation is being created from.
     *
     * @var \WP_Post
     */
    protected $post;

    /**
     * What field in the WordPress post is supposed to be translated? Some examples
     * are: post_content, post_title, post_excerpt, etc.
     *
     * @var string
     */
    protected $postFieldName;

    /**
     * The WordPress post that will be used in creating the unique id for this
     * translation
     *
     * @var \WP_Post
     */
    protected $identifiedAs;

    public function __construct(Language $sourceLang, WP_Post $post, $postFieldName){
        $this->sourceLang = $sourceLang;
        $this->post = $post;
        $this->postFieldName = $postFieldName;
    }

    /**
     * Uses the given post's id in constructing the unique id for this translation.
     *
     * @param \WP_Post $post
     *
     * @return void
     */
    public function identifiedAs(WP_Post $post)
    {
        $this->identifiedAs = $post;
    }

    /**
     * Returns the unique id for this translation instance, and will be later used in
     * importing the translation back to WordPress
     *
     * @return \Locale\Locale\Support\TranslationId
     */
    public function getId()
    {
        return new TranslationId(
            is_null($this->identifiedAs)
                ? $this
                : new static(
                    $this->sourceLang,
                    $this->identifiedAs,
                    $this->postFieldName
                )
        );
    }

    /**
     * Determines whether the WordPress post is a post or not.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->post->post_type === self::POST_TYPE_POST;
    }

    /**
     * Determines whether the WordPress post is a page or not.
     *
     * @return bool
     */
    public function isPage()
    {
        return $this->post->post_type === self::POST_TYPE_PAGE;
    }

    /**
     * Returns the $postFieldName property of this object.
     *
     * @return string
     */
    public function getPostFieldName()
    {
        return $this->postFieldName;
    }

    /**
     * Returns the post associated with this translation.
     *
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'id' => "{$this->getId()}",
            'value' => $this->post->{$this->postFieldName},
            'localeCode' => $this->sourceLang->toLocale(),
        ];
    }
}
