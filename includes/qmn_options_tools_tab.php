<?php
function qmn_settings_tools_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs("Tools", 'mlw_options_tools_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_tools_tab');
function mlw_options_tools_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Update Quiz Table
	if (isset($_POST["mlw_reset_quiz_stats"]) && $_POST["mlw_reset_quiz_stats"] == "confirmation")
	{
		//Variables from reset stats form
		$mlw_reset_stats_quiz_id = $_POST["mlw_reset_quiz_id"];
		$mlw_reset_update_sql = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_views=0, quiz_taken=0, last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=".$mlw_reset_stats_quiz_id;
		$mlw_reset_sql_results = $wpdb->query( $mlw_reset_update_sql );
		if ($mlw_reset_sql_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The stats has been reset successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Stats Have Been Reset For Quiz Number ".$mlw_leaderboard_quiz_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0010.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-8" class="mlw_tab_content">
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
		<p>Use this button to reset all the stats collected for this quiz (Quiz Views and Times Quiz Has Been Taken). </p>
		<button class="button" id="mlw_reset_stats_button">Reset Quiz Views And Taken Stats</button>
		<?php do_action('mlw_qmn_quiz_tools'); ?>
		<div id="mlw_reset_stats_dialog" title="Reset Stats For This Quiz" style="display:none;">
		<p>Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.</p>
		<?php
			echo "<form action='' method='post'>";
			echo "<input type='hidden' name='mlw_reset_quiz_stats' value='confirmation' />";
			echo "<input type='hidden' name='mlw_reset_quiz_id' value='".$quiz_id."' />";
			echo "<p class='submit'><input type='submit' class='button-primary' value='Reset All Stats For Quiz' /></p>";
			echo "</form>";
		?>
		</div>		
	</div>
	<?php
}
?>
