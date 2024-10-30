<?php

namespace Locale\Locale\Entities;

use Locale\Locale\Support\Contracts\Arrayable;
use Locale\Translation;
use Locale\Locale\Support\Arr;
use Locale\Locale\Support\Language;
use Locale\Locale\Support\TranslationId;
use function array_values;
use function get_current_blog_id;
use function Locale\Functions\get_languages;

/**
 * Represents an array of translations mady by Locale.
 *
 * @package Locale\Locale\Entities
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class TranslatedPosts implements Arrayable
{
    /**
     * The translated posts, grouped by their target post id.
     *
     * @var array
     */
    protected $translatedPosts = [];

    /**
     * Adds the translation to a list of translated posts, that is grouped by target
     * post, and locale.
     *
     * The aggregation works by first, matching the given language with the one
     * configured in MultilingualPress to identify the site that is being targeted by
     * a translation, and second, grouping them by their target post. By the end of
     * the aggregation, we would then have a list of post translation metadata, that
     * knows which post, and site it belongs to.
     *
     * @param string $id
     * @param string $translation
     * @param string $language
     *
     * @return bool
     */
    public function aggregate($id, $translation, $language)
    {
        $language = new Language($language);
        $destination = $language->getEquivalentSite();

        if ($destination !== null) {
            $id = TranslationId::fromString($id);
            $group = $this->createGroupName($id, $language);

            if (! $this->groupExists($group)) {
                $siteLanguage = get_languages()[$destination->id];

                $this->translatedPosts[$group] = [
                    Translation::META_KEY => [
                        Translation::SOURCE_POST_ID_KEY => $id->getPostId(),
                        Translation::SOURCE_SITE_KEY => get_current_blog_id(),
                        Translation::TARGET_SITE_KEY => $destination->id,
                        Translation::TARGET_LANG_KEY => $siteLanguage->get_lang_code(),
                    ],
                ];
            }

            if (! $this->groupItemExists($group, $id->getPostField())) {
                $this->translatedPosts[$group][$id->getPostField()] = $translation;

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        // By calling array_values to the translated posts, their grouping key will
        // be removed.
        return array_values($this->translatedPosts);
    }

    /**
     * Creates a unique group name from the translation id target, and language code.
     *
     * @param \Locale\Locale\Support\TranslationId $id
     * @param \Locale\Locale\Support\Language      $language
     *
     * @return string
     */
    protected function createGroupName(TranslationId $id, Language $language)
    {
        return $id->getTranslationTarget() . $language->toTranslationManager();
    }

    /**
     * Determines whether the group exists in the translations array.
     *
     * @param $group
     *
     * @return bool
     */
    protected function groupExists($group)
    {
        return ! empty(Arr::get($this->translatedPosts, $group));
    }

    /**
     * Determines whether the group item exists in the translations array.
     *
     * @param $group
     * @param $item
     *
     * @return bool
     */
    protected function groupItemExists($group, $item)
    {
        return ! empty(Arr::get($this->translatedPosts, "{$group}.{$item}"));
    }
}
