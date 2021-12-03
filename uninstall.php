<?php
/**
 * If uninstall not called from WordPress, then exit.
 *
 * @package QSM
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

//Do not allow to delete the data untill delete_qsm_data option is not set.
$settings   = (array) get_option( 'qmn-settings' );
if ( ! isset( $settings['delete_qsm_data'] ) ) {
    return;
}

global $wpdb;

$qsm_tables = array(
	'mlw_results',
	'mlw_quizzes',
	'mlw_questions',
	'mlw_qm_audit_trail',
);

// Drop the tables associated with our plugin.
foreach ( $qsm_tables as $table_name ) {
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $table_name );
}

// Taken from Easy Digital Downloads. Much better way of doing it than I was doing :)
// Cycle through custom post type array, retreive all posts, delete each one.
$qsm_post_types = array( 'qsm_quiz', 'qmn_log' );
foreach ( $qsm_post_types as $qsm_post_type ) {
	$items = get_posts(
		array(
			'post_type'   => $qsm_post_type,
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item, true );
		}
	}
}

$qsm_options = array(
	'qmn_original_version',
	'mlw_advert_shows',
	'qmn_review_message_trigger',
	'qsm_update_db_column',
	'qsm_change_the_post_type',
	'qmn_quiz_taken_cnt',
	'qmn-settings',
	'qsm-quiz-settings',
	'mlw_quiz_master_version',
	'qmn_tracker_last_time',
	'qmn-tracking-notice',
	'mlw_qmn_review_notice',
	'qsm_upated_question_type_val',
	'qsm_update_quiz_db_column',
	'qsm_update_result_db_column',
);

// Remove the orphaned options now.
foreach ( $qsm_options as $option_name ) {
	delete_option( $option_name );
}

$qsm_transients = array(
	'qsm_sidebar_feed_data',
	'qmn_contributors',
	'qsm_ads_data',
	'qsm_admin_dashboard_data',
);

// Remove the orphaned transients now.
foreach ( $qsm_transients as $transient_name ) {
	delete_transient( $transient_name );
}
