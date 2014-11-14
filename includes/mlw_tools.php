<?php
/*
This page creates the main dashboard for the Quiz Master Next plugin
*/
/* 
Copyright 2013, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_tools(){
	add_meta_box("wpss_mrts", 'Audit Trail', "mlw_tools_box", "quiz_wpss"); 
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
			$j('#dialog').dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Ok: function() {
					$j(this).dialog('close');
					}
				}
			});
		
			$j('#opener').click(function() {
				$j('#dialog').dialog('open');
				return false;
		}	);
		});
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
	<h2>Quiz Master Next Tools<a id="opener" href="">(?)</a></h2>
	
	<div style="float:left; width:100%;" class="inner-sidebar1">
		<?php do_meta_boxes('quiz_wpss','advanced','');  ?>	
	</div>

	<div style="clear:both"></div>
	
	<?php echo mlw_qmn_show_adverts(); ?>

	<div id="dialog" title="Help" style="display:none;">
	<h3><b>Help</b></h3>
	<p>This page is the tools for the Quiz Master Next.</p>
	<p>The first widget lists the audit trail.</p>
	</div>

	</div>
	<?php
}

function mlw_tools_box()
{
	global $wpdb;
	$mlw_qmn_table_limit = 25;
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

	$quotes_list = "";
	$display = "";
	$alternate = "";
	foreach($audit_trails as $quote_data) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$quotes_list .= "<tr{$alternate}>";
		$quotes_list .= "<td>" . $quote_data->trail_id . "</td>";
		$quotes_list .= "<td>" . $quote_data->action_user . "</td>";
		$quotes_list .= "<td>" . $quote_data->action ."</td>";
		$quotes_list .= "<td>" . $quote_data->time . "</td>";
		$quotes_list .= "</tr>";
	}
	
	if( $mlw_qmn_audit_page > 0 )
	{
	   $mlw_qmn_previous_page = $mlw_qmn_audit_page - 2;
	   $display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_previous_page\">Previous 25 Audits</a>";
	   if( $mlw_qmn_audit_left > $mlw_qmn_table_limit )
	   {
			$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_audit_page\">Next 25 Audits</a>";
	   }
	}
	else if( $mlw_qmn_audit_page == 0 )
	{
	   if( $mlw_qmn_audit_left > $mlw_qmn_table_limit )
	   {
			$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_audit_page\">Next 25 Audits</a>";
	   }
	}
	else if( $mlw_qmn_audit_left < $mlw_qmn_table_limit )
	{
	   $mlw_qmn_previous_page = $mlw_qmn_audit_page - 2;
	   $display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_tools&&mlw_audit_page=$mlw_qmn_previous_page\">Previous 25 Audits</a>";
	}
	$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th>ID</th>
			<th>User</th>
			<th>Action</th>
			<th>Time</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
		$display .= "</table>";
	?>
	<div>
	<table width='100%'>
	<tr>
	<td align='left'>
	<?php echo $display; ?>
	</td>
	</tr>
	</table>
	</div>
	<?php
}
?>