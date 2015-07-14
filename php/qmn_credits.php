<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
* This function shows the about page. It also shows the changelog information.
*
* @return void
* @since 4.4.0
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
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Added new template variable %TIMER_MINUTES%</h2>
			<p style="text-align: center;">The %TIMER_MINUTES% variable allows for the time it took the user on the quiz to be displayed on the Emails and the Results Pages in minutes. </p>
			<br />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">Timer now begins counting down after starting quiz if using pagination</h2>
			<p style="text-align: center;">The timer now does not start until the user clicks the Next button when the user has pagination enabled.</p>
			<br />

			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">This Plugin Is Now Translation Ready!</h2>
			<p style="text-align: center;">For those who wish to assist in translating, you can find the POT in the languages folder. If you do not know what that is, feel free to contact me and I will assist you with it.</p>
			<br />
			<hr />
			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">For Developers:</h2>
			<br />

			<h2 style="margin: 1.1em 0 .2em;font-size: 2.4em;font-weight: 300;line-height: 1.3;text-align: center;">We Are On GitHub Now</h2>
			<p style="text-align: center;">We love github and use it for all of our plugins! Be sure to <a href="https://github.com/fpcorso/quiz_master_next/">make suggestions or contribute</a> to our Quiz Master Next repository.</p>
			<br />
		</div>
		<div id="mlw_quiz_changelog" style="display: none;">
			<h2>Changelog</h2>
			<h3><?php echo $mlw_quiz_version; ?> (June 20, 2015)</h3>
			<ul class="changelog">
				<!--
				Examples:
				<li class="add"><div class="two">Add</div>Some feature was added</li>
				<li class="fixed"><div class="two">Fixed</div>Fixed some bug</li>
				-->
				<li>* Bug Fix: Fixed bug that caused issues with validation<a href="https://github.com/fpcorso/quiz_master_next/issues/254">Github Issue #254</a></li>
				<li>* Bug Fix: Fixed a rare permalink issue<a href="https://github.com/fpcorso/quiz_master_next/issues/253">Github Issue #253</a></li>
			</ul>
		</div>
	</div>
<?php
}
?>
