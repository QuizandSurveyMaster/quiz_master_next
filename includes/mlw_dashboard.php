<?php
/*
This page creates the main dashboard for the Quiz Master Next plugin
*/
/* 
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_dashboard()
{
	//Page Variables
	$mlw_quiz_version = get_option('mlw_quiz_master_version');
	
	///Creates the widgets
	add_meta_box("wpss_mrts", 'Quiz Daily Stats - Times Taken', "mlw_dashboard_box", "quiz_wpss");  
	add_meta_box("wpss_mrts", 'Quiz Total Stats', "mlw_dashboard_box_three", "quiz_wpss3");
	add_meta_box("wpss_mrts", 'Quiz Weekly Stats - Times Taken', "mlw_dashboard_box_four", "quiz_wpss4");
	add_meta_box("wpss_mrts", 'Quiz Monthly Stats - Times Taken', "mlw_dashboard_box_five", "quiz_wpss5");
	if ( get_option('mlw_advert_shows') == 'true' )
	{
		add_meta_box("wpss_mrts", 'My Local Webstop Services', "mlw_dashboard_box_six", "quiz_wpss6"); 
	}
	add_meta_box("wpss_mrts", 'Quizzes Taken Today', "mlw_qmn_daily_percent_taken_widget", "quiz_wpss10");
	add_meta_box("wpss_mrts", 'Quizzes Taken Last 7 Days', "mlw_qmn_weekly_percent_taken_widget", "quiz_wpss11");
	add_meta_box("wpss_mrts", 'Quizzes Taken Last 30 Days', "mlw_qmn_monthly_percent_taken_widget", "quiz_wpss12");
	add_meta_box("wpss_mrts", 'Quizzes Taken Last 120 Days', "mlw_qmn_quaterly_percent_taken_widget", "quiz_wpss13");
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-tooltip' );
	?>
	<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>jquery_sparkline.js"></script>
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
			$j(function() {
   				$j( document ).tooltip();
 			});
		});
		$j(function() {
        	$j('.inlinesparkline').sparkline('html', {type: 'line', width: '400', height: '200'}); 
		});
	</script>
	<div class="wrap">
	<h2>Quiz Master Next Version <?php echo $mlw_quiz_version; ?> Statistics</h2>
	
	<?php echo mlw_qmn_show_adverts(); ?>
	<!--Display Widget Boxes-->
	<div style="float:left; width:19%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss10','advanced','');  ?>	
	</div>
	
	<div style="float:left; width:19%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss11','advanced','');  ?>	
	</div>
	
	<div style="float:left; width:19%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss12','advanced','');  ?>	
	</div>
	
	<div style="float:left; width:19%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss13','advanced','');  ?>	
	</div>
	
	<div style="float:right; width:24%; " class="inner-sidebar1">
		<?php if ( get_option('mlw_advert_shows') == 'true' ) {do_meta_boxes('quiz_wpss6','advanced','');} ?>	
	</div>
	
	<div style="float:left; width:38%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss','advanced','');  ?>	
	</div>
	
	<div style="float:left; width:38%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss4','advanced','');  ?>	
	</div>
	
	<!--<div style="clear:both">-->
	
	<div style="float:left; width:38%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss5','advanced','');  ?>	
	</div>
	
	<div style="float:left; width:38%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss3','advanced','');  ?>	
	</div>
	
	<div style="clear:both">
	</div>
	<?php
}

//Quiz Daily Stats Widget - shows graph of quizzes taken each day for last 7 days
function mlw_dashboard_box()
{
	//Gather the weekly stats, one variable for each day for the graph
	global $wpdb;
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".date("Y-m-d")." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_today = $wpdb->get_results($sql);
	$mlw_quiz_taken_today = $wpdb->num_rows;
	
	$mlw_yesterday =  mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	$mlw_yesterday = date("Y-m-d", $mlw_yesterday);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_yesterday." 00:00:00' AND '".$mlw_yesterday." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_yesterday = $wpdb->get_results($sql);
	$mlw_quiz_taken_yesterday = $wpdb->num_rows;
	
	$mlw_two_days_ago =  mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
	$mlw_two_days_ago = date("Y-m-d", $mlw_two_days_ago);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_two_days_ago." 00:00:00' AND '".$mlw_two_days_ago." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_two_days = $wpdb->get_results($sql);
	$mlw_quiz_taken_two_days = $wpdb->num_rows;
	
	$mlw_three_days_ago =  mktime(0, 0, 0, date("m")  , date("d")-3, date("Y"));
	$mlw_three_days_ago = date("Y-m-d", $mlw_three_days_ago);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_three_days_ago." 00:00:00' AND '".$mlw_three_days_ago." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_three_days = $wpdb->get_results($sql);
	$mlw_quiz_taken_three_days = $wpdb->num_rows;
	
	$mlw_four_days_ago =  mktime(0, 0, 0, date("m")  , date("d")-4, date("Y"));
	$mlw_four_days_ago = date("Y-m-d", $mlw_four_days_ago);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_four_days_ago." 00:00:00' AND '".$mlw_four_days_ago." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_four_days = $wpdb->get_results($sql);
	$mlw_quiz_taken_four_days = $wpdb->num_rows;
	
	$mlw_five_days_ago =  mktime(0, 0, 0, date("m")  , date("d")-5, date("Y"));
	$mlw_five_days_ago = date("Y-m-d", $mlw_five_days_ago);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_five_days_ago." 00:00:00' AND '".$mlw_five_days_ago." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_five_days = $wpdb->get_results($sql);
	$mlw_quiz_taken_five_days = $wpdb->num_rows;
	
	$mlw_six_days_ago =  mktime(0, 0, 0, date("m")  , date("d")-6, date("Y"));
	$mlw_six_days_ago = date("Y-m-d", $mlw_six_days_ago);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_six_days_ago." 00:00:00' AND '".$mlw_six_days_ago." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_six_days = $wpdb->get_results($sql);
	$mlw_quiz_taken_six_days = $wpdb->num_rows;
	
	$mlw_last_week =  mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
	$mlw_last_week = date("Y-m-d", $mlw_last_week);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_week." 00:00:00' AND '".$mlw_last_week." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_week = $wpdb->get_results($sql);
	$mlw_quiz_taken_week = $wpdb->num_rows;
	?>
	<div>
	<span class="inlinesparkline"><?php echo $mlw_quiz_taken_week.",".$mlw_quiz_taken_six_days.",".$mlw_quiz_taken_five_days.",".$mlw_quiz_taken_four_days.",".$mlw_quiz_taken_three_days.",".$mlw_quiz_taken_two_days.",".$mlw_quiz_taken_yesterday.",".$mlw_quiz_taken_today; ?></span>
	</div>
	<?php
}

//Quiz Total Stats - shows other useful stats
function mlw_dashboard_box_three()
{
	//Function Variables
	global $wpdb;
	
	//Stats From Quiz Table
	$mlw_stat_total_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes" );
	$mlw_stat_total_deleted_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=1" );
	$mlw_stat_total_active_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0" );
	
	//Stats From Question Table
	$mlw_stat_total_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions" );
	
	
	$sql = "SELECT SUM(quiz_views) AS QuizViews FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0";
	$mlw_quiz_views = $wpdb->get_results($sql);

	foreach($mlw_quiz_views as $mlw_eaches) {
		$mlw_quiz_views = $mlw_eaches->QuizViews;
		break;
	}

	$sql = "SELECT SUM(quiz_taken) AS QuizTaken FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0";
	$mlw_quiz_taken = $wpdb->get_results($sql);

	foreach($mlw_quiz_taken as $mlw_eaches) {
		$mlw_quiz_taken = $mlw_eaches->QuizTaken;
		break;
	}
	
	$sql = "SELECT ROUND(AVG(quiz_views), 0) AS AvgViews FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0";
	$mlw_average_views = $wpdb->get_results($sql);

	foreach($mlw_average_views as $mlw_eaches) {
		$mlw_average_views = $mlw_eaches->AvgViews;
		break;
	}
	
	$sql = "SELECT ROUND(AVG(quiz_taken), 0) AS AvgTaken FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0";
	$mlw_average_taken = $wpdb->get_results($sql);

	foreach($mlw_average_taken as $mlw_eaches) {
		$mlw_average_taken = $mlw_eaches->AvgTaken;
		break;
	}
	
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0 ORDER BY quiz_views DESC LIMIT 1";
	$mlw_quiz_most_viewed = $wpdb->get_results($sql);

	foreach($mlw_quiz_most_viewed as $mlw_eaches) {
		$mlw_quiz_most_viewed = $mlw_eaches->quiz_name;
		break;
	}
	
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0 ORDER BY quiz_taken DESC LIMIT 1";
	$mlw_quiz_most_taken = $wpdb->get_results($sql);

	foreach($mlw_quiz_most_taken as $mlw_eaches) {
		$mlw_quiz_most_taken = $mlw_eaches->quiz_name;
		break;
	}
	?>
	<div>
	<table width='100%'>
	<tr>
	<td align='left'>Total Created Quizzes</td>
	<td align='right'><?php echo $mlw_stat_total_quiz; ?></td>
	</tr>
	<tr>
	<td align='left'>Total Deleted Quizzes</td>
	<td align='right'><?php echo $mlw_stat_total_deleted_quiz; ?></td>
	</tr>
	<tr>
	<td align='left'>Total Active Quizzes</td>
	<td align='right'><?php echo $mlw_stat_total_active_quiz; ?></td>
	</tr>
	<tr>
	<td align='left'>Total Created Questions</td>
	<td align='right'><?php echo $mlw_stat_total_questions; ?></td>
	</tr>
	<tr>
	<td align='left'>Total Times All Active Quizzes Have Been Viewed</td>
	<td align='right'><?php echo $mlw_quiz_views; ?></td>
	</tr>
	<tr>
	<td align='left'>Total Times All Active Quizzes Have Been Taken</td>
	<td align='right'><?php echo $mlw_quiz_taken; ?></td>
	</tr>
	<tr>
	<td align='left'>Average Amount Each Active Quiz Has Been Viewed</td>
	<td align='right'><?php echo $mlw_average_views; ?></td>
	</tr>
	<tr>
	<td align='left'>Average Amount Each Active Quiz Has Been Taken</td>
	<td align='right'><?php echo $mlw_average_taken; ?></td>
	</tr>
	<tr>
	<td align='left'>Quiz That Has Been Viewed The Most</td>
	<td align='right'><?php echo $mlw_quiz_most_viewed; ?></td>
	</tr>
	<tr>
	<td align='left'>Quiz That Has Been Taken The Most</td>
	<td align='right'><?php echo $mlw_quiz_most_taken; ?></td>
	</tr>
	</table>
	</div>
<?php	
}
function mlw_dashboard_box_four()
{
	//Gather the weekly stats, one variable for each day for the graph
	global $wpdb;	
	$mlw_this_week =  mktime(0, 0, 0, date("m")  , date("d")-6, date("Y"));
	$mlw_this_week = date("Y-m-d", $mlw_this_week);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_this_week." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_this_week = $wpdb->get_results($sql);
	$mlw_quiz_taken_this_week = $wpdb->num_rows;
	
	$mlw_last_week_first =  mktime(0, 0, 0, date("m")  , date("d")-13, date("Y"));
	$mlw_last_week_first = date("Y-m-d", $mlw_last_week_first);
	$mlw_last_week_last =  mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
	$mlw_last_week_last = date("Y-m-d", $mlw_last_week_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_week_first." 00:00:00' AND '".$mlw_last_week_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_last_week = $wpdb->get_results($sql);
	$mlw_quiz_taken_last_week = $wpdb->num_rows;
	
	$mlw_two_week_first =  mktime(0, 0, 0, date("m")  , date("d")-20, date("Y"));
	$mlw_two_week_first = date("Y-m-d", $mlw_two_week_first);
	$mlw_two_week_last =  mktime(0, 0, 0, date("m")  , date("d")-14, date("Y"));
	$mlw_two_week_last = date("Y-m-d", $mlw_two_week_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_two_week_first." 00:00:00' AND '".$mlw_two_week_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_two_week = $wpdb->get_results($sql);
	$mlw_quiz_taken_two_week = $wpdb->num_rows;
	
	$mlw_three_week_first =  mktime(0, 0, 0, date("m")  , date("d")-27, date("Y"));
	$mlw_three_week_first = date("Y-m-d", $mlw_three_week_first);
	$mlw_three_week_last =  mktime(0, 0, 0, date("m")  , date("d")-21, date("Y"));
	$mlw_three_week_last = date("Y-m-d", $mlw_three_week_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_three_week_first." 00:00:00' AND '".$mlw_three_week_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_three_week = $wpdb->get_results($sql);
	$mlw_quiz_taken_three_week = $wpdb->num_rows;
	?>
	<div>
	<span class="inlinesparkline"><?php echo $mlw_quiz_taken_three_week.",".$mlw_quiz_taken_two_week.",".$mlw_quiz_taken_last_week.",".$mlw_quiz_taken_this_week; ?></span>
	</div>
	<?php
}
function mlw_dashboard_box_five()
{
	//Gather the monthly stats, one variable for each day for the graph
	global $wpdb;	
	$mlw_this_month =  mktime(0, 0, 0, date("m")  , date("d")-29, date("Y"));
	$mlw_this_month = date("Y-m-d", $mlw_this_month);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_this_month." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_this_month = $wpdb->get_results($sql);
	$mlw_quiz_taken_this_month = $wpdb->num_rows;
	
	$mlw_last_month_first =  mktime(0, 0, 0, date("m")  , date("d")-59, date("Y"));
	$mlw_last_month_first = date("Y-m-d", $mlw_last_month_first);
	$mlw_last_month_last =  mktime(0, 0, 0, date("m")  , date("d")-30, date("Y"));
	$mlw_last_month_last = date("Y-m-d", $mlw_last_month_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_month_first." 00:00:00' AND '".$mlw_last_month_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_last_month = $wpdb->get_results($sql);
	$mlw_quiz_taken_last_month = $wpdb->num_rows;
	
	$mlw_two_month_first =  mktime(0, 0, 0, date("m")  , date("d")-89, date("Y"));
	$mlw_two_month_first = date("Y-m-d", $mlw_two_month_first);
	$mlw_two_month_last =  mktime(0, 0, 0, date("m")  , date("d")-60, date("Y"));
	$mlw_two_month_last = date("Y-m-d", $mlw_two_month_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_two_month_first." 00:00:00' AND '".$mlw_two_month_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_two_month = $wpdb->get_results($sql);
	$mlw_quiz_taken_two_month = $wpdb->num_rows;
	
	$mlw_three_month_first =  mktime(0, 0, 0, date("m")  , date("d")-119, date("Y"));
	$mlw_three_month_first = date("Y-m-d", $mlw_three_month_first);
	$mlw_three_month_last =  mktime(0, 0, 0, date("m")  , date("d")-90, date("Y"));
	$mlw_three_month_last = date("Y-m-d", $mlw_three_month_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_three_month_first." 00:00:00' AND '".$mlw_three_month_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_three_month = $wpdb->get_results($sql);
	$mlw_quiz_taken_three_month = $wpdb->num_rows;
	
	$mlw_four_month_first =  mktime(0, 0, 0, date("m")  , date("d")-149, date("Y"));
	$mlw_four_month_first = date("Y-m-d", $mlw_four_month_first);
	$mlw_four_month_last =  mktime(0, 0, 0, date("m")  , date("d")-120, date("Y"));
	$mlw_four_month_last = date("Y-m-d", $mlw_four_month_last);
	$sql = "SELECT quiz_name FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_four_month_first." 00:00:00' AND '".$mlw_four_month_last." 23:59:59') AND deleted=0";
	$mlw_quiz_taken_four_month = $wpdb->get_results($sql);
	$mlw_quiz_taken_four_month = $wpdb->num_rows;
	
	?>
	<div>
	<span class="inlinesparkline"><?php echo $mlw_quiz_taken_four_month.",".$mlw_quiz_taken_three_month.",".$mlw_quiz_taken_two_month.",".$mlw_quiz_taken_last_month.",".$mlw_quiz_taken_this_month; ?></span>
	</div>
	<?php
}

function mlw_dashboard_box_six()
{
	?>
	<div>
		<h2>Plugin Premium Support</h2>
		<p>Plugin Premium Support includes 1 year of priority support, priority feature requests, and access to WordPress training videos.</p>
		<p>You can also purchase 1-on-1 training to go with your support!</p>
		<p>For details, visit our <a href="http://mylocalwebstop.com/product/plugin-premium-support/" target="_blank" style="color:blue;">Plugin Premium Support</a> page.</p>
		<hr /> 
		<h2>Plugin Installation Services</h2>
		<p>We will install and configure any or all of our WordPress plugins on your existing WordPress site.</p>
		<p>We also offer 1-on-1 training to go with your installation!</p>
		<p>For details, visit our <a href="http://mylocalwebstop.com/product/plugin-installation/" target="_blank" style="color:blue;">Plugin Installation</a> page.</p>
		<hr />
		<h2>WordPress Maintenance Services</h2>
		<p>Our maintenance service includes around the clock security monitoring, off-site backups, plugin updates, theme updates, WordPress updates, WordPress training videos, and a Monthly Status Report.</p>
		<p>Up to 30 minutes of support, consultation, and training included each month.</p>
		<p>Visit our <a href="http://mylocalwebstop.com/wordpress-maintenance-services/" target="_blank" style="color:blue;">WordPress Maintenance Services</a> page for details.</p>
	</div>
	<?php
}
function mlw_qmn_weekly_percent_taken_widget()
{
	global $wpdb;
	
	$mlw_this_week =  mktime(0, 0, 0, date("m")  , date("d")-6, date("Y"));
	$mlw_this_week = date("Y-m-d", $mlw_this_week);
	$mlw_qmn_this_week_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_this_week." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0");
	
	$mlw_last_week_start =  mktime(0, 0, 0, date("m")  , date("d")-13, date("Y"));
	$mlw_last_week_start = date("Y-m-d", $mlw_last_week_start);
	$mlw_last_week_end =  mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
	$mlw_last_week_end = date("Y-m-d", $mlw_last_week_end);
	$mlw_qmn_last_week_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_week_start." 00:00:00' AND '".$mlw_last_week_end." 23:59:59') AND deleted=0");
	
	if ($mlw_qmn_last_week_taken != 0)
	{
		$mlw_qmn_analyze_week = round((($mlw_qmn_this_week_taken - $mlw_qmn_last_week_taken) / $mlw_qmn_last_week_taken) * 100, 2);
	}
	else
	{
		$mlw_qmn_analyze_week = $mlw_qmn_this_week_taken * 100;
	}
	?>
	<div>
		<table width="100%">
			<tr>
				<td><div style="font-size: 60px; text-align:center;"><?php echo $mlw_qmn_this_week_taken; ?></div></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
					<div style="font-size: 40px; text-align:center;">
					<?php 
					echo "<span title='Compared to the previous 7 days'>".$mlw_qmn_analyze_week."%</span>"; 
					if ($mlw_qmn_analyze_week >= 0)
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/green_triangle.png' width='40px' height='40px'/>";
					}
					else
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/red_triangle.png' width='40px' height='40px'/>";
					}
					?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
function mlw_qmn_daily_percent_taken_widget()
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
	?>
	<div>
		<table width="100%">
			<tr>
				<td><div style="font-size: 60px; text-align:center;"><?php echo $mlw_qmn_today_taken; ?></div></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
					<div style="font-size: 40px; text-align:center;">
					<?php 
					echo "<span title='Compared to this day last week'>".$mlw_qmn_analyze_today."%</span>"; 
					if ($mlw_qmn_analyze_today >= 0)
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/green_triangle.png' width='40px' height='40px'/>";
					}
					else
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/red_triangle.png' width='40px' height='40px'/>";
					}
					?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
function mlw_qmn_monthly_percent_taken_widget()
{
	global $wpdb;
	
	$mlw_this_month =  mktime(0, 0, 0, date("m")  , date("d")-29, date("Y"));
	$mlw_this_month = date("Y-m-d", $mlw_this_month);
	$mlw_qmn_this_month_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_this_month." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0");
	
	$mlw_last_month_start =  mktime(0, 0, 0, date("m")  , date("d")-59, date("Y"));
	$mlw_last_month_start = date("Y-m-d", $mlw_last_month_start);
	$mlw_last_month_end =  mktime(0, 0, 0, date("m")  , date("d")-30, date("Y"));
	$mlw_last_month_end = date("Y-m-d", $mlw_last_month_end);
	$mlw_qmn_last_month_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_month_start." 00:00:00' AND '".$mlw_last_month_end." 23:59:59') AND deleted=0");
	
	if ($mlw_qmn_last_month_taken != 0)
	{
		$mlw_qmn_analyze_month = round((($mlw_qmn_this_month_taken - $mlw_qmn_last_month_taken) / $mlw_qmn_last_month_taken) * 100, 2);
	}
	else
	{
		$mlw_qmn_analyze_month = $mlw_qmn_this_month_taken * 100;
	}
	?>
	<div>
		<table width="100%">
			<tr>
				<td><div style="font-size: 60px; text-align:center;"><?php echo $mlw_qmn_this_month_taken; ?></div></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
					<div style="font-size: 40px; text-align:center;">
					<?php 
					echo "<span title='Compared to the previous 30 days'>".$mlw_qmn_analyze_month."%</span>"; 
					if ($mlw_qmn_analyze_month >= 0)
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/green_triangle.png' width='40px' height='40px'/>";
					}
					else
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/red_triangle.png' width='40px' height='40px'/>";
					}
					?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
function mlw_qmn_quaterly_percent_taken_widget()
{
	global $wpdb;
	
	$mlw_this_quater =  mktime(0, 0, 0, date("m")  , date("d")-89, date("Y"));
	$mlw_this_quater = date("Y-m-d", $mlw_this_quater);
	$mlw_qmn_this_quater_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_this_quater." 00:00:00' AND '".date("Y-m-d")." 23:59:59') AND deleted=0");
	
	$mlw_last_quater_start =  mktime(0, 0, 0, date("m")  , date("d")-179, date("Y"));
	$mlw_last_quater_start = date("Y-m-d", $mlw_last_quater_start);
	$mlw_last_quater_end =  mktime(0, 0, 0, date("m")  , date("d")-90, date("Y"));
	$mlw_last_quater_end = date("Y-m-d", $mlw_last_quater_end);
	$mlw_qmn_last_quater_taken = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "mlw_results WHERE (time_taken_real BETWEEN '".$mlw_last_quater_start." 00:00:00' AND '".$mlw_last_quater_end." 23:59:59') AND deleted=0");
	
	if ($mlw_qmn_last_quater_taken != 0)
	{
		$mlw_qmn_analyze_quater = round((($mlw_qmn_this_quater_taken - $mlw_qmn_last_quater_taken) / $mlw_qmn_last_quater_taken) * 100, 2);
	}
	else
	{
		$mlw_qmn_analyze_quater = $mlw_qmn_this_quater_taken * 100;
	}
	?>
	<div>
		<table width="100%">
			<tr>
				<td><div style="font-size: 60px; text-align:center;"><?php echo $mlw_qmn_this_quater_taken; ?></div></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
					<div style="font-size: 40px; text-align:center;">
					<?php 
					echo "<span title='Compared to the previous 120 days'>".$mlw_qmn_analyze_quater."%</span>"; 
					if ($mlw_qmn_analyze_quater >= 0)
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/green_triangle.png' width='40px' height='40px'/>";
					}
					else
					{
						echo "<img src='".plugin_dir_url( __FILE__ )."images/red_triangle.png' width='40px' height='40px'/>";
					}
					?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
?>
