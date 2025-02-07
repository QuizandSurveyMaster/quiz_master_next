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
			'link'        => qsm_get_plugin_link('docs', 'dashboard', 'next_steps', 'dashboard_read_document'),
		],
		[
			'title'       => __('Tutorials', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/camera.png',
			'alt'         => 'camera.png',
			'link'        => 'https://demo.quizandsurveymaster.com/',
		],
		[
			'title'       => __('FAQ', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/faq.png',
			'alt'         => 'faq.png',
			'link'        => 'https://quizandsurveymaster.com/#:~:text=Frequently%20asked%20questions',
		],
		[
			'title'       => __('Contact Support', 'quiz-master-next'),
			'description' => __('Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next'),
			'image'       => QSM_PLUGIN_URL . 'assets/dashboard-support.png',
			'alt'         => 'dashboard-support.png',
			'link'        => qsm_get_plugin_link('contact-support', 'dashboard', 'useful_links', 'dashboard_support'),
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
							<a target="_blank" rel="noopener" href="<?php echo esc_url( $section['link'] )?>" class="welcome-icon"><img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/></a>
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

function qsm_dashboard_display_popular_addon_section( $popular_addons ) {
	?>
	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__('Popular Addons', 'quiz-master-next'); ?></h3>
		<div class="qsm-dashboard-help-center-grid qsm-dashboard-page-common-style">
			<?php foreach ( array_slice($popular_addons, 0, 4) as $addon ) :
				$addon_link = qsm_get_utm_link( $addon['link'], 'addon_setting', 'popular_addon', 'addon-settings_' . sanitize_title( $addon['name'] ) );
				$addon_icon = isset($addon['icon']) && "" != $addon['icon'] ? $addon['icon'] : QSM_PLUGIN_URL . 'assets/chat-smile.png';
				?>
				<div class="qsm-dashboard-help-center-card">
					<div class="qsm-dashboard-help-center-card-icon">
						<div class="qsm-dashboard-help-icon-wrap">
							<img class="qsm-dashboard-help-image" src="<?php echo esc_url( $addon_icon ); ?>" alt="<?php echo esc_attr( $addon['name'] ); ?> Icon" />
							<a target="_blank" rel="noopener" href="<?php echo esc_url($addon_link); ?>">
								<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/cross-right-arrow.png' ); ?>"  alt="<?php esc_attr_e( 'Arrow Icon', 'quiz-master-next' ); ?>" />
							</a>
						</div>
					</div>
					<h3 class="qsm-dashboard-help-center-card-title">
						<?php echo esc_html($addon['name']); ?>
					</h3>
					<p class="qsm-dashboard-help-center-card-description">
						<?php echo esc_html($addon['description']); ?>
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
<div id="welcome_panel" class=" <?php qsm_check_close_hidden_box( 'welcome_panel' ); ?>">
	<div class="qsm-dashboard-welcome-panel-wrap">
		<div class="qsm-welcome-panel-content">
			<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/logo-blue.svg' ); ?>" alt="logo-blue.svg">
			<p class="current_version">
				<?php
				/* translators: %1$s: The current version of the Quiz Master Next plugin. */
				echo esc_html( sprintf( __( 'Version: %1$s', 'quiz-master-next' ), $mlwQuizMasterNext->version ) );
				?>
			</p>
		</div>
		<ul class="qsm-welcome-panel-menu">
			<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('contact-support', 'dashboard', 'useful_links', 'dashboard_support') )?>" class="welcome-icon"><img class="qsm-help-tab-icon" alt="" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Support.svg' ) ?>"><?php esc_html_e( 'Support', 'quiz-master-next' ); ?></a></li>
			<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('docs', 'dashboard', 'next_steps', 'dashboard_read_document') )?>" class="welcome-icon"><span class="dashicons dashicons-media-document"></span><?php esc_html_e( 'Docs', 'quiz-master-next' ); ?></a></li>
			<li><a target="_blank" rel="noopener" href="https://github.com/QuizandSurveyMaster/quiz_master_next" class="welcome-icon"><span class="dashicons dashicons-networking"></span><?php esc_html_e( 'Github', 'quiz-master-next' ); ?></a></li>
			<li><a target="_blank" rel="noopener" href="https://www.facebook.com/groups/516958552587745" class="welcome-icon"><span class="dashicons dashicons-facebook-alt"></span><?php esc_html_e( 'Facebook', 'quiz-master-next' ); ?></a></li>
			<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://next.expresstech.io/qsm', 'dashboard', 'next_steps', 'dashboard_roadmap') )?>" class="welcome-icon"><span class="dashicons dashicons-bell"></span><?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?></a></li>
		</ul>
	</div>
	<?php do_action( 'qsm_welcome_panel' ); ?>
</div>
<div class="wrap">
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
			$popular_addons = qsm_get_widget_data( 'popular_products' );
				qsm_dashboard_display_popular_addon_section($popular_addons);
				qsm_dashboard_display_popular_theme_section($qsm_admin_dd['themes']);
				qsm_dashboard_display_need_help_section();
				qsm_dashboard_display_change_log_section();
			?>
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