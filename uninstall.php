<?php
	global $wpdb;
	wp_clear_scheduled_hook('vs_cron');
	
	delete_option('vs_options');

	$wpdb->query( 'DROP TABLE IF EXISTS wp_vitrinesubmarino');
?>