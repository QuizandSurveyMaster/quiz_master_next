<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function generates the admin side quiz results page
*
* @return void
* @since 4.4.0
*/
function mlw_generate_quiz_results()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}
	global $wpdb;
	global $mlwQuizMasterNext;

	///Delete Results Function
	if (isset($_POST["delete_results"]) && $_POST["delete_results"] == "confirmation")
	{
		///Variables from delete result form
		$mlw_delete_results_confirmation = $_POST["delete_results"];
		$mlw_delete_results_id = $_POST["result_id"];
		$mlw_delete_results_name = $_POST["delete_quiz_name"];
		$mlw_delete_results_update_sql = "UPDATE " . $wpdb->prefix . "mlw_results" . " SET deleted=1 WHERE result_id=".$mlw_delete_results_id;
		$mlw_delete_results_results = $wpdb->query( $mlw_delete_results_update_sql );
		if ($mlw_delete_results_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('Your results has been deleted successfully.','quiz-master-next'), 'success');

			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Results Has Been Deleted From: ".$mlw_delete_results_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0021'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0021", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_POST["bulk_delete"]) && $_POST["bulk_delete"] == "confirmation")
	{
		$results = $_POST["delete_results"];
		if (is_array($results))
		{
			foreach($results as $result)
			{
				$wpdb->update(
					$wpdb->prefix."mlw_results",
					array(
						'deleted' => 1,
					),
					array( 'result_id' => $result ),
					array(
						'%d'
					),
					array( '%d' )
				);
			}
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Results Has Been Bulk Deleted' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
	}

	global $wpdb;
	$mlw_qmn_table_limit = 30;
	$search_phrase_sql = '';
	$order_by_sql = 'ORDER BY result_id DESC';
	if ( isset( $_GET["qmn_search_phrase"] ) && !empty( $_GET["qmn_search_phrase"] ) ) {
		$search_phrase = $_GET["qmn_search_phrase"];
		$search_phrase_sql = " AND (quiz_name LIKE '%$search_phrase%' OR name LIKE '%$search_phrase%')";
	}
	if ( isset( $_GET["qmn_order_by"] ) ) {
		 switch ( $_GET["qmn_order_by"] )
		 {
			 case 'quiz_name':
				 $order_by_sql = "ORDER BY quiz_name DESC";
				 break;
			 case 'name':
				 $order_by_sql = "ORDER BY name DESC";
				 break;
			 case 'point_score':
				 $order_by_sql = "ORDER BY point_score DESC";
				 break;
			 case 'correct_score':
				 $order_by_sql = "ORDER BY correct_score DESC";
				 break;
			 default:
				 $order_by_sql = "ORDER BY result_id DESC";
		 }
	}
	
	$mlw_qmn_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM " . $wpdb->prefix . "mlw_results WHERE deleted='0'$search_phrase_sql" );

	if( isset($_GET['mlw_result_page'] ) )
	{
	   $mlw_qmn_result_page = $_GET['mlw_result_page'] + 1;
	   $mlw_qmn_result_begin = $mlw_qmn_table_limit * $mlw_qmn_result_page ;
	}
	else
	{
	   $mlw_qmn_result_page = 0;
	   $mlw_qmn_result_begin = 0;
	}
	$mlw_qmn_result_left = $mlw_qmn_results_count - ($mlw_qmn_result_page * $mlw_qmn_table_limit);
	if (isset($_GET["quiz_id"]) && $_GET["quiz_id"] != "")
	{
		$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_results WHERE deleted='0' AND quiz_id=%d$search_phrase_sql $order_by_sql LIMIT %d, %d", intval($_GET["quiz_id"]), $mlw_qmn_result_begin, $mlw_qmn_table_limit ) );
	}
	else
	{
		$mlw_quiz_data = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "mlw_results WHERE deleted='0'$search_phrase_sql $order_by_sql LIMIT $mlw_qmn_result_begin, $mlw_qmn_table_limit" );
	}
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-tooltip' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	?>
	<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.0/jquery.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>-->
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;

		$j(function() {
			$j("button, #prev_page, #next_page").button();

		});
		function deleteResults(id,quizName){
			$j("#delete_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#delete_dialog").dialog('open');
			var idHidden = document.getElementById("result_id");
			var idHiddenName = document.getElementById("delete_quiz_name");
			idHidden.value = id;
			idHiddenName.value = quizName;
		};
	</script>
	<style>
  		label {
    		display: inline-block;
    		width: 5em;
  		}
  	</style>
	<style type="text/css">
	div.mlw_quiz_options input[type='text'] {
		border-color:#000000;
		color:#3300CC;
		cursor:hand;
		}
	</style>
	<div class="wrap">
		<div class='mlw_quiz_options'>
			<h2><?php _e('Quiz Results','quiz-master-next'); ?></h2>
			<?php
			$mlwQuizMasterNext->alertManager->showAlerts();
			?>
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<a href="javascript: document.bulk_delete_form.submit();" class="button action">Bulk Delete</a>
				</div>
				<div class="tablenav-pages">
					<span class="displaying-num"><?php echo sprintf(_n('One result', '%s results', $mlw_qmn_results_count, 'quiz-master-next'), number_format_i18n($mlw_qmn_results_count)); ?></span>
					<span class="pagination-links">
						<?php
						$mlw_qmn_previous_page = 0;
						$mlw_current_page = $mlw_qmn_result_page+1;
						$mlw_total_pages = ceil($mlw_qmn_results_count/$mlw_qmn_table_limit);
						if( $mlw_qmn_result_page > 0 )
						{
								$mlw_qmn_previous_page = $mlw_qmn_result_page - 2;
								echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_previous_page\"><</a>";
								echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
								if( $mlw_qmn_result_left > $mlw_qmn_table_limit )
								{
								echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_result_page\">></a>";
								}
							else
							{
								echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_result_page\">></a>";
								}
						}
						else if( $mlw_qmn_result_page == 0 )
						{
							if( $mlw_qmn_result_left > $mlw_qmn_table_limit )
							{
								echo "<a class=\"prev-page disabled\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_previous_page\"><</a>";
								echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
								echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_result_page\">></a>";
							}
						}
						else if( $mlw_qmn_result_left < $mlw_qmn_table_limit )
						{
							$mlw_qmn_previous_page = $mlw_qmn_result_page - 2;
							echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_previous_page\"><</a>";
							echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
							echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_results&&mlw_result_page=$mlw_qmn_result_page\">></a>";
						}
						?>
					</span>
					<br class="clear">
				</div>
			</div>
			<form action='' method="get">
				<input type="hidden" name="page" value="mlw_quiz_results">
				<p class="search-box">
					<label for="qmn_search_phrase">Search Results:</label>
					<input type="search" id="qmn_search_phrase" name="qmn_search_phrase" value="">
					<label for="qmn_order_by">Order By:</label>
					<select id="qmn_order_by" name="qmn_order_by">
						<option value="quiz_name">Quiz Name</option>
						<option value="name">User Name</option>
						<option value="point_score">Points</option>
						<option value="correct_score">Correct Percent</option>
						<option value="default">Default (time)</option>
					</select>
					<button class="button">Search Results</button>
				</p>
			</form>
			<form action="" method="post" name="bulk_delete_form">
				<input type="hidden" name="bulk_delete" value="confirmation" />
				<table class=widefat>
					<thead>
						<tr>
							<th></th>
							<th><?php _e('Actions','quiz-master-next'); ?></th>
							<th><?php _e('Quiz Name','quiz-master-next'); ?></th>
							<th><?php _e('Score','quiz-master-next'); ?></th>
							<th><?php _e('Time To Complete','quiz-master-next'); ?></th>
							<th><?php _e('Name','quiz-master-next'); ?></th>
							<th><?php _e('Business','quiz-master-next'); ?></th>
							<th><?php _e('Email','quiz-master-next'); ?></th>
							<th><?php _e('Phone','quiz-master-next'); ?></th>
							<th><?php _e('Time Taken','quiz-master-next'); ?></th>
						</tr>
					</thead>
					<?php
					$quotes_list = "";
					$display = "";
					$alternate = "";
					foreach($mlw_quiz_data as $mlw_quiz_info) {
						if($alternate) $alternate = "";
						else $alternate = " class=\"alternate\"";
						$mlw_complete_time = '';
						$mlw_qmn_results_array = @unserialize($mlw_quiz_info->quiz_results);
						if (is_array($mlw_qmn_results_array))
						{
							$mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);
							if ($mlw_complete_hours > 0)
							{
								$mlw_complete_time .= "$mlw_complete_hours hours ";
							}
							$mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
							if ($mlw_complete_minutes > 0)
							{
								$mlw_complete_time .= "$mlw_complete_minutes minutes ";
							}
							$mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
							$mlw_complete_time .=  "$mlw_complete_seconds seconds";
						}

						$quotes_list .= "<tr{$alternate}>";
						$quotes_list .= "<td><input type='checkbox' name='delete_results[]' value='".$mlw_quiz_info->result_id. "' /></td>";
						$quotes_list .= "<td><span style='color:green;font-size:16px;'><a href='admin.php?page=mlw_quiz_result_details&&result_id=".$mlw_quiz_info->result_id."'>View</a>|<a onclick=\"deleteResults('".$mlw_quiz_info->result_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='#'>Delete</a></span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_name . "</span></td>";
						if ($mlw_quiz_info->quiz_system == 0)
						{
							$quotes_list .= "<td class='post-title column-title'><span style='font-size:16px;'>" . $mlw_quiz_info->correct ." out of ".$mlw_quiz_info->total." or ".$mlw_quiz_info->correct_score."%</span></td>";
						}
						if ($mlw_quiz_info->quiz_system == 1)
						{
							$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->point_score . " Points</span></td>";
						}
						if ($mlw_quiz_info->quiz_system == 2)
						{
							$quotes_list .= "<td><span style='font-size:16px;'>".__('Not Graded','quiz-master-next')."</span></td>";
						}
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_complete_time ."</span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->name ."</span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->business ."</span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->email ."</span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->phone ."</span></td>";
						$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->time_taken ."</span></td>";
						$quotes_list .= "</tr>";
					}
					$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
					echo $display;
					?>
				</table>
			</form>

			<div id="delete_dialog" title="Delete Results?" style="display:none;">
				<h3><b><?php _e('Are you sure you want to delete these results?','quiz-master-next'); ?></b></h3>
				<form action='' method='post'>
					<input type='hidden' name='delete_results' value='confirmation' />
					<input type='hidden' id='result_id' name='result_id' value='' />
					<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
					<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Delete Results','quiz-master-next'); ?>' /></p>
				</form>
			</div>
		</div>
	</div>
<?php
}
?>
