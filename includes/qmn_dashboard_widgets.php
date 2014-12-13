<?php
function qmn_add_dashboard_widget() 
{
	wp_add_dashboard_widget(
		'qmn_snapshot_widget', 
		'Quiz Master Next Snapshot',
		'qmn_snapshot_dashboard_widget'
	);
}

add_action( 'wp_dashboard_setup', 'qmn_add_dashboard_widget' );


function qmn_snapshot_dashboard_widget() 
{
	global $wpdb;
	$mlw_qmn_today_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".date("Y-m-d")." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0");
	$mlw_last_week =  mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
	$mlw_last_week = date("Y-m-d", $mlw_last_week);
	$mlw_qmn_last_weekday_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_week." 00:00:00' AND '".$mlw_last_week." 23:59:59') AND deleted=0");
	if ($mlw_qmn_last_weekday_taken != 0)
	{
		$mlw_qmn_analyze_today = round((($mlw_qmn_today_taken - $mlw_qmn_last_weekday_taken) / $mlw_qmn_last_weekday_taken) * 100, 2);
	}
	else
	{
		$mlw_qmn_analyze_today = $mlw_qmn_today_taken * 100;
	}
	
	$mlw_stat_total_active_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0 LIMIT 1" );
	$mlw_stat_total_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions WHERE deleted=0 LIMIT 1" );
	
	$mlw_stat_most_popular_quiz = $wpdb->get_row( "SELECT quiz_name FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0 ORDER BY quiz_taken Desc LIMIT 1" );
	$mlw_stat_least_popular_quiz = $wpdb->get_row( "SELECT quiz_name FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0 ORDER BY quiz_taken ASC LIMIT 1" );
	?>
	<style>
	.qmn_dashboard_list
	{
		overflow: hidden;
		margin: 0;
	}
	.qmn_dashboard_list li:first-child
	{
		border-top: 0;
		width: 100%;
	}
	.qmn_dashboard_element
	{
		width: 50%;
		float: left;
		padding: 0;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		margin: 0;
		border-top: 1px solid #ececec;
		color: #aaa;
	}
	.qmn_dashboard_inside
	{
		display: block;
		color: #aaa;
		padding: 9px 12px;
		-webkit-transition: all ease .5s;
		position: relative;
		font-size: 12px;
	}
	.qmn_dashboard_inside strong
	{
		font-size: 18px;
		line-height: 1.2em;
		font-weight: 400;
		display: block;
		color: #21759b;
	}
	.qmn_dashboard_graph
	{
		width: 25%;
		height: 10px;
		display: block;
		float: right;
		position: absolute;
		right: 0;
		top: 50%;
		margin-right: 12px;
		margin-top: -1.25em;
		font-size: 18px
	}
	.qmn_dashboard_graph img
	{
		width: 15px;
		height: 15px;
	}
	</style>
	<ul class="qmn_dashboard_list">
		<li class="qmn_dashboard_element">
			<div class="qmn_dashboard_inside">
				<strong><?php echo $mlw_qmn_today_taken; ?></strong>
				quizzes taken today
				<span class="qmn_dashboard_graph">
					<?php 
					echo $mlw_qmn_analyze_today."% ";
					if ($mlw_qmn_analyze_today >= 0)
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/green_triangle.png'/>";
					}
					else
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/red_triangle.png'/>";
					}
					?>
				</span>
			</div>
		</li>
		<li class="qmn_dashboard_element">
			<div class="qmn_dashboard_inside">
				<strong><?php echo $mlw_stat_total_active_quiz; ?></strong>
				total active quizzes
			</div>
		</li>
		<li class="qmn_dashboard_element">
			<div class="qmn_dashboard_inside">
				<strong><?php echo $mlw_stat_total_questions; ?></strong>
				total active questions
			</div>
		</li>
		<li class="qmn_dashboard_element">
			<div class="qmn_dashboard_inside">
				<strong><?php echo $mlw_stat_most_popular_quiz->quiz_name; ?></strong>
				most popular quiz
			</div>
		</li>
		<li class="qmn_dashboard_element">
			<div class="qmn_dashboard_inside">
				<strong><?php echo $mlw_stat_least_popular_quiz->quiz_name; ?></strong>
				least popular quiz
			</div>
		</li>
	</ul>
	<?php
}
?>
