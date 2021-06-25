<?php
/**
 * Handles the functions/views for the "Style" tab when editing a quiz or survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Style tab to the Quiz Settings page.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_settings_style_tab() {
	 global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Style', 'quiz-master-next' ), 'qsm_options_styling_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_style_tab', 5 );

/**
 * Adds the Style tab content to the tab.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_options_styling_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;

	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_style_add_data( 'qsm_admin_style', 'rtl', 'replace' );
	$quiz_id = intval( $_GET['quiz_id'] );
	if ( isset( $_POST['qsm_style_tab_nonce'] ) && wp_verify_nonce( $_POST['qsm_style_tab_nonce'], 'qsm_style_tab_nonce_action' ) && isset( $_POST['save_style_options'] ) && 'confirmation' == $_POST['save_style_options'] ) {

		$style_quiz_id = intval( $_POST['style_quiz_id'] );
		$quiz_theme    = sanitize_text_field( $_POST['save_quiz_theme'] );
		$quiz_style    = sanitize_textarea_field( htmlspecialchars( stripslashes( $_POST['quiz_css'] ), ENT_QUOTES ) );

		// Saves the new css.
		$results = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mlw_quizzes SET quiz_stye=%s, theme_selected=%s, last_activity=%s WHERE quiz_id=%d", $quiz_style, $quiz_theme, date( 'Y-m-d H:i:s' ), $style_quiz_id ) );
		if ( false !== $results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The style has been saved successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved For Quiz Number $style_quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Error occured when trying to save the styles. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error saving styles', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	if ( isset( $_GET['quiz_id'] ) ) {
		$mlw_quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
	}
	$registered_templates = $mlwQuizMasterNext->pluginHelper->get_quiz_templates();
	?>
<script>
function mlw_qmn_theme(theme) {
	document.getElementById('save_quiz_theme').value = theme;
	jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
	jQuery("#mlw_qmn_theme_block_" + theme).toggleClass("mlw_qmn_themeBlockActive");

}
jQuery(document).ready(function() {
	jQuery('.quiz_style_tab').click(function(e) {
		e.preventDefault();
		var current_id = jQuery(this).attr('data-id');
		jQuery('.quiz_style_tab').removeClass('current');
		jQuery(this).addClass('current');
		jQuery('.quiz_style_tab_content').hide();
		jQuery('#' + current_id).show();
	});
});
</script>

<div class="qsm-sub-tab-menu" style="display: inline-block;width: 100%;">
	<ul class="subsubsub">
		<li>
			<a href="#" data-id="qsm_themes" class="current quiz_style_tab">
				<?php _e( 'Themes', 'quiz-master-next' ); ?></a> |
		</li>
		<li>
			<a href="#" data-id="custom_css" class="quiz_style_tab">
				<?php _e( 'Custom CSS', 'quiz-master-next' ); ?>
			</a> |
		</li>
		<li>
			<a href="#" data-id="legacy" class="quiz_style_tab">
				<?php _e( 'Legacy', 'quiz-master-next' ); ?>
			</a>
		</li>
	</ul>
</div>
<div id="qsm_themes" class="quiz_style_tab_content">
	<?php
	// Include required custom js and css
	wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
	wp_enqueue_script( 'qsm_theme_color_js', plugins_url( '../../js/qsm-theme-color.js', __FILE__ ), array( 'jquery', 'wp-color-picker', 'micromodal_script' ), $mlwQuizMasterNext->version );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ) );
	wp_style_add_data( 'qsm_admin_style', 'rtl', 'replace' );
	wp_enqueue_media();
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(document).on('click', '.qsm-activate-theme', function() {
			jQuery(this).parents('.theme-wrapper').find('input[name=quiz_theme_id]').prop("checked", true);
		});
		jQuery(document).on('input', '.quiz_featured_image', function() {
			jQuery('.qsm_featured_image_preview').attr('src', jQuery(this).val());
		});

		jQuery(document).on('click', '.filter-links a', function() {
			current_id = jQuery(this).attr('data-id');
			jQuery(this).parents('.filter-links').find('li a').each(function() {
				jQuery(this).removeClass('current');
			});
			jQuery(this).addClass('current');
			jQuery(this).parents('#qsm_themes').find('.themes-container').children('div').each(function() {
				if (jQuery(this).hasClass(current_id)) {
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}
			});
		})
	});
	</script>
	<?php
	if ( isset( $_POST['quiz_theme_integration_nouce'] ) && wp_verify_nonce( $_POST['quiz_theme_integration_nouce'], 'quiz_theme_integration' ) ) {
		$quiz_id  = (int) $_GET['quiz_id'];
		$theme_id = (int) $_POST['quiz_theme_id'];
		$mlwQuizMasterNext->theme_settings->activate_selected_theme( $quiz_id, $theme_id );
		if ( $_POST['save_featured_image'] == 'Save' ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Featured image updated successfully.', 'quiz-master-next' ), 'success' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The theme is applied successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved For Quiz Number $quiz_id" );
		}
		$featured_image = isset( $_POST['quiz_featured_image'] ) ? trim( $_POST['quiz_featured_image'] ) : '';
		if ( ! empty( $quiz_id ) ) {
			update_option( "quiz_featured_image_$quiz_id", $featured_image );
		}
	} else {
		$featured_image = get_option( "quiz_featured_image_$quiz_id" );
		$featured_image = ! empty( trim( $featured_image ) ) ? trim( $featured_image ) : '';
	}
	// Read all the themes
	$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme( $quiz_id );

	if ( isset( $_POST['save_theme_settings_nonce'] ) && wp_verify_nonce(
		$_POST['save_theme_settings_nonce'],
		'save_theme_settings'
	) ) {
		unset( $_POST['save_theme_settings_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		$settings_array = array();
		array_map( 'sanitize_text_field', $_POST['settings'] );
		$settings_array = serialize( $_POST['settings'] );
		$results        = $mlwQuizMasterNext->theme_settings->update_quiz_theme_settings(
			$quiz_id,
			$saved_quiz_theme,
			$settings_array
		);
		$mlwQuizMasterNext->alertManager->newAlert(
			__( 'The theme settings saved successfully.', 'quiz-master-next' ),
			'success'
		);
		$mlwQuizMasterNext->audit_manager->new_audit( "Theme settings Have Been Saved For Quiz Number $quiz_id" );
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
	?>
	<div class="wp-filter hide-if-no-js">
		<ul class="filter-links">
			<li>
				<a href="#" class="current" data-id="theme-browser"><?php _e( 'Themes', 'quiz-master-next' ); ?></a>
			</li>
			<li>
				<?php if ( $saved_quiz_theme ) { ?>
				<a href="#" data-id="theme-featured-image"><?php _e( 'Featured Image', 'quiz-master-next' ); ?></a>
				<?php } ?>
			</li>
			<?php do_action( 'qsm_add_filter_menu' ); ?>
		</ul>
	</div>
	<?php
	echo '<form method="POST" action="">';
	wp_nonce_field( 'quiz_theme_integration', 'quiz_theme_integration_nouce' );
	?>
	<div class="themes-container">
		<style>
		.downloaded-theme-button {
			display: none;
		}
		</style>
		<div class="theme-browser rendered current">
			<div class="themes wp-clearfix">
				<?php qsm_get_installed_theme( $saved_quiz_theme ); ?>
			</div>
		</div>
		<div class="theme-featured-image" style="display:none;">
			<input type="text" class="quiz_featured_image" name="quiz_featured_image"
				value="<?php echo $featured_image; ?>" />
			<a id="set_featured_image" class="button "><?php _e( 'Set Featured Image', 'quiz-master-next' ); ?></a>
			<br><img alt="" class="qsm_featured_image_preview" src="<?php echo $featured_image; ?>"><br>
			<input type="submit" name="save_featured_image" class="button button-primary"
				value="<?php _e( 'Save', 'quiz-master-next' ); ?>" />

		</div>
	</div>
	<?php
	echo '</form>';
	?>
</div>
<form action='' method='post' name='quiz_style_form'>
	<div id="legacy" class="quiz_style_tab_content" style="display: none;">
		<p style="font-size: 18px;"><b><?php _e( 'Note: ', 'quiz-master-next' ); ?>
			</b><?php _e( 'This option will be removed in future.', 'quiz-master-next' ); ?></p>
		<input type='hidden' name='save_style_options' value='confirmation' />
		<input type='hidden' name='style_quiz_id' value='<?php echo esc_attr( $quiz_id ); ?>' />
		<input type='hidden' name='save_quiz_theme' id='save_quiz_theme'
			value='<?php echo esc_attr( $mlw_quiz_options->theme_selected ); ?>' />
		<h3 style="display: none;"><?php _e( 'Quiz Styles', 'quiz-master-next' ); ?></h3>
		<p><?php _e( 'Choose your style:', 'quiz-master-next' ); ?></p>
		<style>
		div.mlw_qmn_themeBlockActive {
			background-color: yellow;
		}
		</style>
		<div class="qsm-styles">
			<?php
			foreach ( $registered_templates as $slug => $template ) {
				?>
			<div onclick="mlw_qmn_theme('<?php echo $slug; ?>');" id="mlw_qmn_theme_block_<?php echo $slug; ?>" class="qsm-info-widget 
													<?php
													if ( $mlw_quiz_options->theme_selected == $slug ) {
																echo 'mlw_qmn_themeBlockActive';
													}
													?>
		"><?php echo $template['name']; ?></div>
			<?php
			}
			?>
			<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="qsm-info-widget 
			<?php
			if ( $mlw_quiz_options->theme_selected == 'default' ) {
					echo 'mlw_qmn_themeBlockActive';
			}
			?>
	"><?php _e( 'Custom', 'quiz-master-next' ); ?></div>
			<script>
			mlw_qmn_theme('<?php echo $mlw_quiz_options->theme_selected; ?>');
			</script>
		</div>
		<button id="save_styles_button" class="button-primary">
			<?php _e( 'Save Quiz Style', 'quiz-master-next' ); ?>
		</button>
	</div>
	<div id="custom_css" class="quiz_style_tab_content" style="display: none;">
		<h3><?php _e( 'Custom Style CSS', 'quiz-master-next' ); ?></h3>
		<p><?php _e( 'For help and guidance along with a list of different classes used in this plugin, please visit the following link:', 'quiz-master-next' ); ?>
			<a target="_blank" rel="noopener"
				href="https://quizandsurveymaster.com/docs/advanced-topics/editing-design-styles-css/">CSS in QSM</a>
		</p>
		<table class="form-table">
			<tr>
				<td><textarea style="width: 100%; height: 700px;" id="quiz_css"
						name="quiz_css"><?php echo $mlw_quiz_options->quiz_stye; ?></textarea></td>
			</tr>
		</table>
		<?php wp_nonce_field( 'qsm_style_tab_nonce_action', 'qsm_style_tab_nonce' ); ?>
		<button id="save_styles_button"
			class="button-primary"><?php _e( 'Save Quiz Style', 'quiz-master-next' ); ?></button>
	</div>
</form>
<div class="qsm-popup qsm-popup-slide qsm-theme-color-settings" id="qsm-theme-color-settings" aria-hidden="true">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
			<header class="qsm-popup__header">
				<h2 class="qsm-popup__title" id="modal-2-title">
					<?php _e( 'Customize Quiz Theme', 'quiz-master-next' ); ?>
				</h2>
				<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
			</header>
			<main class="qsm-popup__content" id="thme-color-settings-content">
				<form action="" method="post" class="qsm-theme-settings-frm">
					<?php wp_nonce_field( 'save_theme_settings', 'save_theme_settings_nonce' ); ?>
					<table class="form-table" style="width: 100%;">
						<?php
						$get_theme_settings = $mlwQuizMasterNext->theme_settings->get_active_theme_settings( $quiz_id, $saved_quiz_theme );

						if ( $get_theme_settings ) {
							$i = 0;
							foreach ( $get_theme_settings as $key => $theme_val ) {
								?>
						<tr valign="top">
							<th scope="row" class="qsm-opt-tr">
								<label for="form_type"><?php echo $theme_val['label']; ?></label>
								<input type="hidden" name="settings[<?php echo $i; ?>][label]"
									value="<?php echo $theme_val['label']; ?>">
								<input type="hidden" name="settings[<?php echo $i; ?>][id]"
									value="<?php echo $theme_val['id']; ?>">
								<input type="hidden" name="settings[<?php echo $i; ?>][type]" value="color">
							</th>
							<td>
								<input name="settings[<?php echo $i; ?>][default]" type="text"
									value="<?php echo $theme_val['default']; ?>"
									data-default-color="<?php echo $theme_val['default']; ?>" class="my-color-field" />
							</td>
						</tr>
						<?php
								$i++;
							}
						} else {
							?>
						<tr>
							<td colspan="2">
								<?php _e( 'No settings found', 'quiz-master-next' ); ?>
							</td>
						</tr>
						<?php
						}
						?>
					</table>
				</form>
			</main>
			<footer class="qsm-popup__footer">
				<button id="qsm-save-theme-settings"
					class="button button-primary"><?php _e( 'Save Settings', 'quiz-master-next' ); ?></button>
				<button class="button" data-micromodal-close
					aria-label="Close this dialog window"><?php _e( 'Cancel', 'quiz-master-next' ); ?></button>
			</footer>
		</div>
	</div>
</div>
<?php
}

					add_action( 'admin_menu', 'qsm_register_theme_Setting_submenu_page' );

function qsm_register_theme_Setting_submenu_page() {
	add_submenu_page( null, __( 'Theme Settings', 'quiz-master-next' ), __( 'Theme Settings', 'quiz-master-next' ), 'manage_options', 'qmn_theme_settings', 'qsm_display_theme_settings' );
}

function qsm_display_theme_settings() {
	 global $mlwQuizMasterNext, $wpdb;
	$quiz_id  = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$theme_id = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme( $quiz_id );

	if ( isset( $_POST['save_theme_settings_nonce'] ) && wp_verify_nonce( $_POST['save_theme_settings_nonce'], 'save_theme_settings' ) ) {
		unset( $_POST['save_theme_settings_nonce'] );
		unset( $_POST['_wp_http_referer'] );
		$settings_array = array();
		array_map( 'sanitize_text_field', $_POST['settings'] );
		$settings_array = serialize( $_POST['settings'] );
		$results        = $mlwQuizMasterNext->theme_settings->update_quiz_theme_settings( $quiz_id, $theme_id, $settings_array );
		?>
<div class="notice notice-success is-dismissible" style="margin-top:30px;">
	<p><?php _e( 'Theme settings are saved!', 'quiz-master-next' ); ?></p>
</div>
<?php
	}
	$get_theme_settings = $mlwQuizMasterNext->theme_settings->get_active_theme_settings( $quiz_id, $theme_id )
	?>
<div class="wrap">
	<h1 style="margin-bottom: 10px;">
		<?php
						$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
						echo $quiz_name;
		?>
		<a href="<?php echo admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' ) . $quiz_id . '&tab=style'; ?>"
			class="edit-quiz-name button button-primary"><?php _e( 'Back to themes', 'quiz-master-next' ); ?></a>
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
					<label for="form_type"><?php echo $theme_val['label']; ?></label>
				</th>
				<td>
					<input name="<?php echo $theme_val['id']; ?>" type="text" value="<?php echo $setting_val; ?>"
						data-default-color="<?php echo $setting_val; ?>" class="my-color-field" />
				</td>
			</tr>
			<?php
				}
			} else {
				?>
			<tr>
				<td colspan="2">
					<?php _e( 'No settings found', 'quiz-master-next' ); ?>
				</td>
			</tr>
			<?php
			}
			?>
		</table>
		<button class="button-primary"><?php _e( 'Save Changes', 'quiz-master-next' ); ?></button>
	</form>
</div>
<?php
}
?>