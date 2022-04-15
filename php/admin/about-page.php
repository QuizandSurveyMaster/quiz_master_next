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
<style>
table.widefat td mark.yes, table.widefat th mark.yes {
	color: rgb(122, 208, 58);
	background: none transparent;
}
</style>
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
				<h2 style="text-align: left;margin-bottom: 35px;margin-top: 25px;font-weight: 500;">GitHub Contributors
				</h2>
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
		<li>For assistance in using the plugin, read our <a href="https://quizandsurveymaster.com/docs/" rel="noopener"
				target="_blank">documentation</a></li>
		<li>For support, fill out the form on our <a
				href="https://quizandsurveymaster.com/contact-support/?utm_source=qsm-help-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=contact_us"
				rel="noopener" target="_blank">Contact Us Page</a></li>
	</ul>
	<?php
}

/**
	 * Display a WooCommerce help tip.
	 *
	 * @since  2.5.0
	 *
	 * @param  string $tip        Help tip text.
	 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
	 * @return string
	 */
function qsm_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = htmlspecialchars(
			wp_kses(
				html_entity_decode( $tip ), array(
			'br'	 => array(),
			'em'	 => array(),
			'strong' => array(),
			'small'	 => array(),
			'span'	 => array(),
			'ul'	 => array(),
			'li'	 => array(),
			'ol'	 => array(),
			'p'		 => array(),
				)
			)
		);
	} else {
		$tip = esc_attr( $tip );
	}
	return '<span class="qsm-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Retrieves the MySQL server version. Based on $wpdb.
 *
 * @since 3.4.1
 * @return array Vesion information.
 */
function qsm_get_server_database_version() {
	global $wpdb;

	if ( empty( $wpdb->is_mysql ) ) {
		return array(
			'string' => '',
			'number' => '',
		);
	}

	// phpcs:disable WordPress.DB.RestrictedFunctions, PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved
	if ( $wpdb->use_mysqli ) {
		$server_info = mysqli_get_server_info( $wpdb->dbh );
	} else {
		$server_info = mysql_get_server_info( $wpdb->dbh );
	}
	// phpcs:enable WordPress.DB.RestrictedFunctions, PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved

	return array(
		'string' => $server_info,
		'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
	);
}

/**
 * Notation to numbers.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @param  string $size Size value.
 * @return int
 */
function qsm_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = (int) substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
			// No break.
		case 'T':
			$ret *= 1024;
			// No break.
		case 'G':
			$ret *= 1024;
			// No break.
		case 'M':
			$ret *= 1024;
			// No break.
		case 'K':
			$ret *= 1024;
			// No break.
	}
	return $ret;
}

/**
* Get server related info.
*
* @return array
*/
function qsm_get_server_info() {
	
	// WP memory limit.
	$wp_memory_limit = qsm_let_to_num( WP_MEMORY_LIMIT );
	if ( function_exists( 'memory_get_usage' ) ) {
		$wp_memory_limit = max( $wp_memory_limit, qsm_let_to_num( @ini_get( 'memory_limit' ) ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	// Figure out cURL version, if installed.
	$curl_version = '';
	if ( function_exists( 'curl_version' ) ) {
		$curl_version	 = curl_version();
		$curl_version	 = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
	} elseif ( extension_loaded( 'curl' ) ) {
		$curl_version = __( 'cURL installed but unable to retrieve version.', 'woocommerce' );
	}
	$database_version = qsm_get_server_database_version();
	$server_data		 = array(
		'home_url'					 => get_option( 'home' ),
		'site_url'					 => get_option( 'siteurl' ),
		'wp_version'				 => get_bloginfo( 'version' ),
		'wp_multisite'				 => is_multisite(),
		'wp_memory_limit'			 => $wp_memory_limit,
		'wp_debug_mode'				 => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
		'wp_cron'					 => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
		'server_info'				 => isset( $_SERVER['SERVER_SOFTWARE'] ) ? wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) : '',
		'php_version'				 => phpversion(),
		'php_post_max_size'			 => size_format( qsm_let_to_num( ini_get( 'post_max_size' ) ) ),
		'php_max_execution_time'	 => (int) ini_get( 'max_execution_time' ),
		'php_max_input_vars'		 => (int) ini_get( 'max_input_vars' ),
		'curl_version'				 => $curl_version,
		'max_upload_size'		 => size_format( qsm_let_to_num( wp_max_upload_size() ) ),
		'mysql_version'				 => $database_version['number'],
		'mysql_version_string'		 => $database_version['string'],
		'fsockopen_or_curl_enabled'	 => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
		'php_fsockopen'				 => function_exists( 'fsockopen' ) ? 'Yes' : 'No',
		'php_curl'					 => function_exists( 'curl_init' ) ? 'Yes' : 'No',
		'mbstring_enabled'			 => extension_loaded( 'mbstring' ),
	);
   return $server_data;
}

/**
 * This function echoes out the system info for the user.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_system_meta_box_content() {
	echo wp_kses_post( qsm_get_system_info() );
	
	$environment = qsm_get_server_info();
	?>
	<table class="widefat" cellspacing="0">
		<tbody>
			<tr>
				<td data-export-label="Server Info"><?php esc_html_e( 'Server info', 'woocommerce' ); ?>:</td>
				<td class="help"><?php echo qsm_help_tip( esc_html__( 'Information about the web server that is currently hosting your site.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td><?php echo esc_html( $environment['server_info'] ); ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Version"><?php esc_html_e( 'PHP version', 'woocommerce' ); ?>:</td>
				<td class="help"><?php echo qsm_help_tip( esc_html__( 'The version of PHP installed on your hosting server.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td>
					<?php
					if ( version_compare( $environment['php_version'], '7.2', '>=' ) ) {
						echo '<mark class="yes">' . esc_html( $environment['php_version'] ) . '</mark>';
					} else {
						$update_link = ' <a href="https://docs.woocommerce.com/document/how-to-update-your-php-version/" target="_blank">' . esc_html__( 'How to update your PHP version', 'woocommerce' ) . '</a>';
						$class       = 'error';

						if ( version_compare( $environment['php_version'], '5.4', '<' ) ) {
							$notice = '<span class="dashicons dashicons-warning"></span> ' . __( 'WooCommerce will run under this version of PHP, however, some features such as geolocation are not compatible. Support for this version will be dropped in the next major release. We recommend using PHP version 7.2 or above for greater performance and security.', 'woocommerce' ) . $update_link;
						} elseif ( version_compare( $environment['php_version'], '5.6', '<' ) ) {
							$notice = '<span class="dashicons dashicons-warning"></span> ' . __( 'WooCommerce will run under this version of PHP, however, it has reached end of life. We recommend using PHP version 7.2 or above for greater performance and security.', 'woocommerce' ) . $update_link;
						} elseif ( version_compare( $environment['php_version'], '7.2', '<' ) ) {
							$notice = __( 'We recommend using PHP version 7.2 or above for greater performance and security.', 'woocommerce' ) . $update_link;
							$class  = 'recommendation';
						}

						echo '<mark class="' . esc_attr( $class ) . '">' . esc_html( $environment['php_version'] ) . ' - ' . wp_kses_post( $notice ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<?php if ( function_exists( 'ini_get' ) ) : ?>
				<tr>
					<td data-export-label="PHP Post Max Size"><?php esc_html_e( 'PHP post max size', 'woocommerce' ); ?>:</td>
					<td class="help"><?php echo qsm_help_tip( esc_html__( 'The largest filesize that can be contained in one post.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
					<td><?php echo esc_html( $environment['php_post_max_size'] ); ?></td>
				</tr>
				<tr>
					<td data-export-label="PHP Time Limit"><?php esc_html_e( 'PHP time limit', 'woocommerce' ); ?>:</td>
					<td class="help"><?php echo qsm_help_tip( esc_html__( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
					<td><?php echo esc_html( $environment['php_max_execution_time'] ); ?></td>
				</tr>
				<tr>
					<td data-export-label="PHP Max Input Vars"><?php esc_html_e( 'PHP max input vars', 'woocommerce' ); ?>:</td>
					<td class="help"><?php echo qsm_help_tip( esc_html__( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
					<td><?php echo esc_html( $environment['php_max_input_vars'] ); ?></td>
				</tr>
				<tr>
					<td data-export-label="cURL Version"><?php esc_html_e( 'cURL version', 'woocommerce' ); ?>:</td>
					<td class="help"><?php echo qsm_help_tip( esc_html__( 'The version of cURL installed on your server.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
					<td><?php echo esc_html( $environment['curl_version'] ); ?></td>
				</tr>
			<?php endif; ?>

			<?php

			if ( $environment['mysql_version'] ) :
				?>
				<tr>
					<td data-export-label="MySQL Version"><?php esc_html_e( 'MySQL version', 'woocommerce' ); ?>:</td>
					<td class="help"><?php echo qsm_help_tip( esc_html__( 'The version of MySQL installed on your hosting server.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
					<td>
						<?php
						if ( version_compare( $environment['mysql_version'], '5.6', '<' ) && ! strstr( $environment['mysql_version_string'], 'MariaDB' ) ) {
							/* Translators: %1$s: MySQL version, %2$s: Recommended MySQL version. */
							echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'woocommerce' ), esc_html( $environment['mysql_version_string'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'woocommerce' ) . '</a>' ) . '</mark>';
						} else {
							echo '<mark class="yes">' . esc_html( $environment['mysql_version_string'] ) . '</mark>';
						}
						?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td data-export-label="Max Upload Size"><?php esc_html_e( 'Max upload size', 'woocommerce' ); ?>:</td>
				<td class="help"><?php echo qsm_help_tip( esc_html__( 'The largest filesize that can be uploaded to your WordPress installation.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td><?php echo esc_html( $environment['max_upload_size'] ); ?></td>
			</tr>
			<tr>
				<td data-export-label="fsockopen/cURL"><?php esc_html_e( 'fsockopen/cURL', 'woocommerce' ); ?>:</td>
				<td class="help"><?php echo qsm_help_tip( esc_html__( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td>
					<?php
					if ( $environment['fsockopen_or_curl_enabled'] ) {
						echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'woocommerce' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td data-export-label="Multibyte String"><?php esc_html_e( 'Multibyte string', 'woocommerce' ); ?>:</td>
				<td class="help"><?php echo qsm_help_tip( esc_html__( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td>
					<?php
					if ( $environment['mbstring_enabled'] ) {
						echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
					} else {
						/* Translators: %s: classname and link. */
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'woocommerce' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
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