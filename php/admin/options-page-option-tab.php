<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Options tab to the Quiz Settings page.
 *
 * @return void
 * @since 6.2.0
 */
function qsm_settings_options_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Options', 'quiz-master-next' ), 'qsm_options_option_tab_content', 'options');
}
add_action( 'plugins_loaded', 'qsm_settings_options_tab', 5 );

/**
 * Adds the options content to the Quiz Settings page.
 *
 * @return void
 * @since 6.2.0
 */
function qsm_options_option_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
        ?>
        <p style="text-align: right;"><a href="https://quizandsurveymaster.com/docs/v7/options-tab/" target="_blank"><?php esc_html_e( 'View Documentation', 'quiz-master-next' ); ?></a></p>
        <?php
	$mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_options' );
}
?>
