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
function qsm_generate_about_page() {
	global $mlwQuizMasterNext;
	$version = $mlwQuizMasterNext->version;
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}
	$tab_array = [
		[
			'slug'  => 'about',
			'title' => 'About',
		],
		[
			'slug'  => 'help',
			'title' => 'Help',
		],
	];
	$active_tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'about';

	// Creates the widgets.
	add_meta_box( 'wpss_mrts', __( 'Need Help?', 'quiz-master-next' ), 'qsm_documentation_meta_box_content', 'meta_box_help' );
	add_meta_box( 'wpss_mrts', __( 'System Info', 'quiz-master-next' ), 'qsm_system_meta_box_content', 'meta_box_sys_info' );
	?>

<?php if ( 'help' === $active_tab ) {?>
<div class="wrap qsm-help-page">
	<h2><?php esc_html_e( 'Help Page', 'quiz-master-next' ); ?></h2>
	<?php } elseif ( 'about' === $active_tab ) {?>
	<style>
	div.qsm_icon_wrap {
		background: <?php echo 'url("'. esc_url( plugins_url( '../../assets/icon-128x128.png', __FILE__ ) ). '" )';
		?>no-repeat;
	}
	</style>
	<div class="wrap about-wrap">
		<h1><?php esc_html_e( 'Welcome To Quiz And Survey Master (Formerly Quiz Master Next)', 'quiz-master-next' ); ?>
		</h1>
		<div class="qsm_icon_wrap"><?php echo esc_html( $version ); ?></div>
		<?php } ?>

		<h2 class="nav-tab-wrapper">
			<?php
            foreach ( $tab_array as $tab ) {
                $active_class = '';
                if ( $active_tab === $tab['slug'] ) {
                    $active_class = ' nav-tab-active';
                }
                echo '<a href="?page=qsm_quiz_about&tab=' . esc_attr( $tab['slug'] ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . esc_html( $tab['title'] ) . '</a>';
            }
            ?>
		</h2>
		<br />
		<div>
			<?php
                if ( 'help' === $active_tab ) {
                    qsm_show_adverts();
					?>
			<div style="width:100%;" class="inner-sidebar1">
				<?php do_meta_boxes( 'meta_box_help', 'advanced', '' ); ?>
			</div>

			<div style="width:100%;" class="inner-sidebar1">
				<?php do_meta_boxes( 'meta_box_sys_info', 'advanced', '' ); ?>
			</div>
			<?php
                } elseif ( 'about' === $active_tab ) {
					?>
			<div class="qsm-tab-content tab-3">
				<h2 class="text-left" style="font-weight: 500;">GitHub Contributors</h2>
				<?php
					$contributors = get_transient( 'qmn_contributors' );
					if ( false === $contributors ) {
						$response = wp_remote_get( 'https://api.github.com/repos/QuizandSurveyMaster/quiz_master_next/contributors', array( 'sslverify' => false ) );
						if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
							$contributors = array();
						} else {
							$contributors = json_decode( wp_remote_retrieve_body( $response ) );
						}
					}

					if ( is_array( $contributors ) & ! empty( $contributors ) ) {
						set_transient( 'qmn_contributors', $contributors, 3600 );
						$contributor_list = '<ul class="wp-people-group">';
						foreach ( $contributors as $contributor ) {
							$contributor_list .= '<li class="wp-person">';
							$contributor_list .= sprintf( '<a href="%s" title="%s">',
							esc_url( 'https://github.com/' . $contributor->login ),
							// translators: This is the 'title' attribute for GitHub contributors. This would add the GitHub user such as 'View fpcorso'.
							esc_html( __( 'View ', 'quiz-master-next' ) . $contributor->login )
							);
							$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
							$contributor_list .= '</a>';
							$contributor_list .= sprintf( '<a class="web" href="%s" rel="noopener" target="_blank">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
							$contributor_list .= '</a>';
							$contributor_list .= '</li>';
						}
						$contributor_list .= '</ul>';
						echo wp_kses_post( $contributor_list );
					}
					?>
				<a href="https://github.com/QuizandSurveyMaster/quiz_master_next" rel="noopener" target="_blank"
					class="button-primary">View GitHub Repo</a>
			</div>
			<?php
				}
            ?>
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
		<li>For assistance in using the plugin, read our <a href="<?php echo esc_url( qsm_get_plugin_link('docs', 'qsm-help-page', 'documentation') );?>" rel="noopener" target="_blank">documentation</a></li>
		<li>For support, fill out the form on our <a href="<?php echo esc_url( qsm_get_plugin_link('contact-support', 'qsm-help-page', 'contact_us') );?>" rel="noopener" target="_blank">Contact Us Page</a></li>
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
	echo wp_kses_post( qsm_get_system_info() );
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

	$sys_info .= '<h3>'. __('Site Information', 'quiz-master-next') .'</h3>';
	$sys_info .= __('Site URL:', 'quiz-master-next') . ' ' . site_url() . '<br />';
	$sys_info .= __('Home URL:', 'quiz-master-next') . ' ' . home_url() . '<br />';
	$sys_info .= __('Multisite: ', 'quiz-master-next') . ( is_multisite() ? 'Yes' : 'No' ) . '<br />';

	$sys_info .= '<h3>'. __('WordPress Information', 'quiz-master-next') .'</h3>';
	$sys_info .= __('Version: ', 'quiz-master-next') . get_bloginfo( 'version' ) . '<br />';
	$sys_info .= __('Language: ', 'quiz-master-next') . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . '<br />';
	$sys_info .= __('Permalink Structure: ', 'quiz-master-next') . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . '<br>';
	$sys_info .= __('Active Theme: ', 'quiz-master-next') . "{$theme}";
	$sys_info .= __('Parent Theme: ', 'quiz-master-next') . "{$parent_theme}<br>";
	$sys_info .= __('Debug Mode: ', 'quiz-master-next') . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . '<br />';
	$sys_info .= __('Memory Limit: ', 'quiz-master-next') . WP_MEMORY_LIMIT . '<br />';

	$sys_info .= '<h3>'. __('Plugins Information', 'quiz-master-next') .'</h3>';
	$plugin_mu = get_mu_plugins();
	if ( count( $plugin_mu ) > 0 ) {
		$sys_info .= '<h4>'. __('Must Use', 'quiz-master-next') .'</h4>';
		foreach ( $plugin_mu as $plugin => $plugin_data ) {
			$sys_info .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "<br />";
		}
	}
	$sys_info      .= '<h4>'. __('Active', 'quiz-master-next') .'</h4>';
	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );
	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
			continue;
		}
		$sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . '<br />';
	}
	$sys_info .= '<h4>'. __('Inactive', 'quiz-master-next') .'</h4>';
	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins, true ) ) {
			continue;
		}
		$sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . '<br />';
	}

	$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	$sys_info .= '<h3>'. __('Server Information', 'quiz-master-next') .'</h3>';
	$sys_info .= __('PHP : ', 'quiz-master-next') . PHP_VERSION . '<br />';
	$sys_info .= __('MySQL : ', 'quiz-master-next') . $wpdb->db_version() . '<br />';
	$sys_info .= __('Webserver : ', 'quiz-master-next') . $server_software . '<br />';

	$total_quizzes          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes LIMIT 1" );
	$total_active_quizzes   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0 LIMIT 1" );
	$total_questions        = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions LIMIT 1" );
	$total_active_questions = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 LIMIT 1" );
	$total_results          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results LIMIT 1" );
	$total_active_results   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 LIMIT 1" );

	$sys_info .= '<h3>'. __('QSM Information', 'quiz-master-next') .'</h3>';
	$sys_info .= __('Initial Version : ', 'quiz-master-next') . get_option( 'qmn_original_version' ) . '<br />';
	$sys_info .= __('Current Version : ', 'quiz-master-next') . $mlwQuizMasterNext->version . '<br />';
	$sys_info .= __('Total Quizzes : ', 'quiz-master-next') . "{$total_quizzes}<br />";
	$sys_info .= __('Total Active Quizzes : ', 'quiz-master-next') . "{$total_active_quizzes}<br />";
	$sys_info .= __('Total Questions : ', 'quiz-master-next') . "{$total_questions}<br />";
	$sys_info .= __('Total Active Questions : ', 'quiz-master-next') . "{$total_active_questions}<br />";
	$sys_info .= __('Total Results : ', 'quiz-master-next') . "{$total_results}<br />";
	$sys_info .= __('Total Active Results : ', 'quiz-master-next') . "{$total_active_results}<br />";

	$sys_info     .= '<h3>'. __('QSM Recent Logs', 'quiz-master-next') .'</h3>';
	$recent_errors = $mlwQuizMasterNext->log_manager->get_logs();
	if ( $recent_errors ) {
		foreach ( $recent_errors as $error ) {
			$sys_info .= "Log created at {$error->post_date}: {$error->post_title} - {$error->post_content}<br />";
		}
	} else {
		$sys_info .= __('No recent logs','quiz-master-next') . '<br />';
	}

	return $sys_info;
}

?>