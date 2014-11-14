<?php
/*
This page shows the user how-to's for using the plugin
*/
/* 
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_help_page()
{
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
			$j("button").button();
		});
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
	<h2>How-To<a id="opener" href="">(?)</a></h2>
	Please visit our new <a href="http://mylocalwebstop.com/plugin-documentation/" style="color: blue;">Documentation</a> for using this plugin. It is a work in progress, but we will be deleting this page once it is finished.
	<br />
	<div id="dialog" title="Help">
	<h3><b>Help</b></h3>
	<p>This page contains numerous how-to's for using the plugin.</p>
	</div>	
	</div>
	</div>	
<?php
}
?>