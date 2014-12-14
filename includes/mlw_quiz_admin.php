<?php
/*
This page lists all the quizzes currently on the website and allows you to create more quizzes.
*/
/* 
Copyright 2013, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_admin()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$table_name = $wpdb->prefix . "mlw_quizzes";

	//Create new quiz
	if ( isset( $_POST["create_quiz"] ) && $_POST["create_quiz"] == "confirmation" )
	{
		$quiz_name = htmlspecialchars($_POST["quiz_name"], ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->create_quiz($quiz_name);
	}

	//Delete quiz
	if (isset( $_POST["delete_quiz"] ) && $_POST["delete_quiz"] == "confirmation")
	{
		$mlw_quiz_id = intval($_POST["quiz_id"]);
		$quiz_name = $_POST["delete_quiz_name"];
		$mlwQuizMasterNext->quizCreator->delete_quiz($mlw_quiz_id, $quiz_name);
	}	

	//Edit Quiz Name
	if (isset($_POST["quiz_name_editted"]) && $_POST["quiz_name_editted"] == "confirmation")
	{
		$mlw_edit_quiz_id = intval($_POST["edit_quiz_id"]);
		$mlw_edit_quiz_name = htmlspecialchars($_POST["edit_quiz_name"], ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->edit_quiz_name($mlw_edit_quiz_id, $mlw_edit_quiz_name);		
	}
	
	//Duplicate Quiz
	if (isset($_POST["duplicate_quiz"]) && $_POST["duplicate_quiz"] == "confirmation")
	{
		$mlw_duplicate_quiz_id = intval($_POST["duplicate_quiz_id"]);
		$mlw_duplicate_quiz_name = htmlspecialchars($_POST["duplicate_new_quiz_name"], ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->duplicate_quiz($mlw_duplicate_quiz_id, $mlw_duplicate_quiz_name, isset($_POST["duplicate_questions"]));
	}

	//Retrieve list of quizzes
	global $wpdb;
	$mlw_qmn_table_limit = 25;
	$mlw_qmn_quiz_count = $wpdb->get_var( "SELECT COUNT(quiz_id) FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0'" );
	
	if( isset($_GET{'mlw_quiz_page'} ) )
	{
	   $mlw_qmn_quiz_page = $_GET{'mlw_quiz_page'} + 1;
	   $mlw_qmn_quiz_begin = $mlw_qmn_table_limit * $mlw_qmn_quiz_page ;
	}
	else
	{
	   $mlw_qmn_quiz_page = 0;
	   $mlw_qmn_quiz_begin = 0;
	}
	$mlw_qmn_quiz_left = $mlw_qmn_quiz_count - ($mlw_qmn_quiz_page * $mlw_qmn_table_limit);
	$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT quiz_id, quiz_name, quiz_views, quiz_taken, last_activity 
		FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0' 
		ORDER BY quiz_id DESC LIMIT %d, %d", $mlw_qmn_quiz_begin, $mlw_qmn_table_limit ) );
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
<script type="text/javascript"
  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
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
			$j('#new_quiz_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				width:700,
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
		
			$j('#new_quiz_button').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
			$j('#new_quiz_button_two').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
		});
		function deleteQuiz(id,quizName){
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
			var idText = document.getElementById("delete_quiz_id");
			var idHidden = document.getElementById("quiz_id");
			var idHiddenName = document.getElementById("delete_quiz_name");
			idText.innerHTML = id;
			idHidden.value = id;
			idHiddenName.value = quizName;
		};
		function editQuizName(id, quizName){
			$j("#edit_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#edit_dialog").dialog('open');
			document.getElementById("edit_quiz_name").value = quizName;
			document.getElementById("edit_quiz_id"). value = id;			
		}
		function duplicateQuiz(id, quizName){
			$j("#duplicate_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#duplicate_dialog").dialog('open');
			document.getElementById("duplicate_quiz_name").innerHTML = quizName;
			document.getElementById("duplicate_quiz_id"). value = id;			
		}
	</script>
	<style type="text/css">
	div.mlw_quiz_options input[type='text'] {
		border-color:#000000;
		color:#3300CC; 
		cursor:hand;
		}
	</style>
	<style>
		.linkOptions
		{
			font-size: 14px !important;
		}
		.linkDelete
		{
			color: red !important;
		}
		.linkOptions:hover
		{
			background-color: black;
		}
	</style>
	<div class="wrap">
	<div class='mlw_quiz_options'>
	<h2>Quizzes<a id="new_quiz_button" href="javascript:();" class="add-new-h2">Add New</a></h2>
	<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
	<div style="width: 85%; float: left;">
		<div class="tablenav top">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo $mlw_qmn_quiz_count; ?> quizzes</span>
				<span class="pagination-links">
					<?php
					$mlw_qmn_previous_page = 0;
					$mlw_current_page = $mlw_qmn_quiz_page+1;
					$mlw_total_pages = ceil($mlw_qmn_quiz_count/$mlw_qmn_table_limit);
					if( $mlw_qmn_quiz_page > 0 )
					{
					   	$mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
					   	echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
						echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
					   	if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
					   	{
							echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
					   	}
						else
						{
							echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
					   	}
					}
					else if( $mlw_qmn_quiz_page == 0 )
					{
					   if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
					   {
							echo "<a class=\"prev-page disabled\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
							echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
							echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
					   }
					}
					else if( $mlw_qmn_quiz_left < $mlw_qmn_table_limit )
					{
					   $mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
					   echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
						echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
						echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
					}
					?>
				</span>
				<br class="clear">
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr>
					<th>Quiz ID</th>
					<th>Quiz Name</th>
					<th>Quiz Shortcode</th>
					<th>Leaderboard Shortcode</th>
					<th>Quiz Views</th>
					<th>Quiz Taken</th>
					<th>Last Modified</th>
				</tr>
			</thead>
			<tbody id="the-list">
				<?php 
				$quotes_list = "";
				$display = "";
				$alternate = "";
				foreach($mlw_quiz_data as $mlw_quiz_info) {
					if($alternate) $alternate = "";
					else $alternate = " class=\"alternate\"";
					$quotes_list .= "<tr{$alternate}>";
					$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_id . "</span></td>";
					$quotes_list .= "<td class='post-title column-title'><span style='font-size:16px;'>" . esc_html($mlw_quiz_info->quiz_name) ." </span><span style='color:green;font-size:12px;'><a onclick=\"editQuizName('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>(Edit Name)</a></span>";
					$quotes_list .= "<div class=\"row-actions\"><a class='linkOptions' href='admin.php?page=mlw_quiz_options&&quiz_id=".$mlw_quiz_info->quiz_id."'>Edit</a> | <a class='linkOptions' href='admin.php?page=mlw_quiz_results&&quiz_id=".$mlw_quiz_info->quiz_id."'>Results</a> | <a href='javascript:();' class='linkOptions' onclick=\"duplicateQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\">Duplicate</a> | <a class='linkOptions linkDelete' onclick=\"deleteQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>Delete</a></div></td>";
					$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
					$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster_leaderboard mlw_quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
					$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_views . "</span></td>";
					$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_taken ."</span></td>";
					$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->last_activity ."</span></td>";
					$quotes_list .= "</tr>";
				}
				echo $quotes_list; ?>
			</tbody>
			<tfoot>
				<tr>
					<th>Quiz ID</th>
					<th>Quiz Name</th>
					<th>Quiz Shortcode</th>
					<th>Leaderboard Shortcode</th>
					<th>Quiz Views</th>
					<th>Quiz Taken</th>
					<th>Last Modified</th>
				</tr>
			</tfoot>
		</table>
	</div>
	<div style="width: 15%; float:right;">
		<h3 style="text-align: center;">My Local Webstop News</h3>
		<iframe src="http://www.mylocalwebstop.com/mlw_news.html?cache=<?php echo rand(); ?>" seamless="seamless" style="width: 100%; height: 550px;"></iframe>
	</div>
	<div style="clear: both;"></div>
	<?php echo mlw_qmn_show_adverts(); ?>
	<!--Dialogs-->
	
	<!--New Quiz Dialog-->
	<div id="new_quiz_dialog" title="Create New Quiz" style="display:none;">
		<?php
		echo "<form action='' method='post'>";
		echo "<input type='hidden' name='create_quiz' value='confirmation' />";
		?>
		<table class="wide" style="text-align: left; white-space: nowrap;">
		<thead>
		
		<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td></td>
		</tr>
			
		<tr valign="top">
		<th scope="row"><h3>Create New Quiz</h3></th>
		<td></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Quiz Name</th>
		<td>
		<input type="text" name="quiz_name" value="" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;"/>
		</td>
		</tr>
		</thead>
		</table>
		<?php
		echo "<p class='submit'><input type='submit' class='button-primary' value='Create Quiz' /></p>";
		echo "</form>";
		?>
	</div>
	
	<!--Edit Quiz Name Dialog-->
	<div id="edit_dialog" title="Edit Quiz Name" style="display:none;">
		<h3>Quiz Name:</h3><br />
		<form action='' method='post'>
		<input type="text" id="edit_quiz_name" name="edit_quiz_name" />
		<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" />
		<input type='hidden' name='quiz_name_editted' value='confirmation' />
		<input type="submit" class="button-primary" value="Edit" />
		</form>
	</div>
	
	<!--Duplicate Quiz Dialog-->
	<div id="duplicate_dialog" title="Duplicate Quiz" style="display:none;">
		<h3>Create a new quiz with the same settings as <span id="duplicate_quiz_name"></span>. </h3><br />
		<form action='' method='post'>
			<label for="duplicate_questions">Duplicate questions with quiz</label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
			<br />
			<label for="duplicate_new_quiz_name">Name Of New Quiz:</label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" /><br />
			<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
			<input type='hidden' name='duplicate_quiz' value='confirmation' />
			<input type="submit" class="button-primary" value="Duplicate" />
		</form>
	</div>
	
	<!--Delete Quiz Dialog-->
	<div id="delete_dialog" title="Delete Quiz?" style="display:none;">
	<h3><b>Are you sure you want to delete Quiz <span id="delete_quiz_id"></span>?</b></h3>
	<?php
	echo "<form action='' method='post'>";
	echo "<input type='hidden' name='delete_quiz' value='confirmation' />";
	echo "<input type='hidden' id='quiz_id' name='quiz_id' value='' />";
	echo "<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />";
	echo "<p class='submit'><input type='submit' class='button-primary' value='Delete Quiz' /></p>";
	echo "</form>";	
	?>
	</div>
	
	</div>
	</div>
<?php
}
?>
