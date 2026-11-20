<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Add AJAX action for logged-in users
add_action('wp_ajax_qsm_activate_plugin', 'qsm_activate_plugin_ajax_activate_plugin');

function qsm_activate_plugin_ajax_activate_plugin() {
    // Check if the user has permission to activate plugins
    if ( ! current_user_can('activate_plugins') ) {
        wp_send_json_error(array( 'message' => 'Permission denied.' ));
        wp_die();
    }
    check_ajax_referer('qsm_installer_nonce', 'nonce');
    if ( empty($_POST['plugin_path']) ) {
        wp_send_json_error(array( 'message' => 'No plugin path provided.' ));
        wp_die();
    }
    $plugin_path = isset($_POST['plugin_path']) ? sanitize_text_field(wp_unslash( $_POST['plugin_path'] ) ) : "";
    $result = activate_plugin($plugin_path);
	wp_send_json_success(array( 'message' => 'Plugin activated successfully.' ));
    wp_die();
}

function qsm_activate_plugin_ajax_handler() {
    // Check if the user has permission to activate plugins
    if ( ! current_user_can('activate_plugins') ) {
        wp_send_json_error(array( 'message' => 'Permission denied.' ));
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
		wp_send_json_success(array( 'version' => esc_html__('Version: ', 'quiz-master-next') . $all_plugins[ $plugin_path ]['Version'] ));
	} else {
		wp_send_json_error(array( 'message' => 'Plugin not found.' ));
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
	wp_send_json_success(array( 'id' => $id ));
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
	$filtered_active_themes = array();
	if ( ! empty($active_themes) ) {
		foreach ( $active_themes as $theme ) {
			$theme_dir = WP_PLUGIN_DIR . '/' . $theme['theme'];
			if ( is_dir($theme_dir) ) {
				$filtered_active_themes[] = $theme;
			}
		}
	}

	// Merge installed themes and filtered active themes
	$merged_themes = array();
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
								<a href="javascript:void(0)" class="qsm-theme-action-btn button button-secondary qsm-dashboard-demo-button" disabled="disabled"><?php echo esc_html__( 'Selected', 'quiz-master-next' ); ?></a>
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
								$theme_value = array_merge($theme_value, array(
									'path'         => $addon['path'],
									'icon'         => $addon['icon'],
									'settings_tab' => $addon['settings_tab'],
									'option'       => $addon['option'],
								));
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
											<a href="javascript:void(0)" class="qsm-theme-action-btn button button-secondary qsm-dashboard-demo-button">
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
					// The 16:9 banner the QSM Installer's Extensions page shows; the small icon only when there is none.
					$addon_banner = ! empty( $addon_value['img'] ) ? $addon_value['img'] : '';
					$is_installed = array_key_exists($addon_path, $installed_plugins);
					$is_activated = in_array($addon_path, $activated_plugins, true);
					$addon_card_class = $is_activated ? "qsm-quiz-addon-activated" : "";
					$addon_status = '';
					if ( true == $is_activated || true == $is_installed ) {
						$addon_status = esc_html__( 'Version: ', 'quiz-master-next' ). $all_plugins[ $addon_path ]['Version'];
					} elseif ( false == $is_activated && false == $is_installed && (1 == $installer_activated && 0 == $invalid_and_expired) ) {
						$addon_status = esc_html__( 'Install & Activate', 'quiz-master-next' );
					}
					?>
					<div class="qsm-quiz-addon-steps-card <?php echo esc_attr($addon_card_class); ?>" data-path="<?php echo esc_attr($addon_path); ?>" data-id="<?php echo esc_attr($addon_id); ?>" data-slug="<?php echo esc_attr($addon_slug); ?>">
						<div class="qsm-quiz-addon-steps-images<?php echo $addon_banner ? ' has-banner' : ''; ?>">
							<img class="qsm-quiz-addon-steps-icon" alt="<?php echo esc_attr( $addon_name ); ?>" loading="lazy" src="<?php echo esc_url( $addon_banner ? $addon_banner : $addon_icon ); ?>">
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
		<button type="button" class="button qsm-dashboard-see-more-addons"><?php esc_html_e( 'View more add-ons', 'quiz-master-next' ); ?><?php echo QSM_Admin_UI::icon( 'chev', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></button>
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


/**
 * Renders one "Additional settings" field from qsm_settings_to_create_quiz() with the admin UI kit.
 *
 * Types the kit has no control for (an add-on may add them through the
 * qsm_quiz_wizard_settings_option filter) fall back to QSM_Fields.
 *
 * @since 11.4.0
 * @param array $setting Field array (id, label, type, value, default, options, help).
 * @return void
 */
function qsm_create_quiz_render_setting_field( $setting ) {
	$id    = 'qsm-cq-' . $setting['id'];
	$value = isset( $setting['value'] ) && '' !== $setting['value'] ? $setting['value'] : ( isset( $setting['default'] ) ? $setting['default'] : '' );
	$help  = isset( $setting['help'] ) ? $setting['help'] : '';
	$type  = isset( $setting['type'] ) ? $setting['type'] : '';
	switch ( $type ) {
		case 'toggle':
			$on = isset( $setting['options'][0]['value'] ) ? $setting['options'][0]['value'] : 1;
			echo '<div class="qsm-cq-setting">' . QSM_Admin_UI::toggle( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'name'        => $setting['id'],
					'id'          => $id,
					'value'       => (string) $on,
					'checked'     => (string) $on === (string) $value,
					'label'       => $setting['label'],
					'description' => $help,
				)
			) . '</div>';
			return;
		case 'number':
			$control = QSM_Admin_UI::input(
				array(
					'type'  => 'number',
					'name'  => $setting['id'],
					'id'    => $id,
					'value' => $value,
					'width' => 'sm',
					'attrs' => array( 'min' => '0' ),
				)
			);
			break;
		case 'select':
			$options = array();
			foreach ( (array) $setting['options'] as $option ) {
				$options[ $option['value'] ] = $option['label'];
			}
			$control = QSM_Admin_UI::select(
				array(
					'name'     => $setting['id'],
					'id'       => $id,
					'options'  => $options,
					'selected' => $value,
					'width'    => 'md',
				)
			);
			break;
		default:
			echo '<div class="qsm-cq-setting input-group" id="qsm-quiz-options-' . esc_attr( $setting['id'] ) . '">';
			QSM_Fields::generate_field( $setting, $value );
			echo '</div>';
			return;
	}
	echo '<div class="qsm-cq-setting">' . QSM_Admin_UI::field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'label'   => $setting['label'],
			'for'     => $id,
			'control' => $control,
			'help'    => $help,
		)
	) . '</div>';
}

/**
 * The two ways to create a quiz, as clickable cards. Shown on the QSM dashboard;
 * each card opens the Create Quiz page on that view.
 *
 * @since 11.4.0
 * @return void
 */
function qsm_create_quiz_render_choice_cards() {
	$page_url = admin_url( 'admin.php?page=qsm_create_quiz_page' );
	$choices  = array(
		'custom'   => array(
			'label' => __( 'Start from scratch', 'quiz-master-next' ),
			'text'  => __( 'Start from a blank quiz and set it up yourself.', 'quiz-master-next' ),
			'items' => array(
				__( 'Name your quiz', 'quiz-master-next' ),
				__( 'Pick the quiz type and grading system', 'quiz-master-next' ),
				__( 'Choose a theme', 'quiz-master-next' ),
			),
			'cta'   => __( 'Start from scratch', 'quiz-master-next' ),
			'badge' => __( 'Blank quiz', 'quiz-master-next' ),
		),
		'template' => array(
			'label' => __( 'Use a template', 'quiz-master-next' ),
			'text'  => __( 'Start from a ready-made quiz or survey and import it in one click.', 'quiz-master-next' ),
			'items' => array(
				__( 'Browse demos by category', 'quiz-master-next' ),
				__( 'Preview a demo before you import it', 'quiz-master-next' ),
				__( 'See which add-ons a template uses and turn them on before you import', 'quiz-master-next' ),
			),
			'cta'   => __( 'Browse templates', 'quiz-master-next' ),
			'badge' => __( 'One-click import', 'quiz-master-next' ),
		),
	);
	?>
	<div class="qsm-cq-choices">
		<?php foreach ( $choices as $view => $choice ) { ?>
			<a class="qsm-cq-choice" data-qsm-cq-choice="<?php echo esc_attr( $view ); ?>" href="<?php echo esc_url( add_query_arg( 'view', $view, $page_url ) ); ?>" aria-label="<?php echo esc_attr( $choice['label'] ); ?>">
				<span class="qsm-cq-choice-body">
					<span class="qsm-cq-choice-top"><?php echo QSM_Admin_UI::badge( $choice['badge'], 'template' === $view ? 'info' : '', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
					<span class="qsm-cq-choice-text"><?php echo esc_html( $choice['text'] ); ?></span>
					<span class="qsm-cq-choice-list">
						<?php foreach ( $choice['items'] as $item ) { ?>
							<span><?php echo QSM_Admin_UI::icon( 'check', 'qsm-ui-icon-sm' ) . esc_html( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
						<?php } ?>
					</span>
					<span class="qsm-cq-choice-actions">
						<span class="qsm-ui-btn <?php echo 'template' === $view ? 'qsm-ui-btn-primary' : ''; ?> qsm-cq-choice-cta"><?php echo esc_html( $choice['cta'] ); ?> &rarr;</span>
					</span>
				</span>
			</a>
		<?php } ?>
	</div>
	<?php
}

/**
 * Start-from-scratch view: quiz name, quiz type, grading system, theme, then the optional settings.
 *
 * @since 11.4.0
 * @param array $quizoptions_boxes Quiz types from data/dashboard.json.
 * @param array  $theme_args        Arguments for qsm_dashboard_display_theme_section(), or empty when the theme list is unavailable.
 * @param string $view              Current view, so the other one starts hidden.
 * @param string $back_url          Where Cancel goes.
 * @param array  $addon_args        Arguments for qsm_dashboard_display_addons_section(), or empty when the add-on list is unavailable.
 * @return void
 */
function qsm_create_quiz_render_custom_view( $quizoptions_boxes, $theme_args, $view = 'custom', $back_url = '', $addon_args = array() ) {
	// Type and grading are asked up front, so leave them out of the additional settings.
	$settings = qsm_settings_to_create_quiz( true );
	$grading  = array();
	foreach ( (array) $settings as $index => $setting ) {
		if ( in_array( $setting['id'], array( 'form_type', 'system' ), true ) ) {
			if ( 'system' === $setting['id'] ) {
				$grading = $setting;
			}
			unset( $settings[ $index ] );
		}
	}
	$grading_value = isset( $grading['value'] ) && '' !== $grading['value'] ? (string) $grading['value'] : '0';
	$type_options  = array();
	foreach ( $quizoptions_boxes as $box ) {
		$type_options[ $box['id'] ] = array(
			'label'       => $box['title'],
			'description' => $box['description'],
			'icon'        => 'survey' === $box['type'] ? 'survey' : ( 'form' === $box['type'] ? 'form' : 'quiz' ),
		);
	}
	?>
	<div class="qsm-cq-view" data-qsm-cq-view="custom"<?php echo 'template' === $view ? ' hidden' : ''; ?>>
		<?php
		wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' );
		echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'title'       => __( 'Quiz details', 'quiz-master-next' ),
				'description' => __( 'Give your quiz a name. You can change it later.', 'quiz-master-next' ),
			)
		);
		echo QSM_Admin_UI::field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'label'    => __( 'Quiz name', 'quiz-master-next' ),
				'for'      => 'qsm-cq-quiz-name',
				'required' => true,
				'control'  => QSM_Admin_UI::input(
					array(
						'name'        => 'quiz_name',
						'id'          => 'qsm-cq-quiz-name',
						'class'       => 'quiz_name',
						'placeholder' => __( 'e.g. Product knowledge check', 'quiz-master-next' ),
						'attrs'       => array( 'aria-describedby' => 'qsm-cq-quiz-name-msg' ),
					)
				),
				'class'    => 'qsm-cq-name-field',
			)
		);
		echo '<p class="qsm-ui-caption qsm-cq-name-error" id="qsm-cq-quiz-name-msg" hidden>' . esc_html__( 'Enter a name for your quiz.', 'quiz-master-next' ) . '</p>';
		echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.

		if ( ! empty( $addon_args ) ) {
			echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'title'       => __( 'Add-ons', 'quiz-master-next' ),
					'description' => __( 'Optional. Turn on add-ons to add features to your quiz. Add-ons that suit the quiz type are marked Recommended.', 'quiz-master-next' ),
					'class'       => 'qsm-cq-addon-card',
				)
			);
			call_user_func_array( 'qsm_dashboard_display_addons_section', $addon_args );
			echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		}

		if ( ! empty( $theme_args ) ) {
			// The bundle offer sits in the card header so it is seen before the theme list.
			$bundle_link = qsm_get_utm_link( 'https://quizandsurveymaster.com/pricing/', 'create_quiz', 'bundles', 'new-quiz-steps' );
			echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'title'       => __( 'Theme', 'quiz-master-next' ),
					'description' => __( 'Pick a free or premium theme. Themes that suit the quiz type are marked Recommended.', 'quiz-master-next' ),
					'class'       => 'qsm-cq-theme-card',
					'actions'     => '<a class="qsm-cq-bundle-link" href="' . esc_url( $bundle_link ) . '" target="_blank" rel="noopener" title="' . esc_attr__( 'The bundle includes every theme and add-on.', 'quiz-master-next' ) . '">' . QSM_Admin_UI::icon( 'star', 'qsm-ui-icon-sm' ) . esc_html__( 'Grab the Bundle & Save 90%', 'quiz-master-next' ) . '</a>',
				)
			);
			call_user_func_array( 'qsm_dashboard_display_theme_section', $theme_args );
			echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		}

		echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'title'       => __( 'Additional settings', 'quiz-master-next' ),
				'description' => __( 'Optional. Quiz type, grading system, timer, pagination, login and contact form.', 'quiz-master-next' ),
				'actions'     => QSM_Admin_UI::toggle(
					array(
						'id'         => 'qsm-cq-more-toggle',
						'aria_label' => __( 'Show additional settings', 'quiz-master-next' ),
						'attrs'      => array( 'aria-controls' => 'qsm-cq-more' ),
					)
				),
				'body_id'     => 'qsm-cq-more',
				'class'       => 'qsm-cq-more-card',
			)
		);
		// Quiz type and grading come first; they default to a graded quiz with Correct / Incorrect scoring.
		echo '<div class="qsm-cq-type-grading">';
		if ( ! empty( $type_options ) ) {
			reset( $type_options );
			echo QSM_Admin_UI::field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'label'   => __( 'Quiz type', 'quiz-master-next' ),
					'control' => QSM_Admin_UI::radio_cards(
						array(
							'name'     => 'qsm_quiz_type',
							'options'  => $type_options,
							'selected' => key( $type_options ),
							'attrs'    => array( 'class' => 'qsm-cq-type' ),
						)
					),
					'class'   => 'qsm-cq-types',
				)
			);
		}
		?>
		<input type="hidden" name="form_type" value="0">
		<?php
		echo QSM_Admin_UI::field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'label'   => __( 'Grading system', 'quiz-master-next' ),
				'help'    => __( 'Surveys and simple forms are not graded.', 'quiz-master-next' ),
				'control' => QSM_Admin_UI::radio_cards(
					array(
						'name'     => 'system',
						'selected' => $grading_value,
						'options'  => array(
							'0' => array(
								'label'       => __( 'Correct / Incorrect', 'quiz-master-next' ),
								'description' => __( 'Score is the number of right answers.', 'quiz-master-next' ),
							),
							'1' => array(
								'label'       => __( 'Points', 'quiz-master-next' ),
								'description' => __( 'Each answer is worth points.', 'quiz-master-next' ),
							),
							'3' => array(
								'label'       => __( 'Both', 'quiz-master-next' ),
								'description' => __( 'Track right answers and points.', 'quiz-master-next' ),
							),
						),
					)
				),
				'class'   => 'qsm-cq-grading',
			)
		);
		echo '</div>';
		echo '<div class="qsm-cq-settings-grid">';
		foreach ( $settings as $setting ) {
			qsm_create_quiz_render_setting_field( $setting );
		}
		echo '</div>';
		echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		?>
		<div class="qsm-cq-actionbar">
			<?php
			echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				__( 'Cancel', 'quiz-master-next' ),
				array( 'href' => $back_url ? $back_url : admin_url( 'admin.php?page=qsm_dashboard' ) )
			);
			// id and class are what qsm-admin.js binds the submit to.
			echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				__( 'Create quiz & add questions', 'quiz-master-next' ),
				array(
					'variant' => 'primary',
					'class'   => 'qsm-dashboard-journy-create-quiz',
					'attrs'   => array( 'id' => 'create-quiz-button' ),
				)
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * Use a template view. The QSM Installer add-on prints the demo browser on the
 * qsm_create_quiz_templates action; without it the view says how to get it.
 *
 * @since 11.4.0
 * @param string $view Current view, so this one starts hidden on the custom view.
 * @return void
 */
function qsm_create_quiz_render_template_view( $view = 'custom' ) {
	?>
	<div class="qsm-cq-view" data-qsm-cq-view="template"<?php echo 'template' === $view ? '' : ' hidden'; ?>>
		<?php
		if ( has_action( 'qsm_create_quiz_templates' ) ) {
			/**
			 * Prints the quiz template (demo) browser inside the Create Quiz page.
			 *
			 * @since 11.4.0
			 */
			do_action( 'qsm_create_quiz_templates' );
		} else {
			// Same card as the start screen and the template grid.
			?>
			<div class="qsm-cq-choices qsm-cq-fallback">
				<div class="qsm-cq-choice" data-qsm-cq-fallback>
					<span class="qsm-cq-choice-frame">
						<span class="qsm-cq-choice-media">
							<span class="qsm-cq-choice-icon"><?php echo QSM_Admin_UI::icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
						</span>
					</span>
					<span class="qsm-cq-choice-body">
						<span class="qsm-cq-choice-top"><?php echo QSM_Admin_UI::badge( __( 'Add-on required', 'quiz-master-next' ), 'warning', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></span>
						<span class="qsm-ui-h2 qsm-cq-choice-title"><?php esc_html_e( 'Templates need the QSM Installer add-on', 'quiz-master-next' ); ?></span>
						<span class="qsm-cq-choice-text"><?php esc_html_e( 'Install and activate the QSM Installer add-on (version 2.2.0 or newer) to browse and import ready-made quizzes and surveys.', 'quiz-master-next' ); ?></span>
						<span class="qsm-cq-choice-actions">
							<?php
							echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
								__( 'Start from scratch', 'quiz-master-next' ),
								array( 'attrs' => array( 'data-qsm-cq-go' => 'custom' ) )
							);
							echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
								__( 'Get QSM Installer', 'quiz-master-next' ),
								array(
									'variant' => 'primary',
									'href'    => qsm_get_utm_link( 'https://quizandsurveymaster.com/pricing/', 'create_quiz', 'templates', 'installer-required' ),
									'attrs'   => array( 'target' => '_blank' ),
								)
							);
							?>
						</span>
					</span>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	</div>
	<?php
}

/**
 * Asset version for the Create Quiz page: plugin version plus the file's modified time,
 * so a changed script or stylesheet is never served from the browser cache.
 *
 * @since 11.4.0
 * @param string $file Path relative to the plugin folder, e.g. 'js/qsm-create-quiz-script.js'.
 * @return string
 */
function qsm_create_quiz_asset_version( $file ) {
	global $mlwQuizMasterNext;
	$mtime = file_exists( QSM_PLUGIN_PATH . $file ) ? filemtime( QSM_PLUGIN_PATH . $file ) : 0;
	return $mlwQuizMasterNext->version . ( $mtime ? '.' . $mtime : '' );
}

/**
 * Help menu for the page headers (Create Quiz, Dashboard): the "i" button and the
 * Useful Resources list, same links and icons as the quiz edit page.
 *
 * @since 11.4.0
 * @param string $campaign UTM source for the links, e.g. create_quiz or dashboard.
 * @return void
 */
function qsm_admin_render_help_menu( $campaign = 'create_quiz' ) {
	$doc_icon   = '<span class="dashicons dashicons-media-document"></span>';
	$help_links = array(
		array( __( 'Get support', 'quiz-master-next' ), qsm_get_plugin_link( 'contact-support', $campaign, 'help_box', $campaign . '_support' ), '<img class="qsm-help-tab-icon" alt="" src="' . esc_url( QSM_PLUGIN_URL . 'assets/Support.svg' ) . '">' ),
		array( __( 'Getting Started', 'quiz-master-next' ), qsm_get_plugin_link( 'docs/about-quiz-survey-master/quick-start', $campaign, 'help_box', 'getting_started' ), $doc_icon ),
		array( __( 'Creating Quizzes', 'quiz-master-next' ), qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys', $campaign, 'help_box', $campaign . '_docs' ), $doc_icon ),
		array( __( 'QSM Themes', 'quiz-master-next' ), qsm_get_plugin_link( 'docs/qsm-themes', $campaign, 'help_box', $campaign . '_themes' ), $doc_icon ),
		array( __( 'All Docs', 'quiz-master-next' ), qsm_get_plugin_link( 'docs', $campaign, 'help_box', $campaign . '_all_doc' ), $doc_icon ),
	);
	?>
	<div class="qsm-ui-help-menu">
		<?php
		echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			__( 'Help', 'quiz-master-next' ),
			array(
				'icon'      => 'info',
				'icon_only' => true,
				'class'     => 'qsm-help-tab-handle qsm-ui-help-toggle',
				'attrs'     => array(
					'title'         => __( 'Help', 'quiz-master-next' ),
					'aria-haspopup' => 'true',
				),
			)
		);
		?>
		<div class="qsm-help-tab-dropdown-list qsm-ui-help-list">
			<h3 class="qsm-ui-help-title"><?php esc_html_e( 'Useful Resources', 'quiz-master-next' ); ?></h3>
			<?php foreach ( $help_links as $link ) { ?>
				<a href="<?php echo esc_url( $link[1] ); ?>" rel="noopener" target="_blank" class="qsm-help-tab-item qsm-ui-help-item"><?php echo $link[2]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup, URL escaped above. ?> <?php echo esc_html( $link[0] ); ?><?php echo QSM_Admin_UI::icon( 'chev', 'qsm-ui-icon-sm qsm-ui-help-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></a>
			<?php } ?>
		</div>
	</div>
	<?php
}

/**
 * Page header, styled like the quiz edit frame header: the title of the current view,
 * a "switch" action and the Help menu (same links and icons as the quiz edit page).
 *
 * @since 11.4.0
 * @param string $view   Current view: custom or template.
 * @param array  $titles view => title.
 * @return void
 */
function qsm_create_quiz_render_header( $view, $titles ) {
	?>
	<div class="qsm-ui qsm-ui-quiz-frame-header qsm-cq-header">
		<div class="qsm-ui-page-header qsm-ui-text">
			<div class="qsm-ui-quiz-frame-heading">
				<?php foreach ( $titles as $key => $title ) { ?>
					<div class="qsm-ui-h1 qsm-ui-quiz-title qsm-cq-title" role="heading" aria-level="1" data-qsm-cq-title="<?php echo esc_attr( $key ); ?>"<?php echo $key === $view ? '' : ' hidden'; ?>><?php echo esc_html( $title ); ?></div>
				<?php } ?>
			</div>
			<div class="qsm-ui-page-actions">
				<?php
				echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
					__( 'Use a template instead', 'quiz-master-next' ),
					array(
						'size'  => 'sm',
						'icon'  => 'download',
						'class' => 'qsm-cq-switch',
						'attrs' => array(
							'data-qsm-cq-go'   => 'template',
							'data-qsm-cq-only' => 'custom',
							'hidden'           => 'custom' !== $view,
						),
					)
				);
				echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
					__( 'Start from scratch instead', 'quiz-master-next' ),
					array(
						'size'  => 'sm',
						'icon'  => 'edit',
						'class' => 'qsm-cq-switch',
						'attrs' => array(
							'data-qsm-cq-go'   => 'custom',
							'data-qsm-cq-only' => 'template',
							'hidden'           => 'template' !== $view,
						),
					)
				);
				?>
				<div class="qsm-ui-quiz-frame-tools">
						<?php qsm_admin_render_help_menu( 'create_quiz' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function qsm_create_quiz_page_callback() {
	global $mlwQuizMasterNext;

	wp_enqueue_script( 'qsm-create-quiz-script', QSM_PLUGIN_JS_URL . '/qsm-create-quiz-script.js', array( 'jquery', 'qsm_admin_ui_js' ), qsm_create_quiz_asset_version( 'js/qsm-create-quiz-script.js' ), true );
	wp_enqueue_style( 'qsm-create-quiz-style', QSM_PLUGIN_CSS_URL . '/qsm-create-quiz-style.css', array( 'qsm_admin_ui_css' ), qsm_create_quiz_asset_version( 'css/qsm-create-quiz-style.css' ) );
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
	// The choice between the two views is made on the dashboard; the page opens on the custom form by default.
	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'custom'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
	$view = 'template' === $view ? 'template' : 'custom';
	wp_localize_script( 'qsm-create-quiz-script', 'qsm_admin_new_quiz', array(
		'quizoptions'         => $quizoptions_boxes,
		'installed'           => $installed_plugins,
		'activated'           => $activated_plugins,
		'ajaxurl'             => admin_url('admin-ajax.php'),
		'nonce'               => wp_create_nonce('qsm_installer_nonce'),
		'installer_option'    => $installer_option,
		'installer_activated' => $installer_activated,
		'invalid_and_expired' => $invalid_and_expired,
		'view'                => $view,
		'process'             => __('Processing...', 'quiz-master-next'),
		'installing'          => __('Installing...', 'quiz-master-next'),
		'activating'          => __('Activating...', 'quiz-master-next'),
		'activated_text'      => __('Activated', 'quiz-master-next'),
		'select'              => __('Select', 'quiz-master-next'),
		'selected'            => __('Selected', 'quiz-master-next'),
		'retry'               => __('Retry', 'quiz-master-next'),
	) );

	$titles = array(
		'custom'   => __( 'Create from scratch', 'quiz-master-next' ),
		'template' => __( 'Use a template', 'quiz-master-next' ),
	);
	$theme_args = array();
	$addon_args = array();
	if ( $qsm_admin_dd ) {
		$theme_args = array( $qsm_admin_dd['themes'], $installer_option, $invalid_and_expired, $qsm_admin_dd['all_addons'], $installer_activated, $installer_script );
		$addon_args = array( $qsm_admin_dd['all_addons'], $installer_option, $invalid_and_expired, $installer_activated, $installer_script );
	}
	$last_visited_page = ! empty( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	if ( '' === $last_visited_page || false !== strpos( $last_visited_page, 'page=qsm_create_quiz_page' ) ) {
		$last_visited_page = admin_url( 'admin.php?page=qsm_dashboard' );
	}

	?>
	<div class="wrap qsm-cq-wrap">
		<?php qsm_create_quiz_render_header( $view, $titles ); ?>
	<div class="qsm-ui qsm-ui-text qsm-cq" data-qsm-cq-current="<?php echo esc_attr( $view ); ?>">
		<form action="" method="post" id="new-quiz-form">
			<?php qsm_create_quiz_render_custom_view( $quizoptions_boxes, $theme_args, $view, $last_visited_page, $addon_args ); ?>
		</form>
		<?php
		qsm_create_quiz_render_template_view( $view );
		if ( ! $qsm_admin_dd ) {
			qsm_display_fullscreen_error();
		}
		?>
	</div>
	</div>
	<?php
}
