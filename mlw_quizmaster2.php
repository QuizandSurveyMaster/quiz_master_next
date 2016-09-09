<?php
/**
* Plugin Name: Quiz And Survey Master
* Description: Easily and quickly add quizzes and surveys to your website.
* Version: 4.7.7
* Author: Frank Corso
* Author URI: http://www.quizandsurveymaster.com/
* Plugin URI: http://www.quizandsurveymaster.com/
* Text Domain: quiz-master-next
* Domain Path: /languages
*
* @author Frank Corso
* @version 4.7.7
*/
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'QSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
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
	public $version = '4.7.7';

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
	 * QMN Log Manager Object
	 *
	 * @var object
	 * @since 4.5.0
	 */
	public $log_manager;

	/**
	 * QSM Audit Manager Object
	 *
	 * @var object
	 * @since 4.7.1
	 */
	public $audit_manager;

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
	public function __construct() {
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

		include("php/class-qsm-install.php");

		include("php/class-qmn-log-manager.php");
		$this->log_manager = new QMN_Log_Manager;

		include( "php/class-qsm-audit.php" );
		$this->audit_manager = new QSM_Audit();

		if ( is_admin() ) {
			include("php/stats-page.php");
			include("php/quizzes-page.php");
			include("php/quiz-options-page.php");
			include("php/admin-results-page.php");
			include("php/admin-results-details-page.php");
			include("php/tools-page.php");
			include("php/class-qsm-changelog-generator.php");
			include("php/about-page.php");
			include("php/help-page.php");
			include("php/dashboard-widgets.php");
			include("php/options-page-questions-tab.php");
			include("php/options-page-text-tab.php");
			include("php/options-page-option-tab.php");
			include("php/options-page-leaderboard-tab.php");
			include("php/options-page-certificate-tab.php");
			include("php/options-page-email-tab.php");
			include("php/options-page-results-page-tab.php");
			include("php/options-page-style-tab.php");
			include("php/options-page-tools-tab.php");
			include("php/options-page-preview-tab.php");
			include("php/addons-page.php");
			include("php/settings-page.php");
			include("php/class-qmn-tracking.php");
			include("php/class-qmn-review-message.php");
		}
		include("php/class-qmn-quiz-manager.php");

		include("php/leaderboard-shortcode.php");
		include("php/widgets.php");
		include("php/template-variables.php");
		include("php/adverts-generate.php");
		include("php/question-types.php");
		include("php/default-templates.php");

		include("php/class-qmn-alert-manager.php");
		$this->alertManager = new MlwQmnAlertManager();

		include("php/class-qmn-quiz-creator.php");
		$this->quizCreator = new QMNQuizCreator();

		include("php/class-qmn-plugin-helper.php");
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
		add_action('widgets_init', create_function('', 'return register_widget("Mlw_Qmn_Leaderboard_Widget");'));
		add_shortcode('mlw_quizmaster_leaderboard', 'mlw_quiz_leaderboard_shortcode');
		add_action('plugins_loaded',  array( $this, 'setup_translations'));
		add_action('init', array( $this, 'register_quiz_post_types'));
	}

	/**
	 * Creates Custom Quiz Post Type
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function register_quiz_post_types()
 	{
		$quiz_labels = array(
			'name'               => 'Quizzes',
			'singular_name'      => 'Quiz',
			'menu_name'          => 'Quiz',
			'name_admin_bar'     => 'Quiz',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Quiz',
			'new_item'           => 'New Quiz',
			'edit_item'          => 'Edit Quiz',
			'view_item'          => 'View Quiz',
			'all_items'          => 'All Quizzes',
			'search_items'       => 'Search Quizzes',
			'parent_item_colon'  => 'Parent Quiz:',
			'not_found'          => 'No Quiz Found',
			'not_found_in_trash' => 'No Quiz Found In Trash'
		);
		$has_archive = true;
		$exclude_search = false;
		$cpt_slug = 'quiz';
		$settings = (array) get_option( 'qmn-settings' );
    if (isset($settings['cpt_archive']) && $settings['cpt_archive'] == '1')
		{
			$has_archive = false;
		}
		if (isset($settings['cpt_search']) && $settings['cpt_search'] == '1')
		{
			$exclude_search = true;
		}
		if (isset($settings['cpt_slug']))
		{
			$cpt_slug = trim(strtolower(str_replace(" ", "-", $settings['cpt_slug'])));
		}
		$quiz_args = array(
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_in_nav_menus' => true,
			'labels' => $quiz_labels,
			'publicly_queryable' => true,
			'exclude_from_search' => $exclude_search,
			'label'  => 'Quizzes',
			'rewrite' => array('slug' => $cpt_slug),
			'has_archive'        => $has_archive,
			'supports'           => array( 'title', 'author', 'comments' )
		);

		register_post_type( 'quiz', $quiz_args );
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
			add_menu_page('Quiz And Survey Master', __('Quizzes/Surveys', 'quiz-master-next'), 'moderate_comments', __FILE__, 'mlw_generate_quiz_admin', 'dashicons-feedback');
			add_submenu_page(__FILE__, __('Settings', 'quiz-master-next'), __('Settings', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_options', 'mlw_generate_quiz_options');
			add_submenu_page(__FILE__, __('Results', 'quiz-master-next'), __('Results', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_results', 'mlw_generate_quiz_results');
			add_submenu_page(__FILE__, __('Result Details', 'quiz-master-next'), __('Result Details', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_result_details', 'mlw_generate_result_details');
			add_submenu_page(__FILE__, __('Settings', 'quiz-master-next'), __('Settings', 'quiz-master-next'), 'manage_options', 'qmn_global_settings', array('QMNGlobalSettingsPage', 'display_page'));
			add_submenu_page(__FILE__, __('Tools', 'quiz-master-next'), __('Tools', 'quiz-master-next'), 'manage_options', 'mlw_quiz_tools', 'mlw_generate_quiz_tools');
			add_submenu_page(__FILE__, __('Stats', 'quiz-master-next'), __('Stats', 'quiz-master-next'), 'moderate_comments', 'qmn_stats', 'qmn_generate_stats_page');
			add_submenu_page(__FILE__, __('Addon Settings', 'quiz-master-next'), __('Addon Settings', 'quiz-master-next'), 'manage_options', 'qmn_addons', 'qmn_addons_page');
			add_submenu_page(__FILE__, __('Help', 'quiz-master-next'), __('Help', 'quiz-master-next'), 'moderate_comments', 'mlw_quiz_help', 'mlw_generate_help_page');

			add_dashboard_page(
				__( 'QSM About', 'quiz' ),
				__( 'QSM About', 'quiz' ),
				'manage_options',
				'mlw_qmn_about',
				'mlw_generate_about_page'
			);
		}
	}

	/**
	 * Removes Unnecessary Admin Page
	 *
	 * Removes the update, quiz settings, and quiz results pages from the Quiz Menu
	 *
	 * @since 4.1.0
	 * @return void
	 */
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
register_activation_hook( __FILE__, array( 'QSM_Install', 'install' ) );
?>
