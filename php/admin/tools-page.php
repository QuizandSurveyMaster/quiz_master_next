<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
This page creates the main dashboard for the Quiz And Survey Master plugin
*/


/**
* Generates all of the quiz tools that are used
*
* Long Description
*
* @param type description
* @return type description
* @since 4.4.0
*/
function mlw_generate_quiz_tools()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}
	add_meta_box("qmn_restore_box", 'Restore Quiz', "qmn_restore_function", "quiz_wpss");
	add_meta_box("qmn_audit_box", 'Audit Trail', "mlw_tools_box", "quiz_wpss");

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-tooltip' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );
	?>

	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;

		$j(function() {
			$j("button, #prev_page, #next_page").button();

		});
	</script>
	<style type="text/css">
		textarea{
		border-color:#000000;
		color:#3300CC;
		cursor:hand;
		}
		p em {
		padding-left: 1em;
		color: #555;
		font-weight: bold;
		}
	</style>
	<div class="wrap">
	<h2><?php _e('Tools', 'quiz-master-next'); ?></h2>

	<div style="float:left; width:100%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss','advanced',null);  ?>
	</div>

	<div style="clear:both"></div>

	<?php echo mlw_qmn_show_adverts(); ?>

	</div>
	<?php
}

/**
* Allows the admin to restore a deleted quiz
*
* @return void
* @since 4.4.0
*/
function qmn_restore_function()
{
	global $wpdb;
	if (isset($_POST["restore_quiz"]))
	{
		$restore = $wpdb->update(
			$wpdb->prefix.'mlw_quizzes',
			array(
				'deleted' => 0
			),
			array(
				'quiz_id' => intval($_POST["restore_quiz"])
			),
			array(
				'%d'
			),
			array(
				'%d'
			)
		);
		if (!$restore)
		{
			echo "<span style='color:red;'>".__("There has been an error! Please try again.", "quiz-master-next")."</span>";
		}
		else
		{
			$my_query = new WP_Query( array('post_type' => 'quiz', 'meta_key' => 'quiz_id', 'meta_value' => intval($_POST["restore_quiz"])) );
			if( $my_query->have_posts() )
			{
			  while( $my_query->have_posts() )
				{
			    $my_query->the_post();
					$my_post = array(
				      'ID'           => get_the_ID(),
				      'post_status' => 'publish'
				  );
					wp_update_post( $my_post );
			  }
			}
			wp_reset_postdata();
			echo "<span style='color:red;'>".__("Quiz Has Been Restored!", "quiz-master-next")."</span>";
		}
	}
	$quizzes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=1");
	?>
	<h3><?php _e("Choose a quiz in the drop down and then click the button to restore a deleted quiz.", "quiz-master-next"); ?></h3>
	<form action='' method="post">
		<select name="restore_quiz">
			<?php
			foreach($quizzes as $quiz)
			{
				echo "<option value='".$quiz->quiz_id."'>".$quiz->quiz_name."</option>";
			}
			?>
		</select>
		<input type="submit" value="<?php _e('Restore Quiz', "quiz-master-next"); ?>" class="button" />
	</form>
	<?php
}

/**
* Creates the tools page that is used to make audits on the quizzes.
*
* @return void
* @since 4.4.0
*/
function mlw_tools_box()
{
	global $wpdb;
	$mlw_qmn_table_limit = 30;
	$mlw_qmn_audit_count = $wpdb->get_var( "SELECT COUNT(trail_id) FROM " . $wpdb->prefix . "mlw_qm_audit_trail" );

	if( isset($_GET{'mlw_audit_page'} ) )
	{
	   $mlw_qmn_audit_page = $_GET{'mlw_audit_page'} + 1;
	   $mlw_qmn_audit_begin = $mlw_qmn_table_limit * $mlw_qmn_audit_page ;
	}
	else
	{
	   $mlw_qmn_audit_page = 0;
	   $mlw_qmn_audit_begin = 0;
	}
	$mlw_qmn_audit_left = $mlw_qmn_audit_count - ($mlw_qmn_audit_page * $mlw_qmn_table_limit);

	$audit_trails = $wpdb->get_results( $wpdb->prepare( "SELECT trail_id, action_user, action, time
		FROM " . $wpdb->prefix . "mlw_qm_audit_trail
		ORDER BY trail_id DESC LIMIT %d, %d", $mlw_qmn_audit_begin, $mlw_qmn_table_limit ) );

	if( $mlw_qmn_audit_page > 0 )
	{
		$mlw_qmn_previous_page = $mlw_qmn_audit_page - 2;
		echo "<a id=\"prev_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_previous_page\">".sprintf(__('Previous %s Audits','quiz-master-next'),$mlw_qmn_table_limit)."</a>";
		if( $mlw_qmn_audit_left > $mlw_qmn_table_limit )
		{
			echo "<a id=\"next_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_audit_page\">".sprintf(__('Next %s Audits','quiz-master-next'),$mlw_qmn_table_limit)."</a>";
		}
	}
	else if( $mlw_qmn_audit_page == 0 )
	{
	   if( $mlw_qmn_audit_left > $mlw_qmn_table_limit )
	   {
			echo "<a id=\"next_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_audit_page\">".sprintf(__('Next %s Audits','quiz-master-next'),$mlw_qmn_table_limit)."</a>";
	   }
	}
	else if( $mlw_qmn_audit_left < $mlw_qmn_table_limit )
	{
	   $mlw_qmn_previous_page = $mlw_qmn_audit_page - 2;
	   echo "<a id=\"prev_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_previous_page\">".sprintf(__('Previous %s Audits','quiz-master-next'),$mlw_qmn_table_limit)."</a>";
	}
	?>
	<table class=widefat>
		<thead>
			<tr>
				<th>ID</th>
				<th><?php _e('User','quiz-master-next'); ?></th>
				<th><?php _e('Action','quiz-master-next'); ?></th>
				<th><?php _e('Time','quiz-master-next'); ?></th>
			</tr>
		</thead>
		<tbody id="the-list">

		<?php
	$alternate = "";
	foreach($audit_trails as $quote_data) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		echo "<tr{$alternate}>";
		echo "<td>" . $quote_data->trail_id . "</td>";
		echo "<td>" . $quote_data->action_user . "</td>";
		echo "<td>" . $quote_data->action ."</td>";
		echo "<td>" . $quote_data->time . "</td>";
		echo "</tr>";
	}
	?>
		</tbody>
	</table>
	<?php
}
?>
