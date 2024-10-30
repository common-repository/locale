<?php
/**
 * Hooks
 *
 * @since 1.0.0
 */

use Locale\Module\Mlp\Adapter;
use function Locale\Functions\get_supported_post_types;
use Locale\Locale\Hooks as LocaleHooks;

// CPT Job.
add_action( 'delete_term_taxonomy', 'Locale\\Functions\\delete_all_jobs_posts_based_on_job_taxonomy_term' );

// Jobs Taxonomy.
add_action( 'locale_job_pre_add_form', 'Locale\\Functions\\job_hide_slug' );
add_action( 'locale_job_pre_edit_form', 'Locale\\Functions\\job_hide_slug' );

add_action('init', function () {
    foreach (get_supported_post_types() as $postTypeName) {
        add_filter("handle_bulk_actions-edit-{$postTypeName}", 'Locale\\Functions\\bulk_translate_jobs_by_request_posts', 10, 3 );
    }
}, 11);

// Misc.
add_action(
	'all_admin_notices',
	function () {

		\Locale\Notice\TransientNoticeService::show();
	}
);

add_filter(
	'plugin_row_meta',
	function ( array $links, $file ) {

		static $plugin = null;

		// Avoid to create the same instance multiple times.
		// The action is performed for every plugin in the list.
		if ( null === $plugin ) {
			$plugin = new \Locale\Plugin();
		}

		if ( false !== strpos( $file, 'locale.php' ) ) {
			$links[1] = wp_kses(
				__(
					'By <a href="https://www.locale.to/">Locale</a>',
					'locale'
				),
				'data'
			);
		}

		return $links;
	},
	10,
	2
);
add_filter(
	'admin_footer_text',
	function ( $admin_footer_text ) {
        return $admin_footer_text;
	}
);

// Bootstrapping actions
add_action(
    'inpsyde_mlp_loaded',
    function (Inpsyde_Property_List_Interface $data) {
        $adapter = new Adapter(2,
            $data->get('site_relations'),
            $data->get('content_relations')
        );

        add_filter('locale_relations', [$adapter, 'relations'], 10, 3);
    }
);

// Registration of action hooks. The ordering here is crucial!
// add_action('publish_post', new LocaleHooks\Posts\SendToLocaleAfterPublish, 10, 3);
// add_action('publish_page', new LocaleHooks\Posts\SendToLocaleAfterPublish, 10, 3);
add_action('publish_post', new LocaleHooks\Posts\CreateRevisionAfterPublish, 10, 3);
add_action('publish_page', new LocaleHooks\Posts\CreateRevisionAfterPublish, 10, 3);
// add_action('_wp_put_post_revision', new LocaleHooks\Posts\SendToLocalePublishedUpdate, 10, 3);
add_action('_wp_put_post_revision', new LocaleHooks\Posts\SaveLastPublishedRevisionId, 10, 3);

add_action('added_term_meta', new LocaleHooks\Meta\MarkJobItemsAsTranslated, 10, 4);
add_action('updated_term_meta', new LocaleHooks\Meta\MarkJobItemsAsTranslated, 10, 4);
add_action('added_term_meta', new LocaleHooks\Meta\MarkJobItemsAsNotTranslated, 10, 4);
add_action('updated_term_meta', new LocaleHooks\Meta\MarkJobItemsAsNotTranslated, 10, 4);
add_action('post_submitbox_misc_actions', new LocaleHooks\Posts\HiddenPostIsTranslatedField);

// Registration of filter hooks
add_filter('locale_get_job_items', new LocaleHooks\Posts\JobItemsOnly, 10, 1);
add_filter('http_request_timeout', function () { return 30; });

add_action('manage_posts_columns', LocaleHooks\Posts\TranslationCompleted::asColumn(), 10, 2);
add_action('manage_posts_custom_column', LocaleHooks\Posts\TranslationCompleted::asCell(), 10, 2);
add_action('manage_pages_columns', LocaleHooks\Posts\TranslationCompleted::asColumn(), 10, 2);
add_action('manage_pages_custom_column', LocaleHooks\Posts\TranslationCompleted::asCell(), 10, 2);
