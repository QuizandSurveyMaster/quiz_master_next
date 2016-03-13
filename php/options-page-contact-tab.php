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

}

?>
