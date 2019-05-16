<?php
/**
 * Generates the ads in the plugin.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the advertisements that are used throughout the plugin page.
 *
 * The advertisements are randomly generated every time the page is loaded. The function also handles the CSS for this.
 *
 * @since 6.2.0
 */
function qsm_show_adverts() {

	// Checks if the option for showing ads if True. Will be false if the No Ads addon is installed.
	if ( 'true' == get_option( 'mlw_advert_shows' ) ) {

		global $mlwQuizMasterNext;
		wp_enqueue_style( 'qsm_admin_style', plugins_url( '../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );

		$ad_text  = '';
		$rand_int = rand( 0, 3 );
		switch ( $rand_int ) {
			case 0:
				// WP Health.
				// $ad_text = 'New content for ad.';
				// break;
			case 1:
				// Continued development 1.
				$ad_text = 'Are you finding this plugin very beneficial? Please consider checking out our premium addons which help support continued development of this plugin. Visit our <a href="http://quizandsurveymaster.com/addons/?utm_source=qsm-plugin-ads&utm_medium=plugin&utm_content=continued-development-1&utm_campaign=qsm_plugin">Addon Store</a> for details!';
				break;
			case 2:
				// Reporting and anaylsis 1.
				$ad_text = 'Are you receiving a lot of responses to your quizzes and surveys? Consider our Reporting and Anaylsis addon which analyzes the data for you and allows you to filter the data as well as export it! <a href="http://quizandsurveymaster.com/downloads/results-analysis/?utm_source=qsm-plugin-ads&utm_medium=plugin&utm_content=reporting-analysis-1&utm_campaign=qsm_plugin">Click here for more details!</a>';
				break;
			case 3:
				// Email marketing integrations.
				$ad_text = 'Want to grow your email list? Check out our addons for adding your quiz or survey takers to your email lists! <a href="http://bit.ly/2Bsw0Je" target="_blank">View our addon store</a>.';
				break;
			default:
				// Reporting and anaylsis 2.
				$ad_text = 'Are you receiving a lot of responses to your quizzes and surveys? Consider our Reporting and Anaylsis addon which analyzes the data for you, graphs the data, allows you to filter the data, and export the data! <a href="http://quizandsurveymaster.com/downloads/results-analysis/?utm_source=qsm-plugin-ads&utm_medium=plugin&utm_content=reporting-analysis-2&utm_campaign=qsm_plugin">Click here for more details!</a>';
		}
		?>
		<div class="help-decide">
			<p><?php echo $ad_text . ' <a class="remove-adv-button" target="_blank" href="https://quizandsurveymaster.com/downloads/advertisement-gone/"><span class="dashicons dashicons-no-alt"></span> Remove Ads</a>'; ?></p>
		</div>
		<?php
	}
}
?>
