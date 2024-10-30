<?php

namespace Locale\Functions;

/**
 * Fetch all job items.
 *
 * @since 1.0.0
 *
 * @param int   $term_id The term id from which retrieve the posts.
 * @param array $args    Additional arguments to set to the query. This take precedence.
 *
 * @return array The posts
 */
function get_job_items( $term_id, array $args = [] ) {

	$get_posts = get_posts(
		array_merge(
			[
				'post_type'      => 'job_item',
				'tax_query'      => [
					[
						'taxonomy' => 'locale_job',
						'field'    => 'id',
						'terms'    => $term_id,
					],
				],
				'posts_per_page' => - 1,
				'post_status'    => [ 'draft', 'published' ],
			],
			$args
		)
	);

	if ( ! $get_posts || is_wp_error( $get_posts ) ) {
		return [];
	}

	/**
	 * Get Job Items
	 *
	 * @since 1.0.0
	 *
	 * @param array $posts The posts.
	 */
	return (array) apply_filters( 'locale_get_job_items', $get_posts );
}

/**
 * Delete all post job based on term ID
 *
 * @since 1.0.0
 *
 * @param int $term_id The ID of the term related to the jobs. Used to retrieve the taxonomy.
 *
 * @return void
 */
function delete_all_jobs_posts_based_on_job_taxonomy_term( $term_id ) {

	$term     = get_term( $term_id );
	$taxonomy = is_array( $term->taxonomy ) ? $term->taxonomy[0] : $term->taxonomy;

	if ( is_wp_error( $term ) || 'locale_job' !== $taxonomy ) {
		return;
	}

	$posts = get_posts(
		[
			'post_type'      => 'job_item',
			'post_status'    => 'any',
			'posts_per_page' => - 1,
			'tax_query'      => [
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $term_id,
			],
		]
	);

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
