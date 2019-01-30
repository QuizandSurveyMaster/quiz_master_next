<?php
/**
 * Creates the "Preview" tab when editing the quiz.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Settings Preview tab to the Quiz Settings page.
 *
 * @since 6.2.0
 */
function qsm_settings_preview_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Preview', 'quiz-master-next' ), 'qsm_options_preview_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_preview_tab', 5 );

/**
 * Adds the options preview content to the Options preview tab.
 *
 * @since 6.2.0
 */
function qsm_options_preview_tab_content() {
	?>
	<p>If your quiz looks different on the front end compared to this preview, then there is a conflict with your theme. Check out our <a href="https://docs.quizandsurveymaster.com/article/21-common-theme-conflict-fixes" target="_blank">Common Theme Conflict Fixes</a>.</p>
	<?php
	echo do_shortcode( '[qsm quiz=' . intval( $_GET['quiz_id'] ) . ']' );
	?>
	<?php
}
?>
