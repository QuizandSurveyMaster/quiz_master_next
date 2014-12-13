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
}
?>
