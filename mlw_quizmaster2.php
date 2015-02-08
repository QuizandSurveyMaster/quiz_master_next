<?php
/**
* Plugin Name: Quiz Master Next
* Description: Use this plugin to add multiple quizzes, tests, or surveys to your website.
* Version: 4.0.1
* Author: Frank Corso
* Author URI: http://www.mylocalwebstop.com/
* Plugin URI: http://www.mylocalwebstop.com/
* Text Domain: quiz-master-next
* Domain Path: /languages
*
* Disclaimer of Warranties
* The plugin is provided "as is". My Local Webstop and its suppliers and licensors hereby disclaim all warranties of any kind,
* express or implied, including, without limitation, the warranties of merchantability, fitness for a particular purpose and non-infringement.
* Neither My Local Webstop nor its suppliers and licensors, makes any warranty that the plugin will be error free or that access thereto will be continuous or uninterrupted.
* You understand that you install, operate, and unistall the plugin at your own discretion and risk.
*
* @author Frank Corso
* @version 4.0.1
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
	 * QMN Version Number
	 *
	 * @var string
	 * @since 4.0.0
	 */
	public $version = '4.0.1';

	/**
	 * QMN Alert Manager Object
	 *
	 * @var object
	 * @since 3.7.1
	 */
	public $alertManager;

	/**
	 * QMN Plugin Helper Object
	 *
	 * @var object
	 * @since 4.0.0
	 */
	public $pluginHelper;

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
		if (is_admin())
		{
			include("includes/qmn_dashboard.php");
			include("includes/qmn_quiz_admin.php");
			include("includes/qmn_quiz_options.php");
			include("includes/qmn_results.php");
			include("includes/qmn_results_details.php");
			include("includes/qmn_tools.php");
			include("includes/qmn_credits.php");
			include("includes/qmn_help.php");
			include("includes/qmn_dashboard_widgets.php");
			include("includes/qmn_options_questions_tab.php");
			include("includes/qmn_options_text_tab.php");
			include("includes/qmn_options_option_tab.php");
			include("includes/qmn_options_leaderboard_tab.php");
			include("includes/qmn_options_certificate_tab.php");
			include("includes/qmn_options_email_tab.php");
			include("includes/qmn_options_results_page_tab.php");
			include("includes/qmn_options_style_tab.php");
			include("includes/qmn_options_tools_tab.php");
			include("includes/qmn_options_preview_tab.php");
			include("includes/qmn_addons.php");
			include("includes/qmn_global_settings.php");
			include("includes/qmn_usage_tracking.php");
		}
		include("includes/qmn_quiz.php");
		include("includes/qmn_quiz_install.php");
		include("includes/qmn_leaderboard.php");
		include("includes/qmn_update.php");
		include("includes/qmn_widgets.php");
		include("includes/qmn_template_variables.php");
		include("includes/qmn_adverts.php");
		include("includes/qmn_question_types.php");

		include("includes/qmn_alerts.php");
		$this->alertManager = new MlwQmnAlertManager();

		include("includes/qmn_quiz_creator.php");
		$this->quizCreator = new QMNQuizCreator();

		include("includes/qmn_helper.php");
		$this->pluginHelper = new QMNPluginHelper();
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
		add_action('admin_head', array( $this, 'admin_head'), 900);
		add_action('admin_init', 'mlw_quiz_update');
		add_action('widgets_init', create_function('', 'return register_widget("Mlw_Qmn_Leaderboard_Widget");'));
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
			add_menu_page('Quiz Master Next', __('Quizzes', 'quiz-master-next'), 'moderate_comments', __FILE__, 'mlw_generate_quiz_admin', 'dashicons-feedback');
			add_submenu_page(__FILE__, __('Quiz Settings', 'quiz-master-next'), __('Quiz Settings', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_options', 'mlw_generate_quiz_options');
			add_submenu_page(__FILE__, __('Quiz Results', 'quiz-master-next'), __('Quiz Results', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_results', 'mlw_generate_quiz_results');
			add_submenu_page(__FILE__, __('Quiz Result Details', 'quiz-master-next'), __('Quiz Result Details', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_result_details', 'mlw_generate_result_details');
			add_submenu_page(__FILE__, __('Settings', 'quiz-master-next'), __('Settings', 'quiz-master-next'), 'manage_options', 'qmn_global_settings', array('QMNGlobalSettingsPage', 'display_page'));
			add_submenu_page(__FILE__, __('Tools', 'quiz-master-next'), __('Tools', 'quiz-master-next'), 'manage_options', 'mlw_quiz_tools', 'mlw_generate_quiz_tools');
			add_submenu_page(__FILE__, __('Stats', 'quiz-master-next'), __('Stats', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_stats', 'mlw_generate_quiz_dashboard');
			add_submenu_page(__FILE__, __('Addon Settings', 'quiz-master-next'), __('Addon Settings', 'quiz-master-next'), 'manage_options', 'qmn_addons', 'qmn_addons_page');
			add_submenu_page(__FILE__, __('Help', 'quiz-master-next'), __('Help', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_help', 'mlw_generate_help_page');

			add_dashboard_page(
				__( 'QMN About', 'quiz' ),
				__( 'QMN About', 'quiz' ),
				'manage_options',
				'mlw_qmn_about',
				'mlw_generate_about_page'
			);
		}
	}

	public function admin_head()
	{
		remove_submenu_page( 'index.php', 'mlw_qmn_about' );
		remove_submenu_page( 'quiz-master-next/mlw_quizmaster2.php', 'mlw_quiz_options' );
		remove_submenu_page( 'quiz-master-next/mlw_quizmaster2.php', 'mlw_quiz_result_details' );
	}

	/**
	  * Loads the plugin language files
	  *
	  * @since 3.6.1
	  * @return void
	  */
	public function setup_translations()
	{
		load_plugin_textdomain( 'quiz-master-next', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

$mlwQuizMasterNext = new MLWQuizMasterNext();
register_activation_hook( __FILE__, 'mlw_quiz_activate');
?>
