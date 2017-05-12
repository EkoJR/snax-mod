<?php

// If uninstall.php is not called by WordPress, die.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

//C:\wamp64\www\Snax\wp-content\plugins\snax\includes\core\functions.php
include_once ABSPATH . 'wp-content\plugins\snax\includes\core\functions.php';

global $wpdb;

$table_name      = $wpdb->prefix . snax_get_votes_table_name();
$charset_collate = $wpdb->get_charset_collate();
$column = $wpdb->get_row( 'SELECT wp_post_id FROM ' . $table_name );

// TODO - Add option to delete on deactivation.
if ( isset( $column ) ) {
	$wpdb->query( $wpdb->prepare( "ALTER TABLE $table_name DROP COLUMN wp_post_id" ) );
}
