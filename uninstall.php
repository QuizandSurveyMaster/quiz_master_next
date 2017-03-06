<?php
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;
$table_name = $wpdb->prefix . "mlw_results";
$results = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

$table_name = $wpdb->prefix . "mlw_quizzes";
$results = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

$table_name = $wpdb->prefix . "mlw_questions";
$results = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
$results = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Taken from Easy Digital Downloads. Much better way of doing it than I was doing :)
// Cycle through custom post type array, retreive all posts, delete each one
$qsm_post_types = array( 'quiz', 'qmn_log' );
foreach ( $qsm_post_types as $post_type ) {
	$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item, true);
		}
	}
}


delete_option( 'mlw_quiz_master_version' );
delete_option( 'mlw_qmn_review_notice' );
delete_option( 'mlw_advert_shows' );
delete_option( 'qmn-settings' );
delete_option( 'qmn-tracking-notice' );
?>
