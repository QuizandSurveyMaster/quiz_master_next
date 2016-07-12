<?php
//if uninstall not called from WordPress, then exit
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

delete_option( 'mlw_quiz_master_version' );
delete_option( 'mlw_qmn_review_notice' );
delete_option( 'mlw_advert_shows' );
delete_option( 'qmn-settings' );
delete_option( 'qmn-tracking-notice' );
?>
