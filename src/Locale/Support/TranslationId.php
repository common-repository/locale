<?php

namespace Locale\Locale\Support;

use Locale\Locale\Entities\Translation;
use Locale\Locale\Support\Contracts\Arrayable;
use function explode;
use function sprintf;

/**
 * Represents the ID of a Locale translation
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslationId implements Arrayable
{
    /**
     * The pattern used for constructing a Locale id for a given translation.
     *
     * The first segment should be the page type. e.g., post or page
     * The second segment should be the actual id of a post here in WordPress
     * The third segment should be the field name. We're only translating a select
     * set of post fields, {@see \Locale\Locale\Entities\WordpressPost::TRANSLATABLE_FIELDS}
     *
     * @var string
     */
    const PATTERN = '%s#%s#%s';

    /**
     * The string that separates each segment in the translation id pattern.
     *
     * @var string
     */
    const SEPARATOR = '#';

    /**
     * @var \Locale\Locale\Entities\Translation|string
     */
    protected $translation;

    /**
     * @var mixed|string
     */
    protected $postType;

    /**
     * @var mixed|string
     */
    protected $postId;

    /**
     * @var mixed|string
     */
    protected $postField;

    public function __construct($translation)
    {
        $this->translation = $translation;

        // TODO: Use sscanf() to read the id instead of using explode()
        list($this->postType, $this->postId, $this->postField) = explode(
            self::SEPARATOR,
            "{$this}"
        );
    }

    /**
     * Creates a new TranslationID instance from the given string, assuming that it
     * is a valid TranslationID.
     *
     * @param $string
     *
     * @return static
     */
    public static function fromString($string)
    {
        return new static($string);
    }

    public function __toString()
    {
        if ($this->translation instanceof Translation) {
            return sprintf(
                self::PATTERN,
                $this->translation->isPage()
                    ? Translation::POST_TYPE_PAGE
                    : Translation::POST_TYPE_POST,
                $this->translation->getPost()->ID,
                $this->translation->getPostFieldName()
            );
        }

        // TODO: Throw exception when the string doesn't match the translation id
        //  pattern?
        return $this->translation;
    }

    /**
     * @return mixed|string
     */
    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * @return mixed|string
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @return mixed|string
     */
    public function getPostField()
    {
        return $this->postField;
    }

    /**
     * Returns the translation target of this translation id. The translation target
     * consists of the first two (2) segments of the translation id.
     *
     * @return string
     */
    public function getTranslationTarget()
    {
        return $this->getPostType() . self::SEPARATOR . $this->getPostId();
    }

    /**
     * @inheritDoc
     *
     * Returns the various named segments of the translation id.
     */
    public function toArray()
    {
        return [
            'post_type' => $this->getPostType(),
            'post_id' => $this->getPostId(),
            'post_field' => $this->getPostField(),
        ];
    }
}
