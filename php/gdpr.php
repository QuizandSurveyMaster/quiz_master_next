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
add_filter( 'wp_privacy_personal_data_erasers', 'qsm_register_data_erasers' );

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
	$content .= '<p>' . __( 'In order to distinguish individual users, IP addresses are collected and stored with the responses.', 'quiz-master-next' ) . '</p>';
	$content .= '<p>' . __( 'Each individual form may have fields for a variety of other personal information, such as name and email. This data is needed to identify and possibly communicate with the user. There may be other fields asking for personal information and this data may be for different purposes for each quiz, survey, or form. If any data is to be used for purposes other than grading or survey purposes, this will be disclosed on the form itself.', 'quiz-master-next' ) . '</p>';
	$content .= '<h2>' . __( 'How long we retain your data', 'quiz-master-next' ) . '</h2>';
	$content .= '<p>' . __( 'The responses and data attached to the responses are stored indefinitely until an administrator of this site deletes the responses.', 'quiz-master-next' ) . '</p>';
	$content .= '<p>' . __( 'Change This! If you are using an addon or custom software to sync data with a 3rd party (such as MailChimp), data is retained there which should be mentioned here.', 'quiz-master-next' ) . '</p>';
	$content .= '<h2>' . __( 'Where we send your data', 'quiz-master-next' ) . '</h2>';
	$content .= '<p>' . __( 'Quiz And Survey Master does not send any of your data to anywhere outside of this site by default.', 'quiz-master-next' ) . '</p>';
	$content .= '<p>' . __( 'Change This! If you are sharing the responses with anyone and do not list it anywhere else in your privacy policy, enter information about that here. ', 'quiz-master-next' ) . '</p>';
	return $content;
}

/**
 * Registers the suggested privacy policy content
 *
 * @since 5.3.1
 */
function qsm_register_suggested_privacy_content() {
	if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
		$content = qsm_get_our_suggested_privacy_content();
		wp_add_privacy_policy_content( 'Quiz And Survey Master', $content );
	}
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
	$exporters['quiz-master-next'] = array(
		'exporter_friendly_name' => 'Quiz And Survey Master',
		'callback'               => 'qsm_data_exporter',
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
	$group_id     = 'qsm-form-response';
	$group_label  = __( 'Form Responses', 'quiz-master-next' );

	// Prepare SQL for finding by user.
	$user_id = 0;
	if ( $user && isset( $user->ID ) && 0 !== $user->ID ) {
		$user_id = $user->ID;
		$query = $wpdb->prepare( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE user = %d OR email = %s", $user_id, $email );
	} else {
		$query = $wpdb->prepare( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE email = %s", $email );
	}

	// Calculate query range.
	$total = $wpdb->get_var( $query );
	$per_page  = 25;
	$begin     = $per_page * ( $page - 1 );
	$remaining = $total - ( $page * $per_page );

	// Get the results.
	if(0 !== $user_id){
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE user = %d OR email = %s ORDER BY result_id DESC LIMIT %d, %d", $user_id, $email, $begin, $per_page );
	} else {
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE email = %s ORDER BY result_id DESC LIMIT %d, %d", $email, $begin, $per_page );
	}
	$results = $wpdb->get_results( $query );

	// Cycle through adding to array.
	foreach ( $results as $result ) {
		// Set the ID.
		$item_id = 'qsm-form-response-' . $result->result_id;

		// Prepares our results array.
		if ( is_serialized( $result->quiz_results ) ) {
			$results_array = @unserialize( $result->quiz_results );
			if ( is_array( $results_array ) ) {
				if ( ! isset( $results['contact'] ) ) {
					$results['contact'] = array();
				}
			}
		}
		if ( ! is_array( $results_array ) ) {
			$results_array = array(
				0,
				array(),
				'',
				'contact' => array(),
			);
		}

		// Create the data array.
		$data = array(
			array(
				'name'  => __( 'Form Name', 'quiz-master-next' ),
				'value' => $result->quiz_name,
			),
			array(
				'name'  => __( 'Time Submitted', 'quiz-master-next' ),
				'value' => $result->time_taken,
			),
			array(
				'name'  => __( 'Points Earned', 'quiz-master-next' ),
				'value' => $result->point_score,
			),
			array(
				'name'  => __( 'Score Earned', 'quiz-master-next' ),
				'value' => $result->correct_score,
			),
			array(
				'name'  => __( 'Questions Answered Correctly', 'quiz-master-next' ),
				'value' => $result->correct,
			),
			array(
				'name'  => __( 'Total Questions', 'quiz-master-next' ),
				'value' => $result->total,
			),
			array(
				'name'  => __( 'Name Field', 'quiz-master-next' ),
				'value' => $result->name,
			),
			array(
				'name'  => __( 'Business Field', 'quiz-master-next' ),
				'value' => $result->business,
			),
			array(
				'name'  => __( 'Email Field', 'quiz-master-next' ),
				'value' => $result->email,
			),
			array(
				'name'  => __( 'Phone Field', 'quiz-master-next' ),
				'value' => $result->phone,
			),
			array(
				'name'  => __( 'User ID', 'quiz-master-next' ),
				'value' => $result->user,
			),
			array(
				'name'  => __( 'IP Address', 'quiz-master-next' ),
				'value' => $result->user_ip,
			),
			array(
				'name'  => __( 'Time to complete form', 'quiz-master-next' ),
				'value' => $results_array[0] . ' seconds',
			),
			array(
				'name'  => __( 'Form comments', 'quiz-master-next' ),
				'value' => $results_array[2],
			),
		);

		// Adds contact fields.
		$contact_count = count( $results_array['contact'] );
		for ( $i = 0; $i < $contact_count; $i++ ) {
			$data[] = array(
				'name'  => $results_array['contact'][ $i ]['label'],
				'value' => $results_array['contact'][ $i ]['value'],
			);
		}

		// Adds all answer data.
		foreach ( $results_array[1] as $question ) {
			$data[] = array(
				'name'  => $question[0],
				'value' => "Answer: {$question[1]}. Comments: {$question[3]}",
			);
		}

		// Add to export array.
		$export_items[] = array(
			'group_id'    => $group_id,
			'group_label' => $group_label,
			'item_id'     => $item_id,
			'data'        => $data,
		);
	}

	if ( 0 >= $remaining ) {
		$done = true;
	}

	return array(
		'data' => $export_items,
		'done' => $done,
	);
}

/**
 * 3. Data Eraser
 */

/**
 * Register the plugin's data eraser
 *
 * @since 5.3.1
 * @param array $erasers The erasers registered for WordPress.
 * @return array The erasers with ours appended.
 */
function qsm_register_data_erasers( $erasers ) {
	$erasers['quiz-master-next'] = array(
		'eraser_friendly_name' => 'Quiz And Survey Master',
		'callback'             => 'qsm_data_eraser',
	);
	return $erasers;
}

/**
 * Erases all data for the user
 *
 * @see https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-eraser-to-your-plugin/
 *
 * @since 5.3.1
 * @param string $email The email of the user who wants to erase his or her data.
 * @param int    $page The page we are on in the erasing.
 * @return array The status of erasing the data
 */
function qsm_data_eraser( $email, $page = 1 ) {

	// Sets up variables.
	global $wpdb;
	$items_removed = false;
	$user          = get_user_by( 'email', $email );

	// Deletes all results attached to user.
	$user_sql = '';
	if ( $user && isset( $user->ID ) && 0 !== $user->ID ) {
		$user_count = $wpdb->delete(
			"{$wpdb->prefix}mlw_results",
			array( 'user' => $user->ID )
		);
	}

	// Deletes all results attached email address not attached to user.
	$email_count = $wpdb->delete(
		"{$wpdb->prefix}mlw_results",
		array( 'email' => $email )
	);

	// If we deleted any, then set removed to true.
	$total = intval( $user_count ) + intval( $email_count );
	if ( 0 < $total ) {
		$items_removed = true;
	}

	// Needed array to be returned.
	return array(
		'items_removed'  => $items_removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}
