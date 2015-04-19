<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class To Send Tracking Information Back To My Website
 *
 * @since 4.1.0
 */
class QMNTracking
{
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
	  * @uses QMNTracking::load_dependencies() Loads required filed
	  * @uses QMNTracking::add_hooks() Adds actions to hooks and filters
	  * @return void
	  */
  function __construct()
  {
    $this->load_dependencies();
    $this->add_hooks();
    $this->track_check();
  }

  /**
	  * Load File Dependencies
	  *
	  * @since 4.1.0
	  * @return void
	  */
  private function load_dependencies()
  {

  }

  /**
	  * Add Hooks
	  *
	  * Adds functions to relavent hooks and filters
	  *
	  * @since 4.1.0
	  * @return void
	  */
  private function add_hooks()
  {
    add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    add_action( 'admin_init', array($this, 'admin_notice_check'));
  }

  /**
   * Determines If Ready To Send Data Home
   *
   * Determines if the plugin has been authorized to send the data home in the settings page. Then checks if it has been at least a week since the last send.
   *
   * @since 4.1.0
   * @uses QMNTracking::load_data()
   * @uses QMNTracking::send_data()
   * @return void
   */
  private function track_check()
  {
    $settings = (array) get_option( 'qmn-settings' );
    $tracking_allowed = '0';
		if (isset($settings['tracking_allowed']))
		{
			$tracking_allowed = $settings['tracking_allowed'];
		}
    $last_time = get_option( 'qmn_tracker_last_time' );
    if ($tracking_allowed == '1' && (($last_time && $last_time < strtotime( '-1 week' )) || !$last_time))
    {
      $this->load_data();
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
  private function send_data()
  {
    $response = wp_remote_post( 'http://mylocalwebstop.com/?usage_track=confirmation', array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'        => $this->data,
			'user-agent'  => 'QMN Usage Tracker'
		) );
    if ( is_wp_error( $response ) ) {
		   $error_message = $response->get_error_message();
		   echo "Something went wrong with QMN Usage Tracker: $error_message";
		}
  }

  /**
   * Prepares The Data To Be Sent
   *
   * @since 4.1.0
   * @return void
   */
  private function load_data()
  {
    global $wpdb;
    $data = array();
    $data["plugin"] = "QMN";

    $data['url']    = home_url();
    $data["wp_version"] = get_bloginfo( 'version' );
    $data["php_version"] = PHP_VERSION;
    $data["mysql_version"] = $wpdb->db_version();
    $data["server_app"] = $_SERVER['SERVER_SOFTWARE'];

    // Retrieve current plugin information
		if( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}
		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;

    $theme_data = wp_get_theme();
		$theme = $theme_data->Name . ' ' . $theme_data->Version;
    $data['theme']  = $theme;

    $mlw_stat_total_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes" );
  	$mlw_stat_total_active_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0" );
  	$mlw_stat_total_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions" );
  	$mlw_stat_total_active_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions WHERE deleted=0" );

  	$data["total_quizzes"] = $mlw_stat_total_quiz;
  	$data["total_active_quizzes"] = $mlw_stat_total_active_quiz;
  	$data["total_questions"] = $mlw_stat_total_questions;
  	$data["total_active_questions"] = $mlw_stat_total_active_questions;

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
  public function admin_notice()
  {
    $show_notice = get_option( 'qmn-tracking-notice' );
    $settings = (array) get_option( 'qmn-settings' );

    if ($show_notice)
      return;

    if (isset($settings['tracking_allowed']) && $settings['tracking_allowed'] == '1')
      return;

    if(!current_user_can('manage_options'))
			return;

    if(stristr(network_site_url('/'), 'dev') !== false || stristr(network_site_url('/'), 'localhost') !== false || stristr(network_site_url('/'), ':8888') !== false)
    {
			update_option( 'qmn-tracking-notice', '1' );
		}
    else
    {
      $optin_url  = add_query_arg( 'qmn_track_check', 'opt_into_tracking' );
  		$optout_url = add_query_arg( 'qmn_track_check', 'opt_out_of_tracking' );
  		echo '<div class="updated"><p>';
  			echo __( "Allow Quiz Master Next to anonymously track this plugin's usage and help us make this plugin better? No sensitive data is tracked.", 'quiz-master-next' );
  			echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'quiz-master-next' ) . '</a>';
  			echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'quiz-master-next' ) . '</a>';
  		echo '</p></div>';
    }
  }

  /**
   * Checks If User Has Clicked On Notice
   *
   * @since 4.1.0
   * @return void
   */
  public function admin_notice_check()
  {
    if (isset($_GET["qmn_track_check"]))
    {
      if ($_GET["qmn_track_check"] == 'opt_into_tracking')
      {
        $settings = (array) get_option( 'qmn-settings' );
        $settings['tracking_allowed'] = '1';
        update_option( 'qmn-settings', $settings );
      }
      else
      {
        $settings = (array) get_option( 'qmn-settings' );
        $settings['tracking_allowed'] = '0';
        update_option( 'qmn-settings', $settings );
      }
      update_option( 'qmn-tracking-notice', '1' );
    }
  }
}
$qmnTracking = new QMNTracking();

?>
