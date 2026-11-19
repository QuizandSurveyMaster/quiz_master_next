<?php
/**
 * @since 7.0
 * @since 7.0.2 Removed the transient
 * @param string $name
 */
function qsm_get_widget_data( $name ) {
	$qsm_admin_dd = qsm_get_parsing_script_data();
	return isset( $qsm_admin_dd[ $name ] ) ? $qsm_admin_dd[ $name ] : array();
}

/**
 * @since 7.3.5
 * @return array $blog_data
 */
function qsm_get_blog_data_rss() {
	include_once ABSPATH . WPINC . '/feed.php';
	$blog_data_obj = fetch_feed( 'https://quizandsurveymaster.com/feed/' );
	$maxitems      = 0;
	if ( ! is_wp_error( $blog_data_obj ) ) {
		$maxitems        = $blog_data_obj->get_item_quantity( 2 );
		$blog_data_items = $blog_data_obj->get_items( 0, $maxitems );
	}
	$blog_data = array();
	foreach ( $blog_data_items as $item ) {
		$blog_data[] = array(
			'link'    => esc_url( $item->get_permalink() ),
			'title'   => esc_html( $item->get_title() ),
			'excerpt' => esc_html( $item->get_description() ),
		);
	}
	return $blog_data;
}

/**
 * @since 7.0
 * @param str $widget_id
 * Check widget is opened or closed
 */
function qsm_check_close_hidden_box( $widget_id ) {
	$current_screen = get_current_screen();
	$page_id        = $current_screen->id;
	$user           = wp_get_current_user();
	$closed_div     = get_user_option( "closedpostboxes_$page_id", $user->ID );
	if ( $closed_div && is_array( $closed_div ) ) {
		echo in_array( $widget_id, $closed_div, true ) ? 'closed' : '';
	}

	$hidden_box = get_user_option( "metaboxhidden_$page_id", $user->ID );
	if ( $hidden_box && is_array( $hidden_box ) ) {
		echo in_array( $widget_id, $hidden_box, true ) ? ' hide-if-js' : '';
	}
}

function qsm_check_plugins_compatibility() {
	global $mlwQuizMasterNext;

	if ( class_exists( 'QSM_Installer' ) ) {
		$plugin_path = WP_PLUGIN_DIR . '/qsm-installer/qsm-installer.php';
		$plugin_data = get_plugin_data( $plugin_path );

		/*
		 * The version is required to be non-empty: get_plugin_data() returns a
		 * blank one when the file is missing (the path above is hardcoded, so a
		 * renamed plugin folder lands here), and an empty string compares as
		 * older than everything.
		 *
		 * Two ways to be due, either is enough:
		 *  - below the floor release - decided from the version alone, so it
		 *    shows on the first dashboard load with no page visit needed;
		 *  - behind what the store publishes - which needs the installer's own
		 *    cached answer, filled in once the Extensions page has been opened.
		 * Without the first, an installer that reports an old version but
		 * already carries the update check stayed silent until that visit.
		 */
		if ( ! empty( $plugin_data['Version'] )
			&& ( qsm_installer_is_below_floor( $plugin_data['Version'] ) || qsm_installer_update_is_due( $plugin_data['Version'] ) ) ) {
			$account_url = esc_url( qsm_get_utm_link( 'https://quizandsurveymaster.com/account', 'dashboard', 'useful_links', 'qsm_installer_update' ) );
			?>
			<div class="qsm-dashboard-help-center qsm-dashboard-warning-container">
				<div class="qsm-dashboard-error-content">
					<h3><?php esc_html_e( 'Update Available', 'quiz-master-next' ); ?></h3>
					<p>
						<?php esc_html_e( 'We recommend downloading the latest version of the QSM Installer for a seamless quiz and survey creation experience.', 'quiz-master-next' ); ?>
						<?php
						/*
						 * Name the version they are on. Without it the notice is
						 * unactionable for support: "update the installer" does not
						 * say whether the customer already did.
						 */
						$published = qsm_installer_published_version();

						// Only name the store's version when it is actually newer.
						// The below-floor case forces this notice on by itself, so
						// the store's answer can be the very version they run (a
						// store not yet updated) - and "2.0.2 is available" to
						// somebody on 2.0.2 is wrong.
						$newer = '' !== $published && version_compare(
							qsm_normalize_version( $plugin_data['Version'] ),
							qsm_normalize_version( $published ),
							'<'
						);
						if ( $newer ) {
							printf(
								/* translators: 1: installed QSM Installer version, 2: newest version published by the store. */
								esc_html__( 'You are currently running version %1$s; version %2$s is available.', 'quiz-master-next' ),
								'<strong>' . esc_html( $plugin_data['Version'] ) . '</strong>',
								'<strong>' . esc_html( $published ) . '</strong>'
							);
						} else {
							// The store's number is not known yet: the installer is
							// too old to report it, or has not been asked - the
							// below-floor case gets here before any page visit.
							// Name what we do know.
							printf(
								/* translators: %s: QSM Installer version currently installed on this site. */
								esc_html__( 'You are currently running version %s.', 'quiz-master-next' ),
								'<strong>' . esc_html( $plugin_data['Version'] ) . '</strong>'
							);
						}
						?>
					</p>
					<a href="<?php echo esc_url( $account_url ); ?>" class="qsm-dashboard-error-btn" target="_blank">
						<?php esc_html_e( 'Get Latest QSM Installer', 'quiz-master-next' ); ?>
					</a>
				</div>
			</div>
			<?php
		}
	}

	do_action( 'qsm_admin_dashboard_compatibility_after' );
}

/**
 * Changelog card: the five latest entries of readme.txt, each with its type as a badge.
 *
 * @since 7.0
 * @since 11.4.0 Admin UI kit card.
 * @return void
 */
function qsm_dashboard_display_change_log_section() {
	global $wp_filesystem, $mlwQuizMasterNext;
	require_once ABSPATH . '/wp-admin/includes/file.php';
	WP_Filesystem();
	$change_log  = array();
	$readme_file = QSM_PLUGIN_PATH . 'readme.txt';
	if ( $wp_filesystem->exists( $readme_file ) ) {
		$file_content = $wp_filesystem->get_contents( $readme_file );
		if ( $file_content ) {
			$parts = explode( '== Changelog ==', $file_content, 2 );
			if ( isset( $parts[1] ) ) {
				preg_match_all( '/\* (.+)/', $parts[1], $matches );
				if ( ! empty( $matches[1] ) ) {
					$change_log = array_slice( $matches[1], 0, 5 );
				}
			}
		}
	}
	$badge_types = array(
		'feature'     => 'info',
		'enhancement' => 'success',
		'bug'         => 'warning',
		'fix'         => 'warning',
		'security'    => 'danger',
	);
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'   => __( "What's new", 'quiz-master-next' ),
			'actions' => QSM_Admin_UI::badge( 'v' . $mlwQuizMasterNext->version, '', false ),
			'class'   => 'qsm-db-card qsm-db-changelog',
		)
	);
	if ( $change_log ) {
		?>
		<ul class="qsm-db-changelog-list">
			<?php
			foreach ( $change_log as $single_change_log ) {
				$parts   = explode( ':', $single_change_log, 2 );
				$cl_type = isset( $parts[1] ) ? trim( $parts[0] ) : '';
				$cl_str  = isset( $parts[1] ) ? trim( $parts[1] ) : trim( $parts[0] );
				if ( '' === $cl_str ) {
					continue;
				}
				$type_key = strtolower( $cl_type );
				?>
				<li>
					<?php if ( '' !== $cl_type ) { ?>
						<?php echo QSM_Admin_UI::badge( $cl_type, isset( $badge_types[ $type_key ] ) ? $badge_types[ $type_key ] : '', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
					<?php } ?>
					<span><?php echo wp_kses_post( $cl_str ); ?></span>
				</li>
			<?php } ?>
		</ul>
		<?php
	} else {
		echo '<p class="qsm-ui-small">' . esc_html__( 'No changelog found.', 'quiz-master-next' ) . '</p>';
	}
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
}

/**
 * Help card: documentation, demos, FAQ and support as a list of links.
 *
 * @since 7.0
 * @since 11.4.0 Admin UI kit card.
 * @return void
 */
function qsm_dashboard_display_need_help_section() {
	$sections = array(
		array(
			'title'       => __( 'Documentation', 'quiz-master-next' ),
			'description' => __( 'Step-by-step guides for every feature.', 'quiz-master-next' ),
			'icon'        => 'file',
			'link'        => qsm_get_plugin_link( 'docs', 'dashboard', 'next_steps', 'dashboard_read_document' ),
		),
		array(
			'title'       => __( 'Demos', 'quiz-master-next' ),
			'description' => __( 'Live quizzes and surveys built with QSM.', 'quiz-master-next' ),
			'icon'        => 'eye',
			'link'        => qsm_get_utm_link( 'https://demo.quizandsurveymaster.com/', 'demos', 'dashboard', 'useful_links', 'dashboard_demos' ),
		),
		array(
			'title'       => __( 'FAQ', 'quiz-master-next' ),
			'description' => __( 'Quick answers on setup and troubleshooting.', 'quiz-master-next' ),
			'icon'        => 'info',
			'link'        => 'https://quizandsurveymaster.com/#:~:text=Frequently%20asked%20questions',
		),
		array(
			'title'       => __( 'Contact Support', 'quiz-master-next' ),
			'description' => __( 'Get personal help from our support team.', 'quiz-master-next' ),
			'icon'        => 'mail',
			'link'        => qsm_get_plugin_link( 'contact-support', 'dashboard', 'useful_links', 'dashboard_support' ),
		),
	);
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title' => __( 'Need help?', 'quiz-master-next' ),
			'class' => 'qsm-db-card qsm-db-help',
		)
	);
	?>
	<div class="qsm-db-links">
		<?php foreach ( $sections as $section ) { ?>
			<a class="qsm-db-link" target="_blank" rel="noopener" href="<?php echo esc_url( $section['link'] ); ?>">
				<span class="qsm-db-link-icon"><?php echo QSM_Admin_UI::icon( $section['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
				<span class="qsm-db-link-text">
					<strong><?php echo esc_html( $section['title'] ); ?></strong>
					<span><?php echo esc_html( $section['description'] ); ?></span>
				</span>
				<?php echo QSM_Admin_UI::icon( 'ext', 'qsm-ui-icon-sm qsm-db-link-ext' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
			</a>
		<?php } ?>
	</div>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
}

/**
 * Banner image for a theme or add-on: the same 16:9 image the QSM Installer's Extensions page
 * shows (all_addons[].img), falling back to the item's own image.
 *
 * @since 11.4.0
 * @param array $item       Theme or add-on from the parsing script data.
 * @param array $all_addons all_addons from the parsing script data.
 * @return string Image URL, or '' when there is none.
 */
function qsm_dashboard_banner_image( $item, $all_addons ) {
	foreach ( (array) $all_addons as $addon ) {
		if ( isset( $addon['id'] ) && intval( $addon['id'] ) === intval( $item['id'] ) && ! empty( $addon['img'] ) ) {
			return $addon['img'];
		}
	}
	return ! empty( $item['img'] ) ? $item['img'] : '';
}

/**
 * Popular add-ons card.
 *
 * @since 7.0
 * @since 11.4.0 Admin UI kit card.
 * @param array $popular_addons Add-ons from the parsing script data.
 * @param array $all_addons     all_addons from the parsing script data, for the banner images.
 * @return void
 */
function qsm_dashboard_display_popular_addon_section( $popular_addons, $all_addons = array() ) {
	$desired_order = array( 572582, 591230, 567900, 3437 );
	$sorted_addons = array();
	foreach ( $desired_order as $id ) {
		foreach ( $popular_addons as $addon ) {
			if ( intval( $addon['id'] ) === $id ) {
				$sorted_addons[] = $addon;
			}
		}
	}
	if ( empty( $sorted_addons ) ) {
		return;
	}
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'Explore add-ons', 'quiz-master-next' ),
			'description' => __( 'Popular add-ons that extend your quizzes and surveys.', 'quiz-master-next' ),
			'actions'     => QSM_Admin_UI::button(
				__( 'All add-ons', 'quiz-master-next' ),
				array(
					'size'    => 'sm',
					'variant' => 'quiet',
					'href'    => admin_url( 'admin.php?page=qmn_addons' ),
				)
			),
			'class'       => 'qsm-db-card qsm-db-addons',
		)
	);
	?>
	<div class="qsm-db-addon-grid">
		<?php
		foreach ( array_slice( $sorted_addons, 0, 4 ) as $addon ) {
			$addon_link = qsm_get_utm_link( $addon['link'], 'addon_setting', 'popular_addon', 'addon-settings_' . sanitize_title( $addon['name'] ) );
			$addon_image = qsm_dashboard_banner_image( $addon, $all_addons );
			$addon_desc  = mb_strlen( $addon['description'] ) > 90 ? mb_substr( $addon['description'], 0, 90 ) . '…' : $addon['description'];
			?>
			<a class="qsm-db-tile qsm-db-addon" target="_blank" rel="noopener" href="<?php echo esc_url( $addon_link ); ?>">
				<span class="qsm-db-tile-image"><img src="<?php echo esc_url( $addon_image ? $addon_image : QSM_PLUGIN_URL . 'assets/placeholder.png' ); ?>" alt="<?php echo esc_attr( $addon['name'] ); ?>" loading="lazy"></span>
				<span class="qsm-db-tile-meta">
					<span class="qsm-db-tile-text">
						<strong><?php echo esc_html( $addon['name'] ); ?></strong>
						<span><?php echo esc_html( $addon_desc ); ?></span>
					</span>
					<span class="qsm-db-tile-cta"><?php esc_html_e( 'Learn more', 'quiz-master-next' ); ?><?php echo QSM_Admin_UI::icon( 'ext', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
				</span>
			</a>
		<?php } ?>
	</div>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
}

/**
 * Popular themes card.
 *
 * @since 7.0
 * @since 11.4.0 Admin UI kit card.
 * @param array $themes     Themes from the parsing script data.
 * @param array $all_addons all_addons from the parsing script data, for the banner images.
 * @return void
 */
function qsm_dashboard_display_popular_theme_section( $themes, $all_addons = array() ) {
	$desired_order = array( 547794, 557086, 551027, 302299 );
	$sorted_themes = array();
	foreach ( $desired_order as $id ) {
		foreach ( $themes as $theme ) {
			if ( intval( $theme['id'] ) === $id ) {
				$sorted_themes[] = $theme;
			}
		}
	}
	if ( empty( $sorted_themes ) ) {
		return;
	}
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'Popular themes', 'quiz-master-next' ),
			'description' => __( 'Give your quizzes a ready-made look. Open a demo to see one live.', 'quiz-master-next' ),
			'class'       => 'qsm-db-card qsm-db-themes',
		)
	);
	?>
	<div class="qsm-db-theme-grid">
		<?php
		foreach ( $sorted_themes as $single_theme ) {
			$theme_demo = qsm_get_utm_link( $single_theme['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title( $single_theme['name'] ) );
			?>
			<a class="qsm-db-tile qsm-db-theme" target="_blank" rel="noopener" href="<?php echo esc_url( $theme_demo ); ?>">
				<span class="qsm-db-tile-image"><img src="<?php echo esc_url( qsm_dashboard_banner_image( $single_theme, $all_addons ) ); ?>" alt="<?php echo esc_attr( $single_theme['name'] ); ?>" loading="lazy"></span>
				<span class="qsm-db-tile-meta">
					<strong><?php echo esc_html( $single_theme['name'] ); ?></strong>
					<span class="qsm-db-tile-cta"><?php esc_html_e( 'Demo', 'quiz-master-next' ); ?><?php echo QSM_Admin_UI::icon( 'ext', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
				</span>
			</a>
		<?php } ?>
	</div>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
}

/**
 * Recent activity card: the two latest results. Hidden when there are no results yet.
 *
 * @since 10.2.7
 * @since 11.4.0 Admin UI kit card.
 * @return void
 */
function qsm_dashboard_recent_taken_quiz() {
	global $wpdb, $mlwQuizMasterNext;
	$total_results = (int) $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0" );
	if ( 0 === $total_results ) {
		return;
	}
	$can_view = current_user_can( 'view_qsm_quiz_result' ) || current_user_can( 'administrator' );
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'   => __( 'Recent activity', 'quiz-master-next' ),
			'actions' => QSM_Admin_UI::button(
				/* translators: %s: total number of results. */
				sprintf( __( 'See all results (%s)', 'quiz-master-next' ), number_format_i18n( $total_results ) ),
				array(
					'size'    => 'sm',
					'variant' => 'quiet',
					'href'    => admin_url( 'admin.php?page=mlw_quiz_results' ),
				)
			),
			'class'   => 'qsm-db-card qsm-db-activity',
		)
	);
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted=0 ORDER BY result_id DESC LIMIT 3", ARRAY_A );
	?>
	<ul class="qsm-db-activity-list">
		<?php
		foreach ( $results as $single_result_arr ) {
			$user_id = isset( $single_result_arr['user'] ) ? intval( $single_result_arr['user'] ) : 0;
			$avatar  = $user_id ? get_avatar_url( $user_id ) : QSM_PLUGIN_URL . 'assets/default_image.png';

			// Score.
			$score     = '';
			$form_type = isset( $single_result_arr['form_type'] ) ? intval( $single_result_arr['form_type'] ) : 0;
			if ( 1 === $form_type || 2 === $form_type ) {
				$score = __( 'Not graded', 'quiz-master-next' );
			} else {
				$system = intval( $single_result_arr['quiz_system'] );
				if ( 0 === $system || 3 === $system ) {
					/* translators: 1: correct answers, 2: total questions, 3: percentage. */
					$score = sprintf( __( '%1$s of %2$s correct (%3$s%%)', 'quiz-master-next' ), $single_result_arr['correct'], $single_result_arr['total'], $single_result_arr['correct_score'] );
				}
				if ( 1 === $system || 3 === $system ) {
					/* translators: %s: points scored. */
					$points = sprintf( __( '%s points', 'quiz-master-next' ), $single_result_arr['point_score'] );
					$score  = $score ? $score . ' · ' . $points : $points;
				}
			}

			// Time taken.
			$complete_time = '';
			if ( $mlwQuizMasterNext->pluginHelper->is_new_format_result( $single_result_arr ) ) {
				$results_array = $mlwQuizMasterNext->pluginHelper->get_formated_result_data( $single_result_arr['result_id'] );
			} else {
				$results_array = maybe_unserialize( $single_result_arr['quiz_results'] );
			}
			if ( is_array( $results_array ) && isset( $results_array[0] ) ) {
				$seconds = intval( $results_array[0] );
				$hours   = floor( $seconds / 3600 );
				$minutes = floor( ( $seconds % 3600 ) / 60 );
				$parts   = array();
				if ( $hours > 0 ) {
					/* translators: %d: hours. */
					$parts[] = sprintf( __( '%dh', 'quiz-master-next' ), $hours );
				}
				if ( $minutes > 0 ) {
					/* translators: %d: minutes. */
					$parts[] = sprintf( __( '%dm', 'quiz-master-next' ), $minutes );
				}
				/* translators: %d: seconds. */
				$parts[]       = sprintf( __( '%ds', 'quiz-master-next' ), $seconds % 60 );
				$complete_time = implode( ' ', $parts );
			}
			?>
			<li class="qsm-db-activity-item">
				<img src="<?php echo esc_url( $avatar ); ?>" class="qsm-db-avatar" alt="">
				<div class="qsm-db-activity-main">
					<div class="qsm-db-activity-title">
						<?php
						if ( $user_id ) {
							$actual_user = get_userdata( $user_id );
							$user_name   = ( 'None' === $single_result_arr['name'] && $actual_user ) ? $actual_user->display_name : $single_result_arr['name'];
							echo '<a href="' . esc_url( get_edit_profile_url( $user_id ) ) . '">' . esc_html( $user_name ) . '</a>';
						} else {
							echo '<strong>' . esc_html__( 'Guest', 'quiz-master-next' ) . '</strong>';
						}
						echo ' ' . esc_html__( 'took', 'quiz-master-next' ) . ' ';
						echo '<a href="' . esc_url( admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . intval( $single_result_arr['quiz_id'] ) ) ) . '">' . esc_html( $single_result_arr['quiz_name'] ) . '</a>';
						?>
					</div>
					<div class="qsm-db-activity-meta">
						<?php if ( $score ) { ?>
							<span><?php echo esc_html( $score ); ?></span>
						<?php } ?>
						<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $single_result_arr['time_taken'] ) ) ); ?></span>
						<?php if ( $complete_time ) { ?>
							<?php /* translators: %s: time spent on the quiz, e.g. 2m 10s. */ ?>
							<span><?php echo esc_html( sprintf( __( 'Took %s', 'quiz-master-next' ), $complete_time ) ); ?></span>
						<?php } ?>
					</div>
				</div>
				<?php if ( $can_view ) { ?>
					<p class="row-actions-c qsm-db-activity-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=qsm_quiz_result_details&result_id=' . intval( $single_result_arr['result_id'] ) ) ); ?>"><?php esc_html_e( 'View', 'quiz-master-next' ); ?></a>
						<?php if ( current_user_can( 'administrator' ) ) { ?>
							<a href="javascript:void(0)" data-result_id="<?php echo esc_attr( $single_result_arr['result_id'] ); ?>" class="trash rtq-delete-result"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></a>
						<?php } ?>
					</p>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
}

/**
 * Dashboard header, styled like the quiz edit frame header: logo, version, a Create
 * button, add-on links (qsm_welcome_panel_links) and the Help menu.
 *
 * @since 11.4.0
 * @return void
 */
function qsm_dashboard_render_header() {
	global $mlwQuizMasterNext;
	?>
	<div class="qsm-ui qsm-ui-quiz-frame-header qsm-db-header">
		<div class="qsm-ui-page-header qsm-ui-text">
			<div class="qsm-ui-quiz-frame-heading qsm-db-heading">
				<img class="qsm-db-logo" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/logo-blue.svg' ); ?>" alt="<?php esc_attr_e( 'Quiz and Survey Master', 'quiz-master-next' ); ?>">
				<span class="qsm-db-heading-sep" aria-hidden="true"></span>
				<h1 class="qsm-ui-h2 qsm-db-title"><?php esc_html_e( 'Dashboard', 'quiz-master-next' ); ?></h1>
				<?php echo QSM_Admin_UI::badge( 'v' . $mlwQuizMasterNext->version, '', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
			</div>
			<div class="qsm-ui-page-actions">
				<ul class="qsm-db-header-links"><?php do_action( 'qsm_welcome_panel_links' ); ?></ul>
				<?php
				echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
					__( 'Create quiz', 'quiz-master-next' ),
					array(
						'size'    => 'sm',
						'variant' => 'primary',
						'icon'    => 'plus',
						'href'    => admin_url( 'admin.php?page=qsm_create_quiz_page' ),
					)
				);
				?>
				<div class="qsm-ui-quiz-frame-tools">
					<?php qsm_admin_render_help_menu( 'dashboard' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * @since 7.0
 * @since 11.4.0 Admin UI kit layout: full-width header, fluid two-column card grid.
 * @return void Dashboard for QSM.
 */
function qsm_generate_dashboard_page() {
	// Only let admins and editors see this page.
	if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
		return;
	}
	$qsm_admin_dd = qsm_get_parsing_script_data();
	?>
<div class="wrap qsm-db-wrap">
	<?php qsm_dashboard_render_header(); ?>
	<div class="qsm-ui qsm-ui-text qsm-db">
		<?php
		if ( $qsm_admin_dd ) {
			qsm_check_plugins_compatibility();
		} else {
			qsm_display_fullscreen_error();
		}
		qsm_display_migration_tools_redirect_button();
		?>
		<div class="qsm-db-grid">
			<?php
			echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'title'       => __( 'Create a quiz or survey', 'quiz-master-next' ),
					'description' => __( 'Start from scratch, or import a ready-made quiz or survey in one click.', 'quiz-master-next' ),
					'actions'     => QSM_Admin_UI::badge( __( 'New: Templates', 'quiz-master-next' ), 'info', false ),
					'class'       => 'qsm-db-card qsm-db-create',
				)
			);
			qsm_create_quiz_render_choice_cards();
			echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.

			if ( $qsm_admin_dd ) {
				$all_addons = isset( $qsm_admin_dd['all_addons'] ) ? $qsm_admin_dd['all_addons'] : array();
				qsm_dashboard_display_popular_theme_section( isset( $qsm_admin_dd['themes'] ) ? $qsm_admin_dd['themes'] : array(), $all_addons );
				qsm_dashboard_display_popular_addon_section( isset( $qsm_admin_dd['popular_products'] ) ? $qsm_admin_dd['popular_products'] : array(), $all_addons );
			}
			qsm_dashboard_recent_taken_quiz();
			qsm_dashboard_display_need_help_section();
			qsm_dashboard_display_change_log_section();
			?>
			<ul class="qsm-db-community">
				<li><a target="_blank" rel="noopener" href="https://github.com/QuizandSurveyMaster/quiz_master_next"><?php esc_html_e( 'GitHub', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="https://www.facebook.com/groups/516958552587745"><?php esc_html_e( 'Facebook group', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link( 'https://next.expresstech.io/qsm', 'dashboard', 'next_steps', 'dashboard_roadmap' ) ); ?>"><?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?></a></li>
			</ul>
		</div>
	</div>
</div>
	<?php
}
/**
 * @since 7.0
 * @param Object $upgrader_object
 * @param Array  $options
 * Reset the transient on QSM plugin update
 */
function qsm_reset_transient_dashboard( $upgrader_object, $options ) {
	$current_plugin_path_name = QSM_PLUGIN_BASENAME;
	if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
		foreach ( $options['plugins'] as $each_plugin ) {
			if ( $each_plugin === $current_plugin_path_name ) {
				delete_transient( 'qsm_admin_dashboard_data' );
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'qsm_reset_transient_dashboard', 10, 2 );

/**
 * @since 7.0
 * @param str $widget_id
 * Generate posts
 */
function qsm_dashboard_roadmap( $widget_id ) {
	?>
<div id="<?php echo esc_attr( $widget_id ); ?>" class="postbox <?php qsm_check_close_hidden_box( $widget_id ); ?>">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text"><?php esc_html_e( "Toggle panel: What's Next", 'quiz-master-next' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span>
	</button>
	<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( "What's Next", 'quiz-master-next' ); ?></span></h2>
	<div class="inside">
		<div class="main">
			<ul class="what-new-ul">
				<li>
					<a href="https://app.productstash.io/qsm#/roadmap"
						target="_blank" rel="noopener"> <?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( "Visit out public Roadmap to checkout what's in the development pipepline of QSM.", 'quiz-master-next' ); ?>
					</div>
				</li>
				<li>
					<a href="https://app.productstash.io/qsm#/updates"
						target="_blank" rel="noopener"><?php esc_html_e( 'Recent Updates', 'quiz-master-next' ); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( 'Checkout our updates page to know more about our recent releases', 'quiz-master-next' ); ?>
					</div>
				</li>
				<li>
					<a href="https://app.productstash.io/qsm#/ideas"
						target="_blank" rel="noopener"><?php esc_html_e( 'Submit your ideas', 'quiz-master-next' ); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( 'We are open your suggestions on how to improve QSM. Please visit our ideas page to share your thoughts.', 'quiz-master-next' ); ?>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>
	<?php
}

/**
 * @since 7.0
 * Create new quiz and redirect to newly created quiz
 */
function qsm_create_new_quiz_from_wizard() {
	// Create new quiz.
	if ( isset( $_POST['qsm_new_quiz_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_new_quiz_nonce'] ) ), 'qsm_new_quiz' ) ) {
		global $mlwQuizMasterNext;
		$quiz_name = isset( $_POST['quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_name'] ) ) : '';
		$quiz_name = htmlspecialchars( $quiz_name, ENT_QUOTES );
		$theme_id  = isset( $_POST['quiz_theme_id'] ) ? intval( $_POST['quiz_theme_id'] ) : 0;
		unset( $_POST['qsm_new_quiz_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		unset( $_POST['quiz_theme_id'] );
		/**
		 * Prepare Quiz Options.
		 */
		$quiz_options    = array(
			'quiz_name'              => $quiz_name,
			'quiz_featured_image'    => isset( $_POST['quiz_featured_image'] ) ? esc_url_raw( wp_unslash( $_POST['quiz_featured_image'] ) ) : '',
			'form_type'              => isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : 0,
			'system'                 => isset( $_POST['system'] ) ? sanitize_text_field( wp_unslash( $_POST['system'] ) ) : 3,
			'timer_limit'            => ! empty( $_POST['timer_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['timer_limit'] ) ) : 0,
			'pagination'             => ! empty( $_POST['pagination'] ) ? intval( $_POST['pagination'] ) : 0,
			'enable_pagination_quiz' => isset( $_POST['enable_pagination_quiz'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_pagination_quiz'] ) ) : 0,
			'progress_bar'           => isset( $_POST['progress_bar'] ) ? sanitize_text_field( wp_unslash( $_POST['progress_bar'] ) ) : 0,
			'require_log_in'         => ! empty( $_POST['require_log_in'] ) ? sanitize_text_field( wp_unslash( $_POST['require_log_in'] ) ) : 0,
			'disable_first_page'     => isset( $_POST['disable_first_page'] ) ? sanitize_text_field( wp_unslash( $_POST['disable_first_page'] ) ) : 0,
			'comment_section'        => isset( $_POST['comment_section'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_section'] ) ) : 1,
			'contact_info_location'  => isset( $_POST['enable_contact_form'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_contact_form'] ) ) : 0,
		);
		$get_saved_value = QMNGlobalSettingsPage::get_global_quiz_settings();
		if ( ! empty( $get_saved_value ) && is_array( $get_saved_value ) ) {
			$quiz_options = array_replace( $get_saved_value, $quiz_options );
		}
		/**
		 * Prepare Contact Fields
		 */
		$contact_form = array();
		if ( isset( $_POST['enable_contact_form'] ) && 1 == sanitize_text_field( wp_unslash( $_POST['enable_contact_form'] ) ) ) {
			$cf_fields = QSM_Contact_Manager::default_fields();
			if ( isset( $cf_fields['name'] ) ) {
				$cf_fields['name']['enable'] = 'true';
				$contact_form[]              = $cf_fields['name'];
			}
			if ( isset( $cf_fields['email'] ) ) {
				$cf_fields['email']['enable'] = 'true';
				$contact_form[]               = $cf_fields['email'];
			}
		}
		/**
		 * Prepare Quiz Options
		 */
		$quiz_options = apply_filters( 'qsm_quiz_wizard_settings_option_save', $quiz_options );
		$mlwQuizMasterNext->quizCreator->create_quiz(
			$quiz_name,
			$theme_id,
			array(
				'quiz_options' => $quiz_options,
				'contact_form' => $contact_form,
			)
		);
	}
}

add_action( 'admin_init', 'qsm_create_new_quiz_from_wizard' );

/**
 * Displays a redirect button to the migration tools page on the dashboard.
 *
 * This function outputs a styled section on the dashboard that encourages users
 * to perform a database migration. It includes a heading, a brief description,
 * and a button that links to the migration tools page.
 *
 * @since 11.0.0
 * @return void
 */
function qsm_display_migration_tools_redirect_button() {
	// Only show this section if the migration has not been completed.
	if ( 1 == get_option( 'qsm_migration_results_processed' ) ) {
		return;
	}
	?>
	<div class="qsm-dashboard-migration-section qsm-dashboard-page-common-style">
		<div class="qsm-dashboard-page-header">
			<h3 class="qsm-dashboard-card-title"><?php esc_html_e( 'Database Migration', 'quiz-master-next' ); ?></h3>
		</div>
		<div class="qsm-db-migration-container">
			<div class="qsm-migration-notice qsm-migration-info">
				<div class="qsm-migration-notice-header">
					<strong><?php esc_html_e( 'You’ve updated to QSM 11', 'quiz-master-next' ); ?></strong>
				</div>
				<p><?php esc_html_e( 'Complete a one-time database migration to ensure your quizzes and results work smoothly with the new version and its improved rendering experience.', 'quiz-master-next' ); ?></p>
			</div>
			<div class="qsm-migration-notice qsm-migration-warning">
				<div class="qsm-migration-notice-header">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM11 15H9V13H11V15ZM11 11H9V5H11V11Z" fill="#F59E0B"/>
					</svg>
					<strong><?php esc_html_e( 'Important', 'quiz-master-next' ); ?></strong>
				</div>
				<p><?php esc_html_e( 'After migration, new quiz results will not be compatible with older versions of QSM. If you downgrade later, these results may not be accessible.', 'quiz-master-next' ); ?></p>
			</div>
			<div class="qsm-migration-action">
				<a class="button button-primary qsm-dashboard-section-migration" href="<?php echo esc_url( admin_url( 'admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_migration' ) ); ?>">
					<?php esc_html_e( 'Go To Migration', 'quiz-master-next' ); ?>
				</a>
				<p class="qsm-migration-note"><?php esc_html_e( 'Your data will remain safe during migration', 'quiz-master-next' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}