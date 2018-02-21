<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Options tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_options_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Options", 'quiz-master-next' ), 'mlw_options_option_tab_content' );
}
add_action( "plugins_loaded", 'qmn_settings_options_tab', 5 );

/**
* Adds the options content to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function mlw_options_option_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_options' );
}
?>
