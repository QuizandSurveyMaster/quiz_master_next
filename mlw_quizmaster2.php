<?php

/*
Plugin Name: Quiz Master Next
Description: Use this plugin to add multiple quizzes, tests, or surveys to your website.
Version: 3.7.1
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

/**
  * This class is the main class of the plugin
  *
  * When loaded, it loads the included plugin files and add functions to hooks or filters. The class also handles the admin menu
  *
  * @since 3.6.1
  */
class MLWQuizMasterNext
{
	/**
	 * QMN Alert Manager Object
	 *
	 * @var object
	 * @since 3.7.1
	 */
	public $alertManager;
	
	/**
	 * QMN Quiz Creator Object
	 *
	 * @var object
	 * @since 3.7.1
	 */
	public $quizCreator;
	
	/**
	  * Main Construct Function
	  *
	  * Call functions within class
	  *
	  * @since 3.6.1
	  * @uses MLWQuizMasterNext::load_dependencies() Loads required filed
	  * @uses MLWQuizMasterNext::add_hooks() Adds actions to hooks and filters
	  * @return void
	  */
	public function __construct() 
	{
		$this->load_dependencies();
		$this->add_hooks();
	}
	
	/**
	  * Load File Dependencies
	  *
	  * @since 3.6.1
	  * @return void
	  */
	private function load_dependencies()
	{
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
		include("includes/mlw_adverts.php");
		include("includes/mlw_alerts.php");
		
		$this->alertManager = new MlwQmnAlertManager();
		
		if (is_admin())
		{
			include("includes/mlw_quiz_creator.php");
			$this->quizCreator = new QMNQuizCreator();
		}
	}
	
	/**
	  * Add Hooks
	  * 
	  * Adds functions to relavent hooks and filters
	  *
	  * @since 3.6.1
	  * @return void
	  */
	private function add_hooks()
	{
		add_action('admin_menu', array( $this, 'setup_admin_menu'));
		add_action('admin_init', 'mlw_quiz_update');
		add_action('widgets_init', create_function('', 'return register_widget("Mlw_Qmn_Leaderboard_Widget");'));
		add_shortcode('mlw_quizmaster', 'mlw_quiz_shortcode');
		add_shortcode('mlw_quizmaster_leaderboard', 'mlw_quiz_leaderboard_shortcode');
		add_action('plugins_loaded',  array( $this, 'setup_translations'));
	}
	
	/**
	  * Setup Admin Menu
	  * 
	  * Creates the admin menu and pages for the plugin and attaches functions to them
	  *
	  * @since 3.6.1
	  * @return void
	  */
	public function setup_admin_menu()
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
	
	/**
	  * Loads the plugin language files
	  *
	  * @since 3.6.1
	  * @return void
	  */
	public function setup_translations()
	{
		load_plugin_textdomain( 'mlw_qmn_text_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}

$mlwQuizMasterNext = new MLWQuizMasterNext();
register_activation_hook( __FILE__, 'mlw_quiz_activate');
?>
