<?php

namespace Locale\Functions;

use function get_current_blog_id;
use function is_null;

/**
 * Interface for modules to serve languages.
 *
 * @since 1.0.0
 *
 * @return \Locale\Domain\Language[]
 */
function get_languages() {

    static $languages;

    if (is_null($languages)) {
        /**
         * Get Languages
         *
         * @since 1.0.0
         *
         * @param array An empty array to fill with \Locale\Domain\Language instances.
         * @param int The current blog ID.
         */
        $languages = apply_filters('locale_languages', [], get_current_blog_id());
    }

	return $languages;
}

/**
 * Get the languages by the site id
 *
 * @since 1.0.0
 *
 * @param int $site_id The id of the site.
 *
 * @return array A list of languages by site.
 */
function get_languages_by_site_id( $site_id ) {

	/**
	 * Languages By Site ID Filter
	 *
	 * @since 1.0.0
	 *
	 * @param array $languages The languages list.
	 * @param int   $site_id   The id for which site retrieve the languages.
	 */
	return apply_filters( 'translation_manager_languages_by_site_id', [], $site_id );
}

/**
 * Current Language
 *
 * @since 1.0.0
 *
 * @return \Locale\Domain\Language The instance of the class.
 */
function current_language() {

	/**
	 * Current Language
	 *
	 * @since 1.0.0
	 *
	 * @param \Locale\Domain\Language The instance of the class.
	 */
	return apply_filters( 'locale_current_language', new \Locale\Domain\Language( get_locale(), null ) );
}

/**
 * Retrieve Current Language Code
 *
 * @since 1.0.0
 *
 * @return mixed Whatever \Locale\Domain\Language::get_lang_code() returns
 */
function current_lang_code() {

	$current_language = current_language();

	return $current_language->get_lang_code();
}

/**
 * Get Language Label
 *
 * @since 1.0.0
 *
 * @param string $lang_code The language code to convert to label.
 *
 * @return string The language label
 */
function locale_get_language_label( $lang_code ) {

	$languages = get_languages();

	foreach ( $languages as $language ) {
		if ( $lang_code === $language['lang_code'] ) {
			return $language['label'];
		}
	}

	return '';
}
