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
	if ( ! current_user_can( 'delete_others_qsm_quizzes' ) ) {
		return;
	}
	$tab_array  = array(
		'about'       => array(
			'title' => __( 'About', 'quiz-master-next' ),
			'icon'  => 'info',
		),
		'help'        => array(
			'title' => __( 'Help', 'quiz-master-next' ),
			'icon'  => 'users',
		),
		'system_info' => array(
			'title' => __( 'System Info', 'quiz-master-next' ),
			'icon'  => 'settings',
		),
	);
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'about';
	if ( ! isset( $tab_array[ $active_tab ] ) ) {
		$active_tab = 'about';
	}
	?>
	<div class="wrap qsm-ui qsm-ui-about-page">
		<div class="qsm-ui-page-header qsm-ui-text">
			<div class="qsm-ui-about-brand">
				<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/logo-blue.svg' ); ?>" alt="" width="44" height="44">
				<div>
					<h1 class="qsm-ui-h1"><?php esc_html_e( 'Quiz And Survey Master', 'quiz-master-next' ); ?></h1>
					<p class="qsm-ui-subtitle">
						<?php
						/* translators: %s: plugin version */
						echo esc_html( sprintf( __( 'Version %s', 'quiz-master-next' ), $version ) );
						?>
					</p>
				</div>
			</div>
		</div>
		<hr class="wp-header-end">
		<nav class="qsm-ui-page-tabs-nav" aria-label="<?php esc_attr_e( 'About', 'quiz-master-next' ); ?>">
			<div class="qsm-ui-tabs qsm-ui-page-tabs">
				<?php foreach ( $tab_array as $slug => $tab ) { ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=qsm_quiz_about&tab=' . $slug ) ); ?>" class="qsm-ui-tab"<?php echo $slug === $active_tab ? ' aria-current="page"' : ''; ?>>
						<?php echo QSM_Admin_UI::icon( $tab['icon'], 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
						<?php echo esc_html( $tab['title'] ); ?>
					</a>
				<?php } ?>
			</div>
		</nav>
		<div class="qsm-ui-about-content">
			<?php
			if ( 'help' === $active_tab ) {
				qsm_show_adverts();
				qsm_documentation_meta_box_content();
			} elseif ( 'system_info' === $active_tab ) {
				qsm_about_system_info_tab();
			} else {
				qsm_about_us_tab();
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * About tab: who we are and the GitHub contributors.
 *
 * @since 11.3.0
 * @return void
 */
function qsm_about_us_tab() {
	echo QSM_Admin_UI::card_open( array( 'title' => __( 'About us', 'quiz-master-next' ), 'class' => 'qsm-ui-about-card' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	?>
	<p class="qsm-ui-about-lead"><?php esc_html_e( 'Quiz And Survey Master is a leading WordPress plugin developed by a dedicated team of developers and educational experts. Our mission is to help individuals and organizations create engaging and effective quizzes and surveys that enhance learning and gather valuable insights. With a focus on user experience and flexibility, our plugin is designed to meet the needs of educators, marketers, and businesses alike. Quiz And Survey Master is a powerful and intuitive WordPress plugin that allows users to create a wide range of quizzes and surveys effortlessly.', 'quiz-master-next' ); ?></p>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.

	$contributors = get_transient( 'qmn_contributors' );
	if ( false === $contributors ) {
		$response = wp_remote_get( 'https://api.github.com/repos/QuizandSurveyMaster/quiz_master_next/contributors', array( 'sslverify' => false ) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$contributors = array();
		} else {
			$contributors = json_decode( wp_remote_retrieve_body( $response ) );
		}
		if ( is_array( $contributors ) && ! empty( $contributors ) ) {
			set_transient( 'qmn_contributors', $contributors, 3600 );
		}
	}
	$repo_button = QSM_Admin_UI::button(
		__( 'View GitHub Repo', 'quiz-master-next' ),
		array(
			'variant' => 'primary',
			'size'    => 'sm',
			'icon'    => 'ext',
			'href'    => qsm_get_utm_link( 'https://github.com/QuizandSurveyMaster/quiz_master_next', 'qsm', 'about', 'about_git_repo' ),
			'attrs'   => array(
				'target' => '_blank',
				'rel'    => 'noopener',
			),
		)
	);
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'GitHub Contributors', 'quiz-master-next' ),
			'description' => __( 'The people who build Quiz And Survey Master in the open.', 'quiz-master-next' ),
			'actions'     => $repo_button,
			'class'       => 'qsm-ui-about-card',
		)
	);
	if ( is_array( $contributors ) && ! empty( $contributors ) ) {
		?>
		<ul class="qsm-ui-contributors">
			<?php
			foreach ( $contributors as $contributor ) {
				if ( empty( $contributor->login ) ) {
					continue;
				}
				$url = 'https://github.com/' . $contributor->login;
				?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank" title="<?php echo esc_attr( __( 'View ', 'quiz-master-next' ) . $contributor->login ); ?>">
						<img src="<?php echo esc_url( $contributor->avatar_url ); ?>" width="48" height="48" alt="" loading="lazy">
						<span><?php echo esc_html( $contributor->login ); ?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
		<?php
	} else {
		echo QSM_Admin_UI::empty_state( array( 'icon' => 'users', 'title' => __( 'Contributors could not be loaded', 'quiz-master-next' ), 'text' => __( 'GitHub did not respond. See the full list on the GitHub repo.', 'quiz-master-next' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	}
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
}

/**
 * Help tab: support, setup and services cards.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_documentation_meta_box_content() {
	$cards = array(
		array(
			'image' => 'support.png',
			'title' => __( 'Support & Pre-Sales Questions', 'quiz-master-next' ),
			'text'  => __( 'Get expert answers to your questions about features, pricing, or installation of Quiz and Survey Master.', 'quiz-master-next' ),
			'link'  => qsm_get_plugin_link( 'contact-support', 'qsm', 'help', 'about_help_contact-support' ),
			'label' => __( 'Contact Us', 'quiz-master-next' ),
		),
		array(
			'image' => 'setup.png',
			'title' => __( 'Need help with QSM Setup?', 'quiz-master-next' ),
			'text'  => __( 'Access step-by-step guidance and troubleshooting to ensure your quizzes and surveys are up and running smoothly.', 'quiz-master-next' ),
			'link'  => qsm_get_plugin_link( 'contact-support', 'qsm', 'help', 'about_help_contact-support' ),
			'label' => __( 'Contact Us', 'quiz-master-next' ),
		),
		array(
			'image' => 'services.png',
			'title' => __( 'See All Services', 'quiz-master-next' ),
			'text'  => __( 'Tailor Quiz and Survey Master to your specific needs with our professional customization services for unique functionality.', 'quiz-master-next' ),
			'link'  => qsm_get_utm_link( 'https://justhyre.com/customize-qsm/', 'qsm', 'help', 'see_all_services' ),
			'label' => __( 'See All Services', 'quiz-master-next' ),
		),
	);
	?>
	<div class="qsm-ui-grid-3 qsm-ui-help-cards">
		<?php foreach ( $cards as $card ) { ?>
			<div class="qsm-ui-card qsm-ui-help-card">
				<div class="qsm-ui-card-body qsm-ui-text">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/' . $card['image'] ); ?>" alt="" width="56" height="56">
					<h3 class="qsm-ui-h3"><?php echo esc_html( $card['title'] ); ?></h3>
					<p><?php echo esc_html( $card['text'] ); ?></p>
				</div>
				<div class="qsm-ui-card-foot">
					<?php
					echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
						$card['label'],
						array(
							'size'  => 'sm',
							'icon'  => 'ext',
							'href'  => $card['link'],
							'attrs' => array(
								'target' => '_blank',
								'rel'    => 'noopener',
							),
						)
					);
					?>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * System Info tab: one card per section, with a button that copies the report as plain text.
 *
 * @since 11.3.0
 * @return void
 */
function qsm_about_system_info_tab() {
	$sections = qsm_get_system_info_sections();
	$plain    = array();
	foreach ( $sections as $section ) {
		$plain[] = '### ' . $section['title'] . ' ###';
		foreach ( $section['groups'] as $group ) {
			if ( '' !== $group['title'] ) {
				$plain[] = '-- ' . $group['title'];
			}
			foreach ( $group['rows'] as $row ) {
				$plain[] = ( '' !== $row[0] ? $row[0] . ': ' : '' ) . $row[1];
			}
		}
		$plain[] = '';
	}
	$copy = QSM_Admin_UI::button(
		__( 'Copy system info', 'quiz-master-next' ),
		array(
			'size'  => 'sm',
			'icon'  => 'copy',
			'attrs' => array( 'data-qsm-ui-copy' => '#qsm-system-info-text' ),
		)
	);
	?>
	<div class="qsm-ui-sysinfo-bar qsm-ui-text">
		<p><?php esc_html_e( 'Share this report with support when you open a ticket.', 'quiz-master-next' ); ?></p>
		<?php echo $copy; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
		<label class="qsm-ui-sr" for="qsm-system-info-text"><?php esc_html_e( 'System info', 'quiz-master-next' ); ?></label>
		<textarea id="qsm-system-info-text" class="qsm-ui-sr" readonly tabindex="-1"><?php echo esc_textarea( implode( "\n", $plain ) ); ?></textarea>
	</div>
	<div class="qsm-ui-grid-2 qsm-ui-sysinfo">
		<?php
		foreach ( $sections as $section ) {
			echo QSM_Admin_UI::card_open( array( 'title' => $section['title'], 'class' => 'qsm-ui-sysinfo-card' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			foreach ( $section['groups'] as $group ) {
				if ( '' !== $group['title'] ) {
					echo '<p class="qsm-ui-overline">' . esc_html( $group['title'] ) . '</p>';
				}
				echo '<table class="qsm-ui-kv"><tbody>';
				foreach ( $group['rows'] as $row ) {
					echo '<tr>' . ( '' !== $row[0] ? '<th scope="row">' . esc_html( $row[0] ) . '</th><td>' : '<td colspan="2">' ) . esc_html( $row[1] ) . '</td></tr>';
				}
				echo '</tbody></table>';
			}
			echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		}
		?>
	</div>
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
 * System info as sections of label/value rows.
 *
 * @since 11.3.0
 * @return array[] Each section: title, groups[] (title, rows[] of array( label, value )).
 */
function qsm_get_system_info_sections() {
	global $wpdb;
	global $mlwQuizMasterNext;

	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}
	$yes_no = function ( $on ) {
		return $on ? __( 'Yes', 'quiz-master-next' ) : __( 'No', 'quiz-master-next' );
	};

	$sections   = array();
	$sections[] = array(
		'title'  => __( 'Site Information', 'quiz-master-next' ),
		'groups' => array(
			array(
				'title' => '',
				'rows'  => array(
					array( __( 'Site URL', 'quiz-master-next' ), site_url() ),
					array( __( 'Home URL', 'quiz-master-next' ), home_url() ),
					array( __( 'Multisite', 'quiz-master-next' ), $yes_no( is_multisite() ) ),
				),
			),
		),
	);
	$sections[] = array(
		'title'  => __( 'WordPress Information', 'quiz-master-next' ),
		'groups' => array(
			array(
				'title' => '',
				'rows'  => array(
					array( __( 'Version', 'quiz-master-next' ), get_bloginfo( 'version' ) ),
					array( __( 'Language', 'quiz-master-next' ), defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ),
					array( __( 'Permalink Structure', 'quiz-master-next' ), get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Default', 'quiz-master-next' ) ),
					array( __( 'Active Theme', 'quiz-master-next' ), $theme ),
					array( __( 'Parent Theme', 'quiz-master-next' ), $parent_theme ? $parent_theme : '—' ),
					array( __( 'Debug Mode', 'quiz-master-next' ), defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? __( 'Enabled', 'quiz-master-next' ) : __( 'Disabled', 'quiz-master-next' ) ) : __( 'Not set', 'quiz-master-next' ) ),
					array( __( 'Memory Limit', 'quiz-master-next' ), WP_MEMORY_LIMIT ),
				),
			),
		),
	);

	$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	$sections[]      = array(
		'title'  => __( 'Server Information', 'quiz-master-next' ),
		'groups' => array(
			array(
				'title' => '',
				'rows'  => array(
					array( 'PHP', PHP_VERSION ),
					array( 'MySQL', $wpdb->db_version() ),
					array( __( 'Webserver', 'quiz-master-next' ), $server_software ),
				),
			),
		),
	);

	$sections[] = array(
		'title'  => __( 'QSM Information', 'quiz-master-next' ),
		'groups' => array(
			array(
				'title' => '',
				'rows'  => array(
					array( __( 'Initial Version', 'quiz-master-next' ), (string) get_option( 'qmn_original_version' ) ),
					array( __( 'Current Version', 'quiz-master-next' ), $mlwQuizMasterNext->version ),
					array( __( 'Total Quizzes', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes LIMIT 1" ) ),
					array( __( 'Total Active Quizzes', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0 LIMIT 1" ) ),
					array( __( 'Total Questions', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions LIMIT 1" ) ),
					array( __( 'Total Active Questions', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 LIMIT 1" ) ),
					array( __( 'Total Results', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results LIMIT 1" ) ),
					array( __( 'Total Active Results', 'quiz-master-next' ), (string) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 LIMIT 1" ) ),
				),
			),
		),
	);

	$plugin_groups = array();
	$plugin_mu     = get_mu_plugins();
	if ( count( $plugin_mu ) > 0 ) {
		$rows = array();
		foreach ( $plugin_mu as $plugin_data ) {
			$rows[] = array( $plugin_data['Name'], $plugin_data['Version'] );
		}
		$plugin_groups[] = array(
			'title' => __( 'Must Use', 'quiz-master-next' ),
			'rows'  => $rows,
		);
	}
	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );
	$active_rows    = array();
	$inactive_rows  = array();
	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins, true ) ) {
			$active_rows[] = array( $plugin['Name'], $plugin['Version'] );
		} else {
			$inactive_rows[] = array( $plugin['Name'], $plugin['Version'] );
		}
	}
	$plugin_groups[] = array(
		'title' => __( 'Active', 'quiz-master-next' ),
		'rows'  => $active_rows ? $active_rows : array( array( '', __( 'None', 'quiz-master-next' ) ) ),
	);
	$plugin_groups[] = array(
		'title' => __( 'Inactive', 'quiz-master-next' ),
		'rows'  => $inactive_rows ? $inactive_rows : array( array( '', __( 'None', 'quiz-master-next' ) ) ),
	);
	$sections[] = array(
		'title'  => __( 'Plugins Information', 'quiz-master-next' ),
		'groups' => $plugin_groups,
	);

	$log_rows      = array();
	$recent_errors = $mlwQuizMasterNext->log_manager->get_logs();
	if ( $recent_errors ) {
		foreach ( $recent_errors as $error ) {
			$log_rows[] = array( $error->post_date, $error->post_title . ' - ' . $error->post_content );
		}
	} else {
		$log_rows[] = array( '', __( 'No recent logs', 'quiz-master-next' ) );
	}
	$sections[] = array(
		'title'  => __( 'QSM Recent Logs', 'quiz-master-next' ),
		'groups' => array(
			array(
				'title' => '',
				'rows'  => $log_rows,
			),
		),
	);

	return $sections;
}

/**
 * This function gets the content that is in the system info
 *
 * @return return string This contains all of the system info from the admins server.
 * @since 4.4.0
 */
function qsm_get_system_info() {
	$sys_info = '';
	foreach ( qsm_get_system_info_sections() as $section ) {
		$sys_info .= '<h3>' . esc_html( $section['title'] ) . '</h3>';
		foreach ( $section['groups'] as $group ) {
			if ( '' !== $group['title'] ) {
				$sys_info .= '<h4>' . esc_html( $group['title'] ) . '</h4>';
			}
			foreach ( $group['rows'] as $row ) {
				$sys_info .= ( '' !== $row[0] ? esc_html( $row[0] ) . ': ' : '' ) . esc_html( $row[1] ) . '<br />';
			}
		}
	}
	return $sys_info;
}
