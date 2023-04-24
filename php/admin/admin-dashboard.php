<?php
/**
 * @since 7.0
 * @since 7.0.2 Removed the transient
 * @param string $name
 */
function qsm_get_widget_data( $name ) {
	$qsm_admin_dd = wp_remote_get( QSM_PLUGIN_URL . 'data/parsing_script.json', [ 'sslverify' => false ] );
	$qsm_admin_dd = json_decode( wp_remote_retrieve_body( $qsm_admin_dd ), true );
	return isset( $qsm_admin_dd[ $name ] ) ? $qsm_admin_dd[ $name ] : array();
}

/**
 * @since 7.3.5
 * @return array $blog_data
 */
function qsm_get_blog_data_rss(){
	include_once( ABSPATH . WPINC . '/feed.php' );
	$blog_data_obj = fetch_feed( 'https://quizandsurveymaster.com/feed/' );
	$maxitems = 0;
	if ( ! is_wp_error( $blog_data_obj ) ) {
		$maxitems = $blog_data_obj->get_item_quantity( 2 );
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

/**
 * @since 7.0
 * @param str $status
 * @param obj $args
 * @return Create dashboard screen
 */
function qsm_dashboard_screen_options( $status, $args ) {
	$screen = get_current_screen();
	if ( is_object( $screen ) && 'toplevel_page_qsm_dashboard' === trim( $screen->id ) ) {
		ob_start();
		$page_id = $screen->id;
		$user    = wp_get_current_user();
		?>
<form id="adv-settings" method="post">
	<fieldset class="metabox-prefs">
		<legend>Boxes</legend>
		<?php
		$hidden_box                          = get_user_option( "metaboxhidden_$page_id", $user->ID );
		$hidden_box_arr                      = ! empty( $hidden_box ) ? $hidden_box : array();
		$registered_widget                   = get_option( 'qsm_dashboard_widget_arr', array() );
		$registered_widget['welcome_panel']  = array(
			'title' => __( 'Welcome', 'quiz-master-next' ),
		);
		if ( $registered_widget ) {
			foreach ( $registered_widget as $key => $value ) {
				?>
				<label for="<?php echo esc_attr( $key ); ?>-hide">
					<input class="hide-postbox-tog" name="<?php echo esc_attr( $key ); ?>-hide" type="checkbox" id="<?php echo esc_attr( $key ); ?>-hide" value="<?php echo esc_attr( $key ); ?>" <?php echo ( ! in_array( $key, $hidden_box_arr, true ) ) ? 'checked="checked"' : ''; ?>>
					<?php echo wp_kses_post( $value['title'] ); ?>
				</label>
				<?php
			}
		}
		?>
	</fieldset>
	<?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false, false ); ?>
</form>
<?php
		return ob_get_clean();
	}
	return $status;
}

/**
 * @since 7.0
 * @return HTMl Dashboard for QSM
 */
function qsm_generate_dashboard_page() {
	// Only let admins and editors see this page.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	global $mlwQuizMasterNext;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'QSM Dashboard', 'quiz-master-next' ); ?></h1>
	<div id="welcome_panel" class="qsm_dashboard_page postbox welcome-panel <?php qsm_check_close_hidden_box( 'welcome_panel' ); ?>">
		<div class="qsm-welcome-panel-close">
			<img src="<?php echo esc_url( QSM_PLUGIN_URL . '/assets/icon-128x128.png' ); ?>" alt="Welcome Logo">
			<p class="current_version"><?php echo esc_attr( $mlwQuizMasterNext->version ); ?></p>
		</div>
		<a class="qsm-welcome-panel-dismiss" href="javascript:void(0)"
			aria-label="Dismiss the welcome panel"><?php esc_html_e( 'Dismiss', 'quiz-master-next' ); ?></a>
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'Welcome to Quiz And Survey Master!', 'quiz-master-next' ); ?></h2>
			<p class="about-description"><?php esc_html_e( 'Formerly Quiz Master Next', 'quiz-master-next' ); ?></p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h3><?php esc_html_e( 'Get Started', 'quiz-master-next' ); ?></h3>
					<?php
					$classname = "";
				    $classname = apply_filters( 'qsm_create_quiz_after', $classname);
					$classes = explode(",",$classname);
					$classesname1 = "";
					if ( isset($classes[0]) ) {
						$classesname1 = $classes[0];
					}
					$classesname2 = "";
					if ( isset($classes[1]) ) {
						$classesname2 = $classes[1];
					}
					?>
						<div class="<?php echo esc_attr( $classesname1 );?>"><a class="button button-primary button-hero load-quiz-wizard hide-if-no-customize <?php echo esc_attr( $classesname2 );?>"
						href="javascript:void(0)"><?php esc_html_e( 'Create New Quiz/Survey', 'quiz-master-next' )?></a></div>
					<p class="hide-if-no-customize">
						or, <a href="edit.php?post_type=qsm_quiz"><?php esc_html_e( 'View all quizzes', 'quiz-master-next' )?></a>
					</p>
				</div>
				<div class="welcome-panel-column">
					<h3><?php esc_html_e( 'Next Steps', 'quiz-master-next' ); ?></h3>
					<ul>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('docs', 'dashboard', 'next_steps', 'dashboard_read_document') )?>" class="welcome-icon">
								<span class="dashicons dashicons-media-document"></span>&nbsp;&nbsp;<?php esc_html_e( 'Read Documentation', 'quiz-master-next' ); ?>
							</a>
						</li>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://demo.quizandsurveymaster.com', 'dashboard', 'next_steps', 'dashboard_see_demo') )?>" class="welcome-icon">
								<span class="dashicons dashicons-format-video"></span>&nbsp;&nbsp;<?php esc_html_e( 'See demos', 'quiz-master-next' ); ?>
							</a>
						</li>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('pricing', 'dashboard', 'next_steps', 'dashboard_pricing') )?>" class="welcome-icon">
								<span class="dashicons dashicons-plugins-checked"></span>&nbsp;&nbsp;<?php esc_html_e( 'Extend QSM with PRO Addons', 'quiz-master-next' ); ?>
							</a>
						</li>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://themes.quizandsurveymaster.com', 'dashboard', 'next_steps', 'dashboard_themes') )?>" class="welcome-icon">
								<span class="dashicons dashicons-admin-appearance"></span>&nbsp;&nbsp;<?php esc_html_e( 'Explore QSM Themes', 'quiz-master-next' ); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h3><?php esc_html_e( 'Useful Links', 'quiz-master-next' ); ?></h3>
					<ul>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('contact-support', 'dashboard', 'useful_links', 'dashboard_support') )?>" class="welcome-icon"><span class="dashicons dashicons-admin-users"></span>&nbsp;&nbsp;<?php esc_html_e( 'Contact Support', 'quiz-master-next' ); ?></a></li>
						<li><a target="_blank" rel="noopener" href="https://github.com/QuizandSurveyMaster/quiz_master_next" class="welcome-icon"><span class="dashicons dashicons-editor-code"></span>&nbsp;&nbsp;<?php esc_html_e( 'Github Repository', 'quiz-master-next' ); ?></a></li>
						<li><a target="_blank" rel="noopener" href="https://www.facebook.com/groups/516958552587745" class="welcome-icon"><span class="dashicons dashicons-facebook"></span>&nbsp;&nbsp;<?php esc_html_e( 'Connect on Facebook', 'quiz-master-next' ); ?></a></li>
						<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://next.expresstech.io/qsm', 'dashboard', 'next_steps', 'dashboard_roadmap') )?>" class="welcome-icon"><span class="dashicons dashicons-feedback"></span>&nbsp;&nbsp;<?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php do_action( 'qsm_welcome_panel' ); ?>
	</div>
	<?php
		$qsm_dashboard_widget = array(
			'dashboard_popular_addon'     => array(
				'sidebar'  => 'normal',
				'callback' => 'qsm_dashboard_popular_addon',
				'title'    => 'Popular Addons',
			),
			'dashboard_recent_taken_quiz' => array(
				'sidebar'  => 'normal',
				'callback' => 'qsm_dashboard_recent_taken_quiz',
				'title'    => 'Recent Taken Quiz',
			),
			'dashboard_chagelog'          => array(
				'sidebar'  => 'side',
				'callback' => 'qsm_dashboard_chagelog',
				'title'    => 'Changelog',
			),
			'dashboard_latest_blogs'      => array(
				'sidebar'  => 'normal',
				'callback' => 'qsm_dashboard_latest_blogs',
				'title'    => 'Latest Blogs',
			),
			'dashboard_roadmap'           => array(
				'sidebar'  => 'side',
				'callback' => 'qsm_dashboard_roadmap',
				'title'    => 'roadmap',
			),

		);
		$qsm_dashboard_widget = apply_filters( 'qsm_dashboard_widget', $qsm_dashboard_widget );
		update_option( 'qsm_dashboard_widget_arr', $qsm_dashboard_widget );

		// Get the metabox positions
		$current_screen = get_current_screen();
		$page_id        = $current_screen->id;
		$user           = wp_get_current_user();
		$box_positions  = get_user_option( "meta-box-order_$page_id", $user->ID );
		?>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<?php
						$normal_widgets = $side_widgets = array();
					if ( $box_positions && is_array( $box_positions ) && isset( $box_positions['normal'] ) && '' !== $box_positions['normal'] ) {
						$normal_widgets = explode( ',', $box_positions['normal'] );
						foreach ( $normal_widgets as $value ) {
							if ( isset( $qsm_dashboard_widget[ $value ] ) ) {
								call_user_func( $qsm_dashboard_widget[ $value ]['callback'], $value );
							}
						}
					}
					if ( $box_positions && is_array( $box_positions ) && isset( $box_positions['side'] ) && '' !== $box_positions['side'] ) {
						$side_widgets = explode( ',', $box_positions['side'] );
					}
						$all_widgets = array_merge( $normal_widgets, $side_widgets );
					if ( $qsm_dashboard_widget ) {
						foreach ( $qsm_dashboard_widget as $widgte_id => $normal_widget ) {
							if ( ! in_array( $widgte_id, $all_widgets, true ) && 'normal' === $normal_widget['sidebar'] ) {
								call_user_func( $normal_widget['callback'], $widgte_id );
							}
						}
					}
					?>
				</div>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php
						$normal_widgets = array();
					if ( $box_positions && is_array( $box_positions ) && isset( $box_positions['side'] ) && '' !== $box_positions['side'] ) {
						$normal_widgets = explode( ',', $box_positions['side'] );
						foreach ( $normal_widgets as $value ) {
							if ( isset( $qsm_dashboard_widget[ $value ] ) ) {
								call_user_func( $qsm_dashboard_widget[ $value ]['callback'], $value );
							}
						}
					}
					if ( $qsm_dashboard_widget ) {
						foreach ( $qsm_dashboard_widget as $widgte_id => $normal_widget ) {
							if ( ! in_array( $widgte_id, $all_widgets, true ) && 'side' === $normal_widget['sidebar'] ) {
								call_user_func( $normal_widget['callback'], $widgte_id );
							}
						}
					}
					?>
				</div>
			</div>
		</div>
		<?php
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		?>
	</div><!-- dashboard-widgets-wrap -->
</div>
<?php
	/**
	 * Popup for new wizard
	 */
	qsm_create_new_quiz_wizard();
}

/**
 * @since 7.0
 * @global Obj $mlwQuizMasterNext
 * Generate the post settings and required plugin in popup
 */
function qsm_wizard_template_quiz_options() {
	global $mlwQuizMasterNext;
	$settings              = isset( $_POST['settings'] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST['settings'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$addons                = isset( $_POST['addons'] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST['addons'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$all_settings          = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
	$recommended_addon_str = '';
	if ( $settings ) {
		foreach ( $settings as $key => $single_setting ) {
			$key              = array_search( $key, array_column( $all_settings, 'id' ), true );
			$field            = $all_settings[ $key ];
			$field['label']   = $single_setting['option_name'];
			$field['default'] = $single_setting['value'];
			QSM_Fields::generate_field( $field, $single_setting['value'] );
		}
	} else {
		esc_html_e( 'No settings are found!', 'quiz-master-next' );
	}
	echo '=====';
	if ( $addons ) {
		$recommended_addon_str .= '<ul>';
		foreach ( $addons as $single_addon ) {
			$recommended_addon_str .= '<li>';
			if ( isset( $single_addon['attribute'] ) && '' !== $single_addon['attribute'] ) {
				$attr                   = $single_addon['attribute'];
				$recommended_addon_str .= '<span class="ra-attr qra-att-' . $attr . '">' . $attr . '</span>';
			}
			$link                   = isset( $single_addon['link'] ) ? $single_addon['link'] : '';
			$recommended_addon_str .= '<a target="_blank" href="' . $link . '">';
			if ( isset( $single_addon['img'] ) && '' !== $single_addon['img'] ) {
				$img                    = $single_addon['img'];
				$recommended_addon_str .= '<img src="' . $img . '"/>';
			}
			$recommended_addon_str .= '</a>';
			$recommended_addon_str .= '</li>';
		}
		$recommended_addon_str .= '</ul>';
	} else {
		$recommended_addon_str .= __( 'No addons are found!', 'quiz-master-next' );
	}
	echo wp_kses_post( $recommended_addon_str );
	exit;
}
add_action( 'wp_ajax_qsm_wizard_template_quiz_options', 'qsm_wizard_template_quiz_options' );

/**
 * @since 7.0
 * @param str $widget_id
 * Generate popular addon
 */
function qsm_dashboard_popular_addon( $widget_id ) {
	$all_addons = qsm_get_widget_data( 'all_addons' );
	?>
<div id="<?php echo esc_attr( $widget_id ); ?>" class="postbox <?php qsm_check_close_hidden_box( $widget_id ); ?>">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel', 'quiz-master-next' ); ?>:
			<?php esc_html_e( 'Most Popular Extensions', 'quiz-master-next' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span>
	</button>
	<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Most Popular Extensions', 'quiz-master-next' ); ?></span>
	</h2>
	<div class="inside">
		<div class="main">
			<ul class="popuar-addon-ul">
				<?php
				if ( $all_addons ) {
					foreach ( $all_addons as $key => $single_arr ) {
						if ( in_array( $single_arr['name'], array( "Export & Import", "Reporting & Analysis", "Export Results", "Advanced Question Types" ), true ) ) {
							?>
							<li>
								<a href="<?php echo esc_url( qsm_get_utm_link( $single_arr['link'], 'dashboard', 'all_addon', sanitize_title( $single_arr['name'] ) ) ); ?>" target="_blank" rel="noopener">
									<img src="<?php echo esc_url( $single_arr['img'] ); ?>" title="<?php echo esc_attr( $single_arr['name'] ); ?>" alt="<?php echo esc_attr( $single_arr['name'] ); ?>" >
								</a>
							</li>
							<?php
						}
					}
				}
				?>
			</ul>
			<div class="pa-all-addon">
				<a href="<?php echo esc_url( qsm_get_plugin_link('addons', 'dashboard', 'all_addon', 'dashboard_addons') )?>" rel="noopener" target="_blank"><?php esc_html_e( 'SEE ALL ADDONS', 'quiz-master-next' ); ?></a>
			</div>
		</div>
	</div>
</div>
<?php
}

/**
 * @since 7.0
 * @global obj $wpdb
 * @param str $widget_id
 * Generate recent taken quiz widget
 */
function qsm_dashboard_recent_taken_quiz( $widget_id ) {
	global $wpdb;
	?>
<div id="<?php echo esc_attr( $widget_id ); ?>" class="postbox <?php qsm_check_close_hidden_box( $widget_id ); ?>">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text">
			<?php esc_html_e( 'Toggle panel: Recently Taken Quizzes', 'quiz-master-next' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span>
	</button>
	<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Recently Taken Quizzes', 'quiz-master-next' ); ?></span></h2>
	<div class="inside">
		<div class="main">
			<ul class="recently-taken-quiz-ul">
				<?php
					$mlw_result_data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted=0 ORDER BY result_id DESC LIMIT 2", ARRAY_A );
				if ( $mlw_result_data ) {
					foreach ( $mlw_result_data as $key => $single_result_arr ) {
						?>
				<li>
					<?php
						if ( isset( $single_result_arr['user'] ) && '' !== $single_result_arr['user'] ) {
							echo '<img src="' . esc_url( get_avatar_url( $single_result_arr['user'] ) ) . '" class="avatar avatar-50 photo" alt="User Avatar">';
						} else {
							echo '<img src="' . esc_url( QSM_PLUGIN_URL . '/assets/default_image.png' ) . '" class="avatar avatar-50 photo" alt="Default Image">';
						}
						?>
					<div class="rtq-main-wrapper">
						<span class="rtq_user_info">
							<?php
							if ( isset( $single_result_arr['user'] ) && 0 !== intval( $single_result_arr['user'] ) ) {
								$edit_link = get_edit_profile_url( $single_result_arr['user'] );
								$actual_user = get_userdata( $single_result_arr['user'] );
								$user_name = 'None' === $single_result_arr['name'] ? $actual_user->data->display_name : $single_result_arr['name'];
								echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $user_name ) . '</a>';
							} else {
								esc_html_e( 'Guest', 'quiz-master-next' );
							}
							esc_html_e( ' took quiz ', 'quiz-master-next' );
							echo '<a href="admin.php?page=mlw_quiz_options&quiz_id=' . esc_attr( $single_result_arr['quiz_id'] ) . '">' . esc_html( $single_result_arr['quiz_name'] ) . '</a>';
							?>
						</span>
						<span class="rtq-result-info">
							<?php
							$quotes_list = '';
							$form_type = isset( $single_result_arr['form_type'] ) ? $single_result_arr['form_type'] : 0;
							if ( 1 === intval( $form_type ) || 2 === intval( $form_type ) ) {
								$quotes_list .= __( 'Not Graded', 'quiz-master-next' );
							} else {
								if ( 0 === intval( $single_result_arr['quiz_system'] ) ) {
									$quotes_list .= $single_result_arr['correct'] . ' out of ' . $single_result_arr['total'] . ' or ' . $single_result_arr['correct_score'] . '%';
								}
								if ( 1 === intval( $single_result_arr['quiz_system'] ) ) {
									$quotes_list .= $single_result_arr['point_score'] . ' Points';
								}
								if ( 3 === intval( $single_result_arr['quiz_system'] ) ) {
									$quotes_list .= $single_result_arr['correct'] . ' out of ' . $single_result_arr['total'] . ' or ' . $single_result_arr['correct_score'] . '%<br/>';
									$quotes_list .= $single_result_arr['point_score'] . ' Points';
								}
							}
							echo wp_kses_post( $quotes_list );
							?>
							|
							<?php
							$mlw_complete_time     = '';
							$mlw_qmn_results_array = maybe_unserialize( $single_result_arr['quiz_results'] );
							if ( is_array( $mlw_qmn_results_array ) ) {
								$mlw_complete_hours = floor( $mlw_qmn_results_array[0] / 3600 );
								if ( $mlw_complete_hours > 0 ) {
									$mlw_complete_time .= "$mlw_complete_hours hours ";
								}
								$mlw_complete_minutes = floor( ( $mlw_qmn_results_array[0] % 3600 ) / 60 );
								if ( $mlw_complete_minutes > 0 ) {
									$mlw_complete_time .= "$mlw_complete_minutes minutes ";
								}
								$mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
								$mlw_complete_time   .= "$mlw_complete_seconds seconds";
							}
							esc_html_e( ' Time to complete ', 'quiz-master-next' );
							echo wp_kses_post( $mlw_complete_time );
							?>
						</span>
						<span class="rtq-time-taken"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $single_result_arr['time_taken'] ) ) ); ?></span>
						<p class="row-actions-c">
							<a
								href="admin.php?page=qsm_quiz_result_details&result_id=<?php echo esc_attr( $single_result_arr['result_id'] ); ?>">View</a>
							| <a href="javascript:void(0)" data-result_id="<?php echo esc_attr( $single_result_arr['result_id'] ); ?>"
								class="trash rtq-delete-result"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></a>
						</p>
					</div>
				</li>
				<?php
					}
				}
				?>
			</ul>
			<p>
				<a href="admin.php?page=mlw_quiz_results">
					<?php
						$mlw_result_data = $wpdb->get_row( "SELECT DISTINCT COUNT(result_id) as total_result FROM {$wpdb->prefix}mlw_results WHERE deleted=0", ARRAY_A );
						echo isset( $mlw_result_data['total_result'] ) ? esc_html__( 'See All Results ', 'quiz-master-next' ) : '';
					?>
				</a>
				<?php
					echo isset( $mlw_result_data['total_result'] ) ? '(' . wp_kses_post( $mlw_result_data['total_result'] ) . ')' : '';
				?>
			</p>
		</div>
	</div>
</div>
<?php
}

/**
 * @since 7.0
 * @param str $widget_id
 * Generate posts
 */
function qsm_dashboard_latest_blogs( $widget_id ) {
	?>
<div id="<?php echo esc_attr( $widget_id ); ?>" class="postbox <?php qsm_check_close_hidden_box( $widget_id ); ?>">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text"><?php esc_html_e( "Toggle panel: Latest from our blog", 'quiz-master-next' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span>
	</button>
	<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( "Latest from our blog", 'quiz-master-next' ); ?></span></h2>
	<div class="inside">
		<div class="main">
			<ul class="what-new-ul">
				<?php
				$feed_posts_array = qsm_get_blog_data_rss();
				if ( ! empty( $feed_posts_array ) ) {
					foreach ( $feed_posts_array as $key => $single_feed_arr ) {
						?>
				<li>
					<a href="<?php echo esc_url( qsm_get_utm_link( $single_feed_arr['link'], 'dashboard', 'latest-blog', sanitize_title( $single_feed_arr['title'] ) ) ); ?>" target="_blank" rel="noopener">
						<?php echo wp_kses_post( $single_feed_arr['title'] ); ?>
					</a>
					<div class="post-description">
						<?php echo wp_kses_post( htmlspecialchars_decode( $single_feed_arr['excerpt'], ENT_QUOTES ) ); ?>
					</div>
				</li>
				<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
</div>
<?php
}

/**
 * @since 7.0
 * @param str $widget_id
 * Generate change log
 */
function qsm_dashboard_chagelog( $widget_id ) {
	global $wp_filesystem, $mlwQuizMasterNext;
	require_once ( ABSPATH . '/wp-admin/includes/file.php' );
	WP_Filesystem();
	$change_log  = array();
	$readme_file = QSM_PLUGIN_PATH . 'readme.txt';
	if ( $wp_filesystem->exists( $readme_file ) ) {
		$file_content = $wp_filesystem->get_contents( $readme_file );
		if ( $file_content ) {
			$parts           = explode( '== Changelog ==', $file_content );
			$last_change_log = mlw_qmn_get_string_between( $parts[1], ' =', '= ' );
			$change_log      = array_filter( explode( '* ', trim( $last_change_log ) ) );
		}
	}
	?>
	<div id="<?php echo esc_attr( $widget_id ); ?>" class="postbox <?php qsm_check_close_hidden_box( $widget_id ); ?>">
		<button type="button" class="handlediv" aria-expanded="true">
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Changelog', 'quiz-master-next' ); ?></span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>
		<h2 class="hndle ui-sortable-handle">
			<span><?php esc_html_e( 'Changelog', 'quiz-master-next' ); ?> (<?php echo esc_html( $mlwQuizMasterNext->version ); ?>)</span>
		</h2>
		<div class="inside">
			<div class="main">
				<?php if ( $change_log ) : ?>
					<ul class="changelog-ul">
						<?php
						$i = 0;
						foreach ( $change_log as $single_change_log ) {
							if ( ! empty( $single_change_log ) ) {
								if ( 5 === $i ) {
									break;
								}
								$expload_str = explode( ':', $single_change_log );
								$cl_type     = isset( $expload_str[1] ) ? $expload_str[0] : '';
								$cl_str      = isset( $expload_str[1] ) ? $expload_str[1] : $expload_str[0];
								if ( empty( $cl_str ) ) {
									$cl_str  = $cl_type;
									$cl_type = '';
								}
								?>
								<li>
									<span class="<?php echo esc_attr( strtolower( $cl_type ) ); ?>"><?php echo esc_html( $cl_type ); ?></span>
									<?php echo wp_kses_post( $cl_str ); ?>
								</li>
								<?php
								$i ++;
							}
						}
						?>
					</ul>
				<?php endif; ?>
				<div class="pa-all-addon" style="border-top: 1px solid #ede8e8;padding-top: 15px;">
					<a href="https://wordpress.org/plugins/quiz-master-next/#developers" target="_blank" rel="noopener"><?php esc_html_e( 'View Complete Changelog', 'quiz-master-next' ); ?></a>
				</div>
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
	if ( isset( $_POST['qsm_new_quiz_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['qsm_new_quiz_nonce'] ) ), 'qsm_new_quiz' ) ) {
		global $mlwQuizMasterNext;
		$quiz_name = isset( $_POST['quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_name'] ) ) : '';
		$quiz_name = htmlspecialchars( $quiz_name, ENT_QUOTES );

		$theme_id    = isset( $_POST['quiz_theme_id'] ) ? intval( $_POST['quiz_theme_id'] ) : 0;
		unset( $_POST['qsm_new_quiz_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		unset( $_POST['quiz_theme_id'] );
		/**
		 * Prepare Quiz Options.
		 */
		$quiz_options    = array(
			'quiz_name'              => $quiz_name,
			'quiz_featured_image'    => isset( $_POST['quiz_featured_image'] ) ? esc_url_raw( wp_unslash( $_POST['quiz_featured_image'] ) ) : '',
			'form_type'              => isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : '',
			'system'                 => isset( $_POST['system'] ) ? sanitize_text_field( wp_unslash( $_POST['system'] ) ) : '',
			'timer_limit'            => isset( $_POST['timer_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['timer_limit'] ) ) : '',
			'pagination'             => isset( $_POST['pagination'] ) ? sanitize_text_field( wp_unslash( $_POST['pagination'] ) ) : '',
			'enable_pagination_quiz' => isset( $_POST['enable_pagination_quiz'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_pagination_quiz'] ) ) : 0,
			'progress_bar'           => isset( $_POST['progress_bar'] ) ? sanitize_text_field( wp_unslash( $_POST['progress_bar'] ) ) : 0,
			'require_log_in'         => isset( $_POST['require_log_in'] ) ? sanitize_text_field( wp_unslash( $_POST['require_log_in'] ) ) : '',
			'disable_first_page'     => isset( $_POST['disable_first_page'] ) ? sanitize_text_field( wp_unslash( $_POST['disable_first_page'] ) ) : '',
			'comment_section'        => isset( $_POST['comment_section'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_section'] ) ) : 1,
		);
		$get_saved_value = QMNGlobalSettingsPage::get_global_quiz_settings();
		if ( ! empty( $get_saved_value ) && is_array( $get_saved_value ) ) {
			$quiz_options = array_replace( $get_saved_value, $quiz_options );
		}
		/**
		 * Prepare Contact Fields
		 */
		$contact_form    = array();
		if ( isset( $_POST['enable_contact_form'] ) && 1 == sanitize_text_field( wp_unslash( $_POST['enable_contact_form'] ) ) ) {
			$cf_fields       = QSM_Contact_Manager::default_fields();
			if ( isset( $cf_fields['name'] ) ) {
				$cf_fields['name']['enable'] = 'true';
				$contact_form[]              = $cf_fields['name'];
			}
			if ( isset( $cf_fields['email'] ) ) {
				$cf_fields['email']['enable']    = 'true';
				$contact_form[]                  = $cf_fields['email'];
			}
		}
		/**
		 * Prepare Quiz Options
		 */
		$quiz_options = apply_filters( 'qsm_quiz_wizard_settings_option_save', $quiz_options );
		$mlwQuizMasterNext->quizCreator->create_quiz( $quiz_name, $theme_id, array(
			'quiz_options' => $quiz_options,
			'contact_form' => $contact_form,
		) );
	}
}
add_action( 'admin_init', 'qsm_create_new_quiz_from_wizard' );


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
						target="_blank" rel="noopener"> <?php esc_html_e( "Roadmap", "quiz-master-next"); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( "Visit out public Roadmap to checkout what's in the development pipepline of QSM.", "quiz-master-next"); ?>
					</div>
				</li>
				<li>
					<a href="https://app.productstash.io/qsm#/updates"
						target="_blank" rel="noopener"><?php esc_html_e( "Recent Updates", "quiz-master-next"); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( "Checkout our updates page to know more about our recent releases", "quiz-master-next"); ?>
					</div>
				</li>
				<li>
					<a href="https://app.productstash.io/qsm#/ideas"
						target="_blank" rel="noopener"><?php esc_html_e( "Submit your ideas", "quiz-master-next"); ?>
					</a>
					<div class="post-description">
						<?php esc_html_e( "We are open your suggestions on how to improve QSM. Please visit our ideas page to share your thoughts.", "quiz-master-next"); ?>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>
<?php
}