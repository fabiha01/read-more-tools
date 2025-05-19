<?php
/**
 * Plugin Name: Read More Search CLI
 * Description: WP-CLI command to search for posts containing the read-more-link-block.
 * Version: 1.0.0
 * Author: Fabiha Khatun
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once __DIR__ . '/commands/class-read-more-search-command.php';

    WP_CLI::add_command( 'read-more-search', 'Read_More_Search_Command' );
}
