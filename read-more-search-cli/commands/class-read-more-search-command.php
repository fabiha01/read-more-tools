<?php

/**
 * WP-CLI command to search for posts containing a specific Gutenberg block within a date range.
 */
class Read_More_Search_Command {

    /**
     * Searches for published posts that include the 'read-more-link-block' Gutenberg block 
     * in their content, within a specified date range.
     *
     * ## OPTIONS
     *
     * [--date-before=<date>]
     * : Optional. Date before (inclusive), in YYYY-MM-DD format. Defaults to today.
     *
     * [--date-after=<date>]
     * : Optional. Date after (inclusive), in YYYY-MM-DD format. Defaults to 30 days ago.
     *
     * ## EXAMPLES
     *
     *     wp dm-read-more-search --date-after=2025-01-01 --date-before=2025-01-31
     *
     * The command logs matching post IDs to STDOUT. If no posts are found, or if an error occurs,
     * a message is logged instead.
     *
     * Performance optimized for large datasets: disables cache, limits query fields, and avoids unnecessary calculations.
     *
     * @param array $args       Positional arguments (not used).
     * @param array $assoc_args Associative arguments. Includes optional 'date-before' and 'date-after'.
     *
     * @when after_wp_load
     */
    public function __invoke( $args, $assoc_args ) {
        global $wpdb;

        $date_before = isset( $assoc_args['date-before'] ) ? $assoc_args['date-before'] : date( 'Y-m-d' );
        $date_after  = isset( $assoc_args['date-after'] ) ? $assoc_args['date-after'] : date( 'Y-m-d', strtotime( '-30 days' ) );

        // Validate dates
        if ( ! strtotime( $date_before ) || ! strtotime( $date_after ) ) {
            WP_CLI::error( 'Invalid date format. Use YYYY-MM-DD.' );
        }

        $block_signature = '<!-- wp:create-block/read-more-link-block'; // Adjust to your actual block name
        $paged = 1;
        $posts_per_page = 100;
        $found_any = false;

        WP_CLI::log( "Searching posts from $date_after to $date_before containing the block..." );

        do {
            $query = new WP_Query([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
                'date_query'     => [
                    'after'     => $date_after,
                    'before'    => $date_before,
                    'inclusive' => true,
                ],
                's'              => $block_signature,
                'fields'         => 'ids', // Only fetch post IDs for performance
                'no_found_rows'  => true, // Skip pagination count queries
                'cache_results'  => false, // Disable object caching
            ]);

            if ( $query->have_posts() ) {
                $found_any = true;

                foreach ( $query->posts as $post_id ) {
                    // Fetch post content directly for precision block check
                    $content = $wpdb->get_var( $wpdb->prepare(
                        "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d LIMIT 1",
                        $post_id
                    ));

                    if ( strpos( $content, $block_signature ) !== false ) {
                        WP_CLI::line( $post_id );
                    }
                }
            }

            $paged++;
        } while ( $query->have_posts() );

        if ( ! $found_any ) {
            WP_CLI::log( 'No posts found containing the read-more-link-block in the specified date range.' );
        }
    }
}
