<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Settings Preview tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_preview_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Preview", 'quiz-master-next'), 'mlw_options_preview_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_preview_tab', 5);

/**
* Adds the options preview content to the Options preview tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_preview_tab_content()
{
	?>
	<div id="tabs-preview" class="mlw_tab_content">
		<p>If your quiz looks different on the front end compared to this preview, then there is a conflict with your theme. Check out our <a href="http://quizandsurveymaster.com/common-theme-conflict-fixes/?utm_source=qsm-preview-tab&utm_medium=plugin&utm_campaign=qsm_plugin">Common Theme Conflict Fixes</a>.</a></p>
		<?php
		echo do_shortcode( '[mlw_quizmaster quiz='.intval($_GET["quiz_id"]).']' );
		?>
	</div>
	<?php
}
?>
