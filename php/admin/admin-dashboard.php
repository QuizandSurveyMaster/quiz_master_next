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
function qsm_dashboard_display_change_log_section(){
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
	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php esc_html_e( 'Changelog', 'quiz-master-next' ); ?> (<?php echo esc_html( $mlwQuizMasterNext->version ); ?>)</h3>
		<div class="qsm-dashboard-page-common-style qsm-dashboard-page-changelog">
		
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

function qsm_dashboard_display_need_help_section(){
		// Define sections
	$sections = [
		[
			'title'       => __('Documentation', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/contact.png',
			'alt'         => 'contact.png',
		],
		[
			'title'       => __('Tutorials', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/camera.png',
			'alt'         => 'camera.png',
		],
		[
			'title'       => __('FAQ', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/faq.png',
			'alt'         => 'faq.png',
		],
		[
			'title'       => __('Contact Support', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/dashboard-support.png',
			'alt'         => 'dashboard-support.png',
		],
	];
	?>

	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__('Need Help?', 'quiz-master-next'); ?></h3>
		<div class="qsm-dashboard-help-center-grid qsm-dashboard-page-common-style">
			<?php foreach ( $sections as $section ) : ?>
				<div class="qsm-dashboard-help-center-card">
					<div class="qsm-dashboard-help-center-card-icon">
						<div class="qsm-dashboard-help-icon-wrap">
							<img class="qsm-dashboard-help-image" src="<?php echo esc_url($section['image']); ?>" alt="<?php echo esc_attr($section['alt']); ?>"/>
							<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/>
						</div>
					</div>
					<h3 class="qsm-dashboard-help-center-card-title">
						<?php echo esc_html($section['title']); ?>
					</h3>
					<p class="qsm-dashboard-help-center-card-description">
						<?php echo esc_html($section['description']); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function qsm_dashboard_display_popular_addon_section( $all_addons_parameter ) {
	// Define the card data.
	foreach ( $all_addons_parameter as $addon_value ) {
		if ( ! empty( $addon_value['tags'] ) && in_array( 831, array_column( $addon_value['tags'], 'term_id' ), true ) || in_array( $addon_value['id'], array( 557086, 551029, 551027, 547794, 302299, 302297, 300658, 300513 ), true ) ) {
			continue;
		}
	}
	$features = [
		[
			'title'       => esc_html__( 'Export Import', 'quiz-master-next' ),
			'description' => esc_html__( 'Allowing export/import quizzes, individual questions, or specific settings. Formats like JSON.', 'quiz-master-next' ),
			'icon'        => QSM_PLUGIN_URL . 'assets/chat-smile.png',
		],
		[
			'title'       => esc_html__( 'Gamify', 'quiz-master-next' ),
			'description' => esc_html__( 'Transform your quizzes into engaging adventures with the QSM Gamify Add-On. Set up rules to unlock new quizzes.', 'quiz-master-next' ),
			'icon'        => QSM_PLUGIN_URL . 'assets/star-pen.png',
		],
		[
			'title'       => esc_html__( 'Advance Question Type', 'quiz-master-next' ),
			'description' => esc_html__( 'The Advanced Questions plugin allows you to include three powerful question types in your Quizzes and Surveys.', 'quiz-master-next' ),
			'icon'        => QSM_PLUGIN_URL . 'assets/star-pen.png',
		],
		[
			'title'       => esc_html__( 'Reporting and Analysis', 'quiz-master-next' ),
			'description' => esc_html__( 'This plugin enables you to analyze quiz/survey results through the use of various charts and graphs. You can e...', 'quiz-master-next' ),
			'icon'        => QSM_PLUGIN_URL . 'assets/dots-group.png',
		],
	];
	?>

	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__('Popular Addons', 'quiz-master-next'); ?></h3>
		<div class="qsm-dashboard-help-center-grid qsm-dashboard-page-common-style">
			<?php foreach ( $features as $feature ) : ?>
				<div class="qsm-dashboard-help-center-card">
					<div class="qsm-dashboard-help-center-card-icon">
						<div class="qsm-dashboard-help-icon-wrap">
							<img class="qsm-dashboard-help-image" src="<?php echo esc_url( $feature['icon'] ); ?>" alt="<?php echo esc_attr( $feature['title'] ); ?> Icon" />
							<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/cross-right-arrow.png' ); ?>"  alt="<?php esc_attr_e( 'Arrow Icon', 'quiz-master-next' ); ?>" />
						</div>
					</div>
					<h3 class="qsm-dashboard-help-center-card-title">
						<?php echo $feature['title']; ?>
					</h3>
					<p class="qsm-dashboard-help-center-card-description">
						<?php echo $feature['description']; ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}


function qsm_dashboard_display_popular_theme_section( $themes ) {
	$themes = array_slice($themes, 0, 4);
	?>
	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__('Popular Themes', 'quiz-master-next'); ?></h3>
		<div class="qsm-dashboard-themes-container qsm-dashboard-page-common-style">
			<?php foreach ( $themes as $single_theme ) { ?>
				<div class="qsm-dashboard-themes-card">
					<div class="qsm-dashboard-themes-image-wrapper">
						<img src="<?php echo esc_url($single_theme['img']); ?>" alt="<?php echo esc_attr($single_theme['name']); ?>">
					</div>
					<div class="qsm-dashboard-themes-details-wrapper">
						<h3><?php echo esc_html($single_theme['name']); ?></h3>
						<a class="button button-secondary" href="<?php echo esc_url($single_theme['demo']); ?>" class="qsm-dashboard-themes-button"><?php echo esc_html__('Demo', 'quiz-master-next'); ?></a>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php
}
/**
 * @since 7.0
 * @return HTMl Dashboard for QSM
 */
function qsm_generate_dashboard_page() {
	// Only let admins and editors see this page.
	if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
		return;
	}
	global $mlwQuizMasterNext;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'QSM Dashboard', 'quiz-master-next' ); ?></h1>
	<div id="welcome_panel" class="qsm_dashboard_page postbox welcome-panel <?php qsm_check_close_hidden_box( 'welcome_panel' ); ?>">
		<a class="qsm-welcome-panel-dismiss" href="javascript:void(0)" aria-label="Dismiss the welcome panel"><?php esc_html_e( 'Dismiss', 'quiz-master-next' ); ?></a>
		<div class="qsm-dashboard-welcome-panel-wrap">
		
			<div class="welcome-panel-content">
				<div class="qsm-welcome-panel-content">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . '/assets/logo.png' ); ?>" alt="Welcome Logo">
					<!-- <p class="current_version"><?php echo esc_html( sprintf( __( 'Version: %s', 'quiz-master-next' ), $mlwQuizMasterNext->version ) ); ?></p> -->
				</div>
				<div class="qsm-welcome-panel-content">
					<h3><?php esc_html_e( 'Welcome to Quiz And Survey Master!', 'quiz-master-next' ); ?></h3>
					<p><?php esc_html_e( 'Best WordPress Quiz and Survey Maker Plugin', 'quiz-master-next' ); ?></p>
				</div>
			</div>	
			<ul class="welcome-panel-menu">
				<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('contact-support', 'dashboard', 'useful_links', 'dashboard_support') )?>" class="welcome-icon"><?php esc_html_e( 'Support', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('docs', 'dashboard', 'next_steps', 'dashboard_read_document') )?>" class="welcome-icon"><?php esc_html_e( 'Docs', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="https://github.com/QuizandSurveyMaster/quiz_master_next" class="welcome-icon"><?php esc_html_e( 'Github', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="https://www.facebook.com/groups/516958552587745" class="welcome-icon"><?php esc_html_e( 'Facebook', 'quiz-master-next' ); ?></a></li>
				<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://next.expresstech.io/qsm', 'dashboard', 'next_steps', 'dashboard_roadmap') )?>" class="welcome-icon"><?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?></a></li>
			</ul>
		</div>
		<?php do_action( 'qsm_welcome_panel' ); ?>
	</div>
	<div class="qsm-dashboard-wrapper">
		<div class="qsm-dashboard-container">
			<div class="qsm-dashboard-create-quiz-section qsm-dashboard-page-common-style">
				<div class="qsm-dashboard-page-header">
					<h3 class="qsm-dashboard-card-title"><?php esc_html_e( 'Create a Quiz / Survey', 'quiz-master-next' ); ?></h3>
					<p class="qsm-dashboard-card-description"><?php esc_html_e( 'Design quizzes and surveys tailored to your needs.', 'quiz-master-next' ); ?></p>
				</div>
				<div class="">
					<a class="button button-primary qsm-dashboard-section-create-quiz"  href="<?php echo esc_url(admin_url('admin.php?page=qsm_create_quiz_page')); ?>" ><?php esc_html_e( 'Get Started', 'quiz-master-next' ) ?><img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/right-arrow.png'); ?>" alt="right-arrow.png"/></a>
				</div>
			</div>

			<?php 
			$qsm_admin_dd = wp_remote_get(QSM_PLUGIN_URL . 'data/parsing_script.json', [ 'sslverify' => false ]);
			$qsm_admin_dd = json_decode(wp_remote_retrieve_body($qsm_admin_dd), true);
				qsm_dashboard_display_popular_addon_section($qsm_admin_dd['all_addons']);
				qsm_dashboard_display_popular_theme_section($qsm_admin_dd['themes']);
				qsm_dashboard_display_need_help_section();
				qsm_dashboard_display_change_log_section();
			?>
		</div>
	</div>
		
	<?php
	/*
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

	<?php */ ?>
</div>
<?php
}

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
						if ( in_array( $single_arr['name'], array( "Export & Import", "Reporting and Analysis", "Export Results", "Advanced Question Types" ), true ) ) {
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
			'form_type'              => isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : 0,
			'system'                 => isset( $_POST['system'] ) ? sanitize_text_field( wp_unslash( $_POST['system'] ) ) : 3,
			'timer_limit'            => ! empty( $_POST['timer_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['timer_limit'] ) ) : 0,
			'pagination'             => ! empty( $_POST['pagination'] ) ? intval( $_POST['pagination'] ) : 0,
			'enable_pagination_quiz' => isset( $_POST['enable_pagination_quiz'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_pagination_quiz'] ) ) : 0,
			'progress_bar'           => isset( $_POST['progress_bar'] ) ? sanitize_text_field( wp_unslash( $_POST['progress_bar'] ) ) : 0,
			'require_log_in'         => ! empty( $_POST['require_log_in'] ) ? sanitize_text_field( wp_unslash( $_POST['require_log_in'] ) ) : 0,
			'disable_first_page'     => isset( $_POST['disable_first_page'] ) ? sanitize_text_field( wp_unslash( $_POST['disable_first_page'] ) ) : 0,
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