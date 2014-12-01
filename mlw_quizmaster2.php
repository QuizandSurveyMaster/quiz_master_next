<?php

/*
Plugin Name: Quiz Master Next
Description: Use this plugin to add multiple quizzes, tests, or surveys to your website.
Version: 3.6.1
Author: Frank Corso
Author URI: http://www.mylocalwebstop.com/
Plugin URI: http://www.mylocalwebstop.com/
*/

/* 
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)

Disclaimer of Warranties. 

The plugin is provided "as is". My Local Webstop and its suppliers and licensors hereby disclaim all warranties of any kind, 
express or implied, including, without limitation, the warranties of merchantability, fitness for a particular purpose and non-infringement. 
Neither My Local Webstop nor its suppliers and licensors, makes any warranty that the plugin will be error free or that access thereto will be continuous or uninterrupted.
You understand that you install, operate, and unistall the plugin at your own discretion and risk.
*/


///Files to Include
include("includes/mlw_alerts.php");
include("includes/mlw_quiz.php");
include("includes/mlw_dashboard.php");
include("includes/mlw_quiz_admin.php");
include("includes/mlw_quiz_options.php");
include("includes/mlw_quiz_install.php");
include("includes/mlw_results.php");
include("includes/mlw_results_details.php");
include("includes/mlw_tools.php");
include("includes/mlw_leaderboard.php");
include("includes/mlw_update.php");
include("includes/mlw_qmn_widgets.php");
include("includes/mlw_qmn_credits.php");
include("includes/mlw_template_variables.php");


///Activation Actions
add_action('admin_menu', 'mlw_add_menu');
add_action('admin_init', 'mlw_quiz_update');
add_action('widgets_init', create_function('', 'return register_widget("Mlw_Qmn_Leaderboard_Widget");'));
add_shortcode('mlw_quizmaster', 'mlw_quiz_shortcode');
add_shortcode('mlw_quizmaster_leaderboard', 'mlw_quiz_leaderboard_shortcode');
register_activation_hook( __FILE__, 'mlw_quiz_activate');
register_deactivation_hook( __FILE__, 'mlw_quiz_deactivate');

//Setup Translations
function mlw_qmn_translation_setup() {
  load_plugin_textdomain( 'mlw_qmn_text_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action('plugins_loaded', 'mlw_qmn_translation_setup');


///Create Admin Pages
function mlw_add_menu()
{
	if (function_exists('add_menu_page'))
	{
		add_menu_page('Quiz Master Next', 'Quiz Dashboard', 'moderate_comments', __FILE__, 'mlw_generate_quiz_dashboard', 'dashicons-feedback');
		add_submenu_page(__FILE__, 'Quizzes', 'Quizzes', 'moderate_comments', 'mlw_quiz_admin', 'mlw_generate_quiz_admin');
		add_submenu_page(__FILE__, 'Quiz Settings', 'Quiz Settings', 'moderate_comments', 'mlw_quiz_options', 'mlw_generate_quiz_options');
		add_submenu_page(__FILE__, 'Quiz Results', 'Quiz Results', 'moderate_comments', 'mlw_quiz_results', 'mlw_generate_quiz_results');
		add_submenu_page(__FILE__, 'Quiz Result Details', 'Quiz Result Details', 'moderate_comments', 'mlw_quiz_result_details', 'mlw_generate_result_details');
		add_submenu_page(__FILE__, 'Tools', 'Tools', 'manage_options', 'mlw_quiz_tools', 'mlw_generate_quiz_tools');
		add_submenu_page(__FILE__, 'QMN About', 'QMN About', 'manage_options', 'mlw_qmn_about', 'mlw_generate_about_page');
	}
}


//Admin Notice
add_action('admin_notices', 'mlw_qmn_notice');
function mlw_qmn_notice() {
    if ( get_option('mlw_qmn_review_notice') == 1 && current_user_can( 'manage_options' ) ) 
    {	
		echo "
		<style>
			div.help_decide
			{
				display: block;
				text-align:center;
				letter-spacing: 1px;
				margin: auto;
				text-shadow: 0 1px 1px #000000;
				background: #0d97d8;
				border: 5px solid #106daa;
				-moz-border-radius: 20px;
				-webkit-border-radius: 20px;
				-khtml-border-radius: 20px;
				border-radius: 20px;
				color: #FFFFFF;
			}
			div.help_decide a
			{
				color: yellow;
			}		
			</style>";
        echo '<div class="help_decide"><p>';
        printf(__('You have been using the Quiz Master Next plugin for a while now! Thanks for choosing to use this plugin. If it has benefited your website, please consider purchasing an <a href="http://mylocalwebstop.com/shop/">add-on</a>, giving a <a href="http://wordpress.org/support/view/plugin-reviews/quiz-master-next">review</a>, or taking this <a href="http://mylocalwebstop.com/sample-survey/" target="_blank">survey</a>. | <a href="%1$s">Hide Notice</a>'), '?page=quiz-master-next/mlw_quizmaster2.php&&mlw_qmn_ignore_notice=0');
        echo "</p></div>";
	}
}
//Check to see if notices should be shown or dismissed
add_action('admin_init', 'mlw_qmn_notice_ignore');
function mlw_qmn_notice_ignore() {
	global $wpdb;
	if ( ! get_option('mlw_qmn_review_notice'))
	{
		add_option('mlw_qmn_review_notice' , '0');
	}
	if ($wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results" ) >= 20 && get_option('mlw_qmn_review_notice') == 0)
	{
		update_option('mlw_qmn_review_notice' , '1');
	}
    if ( isset($_GET['mlw_qmn_ignore_notice']) && '0' == $_GET['mlw_qmn_ignore_notice'] ) {
    	update_option('mlw_qmn_review_notice' , '2');
    }
}
function mlw_qmn_show_adverts()
{
	$mlw_advert = "";
	$mlw_advert_text = "";
	if ( get_option('mlw_advert_shows') == 'true' )
	{
		$mlw_random_int = rand(0, 8);
		switch ($mlw_random_int) {
			case 0:
				$mlw_advert_text = "Need support or features? Check out our Premium Support options! Visit our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for details!";
				break;
			case 1:
				$mlw_advert_text = "Is Quiz Master Next beneficial to your website? Please help by giving us a review on WordPress.org by going <a href=\"http://wordpress.org/support/view/plugin-reviews/quiz-master-next\">here</a>!";
				break;
			case 2:
				$mlw_advert_text = "Want help installing and configuring one of our plugins? Check out our Plugin Installation services. Visit our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for details!";
				break;
			case 3:
				$mlw_advert_text = "Would you like to support this plugin but do not need or want premium support? Please consider our inexpensive 'Advertisements Be Gone' add-on which will get rid of these ads. Visit our <a href=\"http://mylocalwebstop.com/shop/\">Plugin Add-On Store</a> for details!";
				break;
			case 4:
				$mlw_advert_text = "Need to be able to export the results of your quizzes? Be sure to check out our new Export Results add-on in our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a>!";
				break;
			case 5:
				$mlw_advert_text = "Need help keeping your plugins, themes, and WordPress up to date? Want around the clock security monitoring and off-site back-ups? How about WordPress training videos, a monthly status report, and support/consultation? Check out our <a href=\"http://mylocalwebstop.com/wordpress-maintenance-services/\">WordPress Maintenance Services</a> for more details!";
				break;
			case 6:
				$mlw_advert_text = "Setting up a new site? Let us take care of the set-up so you back to running your business. Check out our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for more details!";
				break;
			case 7:
				$mlw_advert_text = "Need a page so your users can see their results from all the quizzes they have taken? Try our new User Dashboard add-on. Check out our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for more details!";
				break;
			case 8:
				$mlw_advert_text = "Our new Popular Add-On Pack is now only $45 for 6 add-ons! Now is the time to save almost 50%! Visit our <a href=\"http://mylocalwebstop.com/shop/\">WordPress Store</a> for details.";
				break;
			default:
				$mlw_advert_text = "Need support or features? Check out our Premium Support options! Visit our <a href=\"http://mylocalwebstop.com/shop/\">Plugin Add-On Store</a> for details!";
		}
		$mlw_advert .= "
			<style>
			div.help_decide
			{
				display: block;
				text-align:center;
				letter-spacing: 1px;
				margin: auto;
				text-shadow: 0 1px 1px #000000;
				background: #0d97d8;
				border: 5px solid #106daa;
				-moz-border-radius: 20px;
				-webkit-border-radius: 20px;
				-khtml-border-radius: 20px;
				border-radius: 20px;
				color: #FFFFFF;
			}
			div.help_decide a
			{
				color: yellow;
			}		
			</style>";
		$mlw_advert .= "
			<div class=\"help_decide\">
			<p>$mlw_advert_text</p>
			</div>";
	}
	return $mlw_advert;	
}



/*


*/
?>
