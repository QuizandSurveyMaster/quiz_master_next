<?php
/**
 * Plugin Name: Quiz And Survey Master
 * Description: Easily and quickly add quizzes and surveys to your website.
 * Version: 7.3.5
 * Author: ExpressTech
 * Author URI: https://quizandsurveymaster.com/
 * Plugin URI: https://expresstech.io/
 * Text Domain: quiz-master-next
 *
 * @author QSM Team
 * @version 7.3.5
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QSM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'QSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'QSM_SUBMENU', __FILE__ );
define( 'QSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'hide_qsm_adv', true );
define( 'QSM_THEME_PATH', plugin_dir_path( __DIR__ ) );
define( 'QSM_THEME_SLUG', plugins_url( '/' ) );
define('QSM_PLUGIN_CSS_URL', QSM_PLUGIN_URL . 'css');
define('QSM_PLUGIN_JS_URL', QSM_PLUGIN_URL . 'js');
define('QSM_PLUGIN_PHP_DIR', QSM_THEME_PATH . 'php');
define('QSM_PLUGIN_TXTDOMAIN', 'quiz-master-next');

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
	public $version = '7.3.5';

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
	 * QSM theme settings object
	 *
	 * @var object
	 * @since 7.2.0
	 */
	public $theme_settings;

	/**
	 * QSM migration helper object
	 *
	 * @var object
	 * @since 7.3.0
	 */
	public $migrationHelper;

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

		include 'php/classes/class-qsm-theme-settings.php';
		$this->theme_settings = new QSM_Theme_Settings();

		include 'php/classes/class-qsm-migrate.php';
		$this->migrationHelper = new QSM_Migrate();

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
		add_filter( 'parent_file', array( &$this, 'parent_file' ), 9999, 1 );
		add_action( 'plugins_loaded', array( &$this, 'qsm_load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'qsm_admin_scripts_style' ), 10 );
		add_action( 'admin_init', array( $this, 'qsm_overide_old_setting_options' ) );
		add_action( 'admin_notices', array( $this, 'qsm_admin_notices' ) );
		add_filter( 'manage_edit-qsm_category_columns', array( $this, 'modify_qsm_category_columns' ) );
	}

	/**
	 * Modifies QSM Category taxonomy columns
	 *
	 * @param array $columns
	 * @return array
	 * @since 7.3.0
	 */
	public function modify_qsm_category_columns( $columns ) {
		unset( $columns['posts'] );
		return $columns;
	}

	/**
	 * @since 7.1.4
	 */
	public function qsm_load_textdomain() {
		load_plugin_textdomain( 'quiz-master-next', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Loads admin scripts and style
	 *
	 * @since 7.1.16
	 */
	public function qsm_admin_scripts_style( $hook ) {
		global $mlwQuizMasterNext;
		//common scripts and styles
		wp_enqueue_style( 'qsm_admin_style', plugins_url( 'css/qsm-admin.css', __FILE__ ), array(), $this->version );
		wp_style_add_data( 'qsm_admin_style', 'rtl', 'replace' );
		wp_enqueue_script( 'qmn_admin_js', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'backbone', 'underscore', 'wp-util' ), $this->version, true );

		if ( $hook == 'admin_page_mlw_quiz_options' ) {
			wp_enqueue_script( 'wp-tinymce' );
			wp_enqueue_script( 'micromodal_script', plugins_url( 'js/micromodal.min.js', __FILE__ ), array( 'jquery', 'qmn_admin_js'), $this->version, true);
		}

		if ( $hook == 'toplevel_page_qsm_dashboard' || $hook == 'qsm_page_mlw_quiz_list' ) {
			wp_enqueue_script( 'micromodal_script', plugins_url( 'js/micromodal.min.js', __FILE__ ), array( 'jquery', 'qmn_admin_js'), $this->version, true);
			wp_enqueue_script( 'qsm_quiz_wizard_script', plugins_url( 'js/qsm-quiz-wizard.js', __FILE__ ), array( 'jquery', 'micromodal_script' ), $this->version);
			wp_enqueue_media();
		}

		if ( strpos( $hook, 'qsm' ) !== false || strpos( $hook, 'mlw_quiz' ) !== false ) {
			wp_enqueue_script( 'qsm_admin_notices_script', plugins_url( 'js/qsm-admin-notices.js', __FILE__ ), array( 'jquery' ), $this->version, true );
			wp_localize_script( 'qsm_admin_notices_script', 'qsm_notices_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
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
			'supports'            => array( 'title', 'author', 'comments', 'thumbnail' ),
		);

		// Registers post type.
		register_post_type( 'qsm_quiz', $quiz_args );

		/**
		 * Register Taxonomy
		 */
		$taxonomy_args = array(
			'labels'            => array(
				'menu_name'         => __( 'Question Categories', 'quiz-master-next' ),
				'name'              => __( 'Categories', 'quiz-master-next' ),
				'singular_name'     => __( 'Category', 'quiz-master-next' ),
				'all_items'         => __( 'All Categories', 'quiz-master-next' ),
				'parent_item'       => __( 'Parent Category', 'quiz-master-next' ),
				'parent_item_colon' => __( 'Parent Category:', 'quiz-master-next' ),
				'new_item_name'     => __( 'New Category Name', 'quiz-master-next' ),
				'add_new_item'      => __( 'Add New Category', 'quiz-master-next' ),
				'edit_item'         => __( 'Edit Category', 'quiz-master-next' ),
				'update_item'       => __( 'Update Category', 'quiz-master-next' ),
				'view_item'         => __( 'View Category', 'quiz-master-next' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => false,
		);
		register_taxonomy( 'qsm_category', array( 'qsm-taxonomy' ), $taxonomy_args );
	}

	public function parent_file( $parent_file ) {
		global $menu, $submenu, $parent_file, $submenu_file;
		if ( 'edit-tags.php?taxonomy=qsm_category' == $submenu_file ) {
			$parent_file = 'qsm_dashboard';
		}
		return $parent_file;
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
			$enabled            = get_option( 'qsm_multiple_category_enabled' );
			$qsm_dashboard_page = add_menu_page( 'Quiz And Survey Master', __( 'QSM', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page', 'dashicons-feedback' );
			add_submenu_page( 'qsm_dashboard', __( 'Dashboard', 'quiz-master-next' ), __( 'Dashboard', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page' );
			$qsm_quiz_list_page = add_submenu_page( 'qsm_dashboard', __( 'Quizzes/Surveys', 'quiz-master-next' ), __( 'Quizzes/Surveys', 'quiz-master-next' ), 'edit_posts', 'mlw_quiz_list', 'qsm_generate_quizzes_surveys_page' );
			add_action( "load-$qsm_quiz_list_page", 'qsm_generate_quizzes_surveys_page_screen_options' );
			if ( $enabled && $enabled != 'cancelled' ) {
				$qsm_taxonomy_menu_hook = add_submenu_page( 'qsm_dashboard', __( 'Question Categories', 'quiz-master-next' ), __( 'Question Categories', 'quiz-master-next' ), 'edit_posts', 'edit-tags.php?taxonomy=qsm_category' );
			}
			add_submenu_page( null, __( 'Settings', 'quiz-master-next' ), __( 'Settings', 'quiz-master-next' ), 'edit_posts', 'mlw_quiz_options', 'qsm_generate_quiz_options' );
			add_submenu_page( 'qsm_dashboard', __( 'Results', 'quiz-master-next' ), __( 'Results', 'quiz-master-next' ), 'moderate_comments', 'mlw_quiz_results', 'qsm_generate_admin_results_page' );
			add_submenu_page( null, __( 'Result Details', 'quiz-master-next' ), __( 'Result Details', 'quiz-master-next' ), 'moderate_comments', 'qsm_quiz_result_details', 'qsm_generate_result_details' );
			add_submenu_page( 'qsm_dashboard', __( 'Settings', 'quiz-master-next' ), __( 'Settings', 'quiz-master-next' ), 'manage_options', 'qmn_global_settings', array( 'QMNGlobalSettingsPage', 'display_page' ) );
			add_submenu_page( 'qsm_dashboard', __( 'Tools', 'quiz-master-next' ), __( 'Tools', 'quiz-master-next' ), 'manage_options', 'qsm_quiz_tools', 'qsm_generate_quiz_tools' );
			add_submenu_page( 'qsm_dashboard', __( 'Stats', 'quiz-master-next' ), __( 'Stats', 'quiz-master-next' ), 'moderate_comments', 'qmn_stats', 'qmn_generate_stats_page' );
			add_submenu_page( 'qsm_dashboard', __( 'Addon Settings', 'quiz-master-next' ), '<span style="color:#f39c12;">' . __( 'Addon Settings', 'quiz-master-next' ) . '</span>', 'moderate_comments', 'qmn_addons', 'qmn_addons_page' );
			add_submenu_page( 'qsm_dashboard', __( 'Get a Free Addon', 'quiz-master-next' ), '<span style="color:#f39c12;">' . esc_html__( 'Get a Free Addon!', 'quiz-master-next' ) . '</span>', 'moderate_comments', 'qsm-free-addon', 'qsm_display_optin_page' );
			add_submenu_page( 'qsm_dashboard', __( 'About', 'quiz-master-next' ), __( 'About', 'quiz-master-next' ), 'moderate_comments', 'qsm_quiz_about', 'qsm_generate_about_page' );
			// Register screen option for dashboard page
			add_action( 'screen_settings', 'qsm_dashboard_screen_options', 10, 2 );
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
	 *
	 * @since 7.1.16
	 * @return void
	 */
	public function qsm_overide_old_setting_options() {
		$settings = (array) get_option( 'qmn-settings' );

		if ( isset( $settings['facebook_app_id'] ) ) {

				$facebook_app_id = $settings['facebook_app_id'];

			if ( $facebook_app_id == '483815031724529' ) {
				$settings['facebook_app_id'] = '594986844960937';
				update_option( 'qmn-settings', $settings );
			}
		} else {
			$settings['facebook_app_id'] = '594986844960937';
			update_option( 'qmn-settings', $settings );
		}

	}

	/**
	 * Displays QSM Admin notices
	 *
	 * @return void
	 * @since 7.3.0
	 */
	public function qsm_admin_notices() {
		$multiple_categories = get_option( 'qsm_multiple_category_enabled' );
		if ( ! $multiple_categories ) {
			?>
<div class="notice notice-info multiple-category-notice" style="display:none;">
	<h3>
			<?php _e( 'Database update required', 'quiz-master-next' ); ?>
	</h3>
	<p>
			<?php
			$message  = __( ' QSM has been updated! ', 'quiz-master-next' );
			$message .= sprintf( __( '%1$s We need to upgrade your database so that you can enjoy the latest features. ', 'quiz-master-next' ), '<br/>' );
			$message .= sprintf( __( '%1$s Please note that this action %2$s can not be %3$s rolled back. We recommend you to take a backup of your current site before proceeding.', 'quiz-master-next' ), '<br/>', '<b>', '</b>' );
			echo wp_kses_post( $message );
			?>
	</p>
	<p class="category-action">
			<?php
			$buttons  = sprintf( __( '%1$s Cancel %2$s', 'quiz-master-next' ), '<a href="#" class="button cancel-multiple-category">', '</a>' );
			$buttons .= sprintf( __( '%1$s Update Database %2$s', 'quiz-master-next' ), '&nbsp;&nbsp;&nbsp;<a href="#" class="button button-primary enable-multiple-category">', '</a>' );
			echo wp_kses_post( $buttons );
			?>
	</p>
</div>
			<?php
		}
	}

}

global $mlwQuizMasterNext;
$mlwQuizMasterNext = new MLWQuizMasterNext();
register_activation_hook( __FILE__, array( 'QSM_Install', 'install' ) );
