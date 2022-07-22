<?php
/**
 * Plugin Name: Quiz And Survey Master
 * Description: Easily and quickly add quizzes and surveys to your website.
 * Version: 8.0.3
 * Author: ExpressTech
 * Author URI: https://quizandsurveymaster.com/
 * Plugin URI: https://expresstech.io/
 * Text Domain: quiz-master-next
 *
 * @author QSM Team
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
define( 'QSM_PLUGIN_CSS_URL', QSM_PLUGIN_URL . 'css' );
define( 'QSM_PLUGIN_JS_URL', QSM_PLUGIN_URL . 'js' );
define( 'QSM_PLUGIN_PHP_DIR', QSM_THEME_PATH . 'php' );
define( 'QSM_PLUGIN_TXTDOMAIN', 'quiz-master-next' );

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
	public $version = '8.0.3';

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
	 * Holds quiz_data
	 *
	 * @var object
	 * @since 7.3.8
	 */
	public $quiz = array();

	/*
	* Default MathJax inline scripts.
	*/
	public static $default_MathJax_script = "MathJax = {
		tex: {
		  inlineMath: [['$','$'],['\\\\(','\\\\)']],
		  processEscapes: true
		},
		options: {
		  ignoreHtmlClass: 'tex2jax_ignore|editor-rich-text'
		}
	  };";

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
	 * @since 7.3.5 admin scripts consolidated
	 */
	public function qsm_admin_scripts_style( $hook ) {
		global $mlwQuizMasterNext;
		// admin styles
		wp_enqueue_style( 'qsm_admin_style', plugins_url( 'css/qsm-admin.css', __FILE__ ), array(), $this->version );
		if ( is_rtl() ) {
			wp_enqueue_style( 'qsm_admin_style_rtl', plugins_url( 'css/qsm-admin-rtl.css', __FILE__ ), array(), $this->version );
		}
		// dashboard and quiz list pages
		if ( 'toplevel_page_qsm_dashboard' === $hook || ('edit.php' == $hook && isset( $_REQUEST['post_type'] ) && 'qsm_quiz' == $_REQUEST['post_type']) ) {
			wp_enqueue_script( 'micromodal_script', plugins_url( 'js/micromodal.min.js', __FILE__ ), array( 'jquery', 'qsm_admin_js' ), $this->version, true );
			wp_enqueue_media();
			wp_enqueue_style( 'qsm_admin_dashboard_css', QSM_PLUGIN_CSS_URL . '/admin-dashboard.css', array(), $this->version );
			wp_style_add_data( 'qsm_admin_dashboard_css', 'rtl', 'replace' );
			wp_enqueue_style( 'qsm_ui_css', QSM_PLUGIN_CSS_URL . '/jquery-ui.min.css', array(), '1.13.0' );
		}
		// dashboard
		if ( 'toplevel_page_qsm_dashboard' === $hook ) {
			wp_enqueue_script( 'dashboard' );
			if ( wp_is_mobile() ) {
				wp_enqueue_script( 'jquery-touch-punch' );
			}
		}
		// result details page
		if ( 'admin_page_qsm_quiz_result_details' === $hook ) {
			wp_enqueue_style( 'qsm_common_style', QSM_PLUGIN_CSS_URL . '/common.css', array(), $this->version );
			wp_style_add_data( 'qsm_common_style', 'rtl', 'replace' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-slider-rtl-js', QSM_PLUGIN_JS_URL . '/jquery.ui.slider-rtl.js', array( 'jquery-ui-core', 'jquery-ui-mouse', 'jquery-ui-slider' ), $this->version, true );
			wp_enqueue_style( 'jquery-ui-slider-rtl-css', QSM_PLUGIN_CSS_URL . '/jquery.ui.slider-rtl.css', array(), $this->version );
			wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array(), $this->version, true );
			wp_enqueue_style( 'jquery-redmond-theme', QSM_PLUGIN_CSS_URL . '/jquery-ui.css', array(), $this->version );
		}
		// results page
		if ( 'qsm_page_mlw_quiz_results' === $hook ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-button' );
			wp_enqueue_style( 'qmn_jquery_redmond_theme', QSM_PLUGIN_CSS_URL . '/jquery-ui.css', array(), $this->version );
			wp_enqueue_script( 'micromodal_script', QSM_PLUGIN_JS_URL . '/micromodal.min.js', array( 'jquery' ), $this->version, true );
		}
		// stats page
		if ( 'qsm_page_qmn_stats' === $hook ) {
			wp_enqueue_script( 'ChartJS', QSM_PLUGIN_JS_URL . '/chart.min.js', array(), '3.6.0', true );
		}
		// quiz option pages
		if ( 'admin_page_mlw_quiz_options' === $hook ) {
			wp_enqueue_script( 'wp-tinymce' );
			wp_enqueue_script( 'micromodal_script', plugins_url( 'js/micromodal.min.js', __FILE__ ), array( 'jquery', 'qsm_admin_js' ), $this->version, true );
			$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'questions';
			switch ( $current_tab ) {
				case 'questions':
					wp_enqueue_style( 'qsm_admin_question_css', QSM_PLUGIN_CSS_URL . '/qsm-admin-question.css', array(), $this->version );
					if ( is_rtl() ) {
						wp_enqueue_style( 'qsm_admin_question_css_rtl', plugins_url( 'css/qsm-admin-question-rtl.css', __FILE__ ), array(), $this->version );
					}
					wp_enqueue_script( 'math_jax', QSM_PLUGIN_JS_URL . '/mathjax/tex-mml-chtml.js', false, '3.2.0', true );
					wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
					wp_enqueue_editor();
					wp_enqueue_media();
					break;
				case 'style':
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker');
					wp_enqueue_media();
					break;
				case 'options':
					wp_enqueue_style( 'qmn_jquery_redmond_theme', QSM_PLUGIN_CSS_URL . '/jquery-ui.css', array(), $this->version );
					wp_enqueue_style( 'qsm_datetime_style', QSM_PLUGIN_CSS_URL . '/jquery.datetimepicker.css', array(), $this->version );
					wp_enqueue_script( 'jquery' );
					wp_enqueue_script( 'jquery-ui-core' );
					wp_enqueue_script( 'jquery-ui-dialog' );
					wp_enqueue_script( 'jquery-ui-button' );
					wp_enqueue_script( 'qmn_datetime_js', QSM_PLUGIN_JS_URL . '/jquery.datetimepicker.full.min.js', array(), $this->version, true );
					wp_enqueue_script( 'jquery-ui-tabs' );
					wp_enqueue_script( 'jquery-effects-blind' );
					wp_enqueue_script( 'jquery-effects-explode' );
					break;
				default:
					wp_enqueue_editor();
					wp_enqueue_media();
					break;
			}
		}
		// load admin JS after all dependencies are loaded
		wp_enqueue_script( 'qsm_admin_js', plugins_url( 'js/qsm-admin.js', __FILE__ ), array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-sortable', 'jquery-touch-punch' ), $this->version, true );
		wp_enqueue_script( 'micromodal_script', plugins_url( 'js/micromodal.min.js', __FILE__ ), array( 'jquery', 'qsm_admin_js' ), $this->version, true );
		$qsm_admin_messages = array(
			'error'                      => __('Error', 'quiz-master-next'),
			'success'                    => __('Success', 'quiz-master-next'),
			'category'                   => __('Category', 'quiz-master-next'),
			'try_again'                  => __('Please try again', 'quiz-master-next'),
			'already_exists_in_database' => __('already exists in database', 'quiz-master-next'),
			'confirm_message'            => __('Are you sure?', 'quiz-master-next'),
			'error_delete_result'        => __('Error to delete the result!', 'quiz-master-next'),
			'copied'                     => __('Copied!', 'quiz-master-next'),
			'set_feature_img'            => __('Set Featured Image', 'quiz-master-next'),
			'set_bg_img'                 => __('Set Background Image', 'quiz-master-next'),
			'insert_img'                 => __('Insert Image', 'quiz-master-next'),
			'upload_img'                 => __('Upload Image', 'quiz-master-next'),
			'use_img'                    => __('Use this image', 'quiz-master-next'),
			'updating_db'                => __('Updating database', 'quiz-master-next'),
			'update_db_success'          => __('Database updated successfully.', 'quiz-master-next'),
			'quiz_submissions'           => __('Quiz Submissions', 'quiz-master-next'),
			'saving_contact_fields'      => __('Saving contact fields...', 'quiz-master-next'),
			'contact_fields_saved'       => __('Your contact fields have been saved!', 'quiz-master-next'),
			'contact_fields_save_error'  => __('There was an error encountered when saving your contact fields.', 'quiz-master-next'),
			'saving_emails'              => __('Saving emails...', 'quiz-master-next'),
			'emails_saved'               => __('Emails were saved!', 'quiz-master-next'),
			'emails_save_error'          => __('There was an error when saving the emails.', 'quiz-master-next'),
			'saving_emails'              => __('Saving emails...', 'quiz-master-next'),
			'saving_results_page'        => __('Saving results pages...', 'quiz-master-next'),
			'results_page_saved'         => __('Results pages were saved!', 'quiz-master-next'),
			'results_page_save_error'    => __('There was an error when saving the results pages.', 'quiz-master-next'),
			'all_categories'             => __('All Categories', 'quiz-master-next'),
			'add_question'               => __('Add Question', 'quiz-master-next'),
			'question_created'           => __('Question created!', 'quiz-master-next'),
			'new_question'               => __('Your new question!', 'quiz-master-next'),
			'adding_question'            => __('Adding question...', 'quiz-master-next'),
			'creating_question'          => __('Creating question...', 'quiz-master-next'),
			'duplicating_question'       => __('Duplicating question...', 'quiz-master-next'),
			'saving_question'            => __('Saving question...', 'quiz-master-next'),
			'question_saved'             => __('Question was saved!', 'quiz-master-next'),
			'loading_question'           => __('Loading questions...', 'quiz-master-next'),
			'no_question_selected'       => __('No question is selected.', 'quiz-master-next'),
			'question_reset_message'     => __('All answer will be reset, Do you want to still continue?', 'quiz-master-next'),
			'your_answer'                => __('Your answer', 'quiz-master-next'),
			'insert_image_url'           => __('Insert image URL', 'quiz-master-next'),
			'saving_page_info'           => __('Saving page info', 'quiz-master-next'),
			'saving_page_questions'      => __('Saving pages and questions...', 'quiz-master-next'),
			'saved_page_questions'       => __('Questions and pages were saved!', 'quiz-master-next'),
			'import_question_again'      => __('you want to import this question again?', 'quiz-master-next'),
			'enter_question_title'       => __('Enter Question title or description', 'quiz-master-next'),
			'page_name_required'         => __('Page Name is required!', 'quiz-master-next'),
			'page_name_validation'       => __('Please use only Alphanumeric characters.', 'quiz-master-next'),
			'polar_q_range_error'        => __('Left range and right range should be different', 'quiz-master-next'),
			'range_fields_required'      => __('Range fields are required!', 'quiz-master-next'),
			'points'                     => __('Points', 'quiz-master-next'),
			'left_label'                 => __('Left Label', 'quiz-master-next'),
			'right_label'                => __('Right Label', 'quiz-master-next'),
			'left_range'                 => __('Left Range', 'quiz-master-next'),
			'right_range'                => __('Right Range', 'quiz-master-next'),
			'html_section_empty'         => __('Text/HTML Section cannot be empty', 'quiz-master-next'),
			'blank_number_validation'    => __('Number of <strong>%BLANK%</strong> should be equal to options for sequential matching', 'quiz-master-next'),
			'blank_required_validation'  => __('Atleast one <strong>%BLANK%</strong> and one option is required.', 'quiz-master-next'),
			'polar_options_validation'   => __('You can not add more than 2 answer for Polar Question type', 'quiz-master-next'),
			'hide_advance_options'       => __('Hide advance options «', 'quiz-master-next'),
			'show_advance_options'       => __('Show advance options »', 'quiz-master-next'),
			'category_not_empty'         => __('Category cannot be empty', 'quiz-master-next'),
			'sendy_signup_validation'    => array(
				'required_message'   => __('Please fill in your name and email.', 'quiz-master-next'),
				'email_validation'   => __('Your email address is invalid.', 'quiz-master-next'),
				'list_validation'    => __('Your list ID is invalid.', 'quiz-master-next'),
				'already_subscribed' => __("You're already subscribed!", 'quiz-master-next'),
				'success_message'    => __("Thanks, you are now subscribed to our mailing list!", 'quiz-master-next'),
				'error_message'      => __("Sorry, unable to subscribe. Please try again later!", 'quiz-master-next'),
			),
		);
		wp_localize_script( 'qsm_admin_js', 'qsm_admin_messages', $qsm_admin_messages );

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
		if ( isset( $settings['cpt_archive'] ) && '1' === $settings['cpt_archive'] ) {
			$has_archive = false;
		}

		// Checks if admin turned off search.
		if ( isset( $settings['cpt_search'] ) && '1' === $settings['cpt_search'] ) {
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
			'all_items'          => __( 'Quizzes & Surveys', 'quiz-master-next' ),
			'search_items'       => __( 'Search Quizzes', 'quiz-master-next' ),
			'parent_item_colon'  => __( 'Parent Quiz:', 'quiz-master-next' ),
			'not_found'          => __( 'No Quiz Found', 'quiz-master-next' ),
			'not_found_in_trash' => __( 'No Quiz Found In Trash', 'quiz-master-next' ),
		);

		// Prepares post type array.
		$quiz_args = array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'qsm_dashboard',
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

	public function parent_file( $file_name ) {
		global $menu, $submenu, $parent_file, $submenu_file;
		if ( 'edit-tags.php?taxonomy=qsm_category' === $submenu_file ) {
			$file_name = 'qsm_dashboard';
		}
		return $file_name;
	}

	/**
	 * Setting Menu Position
	 */
	public static function get_free_menu_position( $start, $increment = 0.1 ) {
		foreach ( $GLOBALS['menu'] as $key => $menu ) {
			$menus_positions[] = floatval( $key );
		}
		if ( ! in_array( $start, $menus_positions, true ) ) {
			$start = strval( $start );
			return $start;
		} else {
			$start += $increment;
		}
		/* the position is already reserved find the closet one */
		while ( in_array( $start, $menus_positions, true ) ) {
			$start += $increment;
		}
		$start = strval( $start );
		return $start;
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
			$menu_position = self::get_free_menu_position(26.1, 0.3);
			$qsm_dashboard_page = add_menu_page( 'Quiz And Survey Master', __( 'QSM', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page', 'dashicons-feedback', $menu_position );
			add_submenu_page( 'qsm_dashboard', __( 'Dashboard', 'quiz-master-next' ), __( 'Dashboard', 'quiz-master-next' ), 'edit_posts', 'qsm_dashboard', 'qsm_generate_dashboard_page', 0 );
			if ( $enabled && 'cancelled' !== $enabled ) {
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
			if ( '483815031724529' === $facebook_app_id ) {
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
				<h3><?php esc_html_e( 'Database update required', 'quiz-master-next' ); ?></h3>
				<p>
					<?php esc_html_e( 'QSM has been updated!', 'quiz-master-next' ); ?><br>
					<?php esc_html_e( 'We need to upgrade your database so that you can enjoy the latest features.', 'quiz-master-next' ); ?><br>
					<?php
					/* translators: %s: HTML tag */
					echo sprintf( esc_html__( 'Please note that this action %1$s can not be %2$s rolled back. We recommend you to take a backup of your current site before proceeding.', 'quiz-master-next' ), '<b>', '</b>' );
					?>
				</p>
				<p class="category-action">
					<a href="javascrip:void(0)" class="button cancel-multiple-category"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></a>
					&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" class="button button-primary enable-multiple-category"><?php esc_html_e( 'Update Database', 'quiz-master-next' ); ?></a>
				</p>
			</div>
			<?php
		}

		$settings                        = (array) get_option( 'qmn-settings' );
		$background_quiz_email_process   = isset( $settings['background_quiz_email_process'] ) ? $settings['background_quiz_email_process'] : 1;
		if ( 1 == $background_quiz_email_process && is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
			?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( '"Process emails in background" option is enabled. WPML string translation may not work as expected for email templates. Please disable this option to send translated strings in emails.', 'quiz-master-next' ); ?></p>
			</div>
			<?php
		}
	}


}

global $mlwQuizMasterNext;
$mlwQuizMasterNext = new MLWQuizMasterNext();
register_activation_hook( __FILE__, array( 'QSM_Install', 'install' ) );

/**
 * Displays QSM Admin bar menu
 *
 * @return void
 * @since 7.3.8
 */
function qsm_edit_quiz_admin_option() {
	global $wp_admin_bar, $pagenow, $wpdb;
	if ( 'qsm_quiz' == get_post_type() && 'edit.php' != $pagenow ) {
		$post_id = get_the_ID();
		$quiz_id = get_post_meta( $post_id, 'quiz_id', true );
		if ( ! empty( $quiz_id ) ) {
			$wp_admin_bar->remove_menu('edit');
			$wp_admin_bar->add_menu(
				array(
					'id'    => 'edit-quiz',
					'title' => '<span class="ab-icon dashicons dashicons-edit"></span><span class="ab-label">' . __( 'Edit Quiz', 'quiz-master-next' ) . '</span>',
					'href'  => admin_url() . 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id,
				)
			);
		}
	}
}

add_action( 'admin_bar_menu', 'qsm_edit_quiz_admin_option', 999 );

/**
 * Add inline QSM template
 *
 * @return void
 * @since 7.3.14
 */
function qsm_add_inline_tmpl( $handle, $id, $tmpl ) {
	// Collect input data
	static $data            = array();
	$data[ $handle ][ $id ] = $tmpl;

	// Append template for relevant script handle
	add_filter(
		'script_loader_tag',
		function( $tag, $hndl ) use ( &$data, $id ) {
			// Nothing to do if no match
			if ( ! isset( $data[ $hndl ][ $id ] ) ) {
				return $tag;
			}

			// Script tag replacement aka wp_add_inline_script()
			if ( false !== stripos( $data[ $hndl ][ $id ], '</script>' ) ) {
				$data[ $hndl ][ $id ] = trim(
					preg_replace(
						'#<script[^>]*>(.*)</script>#is',
						'$1',
						$data[ $hndl ][ $id ]
					)
				);
			}

			// Append template
			$tag .= sprintf(
				"<script type='text/template' id='%s'>\n%s\n</script>" . PHP_EOL,
				esc_attr( $id ),
				$data[ $hndl ][ $id ]
			);

			return $tag;
		},
		10,
		3
	);
}
