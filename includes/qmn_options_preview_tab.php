<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function qmn_settings_preview_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Preview", 'quiz-master-next'), 'mlw_options_preview_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_preview_tab', 5);

function mlw_options_preview_tab_content()
{
	?>
	<div id="tabs-preview" class="mlw_tab_content">
		<?php
		echo do_shortcode( '[mlw_quizmaster quiz='.intval($_GET["quiz_id"]).']' );
		?>
	</div>
	<?php
}
?>
