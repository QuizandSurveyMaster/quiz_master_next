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
		<h3><?php echo $mlw_quiz_version; ?> (June 11, 2015)</h3>
		<ul>
                    <li>* Added new template variable %TIMER_MINUTES% <a href="https://github.com/fpcorso/quiz_master_next/issues/209">GitHub Issue #209</li>
                    <li>* Eliminates first page if empty <a href="https://github.com/fpcorso/quiz_master_next/issues/182">GitHub Issue #182</li>
                    <li>* Timer now begins counting down after starting quiz if using pagination <a href="https://github.com/fpcorso/quiz_master_next/issues/181">GitHub Issue #181</li>
                    <li>* Amount Finished Compared To Amount In Quiz <a href="https://github.com/fpcorso/quiz_master_next/issues/21">GitHub Issue #21</li>
                    <li>* Bug Fix: Required Answers And Timer Doesn't Work Well Together <a href="https://github.com/fpcorso/quiz_master_next/issues/220">GitHub Issue #220</li>
                    <li>* Bug Fix: FPDF WriteHTML Path Not Being Created Correctly On Windows <a href="https://github.com/fpcorso/quiz_master_next/issues/204">GitHub Issue #204</li>
                    <li>* Bug Fix: Long quiz URL mangles page layout <a href="https://github.com/fpcorso/quiz_master_next/issues/202">GitHub Issue #202</li>
                    <li>* Bug Fix: Fixed Support Widget bug where the support widget was not always submitting correctly. </li>

		</ul>
		</div>
	</div>
<?php
}
?>
