<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Tools tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_tools_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Tools", 'quiz-master-next'), 'mlw_options_tools_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_tools_tab', 5);

/**
* Adds the Tools tab content to the Tools tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_tools_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval($_GET["quiz_id"]);
	//Update Quiz Table
	if (isset($_POST["mlw_reset_quiz_stats"]) && $_POST["mlw_reset_quiz_stats"] == "confirmation")
	{
		//Variables from reset stats form
		$mlw_reset_stats_quiz_id = intval( $_POST["mlw_reset_quiz_id"] );
		$results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
				'quiz_views' => 0,
				'quiz_taken' => 0,
				'last_activity' => date("Y-m-d H:i:s")
			),
			array( 'quiz_id' => $mlw_reset_stats_quiz_id ),
			array(
				'%d',
				'%d',
				'%s'
			),
			array( '%d' )
		);
		if ( $results ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The stats has been reset successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Quiz Stats Have Been Reset For Quiz Number $mlw_reset_stats_quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0010'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0010", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if ( isset( $_GET["quiz_id"] ) ) {
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
	}
	?>
	<div id="tabs-8" class="mlw_tab_content">
		<script type="text/javascript">
			var $j = jQuery.noConflict();
			// increase the default animation speed to exaggerate the effect
			$j.fx.speeds._default = 1000;
			$j(function() {
				$j( "#tabs" ).tabs();
			});
		</script>
	<script>
	jQuery(function() {
			jQuery('#mlw_reset_stats_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				width:700,
				hide: 'explode',
				buttons: {
				Ok: function() {
					jQuery(this).dialog('close');
					}
				}
			});

			jQuery('#mlw_reset_stats_button').click(function() {
				jQuery('#mlw_reset_stats_dialog').dialog('open');
				return false;
		}	);
		});
	</script>
		<p><?php _e('Use this button to reset all the stats collected for this quiz (Quiz Views and Times Quiz Has Been Taken).', 'quiz-master-next'); ?></p>
		<button class="button" id="mlw_reset_stats_button"><?php _e('Reset Quiz Views And Taken Stats', 'quiz-master-next'); ?></button>
		<hr />
		<h3>Addon Quiz Settings</h3>
		<div id="tabs">
			<ul>
				<?php do_action('mlw_qmn_options_tab'); ?>
			</ul>
			<?php do_action('mlw_qmn_options_tab_content'); ?>
		</div>
		<?php do_action('mlw_qmn_quiz_tools'); ?>
		<div id="mlw_reset_stats_dialog" title="Reset Stats For This Quiz" style="display:none;">
			<p><?php _e('Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.', 'quiz-master-next'); ?></p>
			<form action='' method='post'>
				<input type='hidden' name='mlw_reset_quiz_stats' value='confirmation' />
				<input type='hidden' name='mlw_reset_quiz_id' value='<?php echo $quiz_id; ?>' />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Reset All Stats For Quiz', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>
	</div>
	<?php
}
?>
