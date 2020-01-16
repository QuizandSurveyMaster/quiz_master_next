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
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Contact", 'quiz-master-next' ), 'qsm_options_contact_tab_content' );
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
  $quiz_id = intval( $_GET["quiz_id"] );

  $contact_form = QSM_Contact_Manager::load_fields();

  wp_enqueue_script( 'qsm_contact_admin_script', plugins_url( '../../js/qsm-admin-contact.js' , __FILE__ ), array( 'jquery-ui-sortable' ), $mlwQuizMasterNext->version );
  wp_localize_script( 'qsm_contact_admin_script', 'qsmContactObject', array( 'contactForm' => $contact_form, 'quizID' => $quiz_id ) );
  wp_enqueue_style( 'qsm_contact_admin_style', plugins_url( '../../css/qsm-admin-contact.css' , __FILE__ ), array(), $mlwQuizMasterNext->version );

  /**
   * Example contact form array
   * array(
   *  array(
   *    'label' => 'Name',
   *    'type' => 'text',
   *    'answers' => array(
   *      'one',
   *      'two'
   *    ),
   *    'required' => true
   *    )
   *  )
   */

  ?>
  <h2><?php _e( 'Contact', 'quiz-master-next' ); ?></h2>
  <p>Need assistance with this tab? <a href="https://docs.quizandsurveymaster.com/article/20-collecting-users-information-using-contact-fields" target="_blank">Check out the documentation</a> for this tab!</p>
  <div class="contact-message"></div>
  <a class="save-contact button-primary"><?php _e( 'Save Contact Fields', 'quiz-master-next' ); ?></a>
  <div class="contact-form"></div>
   <a class="add-contact-field button-primary"><?php _e( 'Add New Field', 'quiz-master-next' ); ?></a>
  <?php
}

add_action( 'wp_ajax_qsm_save_contact', 'qsm_contact_form_admin_ajax' );
//add_action( 'wp_ajax_nopriv_qsm_save_contact', 'qsm_contact_form_admin_ajax' );

/**
 * Saves the contact form from the quiz settings tab
 *
 * @since 0.1.0
 * @return void
 */
function qsm_contact_form_admin_ajax() {
	global $wpdb;
	global $mlwQuizMasterNext;
	// Sends posted form data to Contact Manager to sanitize and save.
	$results['status'] =  QSM_Contact_Manager::save_fields( intval( $_POST['quiz_id'] ), $_POST['contact_form'] );
	echo wp_json_encode( $results );
	die();
}

?>
