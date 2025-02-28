<?php

// Add AJAX action for logged-in users
add_action('wp_ajax_qsm_activate_plugin', 'qsm_activate_plugin_ajax_activate_plugin');

function qsm_activate_plugin_ajax_activate_plugin() {
    // Check if the user has permission to activate plugins
    if ( ! current_user_can('activate_plugins') ) {
        wp_send_json_error([ 'message' => 'Permission denied.' ]);
        wp_die();
    }
    check_ajax_referer('qsm_installer_nonce', 'nonce');
    if ( empty($_POST['plugin_path']) ) {
        wp_send_json_error([ 'message' => 'No plugin path provided.' ]);
        wp_die();
    }
    $plugin_path = isset($_POST['plugin_path']) ? sanitize_text_field(wp_unslash( $_POST['plugin_path'] ) ) : "";
    $result = activate_plugin($plugin_path);
	wp_send_json_success([ 'message' => 'Plugin activated successfully.' ]);
    wp_die();
}

function qsm_activate_plugin_ajax_handler() {
    // Check if the user has permission to activate plugins
    if ( ! current_user_can('activate_plugins') ) {
        wp_send_json_error([ 'message' => 'Permission denied.' ]);
        wp_die();
    }

    // Verify nonce
    check_ajax_referer('qsm_installer_nonce', 'nonce');
    $plugin_path = isset($_POST['plugin_path']) ? sanitize_text_field(wp_unslash($_POST['plugin_path'])) : "";

    // Ensure plugin functions are available
    if ( ! function_exists('get_plugins') ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = get_plugins();
	if ( isset($all_plugins[ $plugin_path ]) ) {
		wp_send_json_success([ 'version' => esc_html__('Version: ', 'quiz-master-next') . $all_plugins[ $plugin_path ]['Version'] ]);
	} else {
		wp_send_json_error([ 'message' => 'Plugin not found.' ]);
	}
    wp_die();
}

// Register AJAX handlers
add_action('wp_ajax_qsm_activate_plugin_ajax_handler', 'qsm_activate_plugin_ajax_handler');

// Add AJAX action for logged-in users
add_action('wp_ajax_qsm_get_activated_themes', 'qsm_get_activated_themes_ajax');

function qsm_get_activated_themes_ajax() {

    check_ajax_referer('qsm_installer_nonce', 'nonce');
    $theme_slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash( $_POST['slug'] ) ) : "";
	$theme_slug = 'qsm-theme-'.$theme_slug;
	global $wpdb;
	$query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}mlw_themes WHERE theme = %s", $theme_slug);
	$id = $wpdb->get_var($query);
	wp_send_json_success([ 'id' => $id ]);
    wp_die();
}

function qsm_get_filtered_dashboard_themes() {
	global $mlwQuizMasterNext;
	$installed_themes = $mlwQuizMasterNext->theme_settings->get_installed_themes();
	$active_themes = $mlwQuizMasterNext->theme_settings->get_active_themes();

	// Return an empty array if both themes are empty
	if ( empty($installed_themes) && empty($active_themes) ) {
		return array();
	}

	// Filter active themes to ensure their directories exist
	$filtered_active_themes = [];
	if ( ! empty($active_themes) ) {
		foreach ( $active_themes as $theme ) {
			$theme_dir = WP_PLUGIN_DIR . '/' . $theme['theme'];
			if ( is_dir($theme_dir) ) {
				$filtered_active_themes[] = $theme;
			}
		}
	}

	// Merge installed themes and filtered active themes
	$merged_themes = [];
	foreach ( array_merge($installed_themes, $filtered_active_themes) as $theme ) {
		$key = $theme['theme'];
		if ( ! isset($merged_themes[ $key ]) ) {
			$merged_themes[ $key ] = $theme;
		} else {
			$merged_themes[ $key ] = array_merge($merged_themes[ $key ], $theme);
		}
	}

	return array_values($merged_themes);
}

function qsm_dashboard_display_quizoptions_section( $quizoptions_boxes ) {
	?>
	<div class="qsm-dashboard-choose-quiz-type-wrap">
		<div class="qsm-dashboard-choose-quiz-type" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Choose your quiz type', 'quiz-master-next'); ?></h3>
				<p><?php echo esc_html__('Based on your selection, we will assist you in creating a customized quiz', 'quiz-master-next'); ?></p>
			</div>
			<div class="qsm-dashboard-page-items">
				<?php
				$first = true;
				foreach ( $quizoptions_boxes as $page ) {
					$active_class = $first ? 'qsm-dashboard-page-items-active' : "";
					$first = false;
					?>
						<div class="qsm-dashboard-page-common-style qsm-dashboard-page-item <?php echo esc_attr($active_class); ?>" data-type="<?php echo esc_attr( $page['type'] ); ?>" data-id="<?php echo esc_attr( $page['id'] ); ?>" >
							<h3><?php echo esc_html($page['title']); ?></h3>
							<p><?php echo esc_html($page['description']); ?></p>
						</div>
					<?php
				}
				?>
			</div>
		</div>
	</div> <!-- qsm-dashboard-choose-quiz-type-wrap  -->
	<?php
}

function qsm_dashboard_display_theme_section( $all_themes, $installer_option, $invalid_and_expired, $all_addons, $installer_activated, $installer_script ) {
	global $mlwQuizMasterNext;
	$filtered_themes = qsm_get_filtered_dashboard_themes();
	$addon_lookup = array();
	$installed_plugins = get_plugins();
	$activated_plugins = get_option('active_plugins');
	$all_plugins = get_plugins();
	$selected_bundle = "";
	if ( 1 == $installer_activated ) {
		$addon_lookup = array_column($installer_script, null, 'slug');
		$selected_bundle = isset($installer_option['bundle']) && "" != $installer_option['bundle'] ? $installer_option['bundle'] : "";
	}
	?>
	<div class="qsm-dashboard-choose-theme-wrap">
		<div class="qsm-dashboard-choose-theme" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Select a theme', 'quiz-master-next'); ?></h3>
				<p><?php echo esc_html__('Pick a Free or Premium theme to personalize your quiz experience.', 'quiz-master-next'); ?></p>
			</div>
			<?php
			if ( ! empty( $all_themes ) && is_array( $all_themes ) ) { ?>
				<div class="qsm-quiz-theme-steps-container qsm-dashboard-page-common-style">
					<div class="qsm-quiz-steps-card qsm-quiz-steps-default-theme qsm-quiz-theme-activated" data-id="" data-slug="">
						<div class="qsm-quiz-steps-image">
							<img alt="" src="<?php echo esc_url( QSM_PLUGIN_URL ) . '/assets/screenshot-default-theme.png'; ?>">
						</div>
						<div class="qsm-quiz-steps-content">
							<div class="qsm-quiz-steps-info">
								<h3 class="qsm-quiz-steps-title"><?php esc_html_e( 'Default Theme', 'quiz-master-next' ); ?></h3>
								<p class="qsm-dashboard-addon-status"></p>
							</div>
							<div class="qsm-quiz-steps-action-buttons">
								<a href="javascript:void(0)" class="qsm-theme-action-btn button button-secondary" disabled="disabled"><?php echo esc_html__( 'Selected', 'quiz-master-next' ); ?></a>
							</div>
						</div>
					</div>
					<?php
					foreach ( $all_themes as $theme_key => $theme_value ) {

						$theme_name = $theme_value['name'];

						// Find matching theme details in $filtered_themes by theme_name
						$matching_theme = array_filter($filtered_themes, function ( $filtered_theme ) use ( $theme_name ) {
							return isset($filtered_theme['theme_name']) && $filtered_theme['theme_name'] == $theme_name;
						});

						$matching_theme = ! empty($matching_theme) ? array_shift($matching_theme) : array();
						foreach ( $all_addons as $addon ) {
							if ( $addon['id'] == $theme_value['id'] ) {
								// Merge the additional keys into the theme array
								$theme_value = array_merge($theme_value, [
									'path'         => $addon['path'],
									'icon'         => $addon['icon'],
									'settings_tab' => $addon['settings_tab'],
									'option'       => $addon['option'],
								]);
								break; // Stop looping once a match is found
							}
						}
						$theme_id = $theme_value['id']; // download id
						$database_theme_id = isset($matching_theme['id']) ? $matching_theme['id'] : '';
						$theme_screenshot = $theme_value['img'];
						$theme_link = qsm_get_utm_link($theme_value['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title($theme_name));
						$theme_demo = qsm_get_utm_link($theme_value['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title($theme_name));
						$theme_path = isset($theme_value['path']) ? $theme_value['path'] : '';
						$theme_slug = isset($theme_value['slug']) ? $theme_value['slug'] : '';
						$is_activated = in_array( $theme_path, $activated_plugins, true );
						$is_installed = array_key_exists( $theme_path, $installed_plugins );
						$card_class = $is_activated ? "qsm-quiz-theme-activated" : "";

						?>
						<div class="qsm-quiz-steps-card <?php echo esc_attr($card_class); ?>"  data-id="<?php echo esc_attr( $theme_id ); ?>" data-slug="<?php echo esc_attr( $theme_slug ); ?>" data-path="<?php echo esc_attr( $theme_path ); ?>">
							<div class="qsm-quiz-steps-image">
								<img src="<?php echo esc_url($theme_screenshot); ?>" alt="<?php echo esc_attr( $theme_name ); ?>">
							</div>
							<div class="qsm-quiz-steps-content">
								<div class="qsm-quiz-steps-info">
									<h3 class="qsm-quiz-steps-title"><?php echo esc_html($theme_name); ?></h3>
									<p class="qsm-dashboard-addon-status">
									<?php
									if ( $is_activated || $is_installed ) {
										echo esc_html__( 'Version: ', 'quiz-master-next' ) . esc_html( $all_plugins[ $theme_path ]['Version'] );
									}
									?>
									</p>
								</div>
								<?php if ( 0 == $installer_activated ) { ?>
									<div class="qsm-quiz-steps-action-buttons">
										<?php if ( ! $is_activated && ! $is_installed ) { ?>
											<a href="<?php echo esc_url($theme_link); ?>" class="button button-primary" target="_blank">
												<?php echo esc_html__( 'Upgrade', 'quiz-master-next' ); ?>
											</a>
										<?php
										} elseif ( $is_activated || $is_installed ) { ?>
											<a href="javascript:void(0)" class="qsm-theme-action-btn button button-secondary">
												<?php if ( $is_activated ) {
													echo esc_html__( 'Select', 'quiz-master-next' );
												} elseif ( $is_installed ) {
													echo esc_html__( 'Activate', 'quiz-master-next' );
												}
												?>
											</a>
										<?php } ?>
										<a href="<?php echo esc_url($theme_demo); ?>" class="button button-secondary demo" target="_blank">
											<?php echo esc_html__( 'Demo', 'quiz-master-next' ); ?>
										</a>
									</div>
								<?php }
								do_action('qsm_create_quiz_theme_option_buttons', $installer_activated, $invalid_and_expired, $addon_lookup, $theme_slug, $selected_bundle, $is_installed, $is_activated, $theme_demo ); ?>
							</div>
							<div class="qsm-dashboard-theme-recommended"><?php echo esc_html__('Recommended', 'quiz-master-next'); ?></div>
							<input style="display: none" type="radio" name="quiz_theme_id" value="<?php echo intval( $database_theme_id ); ?>" >
						</div>
						<?php
					} ?>
				</div>
				<?php
			}
			?>
		</div>
	</div><!-- qsm-dashboard-choose-theme-wrap  -->
	<div class="qsm-dashboard-see-more-types-wrap">
		<a href="javascript:void(0)" class="button qsm-dashboard-see-more-themes"><?php echo esc_html__('See More Themes', 'quiz-master-next'); ?></a>
	</div>
	<?php
}

function qsm_dashboard_display_addons_section( $all_addons_parameter, $installer_option, $invalid_and_expired, $installer_activated, $installer_script ) {
	$addon_lookup = array();
	$installed_plugins = get_plugins();
	$activated_plugins = get_option('active_plugins');
	$all_plugins = get_plugins();
	$selected_bundle = "";
	if ( 1 == $installer_activated ) {
		$addon_lookup = array_column($installer_script, null, 'slug');
		$selected_bundle = isset($installer_option['bundle']) && "" != $installer_option['bundle'] ? $installer_option['bundle'] : "";
	}
	?>
	<div class="qsm-dashboard-choose-addon-wrap">
		<div class="qsm-dashboard-choose-addon" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Select Addons', 'quiz-master-next'); ?></h3>
				<p><?php echo esc_html__('Enhance your quiz with additional features using addons', 'quiz-master-next'); ?></p>
			</div>
			<div class="qsm-quiz-addon-steps-grid">
			<?php
				foreach ( $all_addons_parameter as $addon_value ) {
					if ( ! empty( $addon_value['tags'] ) && in_array( 831, array_column( $addon_value['tags'], 'term_id' ), true ) || in_array( $addon_value['id'], array( 557086, 551029, 551027, 547794, 302299, 302297, 300658, 300513 ), true ) ) {
						continue;
					}
					$addon_id = $addon_value['id']; // download id
					$addon_name = $addon_value['name'];
					$addon_link = qsm_get_utm_link( $addon_value['link'], 'addon_setting', 'popular_addon', 'addon-settings_' . sanitize_title( $addon_value['name'] ) );
					$addon_description = $addon_value['description'];
					$addon_path = isset($addon_value['path']) ? $addon_value['path'] : '';
					$addon_slug = isset($addon_value['slug']) ? $addon_value['slug'] : '';
					$addon_icon = isset($addon_value['icon']) && "" != $addon_value['icon'] ? $addon_value['icon'] : QSM_PLUGIN_URL . 'assets/chat-smile.png';
					$is_installed = array_key_exists($addon_path, $installed_plugins);
					$is_activated = in_array($addon_path, $activated_plugins, true);
					$addon_status = '';
					if ( true == $is_activated || true == $is_installed ) {
						$addon_status = esc_html__( 'Version: ', 'quiz-master-next' ). $all_plugins[ $addon_path ]['Version'];
					} elseif ( false == $is_activated && false == $is_installed && (1 == $installer_activated && 0 == $invalid_and_expired) ) {
						$addon_status = esc_html__( 'Install & Activate', 'quiz-master-next' );
					}
					?>
					<div class="qsm-quiz-addon-steps-card" data-path="<?php echo esc_attr($addon_path); ?>" data-id="<?php echo esc_attr($addon_id); ?>" data-slug="<?php echo esc_attr($addon_slug); ?>">
						<div class="qsm-quiz-addon-steps-images">
							<img class="qsm-quiz-addon-steps-icon" alt="Addon" src="<?php echo esc_url($addon_icon); ?>">
						</div>
						<div class="qsm-quiz-addon-steps-info">
							<h3 class="qsm-quiz-addon-steps-title"><?php echo esc_html($addon_name); ?></h3>
							<?php  $display_text = mb_strlen($addon_description) > 110 ? mb_substr($addon_description, 0, 110) . '...' : $addon_description; ?>
							<p class="qsm-quiz-addon-steps-status"><?php echo esc_html($display_text); ?></p>
						</div>
						<?php if ( 0 == $installer_activated ) { ?>
							<div class="qsm-quiz-addon-steps-button">
								<?php if ( ! $is_activated && ! $is_installed ) { ?>
									<p class="qsm-dashboard-addon-status"></p>
									<a href="<?php echo esc_url($addon_link); ?>" class="button button-primary qsm-quiz-addon-steps-upgrade-btn buy" target="_blank">
										<?php echo esc_html__('Upgrade Plan', 'quiz-master-next'); ?>
									</a>
								<?php } elseif ( $is_activated || $is_installed ) {
									$is_woocommerce_activated = 'woocommerce-integration' == $addon_slug && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ? 'qsm-create-quiz-no-activated-tooltip' : ''; ?>
									<p class="qsm-dashboard-addon-status"><?php echo esc_html($addon_status); ?></p>
									<label class="qsm-dashboard-addon-switch <?php echo esc_attr($is_woocommerce_activated); ?>">
										<input type="checkbox" class="qsm-dashboard-addon-toggle"
											<?php checked(esc_attr($is_activated)); ?>
											<?php disabled(esc_attr($is_activated)); ?>>
										<span class="qsm-dashboard-addon-slider">
											<span class="qsm-dashboard-addon-checkmark">&#10003;</span>
										</span>
										<?php
										if ( "" != $is_woocommerce_activated ) { ?>
											<span class="qsm-create-quiz-tooltip"><?php esc_html_e('Please activate the WooCommerce plugin to proceed.', 'quiz-master-next'); ?></span>
										<?php } ?>
									</label>
								<?php } ?>
							</div>
						<?php }
							do_action('qsm_create_quiz_addon_option_buttons', $installer_activated, $invalid_and_expired, $addon_lookup, $addon_slug, $selected_bundle, $is_installed, $is_activated, $addon_link, $addon_status); ?>
						<div class="qsm-dashboard-addon-recommended"><?php echo esc_html__('Recommended', 'quiz-master-next'); ?></div>
					</div>
					<?php
				}
			?>
			</div>
		</div>
	</div><!-- qsm-dashboard-choose-addon-wrap  -->
	<div class="qsm-dashboard-see-more-types-wrap">
		<a href="javascript:void(0)" class="button qsm-dashboard-see-more-addons"><?php echo esc_html__('See more Addons', 'quiz-master-next'); ?></a>
	</div>
    <?php
}

function qsm_dashboard_display_quizform_section() {
	?>
		<div id="quiz_settings" class="qsm-new-menu-elements qsm-dashboard-quiz-form" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Give Your Quiz a Name to Get Started!', 'quiz-master-next'); ?></h3>
			</div>
			<div class="input-group">
				<label for="quiz_name" class="qsm-dashboard-quiz-name-label"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?>
					<span style="color:red">*</span>
				</label>
				<input type="text" class="quiz_name" name="quiz_name" value="" required="" placeholder="<?php esc_html_e( 'Enter a name for this Quiz.', 'quiz-master-next' ); ?>">
			</div>
			<div id="qsm-settings-content" class="qsm-create-quiz-more-settings" style="display: none;">
				<?php qsm_settings_to_create_quiz(); ?>
			</div>

			<div class="qsm-create-quiz-switch">
				<span class="qsm-create-quiz-toggle-label"><?php esc_html_e('Additional Form Settings', 'quiz-master-next'); ?></span>
				<label class="qsm-create-quiz-switch-label">
					<input type="checkbox" class="qsm-create-quiz-show-more-settings">
					<span class="qsm-create-quiz-slider"></span>
				</label>
			</div>
		</div>
	<?php
}


function qsm_create_quiz_page_callback() {
	global $mlwQuizMasterNext;

	wp_enqueue_script( 'qsm-create-quiz-script',  QSM_PLUGIN_JS_URL.'/qsm-create-quiz-script.js', array( 'jquery' ), $mlwQuizMasterNext->version,true);
	wp_enqueue_style( 'qsm-create-quiz-style', QSM_PLUGIN_CSS_URL . '/qsm-create-quiz-style.css', array(), $mlwQuizMasterNext->version );
	do_action( 'qsm_create_quiz_script_style' );
	$qsm_admin_dd = qsm_get_parsing_script_data();
	$qsm_admin_dashboard = qsm_get_parsing_script_data('dashboard.json');
	$quizoptions_boxes = isset($qsm_admin_dashboard['quizoptions']) ? $qsm_admin_dashboard['quizoptions'] : array();

	$installed_plugins = get_plugins();
	$activated_plugins = get_option('active_plugins');
	$installed_plugins = array_keys($installed_plugins);

	$installer_script = $installer_option = array();
	$invalid_and_expired = 1;
	$installer_activated = 0;
	if ( class_exists('QSM_Installer') ) {
		$plugin_path = WP_PLUGIN_DIR . '/qsm-installer/qsm-installer.php';
		$plugin_data = get_plugin_data( $plugin_path );
		if ( isset( $plugin_data['Version'] ) && version_compare( $plugin_data['Version'], '2.0.0', '>=' ) ) {
			$installer_activated = 1;
			$installer_option = QSM_Installer::get_installer_option();
			$installer_script = QSM_Installer::qsm_get_addon_data();
			if ( isset($installer_option['bundle']) && isset($installer_option['license_status']) && 'valid' == $installer_option['license_status'] && isset($installer_option['license_key']) && '' != $installer_option['license_key'] ) {
				$invalid_and_expired = 0;
			}
		}
	}
	wp_localize_script( 'qsm-create-quiz-script', 'qsm_admin_new_quiz', array(
		'quizoptions'         => $quizoptions_boxes,
		'installed'           => $installed_plugins,
		'activated'           => $activated_plugins,
		'ajaxurl'             => admin_url('admin-ajax.php'),
		'nonce'               => wp_create_nonce('qsm_installer_nonce'),
		'installer_option'    => $installer_option,
		'installer_activated' => $installer_activated,
		'invalid_and_expired' => $invalid_and_expired,
		'process'             => __('Processing...', 'quiz-master-next'),
		'installing'          => __('Installing...', 'quiz-master-next'),
		'activating'          => __('Activating...', 'quiz-master-next'),
		'activated_text'      => __('Activated', 'quiz-master-next'),
		'select'              => __('Select', 'quiz-master-next'),
		'selected'            => __('Selected', 'quiz-master-next'),
		'retry'               => __('Retry', 'quiz-master-next'),
		'more_settings'       => __('Additional Form Settings', 'quiz-master-next'),
		'less_settings'       => __('Hide Additional Settings', 'quiz-master-next'),
	) );

	qsm_display_header_section_links();
	?>
	<div class="wrap">
		<div class="qsm-new-quiz-wrapper">
			<div class="qsm-dashboard-header-pagination">
			<?php $last_visited_page = ! empty($_SERVER['HTTP_REFERER']) ? esc_url(sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']))) : esc_url(admin_url('admin.php?page=qsm_dashboard')); ?>
				<a href="<?php echo esc_url($last_visited_page); ?>" class="qsm-dashboard-journy-previous-dashboard" >← <?php echo esc_html__('Back', 'quiz-master-next'); ?></a>
				<a href="javascript:void(0)" class="qsm-dashboard-journy-previous-step" style="display:none;">← <?php echo esc_html__('Back', 'quiz-master-next'); ?></a>
				<a href="javascript:void(0)" class="qsm-dashboard-journy-next-step" style="display:none;"><?php echo esc_html__('Skip this', 'quiz-master-next'); ?></a>
				<a href="javascript:void(0)" class="qsm-dashboard-journy-next-step-proceed button-primary"><?php echo esc_html__('Proceed', 'quiz-master-next'); ?> →</a>
				<a style="display: none;" id="create-quiz-button" href="javascript:void(0)" class="qsm-dashboard-journy-create-quiz button-primary"><?php echo esc_html__('Start Adding Questions', 'quiz-master-next'); ?> →</a>
			</div>
			<div class="qsm-new-quiz-container">
				<form action="" method="post" id="new-quiz-form">
					<div class="qsm-form-inside-container" id="qsm-add-installer">
						<?php wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' );
						if ( $qsm_admin_dd ) {
							$all_addons = $qsm_admin_dd['all_addons'];
							$all_themes = $qsm_admin_dd['themes'];

							$dashboard_pages = [
								[
									'page_no'  => 1,
									'callback' => 'qsm_dashboard_display_quizoptions_section',
									'params'   => [ $quizoptions_boxes ],
								],
								[
									'page_no'  => 2,
									'callback' => 'qsm_dashboard_display_theme_section',
									'params'   => [ $all_themes, $installer_option, $invalid_and_expired, $all_addons, $installer_activated, $installer_script ],
								],
								[
									'page_no'  => 3,
									'callback' => 'qsm_dashboard_display_addons_section',
									'params'   => [ $all_addons, $installer_option, $invalid_and_expired, $installer_activated, $installer_script ],
								],
								[
									'page_no'  => 4,
									'callback' => 'qsm_dashboard_display_quizform_section',
									'params'   => [],
								],
							];

							foreach ( $dashboard_pages as $page ) {
								echo '<div class="qsm-dashboard-container-pages" data-page-no="' . esc_attr($page['page_no']) . '" style="display: none;">';

								if ( function_exists($page['callback']) ) {
									call_user_func_array($page['callback'], $page['params']);
								}

								echo '</div>';
							}
						} else {
							qsm_dashboard_display_quizform_section();
							qsm_display_fullscreen_error();
						}
						?>
					</div>
					<div class="qsm-create-quiz-bottom-right-button" style="display: none;">
						<?php $bundle_link = qsm_get_utm_link( 'https://quizandsurveymaster.com/pricing/', 'create_quiz', 'bundles', 'new-quiz-steps' ); ?>
						<a href="<?php echo esc_url( $bundle_link ); ?>" target="_blank" class="qsm-create-quiz-bundle-button">
							<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/cube.png' ); ?>" alt="cube.png">
							<span> <?php echo esc_html__('Grab the Bundle & Save 90%', 'quiz-master-next'); ?></span>
						</a>
						<small><?php echo esc_html__('*This bundle includes all themes and add-ons', 'quiz-master-next'); ?></small>
					</div>
				</form>
			</div><!-- qsm-new-quiz-container -->
		</div><!-- qsm-new-quiz-wrapper -->
	</div>
	<?php

}