<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class To Send Tracking Information Back To My Website
 *
 * @since 4.1.0
 */
class QSM_Tracking {

	/**
	 * Date To Send Home
	 *
	 * @var array
	 * @since 4.1.0
	 */
	private $data;

	/**
	 * Main Construct Function
	 *
	 * Call functions within class
	 *
	 * @since 4.1.0
	 * @uses QSM_Tracking::add_hooks() Adds actions to hooks and filters
	 * @return void
	 */
	function __construct() {
    	$this->add_hooks();
  	}

	/**
	 * Add Hooks
	 *
	 * Adds functions to relavent hooks and filters
	 *
	 * @since 4.1.0
	 * @return void
	 */
  	private function add_hooks() {
    	add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    	add_action( 'admin_init', array( $this, 'admin_notice_check' ) );
    	add_action( 'plugins_loaded', array( $this, 'track_check' ) );
  	}

	/**
   	 * Determines If Ready To Send Data Home
   	 *
   	 * Determines if the plugin has been authorized to send the data home in the settings page. Then checks if it has been at least a week since the last send.
   	 *
   	 * @since 4.1.0
   	 * @uses QSM_Tracking::load_data()
   	 * @uses QSM_Tracking::send_data()
   	 * @return void
   	 */
  	public function track_check() {
		$settings = (array) get_option( 'qmn-settings' );
    	$tracking_allowed = '0';
		if ( isset( $settings['tracking_allowed'] ) ) {
			$tracking_allowed = $settings['tracking_allowed'];
		}
		$last_time = get_option( 'qmn_tracker_last_time' );

		// We only send data if opted in. Opt-in designated by a value of 1 or 2.
		// We send data once a week.
    	if ( ( 1 === intval( $tracking_allowed ) || 2 === intval( $tracking_allowed) ) && ( ( $last_time && $last_time < strtotime( '-1 week' ) ) || ! $last_time ) ) {
      		$this->load_data( $tracking_allowed );
      		$this->send_data();
      		update_option( 'qmn_tracker_last_time', time() );
    	}
  	}

  	/**
   	 * Sends The Data Home
   	 *
   	 * @since 4.1.0
   	 * @return void
   	 */
  	private function send_data() {
    	$response = wp_remote_post( 'https://data.quizandsurveymaster.com/?usage_track=confirmation', array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'        => $this->data,
			'user-agent'  => 'QSM Usage Tracker',
		) );
    	if ( is_wp_error( $response ) ) {
      		global $mlwQuizMasterNext;
      		$error_message = $response->get_error_message();
      		$mlwQuizMasterNext->log_manager->add( "Error 0024", "Usage tracker failed due to following reason: $error_message", 0, 'error' );
		}
	}

  	/**
   	 * Prepares The Data To Be Sent
   	 *
   	 * @since 4.1.0
   	 * @return void
   	 */
  	private function load_data( $tracking ) {
    	global $wpdb;
    	global $mlwQuizMasterNext;

    	$data = array();
    	$data["plugin"] = "QSM";
    	$data['url']    = home_url();
    	$data["wp_version"] = get_bloginfo( 'version' );
    	$data["php_version"] = PHP_VERSION;
    	$data["mysql_version"] = $wpdb->db_version();
    	$data["server_app"] = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

    	// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}
		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;

		// Active theme information
    	$theme_data = wp_get_theme();
    	$data['theme']  = $theme_data->Name;
    	$data['theme_version'] = $theme_data->Version;

		// Aggregate information on number of quizzes, questions, and results
  		$data["total_quizzes"]          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes" );
  		$data["total_active_quizzes"]   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0" );
  		$data["total_questions"]        = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions" );
  		$data["total_active_questions"] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions WHERE deleted=0" );
    	$data["total_results"]          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results" );
  		$data["total_active_results"]   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE deleted=0" );

    	$data['original_version'] = get_option('qmn_original_version');
    	$data['current_version']  = get_option('mlw_quiz_master_version');

		// Configurations of quizzes and surveys
    	$data['quiz_options'] = $wpdb->get_results( "SELECT quiz_name, quiz_system as system, randomness_order, loggedin_user_contact, show_score, send_user_email, send_admin_email, contact_info_location, user_name, user_comp, user_email, user_phone, comment_section, question_from_total, total_user_tries, certificate_template, pagination, timer_limit, question_numbering, theme_selected, last_activity, require_log_in, limit_total_entries, disable_answer_onselect, ajax_show_correct, quiz_views, quiz_taken FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0" );

		// All error logs from QSM
    	$data['error_logs'] = $mlwQuizMasterNext->log_manager->get_logs();

		// Some meta info about the site
    	$data['site_title']   = get_bloginfo( 'name' );
    	$data['site_desc']    = get_bloginfo( 'description' );
    	$data['site_charset'] = get_bloginfo( 'charset' );
    	$data['lang']         = get_bloginfo( 'language' );

    	$this->data = $data;
  	}

	/**
	 * Adds Admin Notice To Dashboard
	 *
	 * Adds an admin notice asking for authorization to send data home
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function admin_notice() {
		$show_notice = get_option( 'qmn-tracking-notice' );
		$settings = (array) get_option( 'qmn-settings' );

		// If the notice has already been shown, return.
		if ( $show_notice ) {
			return;
		}

		// If the tracking variable has already been set, return.
		if ( isset( $settings['tracking_allowed'] ) && 1 === intval( $settings['tracking_allowed'] ) ) {
			return;
		}

		// If the user does not have the required permissions, return.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// If the site is on a dev or staging site, we do not need the data.
		if ( stristr( network_site_url( '/' ), 'dev' ) !== false || stristr( network_site_url( '/' ), 'localhost' ) !== false || stristr( network_site_url( '/' ), ':8888' ) !== false ) {
			update_option( 'qmn-tracking-notice', '1' );
		} else {
			$optin_url  = esc_url( add_query_arg( 'qmn_track_check', 'opt_into_tracking' ) );
			$optout_url = esc_url( add_query_arg( 'qmn_track_check', 'opt_out_of_tracking' ) );
			echo '<div class="updated">';
				echo '<p>' . esc_html__( "Allow Quiz And Survey Master to track this plugin's usage and help us make this plugin better?", 'quiz-master-next' ) . '<p>';
				echo '<p>' . esc_html__( "No sensitive data is tracked. Only feature usage and data about quizzes, surveys, and questions are collected. No questions or user responses is ever collected.", 'quiz-master-next' ) . '<p>';
				echo '<p><a href="https://quizandsurveymaster.com/what-the-plugin-tracks/?utm_campaign=qsm_plugin&utm_medium=plugin&utm_source=tracking-notice" target="_blank">' . esc_html__( 'Click here to learn more about why we need this data and what we collect', 'quiz-master-next' ) . '</a></p>';
				echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . esc_html__( 'Allow', 'quiz-master-next' ) . '</a>';
				echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . esc_html__( 'Do not allow', 'quiz-master-next' ) . '</a>';
			echo '</div>';
		}
	}

	/**
	 * Checks If User Has Clicked On Notice
	 *
	 * @since 4.1.0
	 * @return void
	 */
  	public function admin_notice_check() {
		// Checks if the notice has been clicked on.
    	if ( isset( $_GET["qmn_track_check"] ) ) {

			// Checks if user opted into tracking.
      		if ( sanitize_text_field( wp_unslash( $_GET["qmn_track_check"] ) ) == 'opt_into_tracking' ) {
        		$settings = (array) get_option( 'qmn-settings' );
        		$settings['tracking_allowed'] = '2';
        		update_option( 'qmn-settings', $settings );
      		} else {
        		$settings = (array) get_option( 'qmn-settings' );
        		$settings['tracking_allowed'] = '0';
        		update_option( 'qmn-settings', $settings );
			}

			// Prevents notice from being shown again.
      		update_option( 'qmn-tracking-notice', '1' );
    	}
  	}
}

$qsm_tracking = new QSM_Tracking();

