<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Style tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_style_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Style", 'quiz-master-next'), 'mlw_options_styling_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_style_tab', 5);

/**
* Adds the Style tab content to the tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_styling_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	if (isset($_POST["save_style_options"]) && $_POST["save_style_options"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_style_id = intval( $_POST["style_quiz_id"] );
		$mlw_qmn_theme = sanitize_text_field( $_POST["save_quiz_theme"] );
		$mlw_qmn_style = htmlspecialchars( stripslashes( $_POST["quiz_css"] ), ENT_QUOTES );

		//Save the new css
		$mlw_save_stle_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET quiz_stye='%s', theme_selected='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_style, $mlw_qmn_theme, $mlw_qmn_style_id ) );
		if ( false != $mlw_save_stle_results ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The style has been saved successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved For Quiz Number $mlw_qmn_style_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0015'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0015", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	$registered_templates = $mlwQuizMasterNext->pluginHelper->get_quiz_templates();
	?>
	<div id="tabs-7" class="mlw_tab_content">
		<script>
			function mlw_qmn_theme(theme)
			{
				document.getElementById('save_quiz_theme').value = theme;
				jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
				jQuery("#mlw_qmn_theme_block_"+theme).toggleClass("mlw_qmn_themeBlockActive");

			}
		</script>
		<?php
			echo "<form action='' method='post' name='quiz_style_form'>";
			echo "<input type='hidden' name='save_style_options' value='confirmation' />";
			echo "<input type='hidden' name='style_quiz_id' value='".$quiz_id."' />";
			echo "<input type='hidden' name='save_quiz_theme' id='save_quiz_theme' value='".$mlw_quiz_options->theme_selected."' />";
		?>
		<h3><?php _e('Quiz Styles', 'quiz-master-next'); ?></h3>
		<p><?php _e('Choose your style:', 'quiz-master-next'); ?></p>
		<style>
			div.mlw_qmn_themeBlock
			{
				cursor: pointer;
				position: relative;
				height: 100px;
				width: 100px;
				background-color: #eee;
				color: blue;
				border: #ccc solid 1px;
				border-radius: 4px;
				padding: 5px 5px 5px 5px;
				display: inline-block;
				box-sizing: border-box;
				margin: auto;
			}
			div.mlw_qmn_themeBlockActive
			{
				background-color: yellow;
			}
		</style>
		<?php
		foreach($registered_templates as $slug => $template) {
			?>
			<div onclick="mlw_qmn_theme('<?php echo $slug; ?>');" id="mlw_qmn_theme_block_<?php echo $slug; ?>" class="mlw_qmn_themeBlock <?php if ($mlw_quiz_options->theme_selected == $slug) {echo 'mlw_qmn_themeBlockActive';} ?>"><?php echo $template["name"]; ?></div>
			<?php
		}
		?>
		<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="mlw_qmn_themeBlock <?php if ($mlw_quiz_options->theme_selected == 'default') {echo 'mlw_qmn_themeBlockActive';} ?>"><?php _e('Custom', 'quiz-master-next'); ?></div>
		<script>
			mlw_qmn_theme('<?php echo $mlw_quiz_options->theme_selected; ?>');
		</script>
		<br /><br />
		<button id="save_styles_button" class="button-primary" onclick="javascript: document.quiz_style_form.submit();"><?php _e('Save Quiz Style', 'quiz-master-next'); ?></button>
		<hr />
		<h3><?php _e('Custom Style CSS', 'quiz-master-next'); ?></h3>
		<p><?php _e('For detailed help and guidance along with a list of different classes used in this plugin, please visit the following link:', 'quiz-master-next'); ?>
		<a target="_blank" href="http://quizmasternext.com/quiz-master-next-editing-quizs-style/">Style Guide</a></p>
		<button id="save_styles_button" class="button-primary" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>

		<table class="form-table">
			<tr>
				<td><textarea style="width: 100%; height: 700px;" id="quiz_css" name="quiz_css"><?php echo $mlw_quiz_options->quiz_stye; ?></textarea></td>
			</tr>
		</table>
		<button id="save_styles_button" class="button-primary" onclick="javascript: document.quiz_style_form.submit();"><?php _e('Save Quiz Style', 'quiz-master-next'); ?></button>
		</form>
	</div>
	<?php
}
?>
