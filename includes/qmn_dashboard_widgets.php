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

}
?>
