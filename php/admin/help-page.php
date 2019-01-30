<?php
/**
 * Creates the Help page within the admin area
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function generates the help page.
 *
 * @return void
 * @since 6.2.0
 */
function qsm_generate_help_page() {
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ) );

	// Creates the widgets.
	add_meta_box( 'wpss_mrts', __( 'Need Help?', 'quiz-master-next' ), 'qsm_documentation_meta_box_content', 'meta_box_help' );
	add_meta_box( 'wpss_mrts', __( 'System Info', 'quiz-master-next' ), 'qsm_system_meta_box_content', 'meta_box_sys_info' );
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Help Page', 'quiz-master-next' ); ?></h2>
		<?php qsm_show_adverts(); ?>

		<!--Display Widget Boxes-->
		<div style="width:100%;" class="inner-sidebar1">
			<?php do_meta_boxes( 'meta_box_help', 'advanced', '' ); ?>
		</div>

		<div style="width:100%;" class="inner-sidebar1">
			<?php do_meta_boxes( 'meta_box_sys_info', 'advanced', '' ); ?>
		</div>

	</div>
<?php
}

/**
 * This function creates the text that is displayed on the help page.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_documentation_meta_box_content() {
	?>
	<p><?php esc_html_e( 'Need help with the plugin? Try any of the following:', 'quiz-master-next' ); ?></p>
	<ul>
		<li>For assistance in using the plugin, read our <a href="https://docs.quizandsurveymaster.com" target="_blank">documentation</a></li>
		<li>For support, fill out the form on our <a href="https://quizandsurveymaster.com/quiz/contact/?utm_source=qsm-help-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=contact_us" target="_blank">Contact Us Page</a></li>
	</ul>
	<?php
}

/**
 * This function echoes out the system info for the user.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_system_meta_box_content() {
	echo qsm_get_system_info();
}

/**
 * This function gets the content that is in the system info
 *
 * @return return string This contains all of the system info from the admins server.
 * @since 4.4.0
 */
function qsm_get_system_info() {
	global $wpdb;
	global $mlwQuizMasterNext;

	$sys_info = '';

	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}

	$sys_info .= '<h3>Site Information</h3><br />';
	$sys_info .= 'Site URL: ' . site_url() . '<br />';
	$sys_info .= 'Home URL: ' . home_url() . '<br />';
	$sys_info .= 'Multisite: ' . ( is_multisite() ? 'Yes' : 'No' ) . '<br />';

	$sys_info .= '<h3>WordPress Information</h3><br />';
	$sys_info .= 'Version: ' . get_bloginfo( 'version' ) . '<br />';
	$sys_info .= 'Language: ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . '<br />';
	$sys_info .= 'Permalink Structure: ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . '<br>';
	$sys_info .= "Active Theme: {$theme}<br />";
	$sys_info .= "Parent Theme: {$parent_theme}<br>";
	$sys_info .= 'Debug Mode: ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . '<br />';
	$sys_info .= 'Memory Limit: ' . WP_MEMORY_LIMIT . '<br />';

	$sys_info .= '<h3>Plugins Information</h3><br />';
	$plugin_mu = get_mu_plugins();
	if ( count( $plugin_mu ) > 0 ) {
		$sys_info .= '<h4>Must Use</h4><br />';
		foreach ( $plugin_mu as $plugin => $plugin_data ) {
			$sys_info .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "<br />";
		}
	}
	$sys_info      .= '<h4>Active</h4><br />';
	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );
	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( ! in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}
		$sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . '<br />';
	}
	$sys_info .= '<h4>Inactive</h4><br />';
	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}
		$sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . '<br />';
	}

	$sys_info .= '<h3>Server Information</h3><br />';
	$sys_info .= 'PHP : ' . PHP_VERSION . '<br />';
	$sys_info .= 'MySQL : ' . $wpdb->db_version() . '<br />';
	$sys_info .= 'Webserver : ' . $_SERVER['SERVER_SOFTWARE'] . '<br />';

	$total_quizzes          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes LIMIT 1" );
	$total_active_quizzes   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0 LIMIT 1" );
	$total_questions        = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions LIMIT 1" );
	$total_active_questions = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 LIMIT 1" );
	$total_results          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results LIMIT 1" );
	$total_active_results   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 LIMIT 1" );

	$sys_info .= '<h3>QSM Information</h3><br />';
	$sys_info .= 'Initial Version : ' . get_option( 'qmn_original_version' ) . '<br />';
	$sys_info .= 'Current Version : ' . $mlwQuizMasterNext->version . '<br />';
	$sys_info .= "Total Quizzes : {$total_quizzes}<br />";
	$sys_info .= "Total Active Quizzes : {$total_active_quizzes}<br />";
	$sys_info .= "Total Questions : {$total_questions}<br />";
	$sys_info .= "Total Active Questions : {$total_active_questions}<br />";
	$sys_info .= "Total Results : {$total_results}<br />";
	$sys_info .= "Total Active Results : {$total_active_results}<br />";

	$sys_info     .= '<h3>QSM Recent Logs</h3><br />';
	$recent_errors = $mlwQuizMasterNext->log_manager->get_logs();
	if ( $recent_errors ) {
		foreach ( $recent_errors as $error ) {
			$sys_info .= "Log created at {$error->post_date}: {$error->post_title} - {$error->post_content}<br />";
		}
	} else {
		$sys_info .= 'No recent logs<br />';
	}

	return $sys_info;
}

?>
