<?php
/*
This page shows the about page
*/
/*
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_about_page()
{
	global $mlwQuizMasterNext;
	$mlw_quiz_version = $mlwQuizMasterNext->version;
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
	<h1><?php _e('Welcome To Quiz Master Next', 'quiz-master-next'); ?></h1>
	<div class="about-text"><?php _e('Thank you for updating!', 'quiz-master-next'); ?></div>
	<div class="mlw_qmn_icon_wrap"><?php echo $mlw_quiz_version; ?></div>
	<h2 class="nav-tab-wrapper">
		<a href="javascript:mlw_qmn_setTab(1);" id="mlw_qmn_tab_1" class="nav-tab nav-tab-active">
			<?php _e("What's New!", 'quiz-master-next'); ?></a>
		<a href="javascript:mlw_qmn_setTab(2);" id="mlw_qmn_tab_2" class="nav-tab">
			<?php _e('Changelog', 'quiz-master-next'); ?></a>
		<a href="javascript:mlw_qmn_setTab(3);" id="mlw_qmn_tab_3" class="nav-tab">
			<?php _e('Requested Features', 'quiz-master-next'); ?></a>
	</h2>
	<div id="mlw_quiz_what_new">
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Admin Result Pages Redesigned</h2>
	<p style="text-align: center;">This version redesigned the admin result pages. You can now multi-delete results. You can now go to the previous/next results when viewing results.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Restore Deleted Quizzes</h2>
	<p style="text-align: center;">You can now restore quizzes that you have deleted! To do so, visit the Tools page.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">New Quizzes Have Direct URLs</h2>
	<p style="text-align: center;">We have started to incorporate the custom post type system. As such, newer quizzes will have direct urls. Users can search for quizzes and view an archive of your quizzes. To disable these features, navigate to our new Settings page in the Quizzes menu.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Ability To Use Own Facebook App ID</h2>
	<p style="text-align: center;">Many users have been asking to use their own app ID instead. You can now use your own app by changing the app id on the new Settings page.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Please Take Our Survey To Better Improve This Plugin</h2>
	<p style="text-align: center;">When you have a moment, please take our survey for this plugin. By filling out the survey, you are helping us improve this plugin. Users who take the survey between now and March 31st, 2015 will be emailed a 25% off coupon for our WordPress Store. When you are ready, please <a href='http://mylocalwebstop.com/quiz-master-next-survey/'>take our survey</a>.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">This Plugin Is Now Translation Ready!</h2>
	<p style="text-align: center;">For those who wish to assist in translating, you can find the POT in the languages folder. If you do not know what that is, feel free to contact me and I will assist you with it.</p>
	<br />
	<hr />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">For Developers:</h2>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">New Hook For The Template Variable List</h2>
	<p style="text-align: center;">You can now display your custom variables using the new qmn_template_variable_list hook.</p>
	<br />
	<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">We Are On GitHub Now</h2>
	<p style="text-align: center;">We have had several users ask for this so we thought we would try it out. We now love github! Be sure to <a href="https://github.com/fpcorso/quiz_master_next/">make suggestions or contribute</a>.</p>
	<br />
	</div>
	<div id="mlw_quiz_changelog" style="display: none;">
	<h3><?php echo $mlw_quiz_version; ?> (February 11, 2015)</h3>
	<ul>
		<li>* Added Ability To Restore Deleted Quizzes <a href="https://github.com/fpcorso/quiz_master_next/issues/107" target="_blank">GitHub Issue #107</a></li>
		<li>* Added Ability To Multi-Delete Results <a href="https://github.com/fpcorso/quiz_master_next/issues/4" target="_blank">GitHub Issue #4</a></li>
		<li>* Redesigned Admin Results Pages <a href="https://github.com/fpcorso/quiz_master_next/issues/106" target="_blank">GitHub Issue #106</a></li>
		<li>* Added Ability To Go To Next/Previous Results <a href="https://github.com/fpcorso/quiz_master_next/issues/71" target="_blank">GitHub Issue #71</a></li>
		<li>* Quizzes Now Have Direct URL And Archive <a href="https://github.com/fpcorso/quiz_master_next/issues/104" target="_blank">GitHub Issue #104</a></li>
		<li>* Added Ability To Use Own Facebook App ID <a href="https://github.com/fpcorso/quiz_master_next/issues/137" target="_blank">GitHub Issue #137</a></li>
		<li>* In Code: New Hook For Template Variable List</li>
		<li>* In Code: Certificate On Admin Results Now Hooked Into New Template Variable System</li>
		<li>* In Code: Converted Support Widget To Remote Post <a href="https://github.com/fpcorso/quiz_master_next/issues/114" target="_blank">GitHub Issue #114</a></li>
		<li>* In Code: Added Uninstall File Direct Access Check</li>
		<li>* Bug Fix: Fixed Dashboard Widget No Quiz Bug <a href="https://github.com/fpcorso/quiz_master_next/issues/119" target="_blank">GitHub Issue #119</a></li>
		<li>* Bug Fix: Fixed Shortcode No Quiz Bug <a href="https://github.com/fpcorso/quiz_master_next/issues/120" target="_blank">GitHub Issue #120</a></li>
		<li>* Bug Fix: Fixed Required Contact User Bug <a href="https://github.com/fpcorso/quiz_master_next/issues/123" target="_blank">GitHub Issue #123</a></li>
		<li>* Bug Fix: Fixed Text Template Bug <a href="https://github.com/fpcorso/quiz_master_next/issues/117" target="_blank">GitHub Issue #117</a></li>
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
		<li>More Social Media Integration</li>
		<li>Show Question Amount On Pagination</li>
		<li>Allow Quiz To Not Show Start Page</li>
		<li>Progress Bar For Timer</li>
		<li>Ability To Redirect User Instead Of Showing Results Page</li>
		<li>Graphical Click Aware Questions</li>
		<li>Results Bar Graph For Users Taking Polls</li>
		<li>Head To Head Comparison Questions</li>
		<li>Ability To Highlight Incorrect Answers</li>
		<li>Set Default Question Type</li>
		<li>Show Pop-Up When Clicking Submit</li>
		<li>Conditional Continuation To Next Quiz</li>
		<li>Analyse Level Of Difficulty</li>
		<li>Question Bank</li>
		<li>Global Quiz Settings</li>
		<li>Print Quiz</li>
		<li>Easier Media In Questions</li>
		<li>Easier Media In Answers</li>
		<li>Mark Question To Be Reviewed</li>
	</ul>
	</div>
	</div>
<?php
}
?>
