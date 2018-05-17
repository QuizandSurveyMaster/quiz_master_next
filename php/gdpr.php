<?php
/**
 * This file handles elements needed for GDPR. This requires WordPress 4.9.6 or later. This includes:
 *
 * 1. Privacy Policy
 * 2. Data Exporter
 * 3. Data Eraser
 *
 * @since 5.3.1
 * @package QSM
 */

// Add our actions and filters to functions below.
add_action( 'admin_init', 'qsm_register_suggested_privacy_content', 20 );
add_filter( 'wp_privacy_personal_data_exporters', 'qsm_register_data_exporters' );

/**
 * 1. Privacy Policy
 */

/**
 * Gets the suggested privacy policy content for this plugin
 *
 * @since 5.3.1
 * @return string The HTML contect for the policy
 */
function qsm_get_our_suggested_privacy_content() {
	$content  = '<p>' . __( 'Many of our quizzes, surveys, and forms are created using Quiz And Survey Master.', 'quiz-master-next' ) . '</p>';
	$content .= '<h2>' . __( 'The data the software collects', 'quiz-master-next' ) . '</h2>';
	$content .= '<p>' . __( 'In order to distinguish individual users, IP addresses are collected and store with the responses.', 'quiz-master-next' ) . '</p>';
	$content .= '<p>' . __( 'Each individual form may have fields for a variety of other personal information, such as name and email. This data is needed to identify and possibly communicate with the user. There may be other fields asking for personal information and this data may be for different purposes for each quiz, survey, or form. If any data is to be used for purposes other than grading or survey purposes, this will be disclosed on the form itself.', 'quiz-master-next' ) . '</p>';
	$content .= '<h2>' . __( 'How long we retain your data', 'quiz-master-next' ) . '</h2>';
	$content  = '<p>' . __( 'The responses and data attached to the responses are stored indefinitely until an administrator of this site deletes the responses.', 'quiz-master-next' ) . '</p>';
	$content  = '<p>' . __( 'Change This! If you are using an addon or custom software to sync data with a 3rd party (such as MailChimp), data is retained there which should be mentioned here.', 'quiz-master-next' ) . '</p>';
	$content .= '<h2>' . __( 'Where we send your data', 'quiz-master-next' ) . '</h2>';
	$content  = '<p>' . __( 'Quiz And Survey Master does not send any of your data to anywhere outside of this site by default.', 'quiz-master-next' ) . '</p>';
	$content  = '<p>' . __( 'Change This! If you are sharing the responses with anyone and do not list it anywhere else in your privacy policy, enter information about that here. ', 'quiz-master-next' ) . '</p>';
	return $content;
}

/**
 * Registers the suggested privacy policy content
 *
 * @since 5.3.1
 */
function qsm_register_suggested_privacy_content() {
	$content = qsm_get_our_suggested_privacy_content();
	wp_add_privacy_policy_content( 'Quiz And Survey Master', $content );
}

/**
 * 2. Data Exporter
 */

/**
 * Register the plugin's data exporter
 *
 * @since 5.3.1
 * @param array $exporters The exporters registered for WordPress.
 * @return array The exporters with ours appended.
 */
function qsm_register_data_exporters( $exporters ) {
	$exporters[] = array(
		'exporter_friendly_name' => 'Quiz And Survey Master',
		'callback'               => '',
	);
	return $exporters;
}

/**
 * Creates an array of all data for the user
 *
 * @see https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-exporter-to-your-plugin/
 *
 * @since 5.3.1
 * @param string $email The email of the user who wants to export his or her data.
 * @param int    $page The page we are on in the exporting.
 * @return array The data for the export
 */
function qsm_data_exporter( $email, $page = 1 ) {

	// Sets up variables.
	global $wpdb;
	$export_items = array();
	$done         = false;
	$user         = get_user_by( 'email', $email );

	// Get all results by user ID and email.
	$user_sql = '';
	if ( $user && isset( $user->ID ) && 0 !== $user->ID ) {
		$user_sql = "user = {$user->ID} OR ";
	}
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE $user_sql email = '%s' ORDER BY result_id DESC", $email ) );

	// Cycle through adding to array.
	foreach ( $results as $result ) {
		// Do stuff...
	}

	return array(
		'data' => $export_items,
		'done' => $done,
	);
}
