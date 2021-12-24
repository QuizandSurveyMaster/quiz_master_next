<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function adds the contact tab using our API.
*
* @return type description
* @since 4.7.0
*/
function qsm_settings_contact_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Contact", 'quiz-master-next' ), 'qsm_options_contact_tab_content', 'contact' );
}
add_action("plugins_loaded", 'qsm_settings_contact_tab', 5);

/**
* Adds the content for the options for contact tab.
*
* @return void
* @since 4.7.0
*/
function qsm_options_contact_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;

	$quiz_id = isset( $_GET["quiz_id"] ) ? intval( $_GET["quiz_id"] ) : 0;
	$user_id = get_current_user_id();
	$contact_form = QSM_Contact_Manager::load_fields();
	wp_localize_script( 'qsm_admin_js', 'qsmContactObject', array(
		'contactForm' => $contact_form,
		'quizID'      => $quiz_id,
		'saveNonce'   => wp_create_nonce( 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ),
	) );
	?>
	<h2 style="display: none;"><?php esc_html_e( 'Contact', 'quiz-master-next' ); ?></h2>
	<p style="text-align: right;"><a href="https://quizandsurveymaster.com/docs/v7/contact-tab/" target="_blank" rel="noopener"><?php esc_html_e( 'View Documentation', 'quiz-master-next' ); ?></a></p>
	<div class="contact-message"></div>
	<a class="save-contact button-primary"><?php esc_html_e( 'Save Contact Fields', 'quiz-master-next' ); ?></a>
	<div class="contact-form"></div>
	<a class="add-contact-field button-primary"><?php esc_html_e( 'Add New Field', 'quiz-master-next' ); ?></a>
	<?php
}

add_action( 'wp_ajax_qsm_save_contact', 'qsm_contact_form_admin_ajax' );

/**
 * Saves the contact form from the quiz settings tab
 *
 * @since 0.1.0
 * @return void
 */
function qsm_contact_form_admin_ajax() {
	$quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
	$user_id = get_current_user_id();
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) ) {
		die ( 'Busted!');
	}

		global $wpdb;
		global $mlwQuizMasterNext;

	$results = $data = array();
	if ( isset( $_POST['contact_form'] ) ) {
		$data = qsm_sanitize_rec_array( wp_unslash( $_POST['contact_form'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	// Sends posted form data to Contact Manager to sanitize and save.
	$results['status'] = QSM_Contact_Manager::save_fields( $quiz_id, $data );
	echo wp_json_encode( $results );
	die();
}

?>
