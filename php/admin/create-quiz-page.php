<?php 

function qsm_get_filtered_dashboard_themes( $themes_data ) {
	global $mlwQuizMasterNext;
	$installed_themes = $mlwQuizMasterNext->theme_settings->get_installed_themes();
	$active_themes = $mlwQuizMasterNext->theme_settings->get_active_themes();

	// Return an empty array if both themes are empty
	if (empty($installed_themes) && empty($active_themes)) {
		return array();
	}

	// Filter active themes to ensure their directories exist
	$filtered_active_themes = [];
	if (!empty($active_themes)) {
		foreach ($active_themes as $theme) {
			$theme_dir = WP_PLUGIN_DIR . '/' . $theme['theme'];
			if (is_dir($theme_dir)) {
				$filtered_active_themes[] = $theme;
			}
		}
	}

	// Merge installed themes and filtered active themes
	$merged_themes = [];
	foreach (array_merge($installed_themes, $filtered_active_themes) as $theme) {
		$key = $theme['theme'];
		if (!isset($merged_themes[$key])) {
			$merged_themes[$key] = $theme;
		} else {
			$merged_themes[$key] = array_merge($merged_themes[$key], $theme);
		}
	}

	return array_values($merged_themes);
}

function qsm_get_id_by_theme_name($array, $themeName) {  
    $themeNames = array_column($array, 'theme_name'); 
    $index = array_search($themeName, $themeNames);
    return $index !== false ? $array[$index]['id'] : '';
}

function qsm_dashboard_display_need_help_section(){
	?>
		<div class="qsm-dashboard-help-center">
			<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__( 'Need Help?', 'quiz-master-next' ); ?></h3>
			<p><?php echo esc_html__( 'Welcome to the QSM Plugin Help Center!', 'quiz-master-next' ); ?></p>
			<div class="qsm-dashboard-help-center-grid qsm-dashboard-page-common-style">
				<div class="qsm-dashboard-help-center-card">
				<div class="qsm-dashboard-help-center-card-icon">
					<div class="qsm-dashboard-help-icon-wrap">
						<img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/contact.png'); ?>" alt="contact.png"/>
						<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/>
					</div>
				</div>
				<h3 class="qsm-dashboard-help-center-card-title">
					<?php echo esc_html__( 'Documentation', 'quiz-master-next' ); ?>
				</h3>
				<p class="qsm-dashboard-help-center-card-description">
					<?php echo esc_html__( 'Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next' ); ?>
				</p>
				</div>
				<div class="qsm-dashboard-help-center-card">
				<div class="qsm-dashboard-help-center-card-icon">
					<div class="qsm-dashboard-help-icon-wrap">
						<img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/camera.png'); ?>" alt="camera.png"/>
						<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/>
					</div>
				</div>
				<h3 class="qsm-dashboard-help-center-card-title">
					<?php echo esc_html__( 'Tutorials', 'quiz-master-next' ); ?>
				</h3>
				<p class="qsm-dashboard-help-center-card-description">
					<?php echo esc_html__( 'Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next' ); ?>
				</p>
				</div>
				<div class="qsm-dashboard-help-center-card">
				<div class="qsm-dashboard-help-center-card-icon">
					<div class="qsm-dashboard-help-icon-wrap">
						<img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/faq.png'); ?>" alt="faq.png"/>
						<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/>
					</div>
				</div>
				<h3 class="qsm-dashboard-help-center-card-title">
					<?php echo esc_html__( 'FAQ', 'quiz-master-next' ); ?>
				</h3>
				<p class="qsm-dashboard-help-center-card-description">
					<?php echo esc_html__( 'Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next' ); ?>
				</p>
				</div>
				<div class="qsm-dashboard-help-center-card">
				<div class="qsm-dashboard-help-center-card-icon">
					<div class="qsm-dashboard-help-icon-wrap">
						<img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/dashboard-support.png'); ?>" alt="dashboard-support.png"/>
						<img class="qsm-dashboard-help-arrow" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/cross-right-arrow.png'); ?>" alt="cross-right-arrow.png"/>
					</div>
				</div>
				<h3 class="qsm-dashboard-help-center-card-title">
					<?php echo esc_html__( 'Contact Support', 'quiz-master-next' ); ?>
				</h3>
				<p class="qsm-dashboard-help-center-card-description">
					<?php echo esc_html__( 'Comprehensive guides to help you understand and use all features of QSM Plugin.', 'quiz-master-next' ); ?>
				</p>
				</div>
			</div>
		</div>
	<?php 
}

function qsm_get_dashboard_steps_option(){
	return get_option( 'qsm_dashboard_journy', array(
		'firststep' => 0,
		'secondstep' => 0,
		'thirdstep' => 0,
		'forthstep' => 0
	) );
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
				foreach ($quizoptions_boxes as $page) {
					?>
						<div class="qsm-dashboard-page-common-style qsm-dashboard-page-item" data-type="<?php echo esc_attr( $page['type'] ); ?>">
							<h3><?php echo $page['title']; ?></h3>
							<p><?php echo $page['description']; ?></p>
						</div>
					<?php 
				}
				?>
			</div>
			<div class="qsm-dashboard-see-more-types-wrap">
				<a href="javascript:void(0)" class="button"><?php echo esc_html__('See More Types', 'quiz-master-next'); ?></a>
			</div>
		</div>
	</div> <!-- qsm-dashboard-choose-quiz-type-wrap  -->
	<?php 
}

function qsm_dashboard_display_theme_section( $all_themes, $installer_option, $invalid_and_expired, $themeBundleArray ) {
	global $mlwQuizMasterNext;
	$filtered_themes = qsm_get_filtered_dashboard_themes( $all_themes );
	
	$theme_loop_data = array();
	
	$installed_plugins = get_plugins();
	$activated_plugins = get_option('active_plugins');

	foreach ($all_themes as $theme_key => $theme_value) {
		$theme_name = $theme_value['name'];
		$theme_screenshot = $theme_value['img'];
		$theme_url = qsm_get_utm_link($theme_value['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title($theme_name));
		$theme_demo = qsm_get_utm_link($theme_value['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title($theme_name));

		// Find matching theme details in $filtered_themes by theme_name
		$matching_theme = array_filter($filtered_themes, function ($filtered_theme) use ($theme_name) {
			return isset($filtered_theme['theme_name']) && $filtered_theme['theme_name'] == $theme_name;
		});

		$matching_theme = !empty($matching_theme) ? array_shift($matching_theme) : array();
		$theme_options = array();
		if(isset($themeBundleArray[$theme_value['id']])){
			$theme_options = $themeBundleArray[$theme_value['id']];
		}

		// Add data to $theme_loop_data
		$theme_loop_data[$theme_value['id']] = array(
			'name' => $theme_name,
			'img' => $theme_screenshot,
			'link' => $theme_url,
			'demo' => $theme_demo,
			'id' => isset($matching_theme['id']) ? $matching_theme['id'] : '',
			'theme' => isset($matching_theme['theme']) ? $matching_theme['theme'] : '',
			'theme_active' => isset($matching_theme['theme_active']) ? $matching_theme['theme_active'] : '',
			'path' => isset($theme_options['path']) ? $theme_options['path'] : '',
			'slug' => isset($theme_options['slug']) ? $theme_options['slug'] : '',
			'option' => isset($theme_options['option']) ? $theme_options['option'] : '',
			'settings_tab' => isset($theme_options['settings_tab']) ? $theme_options['settings_tab'] : '',
		);
	}
	
	?>
	<div class="qsm-dashboard-choose-theme-wrap">
		<div class="qsm-dashboard-choose-theme" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Select a theme', 'quiz-master-next'); ?></h3>
				<p><?php echo esc_html__('Pick a Free or Premium theme to personalize your quiz experience.', 'quiz-master-next'); ?></p>
			</div>
			<!-- <div class="qsm-dashboard-theme-item theme-wrapper">
				<div class="qsm-dashboard-theme-item-top">
					<div class="qsm-dashboard-theme-item-left">
						<img alt="" src="<?php echo esc_url( QSM_PLUGIN_URL ) . '/assets/screenshot-default-theme.png'; ?>">
					</div>
					<div class="qsm-dashboard-theme-item-right">
						<input style="display: none" type="radio" name="quiz_theme_id" value="0" >
						<h3 class="qsm-dashboard-theme-item-title"><?php esc_html_e( 'Default Theme', 'quiz-master-next' ); ?></h3>
						<button class="qsm-dashboard-select-theme button"><?php echo esc_html_e( 'Select', 'quiz-master-next' ); ?></button>
					</div>
				</div>
			</div> -->
			<div class="qsm-quiz-page-addon qsm-addon-theme-list" >
				<div class="qsm-popular-themes" id="qsm-popular-themes">
					<div class="qsm-card-group" >
						<?php
						foreach ($theme_loop_data as $theme_id_not_db => $theme_value) { ?>
							<div class="qsm-installer-container theme-wrapper qsm-card-single" data-id="<?php echo esc_attr($theme_id_not_db); ?>">
								<div class="qsm-installer-top">
									<div class="qsm-installer-left">
										<div class="qsm-installer-image">
											<img alt="Addon Image" src="<?php echo esc_url( $theme_value['img'] ); ?>">
										</div>
									</div>
									<div class="qsm-installer-right">
										<div class="qsm-installer-paragraph">
											<div class="qsm-dashboard-theme-demo-link">
												<a target="_blank" rel="noopener" href="<?php echo esc_url( $theme_value['demo'] ); ?>">
													<?php echo esc_html__('Demo ', 'quiz-master-next'); ?> <img class="qsm-dashboard-new-tab-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/new-tab.png'); ?>" alt="new-tab.png"/>
												</a>
											</div>
										</div>
									</div>
									<input style="display: none" type="radio" name="quiz_theme_id" value="<?php echo intval( $theme_value['id'] ); ?>" >
									<div class="qsm-plugin-button-wrap">
										<h3 class="qsm-dashboard-theme-title"><?php echo esc_html( $theme_value['name']); ?></h3>
										<?php
										$is_activated = '';
										if ( isset($installed_plugins[ $theme_value['path'] ]) ) { ?>
											<?php $option_settings = wp_parse_args( get_option( $theme_value['option'], array(
												'license_key'    => '',
												'license_status' => '',
												'last_validate'  => 'invalid',
												'expiry_date'    => '',
											)));

											if ( in_array($theme_value['path'], $activated_plugins, true) ) { 
												// do nothing
											} else {
												if ( 0 == $invalid_and_expired && "" != $option_settings['license_key'] ) { ?>
												<div data-slug="<?php echo esc_attr($theme_value['slug']); ?>" class="qsm-activate-button qsm-installer-action " data-single="bundle">
													<span class="qsm-plugin-status"><?php esc_html_e( 'Deactivated', 'qsm-installer' ); ?></span>
													<button class="button button-primary"><?php esc_html_e( 'Activate', 'qsm-installer' ); ?></button>
												</div>
											<?php } else {
												if ( "" == $option_settings['license_key'] ) { ?>
													<div class="qsm-dashboard-buy-theme-button" data-class="disable" data-slug="<?php echo esc_attr($theme_value['slug']); ?>" >
														<a target="_blank" class="button button-primary" href="?page=qmn_addons&tab=<?php echo esc_attr( strtolower(str_replace(' ', '-', $theme_value['settings_tab'])) ); ?>"><?php esc_html_e( 'Settings', 'qsm-installer' ); ?></a>
													</div>
												<?php }
												}
											}
										} else {
											if ( 0 == $invalid_and_expired ) { ?>
												<div data-slug="<?php echo esc_attr($theme_value['slug']); ?>" class="qsm-installer-button qsm-installer-action " data-single="bundle">
													<span class="qsm-plugin-status"></span>
													<button class="button button-secondary"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Install & Activate', 'qsm-installer' ); ?></button>
												</div>
											<?php } else {
												if(0 == $theme_value['theme_active']) { ?>
												<span></span>
												<div data-slug="<?php echo esc_attr($theme_value['slug']); ?>" >
													<a class="button button-primary" target="_blank" href="<?php echo esc_url( $theme_value['demo'] ); ?>"><?php esc_html_e( 'Upgrade Plan', 'qsm-installer' ); ?></a>
												</div>
											<?php } 
										}} ?>
										<span style="display: none;" class="qsm-ajax-response"></span>
									</div>
									<div class="qsm-dashboard-theme-recommended"><?php echo esc_html__('Recommended', 'quiz-master-next'); ?></div>
									<div class="qsm-dashboard-theme-select-circle"><img src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/dashboard-right.png'); ?>" alt="dashboard-right.png"/></div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div><!-- qsm-dashboard-choose-theme-wrap  -->
	<div class="qsm-dashboard-see-more-types-wrap">
		<a href="javascript:void(0)" class="button qsm-dashboard-see-more-themes"><?php echo esc_html__('See More Themes', 'quiz-master-next'); ?></a>
	</div>
	<?php 
}

function qsm_dashboard_display_addons_section( $all_addons_parameter, $installer_option, $invalid_and_expired, $addonBundleArray ) {

	$addon_loop_data = array();
	$view_details = __( 'View Details', 'quiz-master-next' );
	$installed_plugins = get_plugins();
	$activated_plugins = get_option('active_plugins');

	foreach ( $all_addons_parameter as $key => $addon_value ) {
		if ( ! empty( $addon_value['tags'] ) && in_array( 831, array_column( $addon_value['tags'], 'term_id' ), true ) || in_array( $addon_value['id'], array(557086, 551029, 551027, 547794, 302299, 302297, 300658, 300513), true ) ) {
			continue;
		}
		$addon_name = $addon_value['name'];
		$addon_screenshot = $addon_value['img'];
		$addon_description = $addon_value['description'];
		$addon_link = qsm_get_utm_link( $addon_value['link'], 'addon_setting', 'popular_addon', 'addon-settings_' . sanitize_title( $addon_value['name'] ) );
		$addon_options = array();
		if(isset($addonBundleArray[$addon_value['id']])){
			$addon_options = $addonBundleArray[$addon_value['id']];
		}
		$addon_loop_data[$addon_value['id']] = array(
			'name' => $addon_name,
			'img' => $addon_screenshot,
			'link' => $addon_link,
			'description' => $addon_description,
			'path' => isset($addon_options['path']) ? $addon_options['path'] : '',
			'slug' => isset($addon_options['slug']) ? $addon_options['slug'] : '',
			'option' => isset($addon_options['option']) ? $addon_options['option'] : '',
			'settings_tab' => isset($addon_options['settings_tab']) ? $addon_options['settings_tab'] : '',
		);
	}

	?>
	<div class="qsm-dashboard-choose-addon-wrap">
		<div class="qsm-dashboard-choose-addon" >
			<div class="qsm-dashboard-page-header">
				<h3><?php echo esc_html__('Select Addons', 'quiz-master-next'); ?></h3>
				<p><?php echo esc_html__('Enhance your quiz with additional features using addons', 'quiz-master-next'); ?></p>
			</div>
			
			<div class="qsm-quiz-page-addon qsm-addon-page-list" >
				<div class="qsm-popular-addons" id="qsm-popular-addons">
					<div class="qsm-card-group" style="margin: 20px 0 ;">
						<?php
						foreach ($addon_loop_data as $addon_id_not_db => $addon_value) { ?>
							<div class="qsm-installer-container qsm-card-single" data-id="<?php echo esc_attr($addon_id_not_db); ?>">
								<div class="qsm-installer-top">
									<div class="qsm-installer-left">
										<div class="qsm-installer-image">
											<img alt="Addon Image" src="<?php echo esc_url( $addon_value['img'] ); ?>">
										</div>
									</div>
									<div class="qsm-installer-right">
										<div class="qsm-installer-paragraph">
											
										</div>
									</div>
								</div>
								<div class="qsm-plugin-button-wrap">
									<p><?php echo esc_html(mb_strlen($d = $addon_value['description']) > 110 ? mb_substr($d, 0, 110) . '...' : $d); ?></p>
								<?php
									$is_activated = '';
									$is_installed = 0;

									if ( isset($installed_plugins[ $addon_value['path'] ]) ) { ?>
										<?php
										$is_installed = 1;
										$option_settings = wp_parse_args( get_option( $addon_value['option'], array(
											'license_key'    => '',
											'license_status' => '',
											'last_validate'  => 'invalid',
											'expiry_date'    => '',
										)));

										if ( in_array($addon_value['path'], $activated_plugins, true) ) { 
											$is_activated = 'active';
										} else {
											if ( 0 == $invalid_and_expired && "" != $option_settings['license_key'] ) { ?>
											<div data-slug="<?php echo esc_attr($addon_value['slug']); ?>" class="qsm-activate-button qsm-installer-action <?php echo esc_attr($is_activated); ?>" data-single="bundle">
												<span class="qsm-plugin-status"><?php esc_html_e( 'Deactivated', 'qsm-installer' ); ?></span>
												<button class="button button-primary"><?php esc_html_e( 'Activate', 'qsm-installer' ); ?></button>
											</div>
										<?php } else {
											if ( "" == $option_settings['license_key'] ) { ?>
												<a target="_blank" class="button button-primary" href="?page=qmn_addons&tab=<?php echo esc_attr( strtolower(str_replace(' ', '-', $addon_value['settings_tab'])) ); ?>"><?php esc_html_e( 'Settings', 'qsm-installer' ); ?></a>
											<?php }
											}
										}
									} else {
										if ( 0 == $invalid_and_expired ) { ?>
											<div data-slug="<?php echo esc_attr($addon_value['slug']); ?>" class="qsm-installer-button qsm-installer-action <?php echo esc_attr($is_activated); ?>" data-single="bundle">
												<span class="qsm-plugin-status"></span>
												<button class="button button-secondary"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Install & Activate', 'qsm-installer' ); ?></button>
											</div>
										<?php } else { ?>
											<span></span>
											<div data-slug="<?php echo esc_attr($addon_value['slug']); ?>">
												<!-- Upgrade Plan Button -->
												<a class="button button-primary" target="_blank" rel="noopener" href="<?php echo esc_url($addon_value['link']); ?>">
													<?php esc_html_e('Upgrade Plan', 'qsm-installer'); ?>
												</a>
											</div>
										<?php }
									} ?>
									<span style="display: none;" class="qsm-ajax-response"></span>
								</div>
								<div class="qsm-dashboard-addon-recommended"><?php echo esc_html__('Recommended', 'quiz-master-next'); ?></div>
								<div class="qsm-dashboard-addon-select-circle <?php echo esc_attr($is_activated); ?>"><img src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/dashboard-right.png'); ?>" alt="dashboard-right.png"/></div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div><!-- qsm-dashboard-choose-addon-wrap  -->
	
	<div class="qsm-dashboard-see-more-types-wrap">
		<a href="javascript:void(0)" class="button qsm-dashboard-see-more-addons"><?php echo esc_html__('See more Addons', 'quiz-master-next'); ?></a>
	</div>
    <?php
}

function qsm_dashboard_display_quizform_section( $parameters = array()) {
	?>
		<div id="quiz_settings" class="qsm-new-menu-elements qsm-dashboard-quiz-form" >
			<div class="input-group">
				<label for="quiz_name"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?>
					<span style="color:red">*</span>
				</label>
				<input type="text" class="quiz_name" name="quiz_name" value="" required="" placeholder="<?php esc_html_e( 'Enter a name for this Quiz.', 'quiz-master-next' ); ?>">
			</div>
			<!-- <div class="input-group qsm-quiz-options-featured_image">
				<label for="quiz_name"><?php esc_html_e( 'Quiz Featured Image', 'quiz-master-next' ); ?>
				</label>
				<span id="qsm_span">
					<input type="text" class="quiz_featured_image" name="quiz_featured_image" value="">
					<a id="set_featured_image" class="button "><?php esc_html_e( 'Set Featured Image', 'quiz-master-next' ); ?></a>
				</span>
				<span class="qsm-opt-desc"><?php esc_html_e( 'Enter an external URL or Choose from Media Library. Can be changed further from style tab', 'quiz-master-next' ); ?></span>
			</div> -->
			
			<div id="qsm-settings-content" style="display: none;">
				<?php qsm_settings_to_create_quiz(); ?>
			</div>
			<a href="javascript:void(0)" id="show-more-button" class="qsm-dashboard-show-more-settings">
				<?php esc_html_e('More Settings', 'quiz-master-next'); ?>
			</a>

		</div>
	<?php 
}


function qsm_create_quiz_page_callback() {

	
    global $mlwQuizMasterNext;
	$qsm_admin_dd = wp_remote_get(QSM_PLUGIN_URL . 'data/parsing_script.json', [ 'sslverify' => false ]);
	$qsm_admin_dd = json_decode(wp_remote_retrieve_body($qsm_admin_dd), true);

	$qsm_admin_dashboard = wp_remote_get(QSM_PLUGIN_URL . 'data/dashboard.json', [ 'sslverify' => false ]);
	$qsm_admin_dashboard = json_decode(wp_remote_retrieve_body($qsm_admin_dashboard), true);
	
	wp_localize_script( 'qsm_admin_js', 'qsm_admin_dashboard_suggestions', array(
		'themes' => $qsm_admin_dashboard['suggested_themes'],
		'addons' => $qsm_admin_dashboard['suggested_addons']
	) );
	$installer_option = array();
	$invalid_and_expired = 1;
	$selected_bundle         = '';
	// Check if the class QSM_Installer exists
	if (class_exists('QSM_Installer')) {
		// Retrieve the installer options
		$installer_option = QSM_Installer::get_installer_option();
		
		// Validate bundle and expiry date
		if ( isset($installer_option['bundle']) && isset($installer_option['license_status']) && 'valid' == $installer_option['license_status'] && isset($installer_option['license_key']) && '' != $installer_option['license_key']) {
			// If valid, set invalid_and_expired to 0
			
			$invalid_and_expired = 0;
			wp_enqueue_style( 'qsm_installer_admin_style', QSM_INSTALLER_PLUGIN_URL.'css/qsm-installer-admin.css', array(),  QSM_INSTALLER_VERSION);
			wp_enqueue_script( 'qsm_installer_admin_script', QSM_INSTALLER_PLUGIN_URL.'js/qsm-installer-admin.js', array( 'jquery' ), QSM_INSTALLER_VERSION, true );
			$settings_data   = QSM_Installer::get_installer_option();
			$bundle_license_key     = isset( $settings_data['license_key'] ) ? trim( $settings_data['license_key'] ) : '';
			wp_localize_script('qsm_installer_admin_script', 'qsm_installer_js', array(
				'ajaxurl'            => admin_url('admin-ajax.php'),
				'nonce'              => wp_create_nonce('qsm_installer_nonce'),
				'install'            => __('Installing...', 'qsm-installer'),
				'activate'           => __('Activating Plugin...', 'qsm-installer'),
				'deactivate'         => __('Deactivating...', 'qsm-installer'),
				'checkupdate'        => __('Checking for updates...', 'qsm-installer'),
				'update'             => __('Updating plugin...', 'qsm-installer'),
				'installbtn'         => __('Install',  'qsm-installer'),
				'activebtn'          => __('Activate',  'qsm-installer'),
				'deactivatebtn'      => __('Deactivate',  'qsm-installer'),
				'deactivated'        => __('Deactivated',  'qsm-installer'),
				'activated'          => __('Activated',  'qsm-installer'),
				'checkupdatebtn'     => __('Check For Update',  'qsm-installer'),
				'updatebtn'          => __('Update Now',  'qsm-installer'),
				'updated'            => __('Updated',  'qsm-installer'),
				'install_key_error'  => __('Please check your license key',  'qsm-installer'),
				'license_key'        => $bundle_license_key,
				'change_license'     => __('Change key',  'qsm-installer'),
				'userValidationAjax' => QSM_INSTALLER_API_URL.'/wp-json/validate-license/v1/verify',
				'invalid'            => __('Please enter a valid license key.', 'qsm-installer'),
				'hold'               => __('Please Wait! We are validating your license', 'qsm-installer'),
				'empty'              => __('Please enter a license key.', 'qsm-installer'),
				'try_again'          => __('Try Again', 'qsm-installer'),
				'view_details'       => __('View Details', 'qsm-installer'),
			));
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QSM Dashboard', 'quiz-master-next' ); ?></h1>
		<div class="qsm-dashboard-wrapper">
			<div class="qsm-dashboard-header">
				<div class="qsm-dashboard-header-pagination">
					<a href="javascript:void(0)" class="qsm-dashboard-journy-previous-step"><img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/left-arrow.png'); ?>" alt="left-arrow.png"/><?php echo esc_html__('Back', 'quiz-master-next'); ?></a>
					<a href="javascript:void(0)" class="qsm-dashboard-journy-next-step"><?php echo esc_html__('Skip this', 'quiz-master-next'); ?></a>
					<a href="javascript:void(0)" class="qsm-dashboard-journy-next-step-proceed button-primary"><?php echo esc_html__('Proceed', 'quiz-master-next'); ?></a>
					<a style="display: none;" id="create-quiz-button" href="javascript:void(0)" class="qsm-dashboard-journy-create-quiz button-primary"><?php echo esc_html__('Create Quiz', 'quiz-master-next'); ?></a>
				</div>
				<div class="qsm-dashboard-header-info"></div>
			</div>
			<div class="qsm-dashboard-container">
				<form action="" method="post" id="new-quiz-form">
					<div class="qsm-form-inside-container" id="qsm-add-installer">
						<?php wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' );
						$quizoptions_boxes = $qsm_admin_dashboard['quizoptions'];
						$all_addons = qsm_get_widget_data( 'all_addons' );
						$all_themes = $qsm_admin_dd['themes'];
						
						$dashboard_pages = [
							['page_no' => 1, 'callback' => 'qsm_dashbord_display_quizoptions_section', 'params' => [$quizoptions_boxes]],
							['page_no' => 2, 'callback' => 'qsm_dashboard_display_theme_section', 'params' => [$all_themes, $installer_option, $invalid_and_expired, $qsm_admin_dashboard['themes']]],
							['page_no' => 3, 'callback' => 'qsm_dashboard_display_addons_section', 'params' => [$all_addons, $installer_option, $invalid_and_expired, $qsm_admin_dashboard['addons']]],
							['page_no' => 4, 'callback' => 'qsm_dashboard_display_quizform_section', 'params' => []],
						];

						foreach ($dashboard_pages as $page) {
							echo '<div class="qsm-dashboard-container-pages" data-page-no="' . esc_attr($page['page_no']) . '" style="display: none;">';

							if (function_exists($page['callback'])) {
								call_user_func_array($page['callback'], $page['params']);
							}

							echo '</div>';
						}
						?>
					</div>
				</form>
				<?php 
				// Other function to call
				qsm_dashboard_display_need_help_section();
				?>
			</div><!-- qsm-dashboard-container -->
		</div><!-- qsm-dashboard-wrapper -->
	</div>
	<?php

}