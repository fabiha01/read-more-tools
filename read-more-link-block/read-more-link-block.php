<?php
/**
 * Plugin Name:       Read More Link Block
 * Description:       Adds a Gutenberg block that allows selecting a post and creating a link to it.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Fabiha Khatun
 * Text Domain:       read-more-link-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the custom Gutenberg block using the appropriate metadata collection functions,
 * depending on the version of WordPress.
 *
 * This function supports both:
 * - `wp_register_block_types_from_metadata_collection` (introduced in WP 6.8)
 * - `wp_register_block_metadata_collection` (introduced in WP 6.7)
 * as fallbacks for backwards compatibility.
 *
 * It loads block metadata from a `blocks-manifest.php` file.
 *
 * @return void
 */
function create_block_read_more_link_block_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}

	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';

	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'create_block_read_more_link_block_block_init' );

/**
 * Registers a custom REST API route used to search and paginate posts.
 *
 * This route is accessible via:
 * `/wp-json/rmlb/v1/search-posts`
 *
 * @return void
 */
function rmlb_register_rest_route() {
	register_rest_route( 'rmlb/v1', '/search-posts', [
		'methods'             => 'GET',
		'callback'            => 'rmlb_search_posts',
		'permission_callback' => '__return_true',
	] );
}
add_action( 'rest_api_init', 'rmlb_register_rest_route' );

/**
 * Callback handler for the custom REST API endpoint.
 * Accepts search query and pagination parameters and returns matching posts.
 *
 * @param WP_REST_Request $request The REST request containing 'search' and 'page' parameters.
 * @return WP_REST_Response The formatted list of posts and total page count.
 */
function rmlb_search_posts( $request ) {
	$search   = sanitize_text_field( $request->get_param( 'search' ) );
	$paged    = max( 1, intval( $request->get_param( 'page' ) ) );
	$per_page = 5;

	$args = [
		'post_type'      => 'post',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		's'              => $search,
		'post_status'    => 'publish',
	];

	// If the search term is numeric, restrict to that post ID
	if ( is_numeric( $search ) ) {
		$args['post__in'] = [ (int) $search ];
	}

	$query   = new WP_Query( $args );
	$results = [];

	foreach ( $query->posts as $post ) {
		$results[] = [
			'id'    => $post->ID,
			'title' => get_the_title( $post ),
			'link'  => get_permalink( $post ),
		];
	}

	return rest_ensure_response( [
		'posts'     => $results,
		'max_pages' => $query->max_num_pages,
	] );
}
