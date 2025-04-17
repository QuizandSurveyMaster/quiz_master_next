<?php
/**
 * Handles the functions/views for the "Style" tab when editing a quiz or survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function adds the inline scripts for quiz options style tab
 *
 * @since 7.3.5
 */

/**
 * Adds the Style tab to the Quiz Settings page.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_settings_style_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Style', 'quiz-master-next' ), 'qsm_options_styling_tab_content', 'style' );
}
add_action( 'init', 'qsm_settings_style_tab', 5 );

/**
 * Adds the Style tab content to the tab.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_options_styling_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;

	if ( isset( $_POST['qsm_style_tab_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_style_tab_nonce'] ) ), 'qsm_style_tab_nonce_action' ) ) {

		$quiz_id  = isset( $_GET['quiz_id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['quiz_id'] ) ) : '';
		$quiz_theme    = isset( $_POST['save_quiz_theme'] ) ? sanitize_text_field( wp_unslash( $_POST['save_quiz_theme'] ) ) : '';
		$quiz_style    = isset( $_POST['quiz_css'] ) ? htmlspecialchars( preg_replace( '#<script(.*?)>(.*?)</script>#is', '', sanitize_textarea_field( wp_unslash( $_POST['quiz_css'] ) ) ), ENT_QUOTES, 'UTF-8', false) : '';

		// Saves the new css.
		$results = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mlw_quizzes SET quiz_stye=%s, theme_selected=%s, last_activity=%s WHERE quiz_id=%d", $quiz_style, $quiz_theme, gmdate( 'Y-m-d H:i:s' ), $quiz_id ) );
		if ( false !== $results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The style has been saved successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved", $quiz_id, "" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Error occured when trying to save the styles. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error saving styles', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}
	do_action( 'qsm_save_style_section' );

	if ( isset( $_GET['quiz_id'] ) ) {
		$quiz_id = intval( $_GET['quiz_id'] );
		$mlw_quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
	}
	$registered_templates = $mlwQuizMasterNext->pluginHelper->get_quiz_templates();
	?>

	<div class="qsm-sub-tab-menu" style="display: inline-block;width: 100%;">
		<ul class="subsubsub">
			<li>
				<a href="javascript:void(0)" data-id="theme-browser" class="current quiz_style_tab"><?php esc_html_e( 'Themes', 'quiz-master-next' ); ?></a>
			</li>
			<?php
			if ( ! class_exists( 'QSM_Ultimate' ) ) {
				?>
				<li>
					<a href="javascript:void(0)" data-id="qsm-ultimate-upgrade" class="quiz_style_tab"><?php esc_html_e( 'Appearance', 'quiz-master-next' ); ?></a>
				</li>
				<?php
			}
			?>
			<?php do_action( 'qsm_add_style_sub_menu_after' ); ?>
			<li>
				<a href="javascript:void(0)" data-id="theme-featured-image" class="quiz_style_tab"><?php esc_html_e( 'Featured Image', 'quiz-master-next' ); ?></a>
			</li>
			<li>
				<a href="javascript:void(0)" data-id="custom_css" class="quiz_style_tab"><?php esc_html_e( 'CSS', 'quiz-master-next' ); ?></a>
			</li>
			<li>
				<a href="javascript:void(0)" data-id="legacy" class="quiz_style_tab"><?php esc_html_e( 'Legacy', 'quiz-master-next' ); ?></a>
			</li>
		</ul>
	</div>
	<?php
	if ( isset( $_POST['quiz_theme_integration_nouce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['quiz_theme_integration_nouce'] ) ), 'quiz_theme_integration' ) ) {
		$quiz_id  = isset( $_GET['quiz_id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['quiz_id'] ) ) : '';
		$theme_id = isset( $_POST['quiz_theme_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['quiz_theme_id'] ) ) : '';

		$mlwQuizMasterNext->theme_settings->activate_selected_theme( $quiz_id, $theme_id );
		if ( isset($_POST['save_featured_image']) && 'Save' === $_POST['save_featured_image'] ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Featured image updated successfully.', 'quiz-master-next' ), 'success' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The theme is applied successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved", $quiz_id, "" );
		}
		$featured_image = isset( $_POST['quiz_featured_image'] ) ? esc_url_raw( wp_unslash( $_POST['quiz_featured_image'] ) ) : '';
		if ( ! empty( $quiz_id ) ) {
			update_option( "quiz_featured_image_$quiz_id", $featured_image );
		}
	} else {
		$featured_image = get_option( "quiz_featured_image_$quiz_id" );
		$featured_image = ! empty( trim( $featured_image ) ) ? trim( $featured_image ) : '';
	}
	// Read all the themes
	$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme( $quiz_id );

	if ( isset( $_POST['save_theme_settings_nonce'], $_POST['settings'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_theme_settings_nonce'] ) ), 'save_theme_settings' ) ) {
		unset( $_POST['save_theme_settings_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		$settings_array = qsm_sanitize_rec_array( wp_unslash( $_POST['settings'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$results        = $mlwQuizMasterNext->theme_settings->update_quiz_theme_settings(
			$quiz_id,
			$saved_quiz_theme,
			$settings_array
		);
		$mlwQuizMasterNext->alertManager->newAlert(
			__( 'The theme settings saved successfully.', 'quiz-master-next' ),
			'success'
		);
		$mlwQuizMasterNext->audit_manager->new_audit( "Theme settings Have Been Saved", $quiz_id, "" );
	}
	$folder_name    = QSM_THEME_PATH;
	$folder_slug    = QSM_THEME_SLUG;
	$theme_folders  = array();
	$post_permalink = $edit_link = '';
	// Get quiz post based on quiz id
	$args      = array(
		'posts_per_page' => 1,
		'post_type'      => 'qsm_quiz',
		'meta_query'     => array(
			array(
				'key'     => 'quiz_id',
				'value'   => $quiz_id,
				'compare' => '=',
			),
		),
	);
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_permalink = get_the_permalink( get_the_ID() );
			$edit_link      = get_edit_post_link( get_the_ID() );
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	}

	echo '<form method="POST" action="">';
	wp_nonce_field( 'quiz_theme_integration', 'quiz_theme_integration_nouce' );
	?><div class="themes-container">
		<style>
		.downloaded-theme-button {
			display: none;
		}
		</style>
		<div id="theme-browser" class="theme-browser quiz_style_tab_content current">
		<h1 class="qsm-theme-featured-image-title"><?php esc_html_e( 'Themes', 'quiz-master-next' ); ?></h1>
		<p class="qsm-theme-featured-image-description"><?php esc_html_e( 'Choose themes to enhance your quizzes and surveys, ensuring they align with your brand\'s aesthetic. You can easily customize the theme to change its look and feel.', 'quiz-master-next' ); ?></p>
			<div class="themes wp-clearfix">
				<?php
				qsm_get_installed_theme( $saved_quiz_theme );
				qsm_fetch_theme_data();
				qsm_get_default_wizard_themes();
				?>
			</div>
		</div>
		<div id="theme-featured-image" class="theme-featured-image rendered quiz_style_tab_content" style="display:none;">
			<h1 class="qsm-theme-featured-image-title"><?php esc_html_e( 'Featured Image', 'quiz-master-next' ); ?></h1>
			<p class="qsm-theme-featured-image-description"><?php esc_html_e( 'Add a featured image to enhance your quiz\'s visual appeal and align it with your brand\'s style.', 'quiz-master-next' ); ?></p>
			<div class="qsm-theme-featured-image-update">
				<input type="text" class="quiz_featured_image" name="quiz_featured_image" value="<?php echo esc_url( $featured_image ); ?>" />
				<input type="submit" name="save_featured_image" class="button button-secondary" value="<?php esc_attr_e( 'Save Image', 'quiz-master-next' ); ?>" />
			</div>
			<br><img alt="" class="qsm_featured_image_preview" src="<?php echo ! empty( $featured_image ) ? esc_url( $featured_image ) : esc_url( QSM_PLUGIN_URL . 'assets/placeholder.png' ); ?>"><br>
			<button id="qsm-set-theme-feature-image" class="button button-secondary qsm-theme-featured-image-change qsm-common-button-styles"><?php esc_attr_e( 'Upload Image', 'quiz-master-next' ); ?></button>
		</div>
		</div>
	<?php
	echo '</form>';
	?>
	<form action='' method='post' name='quiz_style_form'>
		<div id="legacy" class="quiz_style_tab_content" style="display: none;">
			<p style="font-size: 18px;"><strong><?php esc_html_e( 'Note: ', 'quiz-master-next' ); ?></strong><?php esc_html_e( 'This option will be removed in future.', 'quiz-master-next' ); ?></p>
			<input type='hidden' name='save_style_options' value='confirmation' />
			<input type='hidden' name='style_quiz_id' value='<?php echo esc_attr( $quiz_id ); ?>' />
			<input type='hidden' name='save_quiz_theme' id='save_quiz_theme' value='<?php echo esc_attr( $mlw_quiz_options->theme_selected ); ?>' />
			<h3 style="display: none;"><?php esc_html_e( 'Quiz Styles', 'quiz-master-next' ); ?></h3>
			<p><?php esc_html_e( 'Choose your style:', 'quiz-master-next' ); ?></p>
			<div class="qsm-styles">
				<?php foreach ( $registered_templates as $slug => $template ) { ?>
					<div onclick="mlw_qmn_theme('<?php echo esc_js( $slug ); ?>');" id="mlw_qmn_theme_block_<?php echo esc_attr( $slug ); ?>" class="qsm-info-widget <?php echo ( $mlw_quiz_options->theme_selected === $slug ) ? 'mlw_qmn_themeBlockActive' : ''; ?>">
						<?php echo wp_kses_post( $template['name'] ); ?>
					</div>
				<?php } ?>

				<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="qsm-info-widget <?php echo ( 'default' === $mlw_quiz_options->theme_selected ) ? 'mlw_qmn_themeBlockActive' : ''; ?>">
					<?php esc_html_e( 'Custom', 'quiz-master-next' ); ?>
				</div>

				<?php
				wp_add_inline_script(
					'qsm_admin_js',
					'mlw_qmn_theme(\'' . esc_js( $mlw_quiz_options->theme_selected ) . '\')'
				);
				?>
			</div>
			<button id="save_styles_button" class="button-primary">
				<?php esc_html_e( 'Save Quiz Style', 'quiz-master-next' ); ?>
			</button>
		</div>
		<div id="custom_css" class="quiz_style_tab_content" style="display: none;">
			<h1 class="qsm-theme-featured-image-title"><?php esc_html_e( 'Custom Style CSS', 'quiz-master-next' ); ?></h1>
			<p class="qsm-theme-featured-image-description"><?php esc_html_e( 'Now you can easily customize the appearance', 'quiz-master-next' ); ?></p>
			<textarea style="width: 100%; height: 700px;" id="quiz_css" name="quiz_css"><?php echo esc_textarea( preg_replace( '#<script(.*?)>(.*?)</script>#is', '', htmlspecialchars_decode( $mlw_quiz_options->quiz_stye, ENT_QUOTES) ) ); ?></textarea></td>
			<?php wp_nonce_field( 'qsm_style_tab_nonce_action', 'qsm_style_tab_nonce' ); ?>
			<button id="save_styles_button" class="button-primary qsm-common-button-styles"><?php esc_html_e( 'Save Quiz Style', 'quiz-master-next' ); ?></button>
		</div>
		<?php do_action( 'qsm_add_style_section_content' ); ?>
	</form>
<?php
	if ( ! class_exists( 'QSM_Ultimate' ) ) {
		$ultimate_args = array(
			"id"           => 'qsm-ultimate',
			"title"        => __( 'Go Beyond Standard Quizzes', 'quiz-master-next' ),
			"description"  => __( 'Take full control over quiz customization and User Experience with QSM Ultimate Add-On.', 'quiz-master-next' ),
			"chart_image"  => plugins_url( '', dirname( __FILE__ ) ) . '/images/Ultimate.png',
			"warning"      => __( 'Ultimate Addon required', 'quiz-master-next' ),
			"information"  => __( 'QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today. OR you can buy Ultimate Addon separately.', 'quiz-master-next' ),
			"buy_btn_text" => __( 'Buy Ultimate Addon', 'quiz-master-next' ),
			"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/Ultimate', 'result_page', 'ultimate', 'result-ultimate-upsell_read_documentation', 'qsm_plugin_upsell' ),
			"upgrade_link" => qsm_get_plugin_link( 'pricing', 'result_page', 'ultimate', 'result-ultimate-upsell_upgrade', 'qsm_plugin_upsell' ),
			"addon_link"   => qsm_get_plugin_link( 'downloads/Ultimate', 'result_page', 'ultimate', 'result-ultimate-upsell_buy_addon', 'qsm_plugin_upsell' ),
			"list_items"   => array(
				__("Adjust backgrounds, fonts, colors, and borders.", "quiz-master-next"),
				__("Customize buttons, answer choices, timers, and progress bars.", "quiz-master-next"),
				__("Control layout, positioning, and label displays.", "quiz-master-next"),
			),
		);
		?>
		<div id="qsm-ultimate-upgrade" class="quiz_style_tab_content" style="display: none;">
			<h1 class="qsm-theme-featured-image-title"><?php esc_html_e( 'Customize Quiz Appearance', 'quiz-master-next' ); ?></h1>
			<p class="qsm-theme-featured-image-description"><?php esc_html_e( 'Personalize the look and feel of your quizzes and surveys effortlessly.', 'quiz-master-next' ); ?></p>
			<?php qsm_admin_upgrade_popup( $ultimate_args, 'page' );    ?>
		</div>
		<?php
	}
?>
<div class="qsm-popup qsm-popup-slide qsm-theme-color-settings" id="qsm-theme-color-settings" aria-hidden="true">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
			<form action="" method="post" class="qsm-theme-settings-frm">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-2-title">
						<?php esc_html_e( 'Theme Settings', 'quiz-master-next' ); ?>
					</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="theme-color-settings-content">
					<?php wp_nonce_field( 'save_theme_settings', 'save_theme_settings_nonce' ); ?>
					<table class="form-table" style="width: 100%;">
						<?php
						global $wpdb;
						$get_theme_settings         = $mlwQuizMasterNext->theme_settings->get_active_theme_settings( $quiz_id, $saved_quiz_theme );
						$get_theme_default_settings = $wpdb->get_var( $wpdb->prepare( "SELECT default_settings from {$wpdb->prefix}mlw_themes WHERE id = %d", $saved_quiz_theme ) );
						$get_theme_settings         = maybe_unserialize($get_theme_settings);
						$get_theme_default_settings = maybe_unserialize($get_theme_default_settings);
						do_action( 'qsm_theme_option_before', $quiz_id, $saved_quiz_theme, $get_theme_settings, $get_theme_default_settings );
						if ( $get_theme_settings ) {
							foreach ( $get_theme_settings as $key => $theme_val ) {
								if ( empty( $theme_val['type'] ) ) {
									continue;
								}
								if ( '' === $theme_val ) {
									$theme_val = $get_theme_default_settings[ $key ];
								}
								?>
								<tr valign="top">
									<th scope="row" class="qsm-opt-tr">
										<label for="form_type"><?php echo esc_attr( $theme_val['label'] ); ?></label>
										<input type="hidden" name="settings[<?php echo esc_attr( $key ); ?>][label]"
											value="<?php echo esc_attr( $theme_val['label'] ); ?>">
										<input type="hidden" name="settings[<?php echo esc_attr( $key ); ?>][id]"
											value="<?php echo esc_attr( $theme_val['id'] ); ?>">
										<input type="hidden" name="settings[<?php echo esc_attr( $key ); ?>][type]" value="<?php echo esc_attr( $theme_val['type'] ); ?>">
									</th>
									<td>
										<?php
										switch ( $theme_val['type'] ) {
											case 'image':
                                                ?>
												<input class="quiz_background_image" name="settings[<?php echo esc_attr( $key ); ?>][default]" type="text" value="<?php echo esc_attr( $theme_val['default'] ); ?>" >
												<a class="set_background_image button" href="javascript:void(0);"><?php esc_html_e( 'Select Image', 'quiz-master-next' ); ?></a>
												<?php
												break;
											case 'color':
                                                ?>
												<input name="settings[<?php echo esc_attr( $key ); ?>][default]" type="text" data-alpha-enabled="true" value="<?php echo esc_attr( $theme_val['default'] ); ?>" data-default-color="<?php echo esc_attr( $theme_val['default'] ); ?>" class="qsm-color-field" />
												<?php
												break;
											case 'checkbox':
												$theme_val['default'] = ! empty($theme_val['default']) ? $theme_val['default'] : 0;
												?>
												<input type="hidden" name="settings[<?php echo esc_attr( $key ); ?>][value]" value="<?php echo esc_attr( $theme_val['value'] ); ?>">
												<input name="settings[<?php echo esc_attr( $key ); ?>][default]" type="checkbox" value="<?php echo esc_attr( $theme_val['value'] ); ?>" <?php echo $theme_val['value'] === $theme_val['default'] ? "checked" : ""; ?> />
												<?php
												break;
											default:
                                                ?>
												<input name="settings[<?php echo esc_attr( $key ); ?>][default]" type="text" value="<?php echo esc_attr( $theme_val['default'] ); ?>"/>
												<?php
												break;
										} ?>
									</td>
								</tr>
								<?php
							}
						}
						?>
					</table>
				<?php do_action( 'qsm_theme_option_end', $quiz_id, $saved_quiz_theme, $get_theme_settings, $get_theme_default_settings ); ?>
				</main>
				<footer class="qsm-popup__footer">
					<button type="submit" id="qsm-save-theme-settings"
						class="button button-primary"><?php esc_html_e( 'Save Settings', 'quiz-master-next' ); ?></button>
					<button class="button" data-micromodal-close
						aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
				</footer>
			</form>
		</div>
	</div>
</div>
<?php
}

add_action( 'admin_menu', 'qsm_register_theme_setting_submenu_page' );

function qsm_register_theme_setting_submenu_page() {
	add_submenu_page( 'options.php', __( 'Theme Settings', 'quiz-master-next' ), __( 'Theme Settings', 'quiz-master-next' ), 'manage_options', 'qmn_theme_settings', 'qsm_display_theme_settings' );
}

function qsm_display_theme_settings() {
	global $mlwQuizMasterNext, $wpdb;
	$quiz_id  = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$theme_id = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme( $quiz_id );

	if ( isset( $_POST['save_theme_settings_nonce'], $_POST['settings'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_theme_settings_nonce'] ) ), 'save_theme_settings' ) ) {
    unset( $_POST['save_theme_settings_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		$settings_array = qsm_sanitize_rec_array( wp_unslash( $_POST['settings'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$results        = $mlwQuizMasterNext->theme_settings->update_quiz_theme_settings( $quiz_id, $theme_id, $settings_array );
		?>
<div class="notice notice-success is-dismissible" style="margin-top:30px;">
	<p><?php esc_html_e( 'Theme settings are saved!', 'quiz-master-next' ); ?></p>
</div>
<?php
	}
	$get_theme_settings = $mlwQuizMasterNext->theme_settings->get_active_theme_settings( $quiz_id, $theme_id )
	?>
<div class="wrap">
	<h1 style="margin-bottom: 10px;">
		<?php
		$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
		echo esc_attr( $quiz_name );
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id . '&tab=style' ) ); ?>"
			class="edit-quiz-name button button-primary"><?php esc_html_e( 'Back to themes', 'quiz-master-next' ); ?></a>
	</h1>
	<form action="" method="post">
		<?php wp_nonce_field( 'save_theme_settings', 'save_theme_settings_nonce' ); ?>
		<table class="form-table" style="width: 100%;">
			<?php
			$theme_settings = array();
			$theme_settings = apply_filters( 'qsm_theme_settings', $theme_settings, $quiz_id );
			if ( $theme_settings ) {
				foreach ( $theme_settings as $key => $theme_val ) {
					$setting_val = isset( $get_theme_settings[ $theme_val['id'] ] ) ? $get_theme_settings[ $theme_val['id'] ] : $theme_val['default'];
					?>
			<tr valign="top">
				<th scope="row" class="qsm-opt-tr">
					<label for="form_type"><?php echo esc_attr( $theme_val['label'] ); ?></label>
				</th>
				<td>
					<input name="<?php echo esc_attr( $theme_val['id'] ); ?>" type="text" value="<?php echo esc_attr( $setting_val ); ?>"
						data-default-color="<?php echo esc_attr( $setting_val ); ?>" class="qsm-color-field" />
				</td>
			</tr>
			<?php
				}
			} else {
				?>
			<tr>
				<td colspan="2">
					<?php esc_html_e( 'No settings found', 'quiz-master-next' ); ?>
				</td>
			</tr>
			<?php
			}
			?>
		</table>
		<button class="button-primary"><?php esc_html_e( 'Save Changes', 'quiz-master-next' ); ?></button>
	</form>
</div>
<?php
}
?>