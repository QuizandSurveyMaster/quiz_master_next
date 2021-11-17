<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Loads admin scripts and style
 *
 * @since 7.3.5
 */
function qsm_admin_enqueue_scripts_options_page_contact($hook){
	if ( 'admin_page_mlw_quiz_options' != $hook  ) {
		return;
	}
  if(  isset($_GET['tab'] ) && "contact" === $_GET['tab']){
    global $mlwQuizMasterNext;
    wp_enqueue_script( 'qsm_contact_admin_script', QSM_PLUGIN_JS_URL.'/qsm-admin-contact.js', array( 'jquery-ui-sortable' ), $mlwQuizMasterNext->version, true );
    wp_enqueue_style( 'qsm_contact_admin_style', QSM_PLUGIN_CSS_URL.'/qsm-admin-contact.css', array(), $mlwQuizMasterNext->version );
  }
}
add_action( 'admin_enqueue_scripts', 'qsm_admin_enqueue_scripts_options_page_contact', 20 );

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
  $quiz_id = intval( sanitize_text_field( $_GET["quiz_id"] ) );
  $user_id = get_current_user_id();
  $contact_form = QSM_Contact_Manager::load_fields();
  wp_localize_script( 'qsm_contact_admin_script', 'qsmContactObject', array( 'contactForm' => $contact_form, 'quizID' => $quiz_id, 'saveNonce' => wp_create_nonce( 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) ) );

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
  <h2 style="display: none;"><?php _e( 'Contact', 'quiz-master-next' ); ?></h2>
  <p style="text-align: right;"><a href="https://quizandsurveymaster.com/docs/v7/contact-tab/" target="_blank"><?php _e( 'View Documentation', 'quiz-master-next' ); ?></a></p>
  <div class="contact-message"></div>
  <a class="save-contact button-primary"><?php _e( 'Save Contact Fields', 'quiz-master-next' ); ?></a>
  <div class="contact-form"></div>
   <a class="add-contact-field button-primary"><?php _e( 'Add New Field', 'quiz-master-next' ); ?></a>
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
  $nonce = sanitize_text_field( $_POST['nonce'] );
  $quiz_id = intval( sanitize_text_field( $_POST['quiz_id'] ) );
  $user_id = get_current_user_id();
  if ( ! wp_verify_nonce( $nonce, 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) ) {
    die ( 'Busted!');
  }
    
	global $wpdb;
	global $mlwQuizMasterNext;
	// Sends posted form data to Contact Manager to sanitize and save.
	$results['status'] =  QSM_Contact_Manager::save_fields( $quiz_id, $_POST['contact_form'] );
	echo wp_json_encode( $results );
	die();
}

?>
