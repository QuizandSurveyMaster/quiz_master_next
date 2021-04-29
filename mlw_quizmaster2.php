<?php
/**
 * Plugin Name: Quiz And Survey Master
 * Description: Easily and quickly add quizzes and surveys to your website.
 * Version: 7.1.16
 * Author: ExpressTech
 * Author URI: https://quizandsurveymaster.com/
 * Plugin URI: https://expresstech.io/
 * Text Domain: quiz-master-next
 *
 * @author QSM Team
 * @version 7.1.16
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'QSM_SUBMENU', __FILE__);
define('QSM_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'hide_qsm_adv', true);

/**
 * This class is the main class of the plugin
 *
 * When loaded, it loads the included plugin files and add functions to hooks or filters. The class also handles the admin menu
 *
 * @since 3.6.1
 */
class MLWQuizMasterNext {

	/**
	 * QSM Version Number
	 *
	 * @var string
	 * @since 4.0.0
	 */
	public $version = '7.1.16';

	/**
	 * QSM Alert Manager Object
	 *
	 * @var object
	 * @since 3.7.1
	 */
	public $alertManager;

	/**
	 * QSM Plugin Helper Object
	 *
	 * @var object
	 * @since 4.0.0
	 */
	public $pluginHelper;

	/**
	 * QSM Quiz Creator Object
	 *
	 * @var object
	 * @since 3.7.1
	 */
	public $quizCreator;

	/**
	 * QSM Log Manager Object
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
	 * QSM Settings Object
	 *
	 * @var object
	 * @since 5.0.0
	 */
	public $quiz_settings;

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
	private function load_dependencies() {

		include 'php/classes/class-qsm-install.php';
		include 'php/classes/class-qsm-fields.php';

		include 'php/classes/class-qmn-log-manager.php';
		$this->log_manager = new QMN_Log_Manager();

		include 'php/classes/class-qsm-audit.php';
		$this->audit_manager = new QSM_Audit();

		if ( is_admin() ) {
			include 'php/admin/functions.php';
			include 'php/admin/stats-page.php';
			include 'php/admin/quizzes-page.php';
			include 'php/admin/admin-dashboard.php';
			include 'php/admin/quiz-options-page.php';
			include 'php/admin/admin-results-page.php';
			include 'php/admin/admin-results-details-page.php';
			include 'php/admin/tools-page.php';
			include 'php/classes/class-qsm-changelog-generator.php';
			include 'php/admin/about-page.php';
			include 'php/admin/dashboard-widgets.php';
			include 'php/admin/options-page-questions-tab.php';
			include 'php/admin/options-page-contact-tab.php';
			include 'php/admin/options-page-text-tab.php';
			include 'php/admin/options-page-option-tab.php';
			include 'php/admin/options-page-email-tab.php';
			include 'php/admin/options-page-results-page-tab.php';
			include 'php/admin/options-page-style-tab.php';
			include 'php/admin/addons-page.php';
			include 'php/admin/settings-page.php';
			include 'php/classes/class-qsm-tracking.php';
			include 'php/classes/class-qmn-review-message.php';
			include 'php/gdpr.php';
		}
		include 'php/classes/class-qsm-questions.php';
		include 'php/classes/class-qsm-contact-manager.php';
		include 'php/classes/class-qsm-results-pages.php';
		include 'php/classes/class-qsm-emails.php';
		include 'php/classes/class-qmn-quiz-manager.php';

		include 'php/template-variables.php';
		include 'php/adverts-generate.php';
		include 'php/question-types.php';
		include 'php/default-templates.php';
		include 'php/shortcodes.php';

		if ( function_exists( 'register_block_type' ) ) {
			include 'blocks/block.php';
		}

		include 'php/classes/class-qmn-alert-manager.php';
		$this->alertManager = new MlwQmnAlertManager();

		include 'php/classes/class-qmn-quiz-creator.php';
		$this->quizCreator = new QMNQuizCreator();

		include 'php/classes/class-qmn-plugin-helper.php';
		$this->pluginHelper = new QMNPluginHelper();

		include 'php/classes/class-qsm-settings.php';
		$this->quiz_settings = new QSM_Quiz_Settings();

		include 'php/rest-api.php';
	}

	/**
	 * Add Hooks
	 *
	 * Adds functions to relavent hooks and filters
	 *
	 * @since 3.6.1
	 * @return void
	 */
	private function add_hooks() {
		add_action( 'admin_menu', array( $this, 'setup_admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ), 900 );
		add_action( 'init', array( $this, 'register_quiz_post_types' ) );
        add_action('plugins_loaded', array(&$this, 'qsm_load_textdomain'));
		add_action('admin_enqueue_scripts', array($this, 'qsm_admin_scripts_style'));
		add_action('admin_init', array($this, 'qsm_overide_old_setting_options'));
	}
        
        /**
         * @since 7.1.4
         */
        public function qsm_load_textdomain(){
            load_plugin_textdomain( 'quiz-master-next', false, dirname(plugin_basename(__FILE__)) . '/lang/');
        }

	/**
	 * Loads admin scripts and style
	 * 
	 * @since 7.1.16
	 */
	public function qsm_admin_scripts_style($hook_prefix){
		if($hook_prefix == 'admin_page_mlw_quiz_options'){
			wp_enqueue_script( 'wp-tinymce' );
		}
	}

	/**
	 * Creates Custom Quiz Post Type
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function register_quiz_post_types() {

		// Checks settings to see if we need to alter the defaults.
		$has_archive    = true;
		$exclude_search = false;
		$cpt_slug       = 'quiz';
		$settings       = (array) get_option( 'qmn-settings' );
		$plural_name    = __( 'Quizzes & Surveys', 'quiz-master-next' );

		// Checks if admin turned off archive.
		if ( isset( $settings['cpt_archive'] ) && '1' == $settings['cpt_archive'] ) {
			$has_archive = false;
		}

		// Checks if admin turned off search.
		if ( isset( $settings['cpt_search'] ) && '1' == $settings['cpt_search'] ) {
			$exclude_search = true;
		}

		// Checks if admin changed slug.
		if ( isset( $settings['cpt_slug'] ) ) {
			$cpt_slug = trim( strtolower( str_replace( ' ', '-', $settings['cpt_slug'] ) ) );
		}

		// Checks if admin changed plural name.
		if ( isset( $settings['plural_name'] ) ) {
			$plural_name = trim( $settings['plural_name'] );
		}

		// Prepares labels.
		$quiz_labels = array(
			'name'               => $plural_name,
			'singular_name'      => __( 'Quiz', 'quiz-master-next' ),
			'menu_name'          => __( 'Quiz', 'quiz-master-next' ),
			'name_admin_bar'     => __( 'Quiz', 'quiz-master-next' ),
			'add_new'            => __( 'Add New', 'quiz-master-next' ),
			'add_new_item'       => __( 'Add New Quiz', 'quiz-master-next' ),
			'new_item'           => __( 'New Quiz', 'quiz-master-next' ),
			'edit_item'          => __( 'Edit Quiz', 'quiz-master-next' ),
			'view_item'          => __( 'View Quiz', 'quiz-master-next' ),
			'all_items'          => __( 'All Quizzes', 'quiz-master-next' ),
			'search_items'       => __( 'Search Quizzes', 'quiz-master-next' ),
			'parent_item_colon'  => __( 'Parent Quiz:', 'quiz-master-next' ),
			'not_found'          => __( 'No Quiz Found', 'quiz-master-next' ),
			'not_found_in_trash' => __( 'No Quiz Found In Trash', 'quiz-master-next' ),
		);

		// Prepares post type array.
		$quiz_args = array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => true,
			'labels'              => $quiz_labels,
			'publicly_queryable'  => true,
			'exclude_from_search' => $exclude_search,
			'label'               => $plural_name,
			'rewrite'             => array( 'slug' => $cpt_slug ),
			'has_archive'         => $has_archive,
			'supports'            => array( 'title', 'author', 'comments', 'thumbnail' )
		);

		// Registers post type.
		register_post_type( 'qsm_quiz', $quiz_args );
	}

	/**
	 * Setup Admin Menu
	 *
	 * Creates the admin menu and pages for the plugin and attaches functions to them
	 *
	 * @since 3.6.1
	 * @return void
	 */
	public function setup_admin_menu() {
		if ( function_exists( 'add_menu_page' ) ) {
                        global $qsm_quiz_list_page;
			$qsm_dashboard_page = add_menu_page( 'Quiz And Survey Master', __( 'QSM', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page', 'dashicons-feedback' );
			add_submenu_page( 'qsm_dashboard', __( 'Dashboard', 'quiz-master-next' ),  __( 'Dashboard', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page' );
			$qsm_quiz_list_page = add_submenu_page( 'qsm_dashboard', __( 'Quizzes/Surveys', 'quiz-master-next' ),  __( 'Quizzes/Surveys', 'quiz-master-next' ), 'edit_posts', 'mlw_quiz_list', 'qsm_generate_quizzes_surveys_page' );
                        add_action("load-$qsm_quiz_list_page", 'qsm_generate_quizzes_surveys_page_screen_options');
			add_submenu_page( NULL, __( 'Settings', 'quiz-master-next' ), __( 'Settings', 'quiz-master-next' ), 'edit_posts', 'mlw_quiz_options', 'qsm_generate_quiz_options' );
			add_submenu_page( 'qsm_dashboard', __( 'Results', 'quiz-master-next' ), __( 'Results', 'quiz-master-next' ), 'moderate_comments', 'mlw_quiz_results', 'qsm_generate_admin_results_page' );
			add_submenu_page( NULL, __( 'Result Details', 'quiz-master-next' ), __( 'Result Details', 'quiz-master-next' ), 'moderate_comments', 'qsm_quiz_result_details', 'qsm_generate_result_details' );
			add_submenu_page( 'qsm_dashboard', __( 'Settings', 'quiz-master-next' ), __( 'Settings', 'quiz-master-next' ), 'manage_options', 'qmn_global_settings', array( 'QMNGlobalSettingsPage', 'display_page' ) );
			add_submenu_page( 'qsm_dashboard', __( 'Tools', 'quiz-master-next' ), __( 'Tools', 'quiz-master-next' ), 'manage_options', 'qsm_quiz_tools', 'qsm_generate_quiz_tools' );
			add_submenu_page( 'qsm_dashboard', __( 'Stats', 'quiz-master-next' ), __( 'Stats', 'quiz-master-next' ), 'moderate_comments', 'qmn_stats', 'qmn_generate_stats_page' );
			add_submenu_page( 'qsm_dashboard', __( 'Addon Settings', 'quiz-master-next' ), '<span style="color:#f39c12;">' . __( 'Addon Settings', 'quiz-master-next' ) . '</span>', 'moderate_comments', 'qmn_addons', 'qmn_addons_page' );
                        add_submenu_page( 'qsm_dashboard', __( 'Get a Free Addon', 'quiz-master-next' ), '<span style="color:#f39c12;">' . esc_html__( 'Get a Free Addon!', 'quiz-master-next' ) . '</span>', 'moderate_comments', 'qsm-free-addon', 'qsm_display_optin_page' );
                        add_submenu_page( 'qsm_dashboard', __( 'Roadmap', 'quiz-master-next' ), __( 'Roadmap', 'quiz-master-next' ), 'moderate_comments', 'qsm_roadmap_page', 'qsm_generate_roadmap_page' );
						// Merging Help page in About page
						// add_submenu_page( 'qsm_dashboard', __( 'About', 'quiz-master-next' ), __( 'About', 'quiz-master-next' ), 'moderate_comments', 'qsm_about_page', 'qsm_generate_about_page' );
			add_submenu_page( 'qsm_dashboard', __( 'About', 'quiz-master-next' ), __( 'About', 'quiz-master-next' ), 'moderate_comments', 'qsm_quiz_about', 'qsm_generate_about_page' );                        
                        //Register screen option for dashboard page
                        add_action("screen_settings", "qsm_dashboard_screen_options", 10, 2);
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
	public function admin_head() {
		remove_submenu_page( 'quiz-master-next/mlw_quizmaster2.php', 'mlw_quiz_options' );
		remove_submenu_page( 'quiz-master-next/mlw_quizmaster2.php', 'qsm_quiz_result_details' );
	}
	/**
	 * Overide Old Quiz Settings Options
	 * @since 7.1.16
	 * @return void
	 */
	public function qsm_overide_old_setting_options()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$facebook_app_id = $settings['facebook_app_id'];
		if($facebook_app_id == '483815031724529')
		{
			$settings['facebook_app_id'] = '594986844960937';
			update_option( 'qmn-settings', $settings );
		}
	}
}

global $mlwQuizMasterNext;
$mlwQuizMasterNext = new MLWQuizMasterNext();
register_activation_hook( __FILE__, array( 'QSM_Install', 'install' ) );
?>
