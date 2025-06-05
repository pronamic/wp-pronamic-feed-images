<?php
/**
 * Uninstall
 *
 * @package Pronamic\WordPressFeedImages
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'pronamic_feed_images_size' );
