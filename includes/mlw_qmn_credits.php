<?php
/*
This page shows the about page
*/
/* 
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_about_page()
{
	//Page Variables
	$mlw_quiz_version = get_option('mlw_quiz_master_version');
	
	
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
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
		function mlw_qmn_setTab(tab) {
			jQuery("a.nav-tab-active").toggleClass("nav-tab-active");
			if (tab == 1)
			{
				jQuery("#mlw_quiz_what_new").show();
				jQuery("#mlw_quiz_changelog").hide();
				jQuery("#mlw_quiz_requested").hide();
				jQuery("#mlw_qmn_tab_1").toggleClass("nav-tab-active");
			}
			if (tab == 2)
			{
				jQuery("#mlw_quiz_what_new").hide();
				jQuery("#mlw_quiz_changelog").show();
				jQuery("#mlw_quiz_requested").hide();
				jQuery("#mlw_qmn_tab_2").toggleClass("nav-tab-active");
			}
			if (tab == 3)
			{
				jQuery("#mlw_quiz_what_new").hide();
				jQuery("#mlw_quiz_changelog").hide();
				jQuery("#mlw_quiz_requested").show();
				jQuery("#mlw_qmn_tab_3").toggleClass("nav-tab-active");
			}
		}
	</script>
	<style>
		div.mlw_qmn_icon_wrap
		{
			background: <?php echo 'url("'.plugins_url( 'images/quiz_icon.png' , __FILE__ ).'")'; ?> no-repeat;
			background: none, <?php echo 'url("'.plugins_url( 'images/quiz_icon.png' , __FILE__ ).'")'; ?> no-repeat;
			position: absolute; 
			top: 0; 
			right: 0; 
			background-color: #0d97d8;
			color: yellow;
			background-position: center 24px;
			background-size: 85px 85px;
			font-size: 14px;
			text-align: center;
			font-weight: 600;
			margin: 5px 0 0;
			padding-top: 120px;
			height: 40px;
			display: inline-block;
			width: 150px;
			text-rendering: optimizeLegibility;
			border: 5px solid #106daa;
			-moz-border-radius: 20px;
			-webkit-border-radius: 20px;
			-khtml-border-radius: 20px;
			border-radius: 20px;
		}
	</style>
	<div class="wrap about-wrap">
	<h1>Welcome To Quiz Master Next <?php echo $mlw_quiz_version; ?></h1>
	<div class="about-text">Thank you for updating!</div>
	<div class="mlw_qmn_icon_wrap">Version <?php echo $mlw_quiz_version; ?></div>
	<h2 class="nav-tab-wrapper">
		<a href="javascript:mlw_qmn_setTab(1);" id="mlw_qmn_tab_1" class="nav-tab nav-tab-active">
			What&#8217;s New In 3.6</a>
		<a href="javascript:mlw_qmn_setTab(2);" id="mlw_qmn_tab_2" class="nav-tab">
			Changelog For <?php echo $mlw_quiz_version; ?>	</a>
		<a href="javascript:mlw_qmn_setTab(3);" id="mlw_qmn_tab_3" class="nav-tab">
			Requested Features</a>
	</h2>
	<div id="mlw_quiz_what_new">
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Separate Social Network Text Templates</h2>
	<p style="text-align: center;">You can now customize the sharing text for each social network instead of having one for all of them.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">New Developer Filters</h2>
	<p style="text-align: center;">For the developers that like to extend this plugin's functionality, there are now two new filters for creating your own template variables.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">We Are On GitHub Now</h2>
	<p style="text-align: center;">We have had several users ask for this so we thought we would try it out. We now love github! Be sure to <a href="https://github.com/fpcorso/quiz_master_next/">make suggestions or contribute</a>.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Popular Add-on Pack</h2>
	<p style="text-align: center;">Our Popular Add-On Pack is now only $45 for 6 add-ons! Now is the time to save almost 50%! Visit our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for details.</p>
	</div>
	<div id="mlw_quiz_changelog" style="display: none;">
	<h3><?php echo $mlw_quiz_version; ?> (December 2, 2014)</h3>
	<ul>
		<li>* Add Separate Social Network Text Templates</li>
		<li>* Added Hours Field To Timer And Completion Time</li>
		<li>* Converted Quizzes Page To Use wpdb->update And wpdb->insert Instead Of query</li>
		<li>* Created New Filter For Creating New Variables For Quiz Page</li>
		<li>* Created New Filter For Creating New Variables For Results Page</li>
	</ul>
	</div>
	<div id="mlw_quiz_requested" style="display: none;">
	<h3>Requested Features For Future Updates By Premium Support Users</h3>
	<ul>
		<li>None</li>
	</ul>
	<h3>Requested Features For Future Updates By Non-Premium Support Users</h3>
	<ul>
		<li>Importing Questions</li>
		<li>Stats For Each Quiz</li>
		<li>Categories</li>
		<li>More Social Media Integration</li>
		<li>Show Question Amount On Pagination</li>
		<li>Required Questions</li>
		<li>Allow Quiz To Not Show Start Page</li>
		<li>Progress Bar For Timer</li>
		<li>Ability To Redirect User Instead Of Showing Results Page</li>
		<li>Multi-Delete Option For Quiz Results</li>
		<li>Graphical Click Aware Questions</li>
		<li>Results Bar Graph For Users Taking Polls</li>
		<li>Head To Head Comparison Questions</li>
		<li>Ability To Highlight Incorrect Answers</li>
		<li>Set Default Question Type</li>
		<li>Show Pop-Up When Clicking Submit</li>
		<li>Conditional Continuation To Next Quiz</li>
	</ul>
	</div>
	</div>
<?php
}
?>
