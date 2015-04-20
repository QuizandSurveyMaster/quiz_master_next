<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
This page shows the about page
*/

function mlw_generate_about_page()
{
	global $mlwQuizMasterNext;
	$mlw_quiz_version = $mlwQuizMasterNext->version;
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	wp_enqueue_style( 'qmn_admin_style', plugins_url( '../css/qmn_admin.css' , __FILE__ ) );
	wp_enqueue_script('qmn_admin_js', plugins_url( '../js/admin.js' , __FILE__ ));
	?>
	<style>
		div.mlw_qmn_icon_wrap
		{
			background: <?php echo 'url("'.plugins_url( 'images/quiz_icon.png' , __FILE__ ).'")'; ?> no-repeat;
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
		</h2>
		<div id="mlw_quiz_what_new">
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Newly Updated Questions Tab</h2>
			<p style="text-align: center;">The newly updated questions tab now allows you to use the WordPress editor to edit your questions. This allows for easier media adding to question. We also added the ability to search through your questions as well. You can now also use shortcodes in the question.</p>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Redesigned Stats Page</h2>
			<p style="text-align: center;">Continuing on with our process to slowly redesign the plugin, this update brings an updated stats page. We added new dynamic charts. There are many more items that we will be adding to this page over the next few updates.</p>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Please Take Our Survey To Better Improve This Plugin</h2>
			<p style="text-align: center;">When you have a moment, please take our survey for this plugin. By filling out the survey, you are helping us improve this plugin. When you are ready, please <a href='http://mylocalwebstop.com/quiz-master-next-survey/'>take our survey</a>.</p>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">This Plugin Is Now Translation Ready!</h2>
			<p style="text-align: center;">For those who wish to assist in translating, you can find the POT in the languages folder. If you do not know what that is, feel free to contact me and I will assist you with it.</p>
			<br />
			<hr />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">For Developers:</h2>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Add New Tabs To Stats Page</h2>
			<p style="text-align: center;">With our new stats page, developers can create their own tabs using our plugin helper class.</p>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">We Are On GitHub Now</h2>
			<p style="text-align: center;">We love github and use it for all of our plugins! Be sure to <a href="https://github.com/fpcorso/quiz_master_next/">make suggestions or contribute</a> to our Quiz Master Next repository.</p>
			<br />
		</div>
		<div id="mlw_quiz_changelog" style="display: none;">
		<h3><?php echo $mlw_quiz_version; ?> (April 20, 2015)</h3>
		<ul>
			<li>* Redesigned Stats Page <a target="_blank" href='https://github.com/fpcorso/quiz_master_next/issues/177'>GitHub Issue #177</a></li>
			<li>* Added Ability To Have Shortcodes In Questions <a target="_blank" href='https://github.com/fpcorso/quiz_master_next/issues/175'>GitHub Issue #175</a></li>
			<li>* Added Editor To Questions <a target="_blank" href='https://github.com/fpcorso/quiz_master_next/issues/36'>GitHub Issue #36</a></li>
			<li>* Added Ability To Edit Quiz Post <a target="_blank" href='https://github.com/fpcorso/quiz_master_next/issues/176'>GitHub Issue #176</a></li>
			<li>* Added Ability To Search Questions <a target="_blank" href='https://github.com/fpcorso/quiz_master_next/issues/151'>GitHub Issue #151</a></li>
			<li>* Design Changes To Questions Tab</li>
			<li>* Minor Design Changes</li>
			<li>* Minor Bug Fixes</li>
		</ul>
		</div>
	</div>
<?php
}
?>
