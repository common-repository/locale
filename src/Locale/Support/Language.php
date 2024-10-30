<?php

namespace Locale\Locale\Support;

use function Locale\Functions\get_languages;
use function str_contains;
use function strtolower;

/**
 * Creates a selectable language from the supported Locale languages, based on the
 * given Locale language code.
 *
 * @package Locale\Locale\Support
 *
 * @author  Peter Cortez <peter@locale.to>
 */
class Language
{
    /**
     * The locale from whom we're getting the Locale language.
     *
     * @var string
     */
    protected $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Returns the Locale language code for this language.
     *
     * @return string
     */
    public function toLocale()
    {
        return str_replace('_', '-', $this->locale);
    }

    /**
     * Returns the Locale language code for this language.
     *
     * @return string
     */
    public function toTranslationManager()
    {
        return str_replace('-', '_', $this->locale);
    }

    /**
     * Determines whether this language is similar to the supplied one by checking if
     * it somehow looks like the other. A use case for this is when comparing `us`
     * and `en_US`. In a way, the two (2) is similar.
     *
     * We're doing this because MultilingualPress and Locale has a different
     * language code for a given locale. e.g., english in Locale has a language code
     * of `en` while english in MultilingualPress returns `en_US`.
     *
     * @param \Locale\Locale\Support\Language $projItemLang
     *
     * @return bool
     */
    public function isSimilarTo(Language $projItemLang)
    {
        $_this = strtolower($this->toLocale());
        $_that = strtolower($projItemLang->toLocale());

        return str_contains($_this, $_that) || str_contains($_that, $_this);
    }

    /**
     * Determines whether this language is equal to the supplied one, ignoring the
     * separator (dash, and underscore).
     *
     * @param \Locale\Locale\Support\Language $projItemLang
     *
     * @return bool
     */
    public function isEqualTo(Language $projItemLang)
    {
        return strtolower($this->toLocale()) === strtolower($projItemLang->toLocale());
    }

    /**
     * Returns the site whose language code is equal to this language.
     *
     * @return \Locale\Domain\Language
     */
    public function getEquivalentSite()
    {
        foreach (get_languages() as $siteId => $lang) {
            $comparator = new Language($lang->get_lang_code());

            if ($this->isEqualTo($comparator)) {
                $lang->id = $siteId;
                return $lang;
            }
        }

        return null;
    }

    /**
     * Returns the site whose language code is similar to this language.
     *
     * @return \Locale\Domain\Language[]
     */
    public function getSimilarSites()
    {
        $similar = [];
        foreach (get_languages() as $siteId => $lang) {
            $comparator = new Language($lang->get_lang_code());

            if ($this->isSimilarTo($comparator) && !$this->isEqualTo($comparator)) {
                $lang->id = $siteId;
                $similar[] = $lang;
            }
        }

        return $similar;
    }
}
