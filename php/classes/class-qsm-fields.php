<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles creating form fields based on supplied arrays
 *
 * @since 5.0.0
 */
class QSM_Fields {

	/**
	 * Cycles through the fields in the array and generates each field
	 *
	 * @since 5.0.0
	 * @param array $fields The array that contains the data for all fields
	 * @param array $settings The array that holds the settings for this section
	 */
	public static function generate_section( $fields, $section ) {
		global $mlwQuizMasterNext;
		global $wpdb;
    	$result_page_fb_image = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'result_page_fb_image' );
		$settings_array_before_update = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( $section );

		// If nonce is correct, save settings
		if ( ( isset( $_POST["save_settings_nonce"] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_settings_nonce'] ) ), 'save_settings' ) ) || ( isset( $_POST["save_global_default_ettings_nonce"] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_global_default_ettings_nonce'] ) ), 'set_global_default_settings' ) ) ) {
			// Cycle through fields to retrieve all posted values
			$settings_array = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( $section );
			foreach ( $fields as $field ) {
				// Sanitize the values based on type
				$sanitized_value = '';
				if ( isset( $_POST[ $field["id"] ] ) ) {
					switch ( $field["type"] ) {
						case 'text':
							$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) );
							break;

						case 'url':
							$sanitized_value = esc_url_raw( wp_unslash( $_POST[ $field["id"] ] ) );
							break;

						case 'checkbox':
							$sanitized_value = isset( $_POST[ $field["id"] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) ) : 0;
							break;
						case 'date':
							$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) );
							break;

						case 'number':
							$sanitized_value = intval( $_POST[ $field["id"] ] );
							break;

						case 'editor':
							$sanitized_value = wp_kses_post( wp_unslash( $_POST[ $field["id"] ] ) );
							break;

						default:
							$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) );
							break;
					}
				}
				$settings_array[ $field["id"] ] = $sanitized_value;
			}
			$quiz_id = isset( $_GET["quiz_id"] ) ? intval( $_GET["quiz_id"] ) : 0;

			// Update the settings and show alert based on outcome
			$results = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( $section, $settings_array );
			if ( false !== $results ) {
				do_action( 'qsm_saved_quiz_settings', $quiz_id, $section, $settings_array );
				$get_updated_setting_data = array_diff_assoc($settings_array, $settings_array_before_update);
				$json_updated_setting_data = wp_json_encode($get_updated_setting_data);
				$mlwQuizMasterNext->alertManager->newAlert( __( 'The settings has been updated successfully.', 'quiz-master-next' ), 'success' );
				$mlwQuizMasterNext->audit_manager->new_audit( 'Settings Have Been Edited', $quiz_id, $json_updated_setting_data );
				// update post_modified
				$datetime  = current_time( 'Y-m-d H:i:s', 0 );
				$update = array(
					'ID'            => get_the_ID(),
					'post_modified' => $datetime,
				);
				wp_update_post( $update );
			} else {
				$mlwQuizMasterNext->alertManager->newAlert( __( 'There was an error when updating the settings. Please try again.', 'quiz-master-next' ), 'error');
			}
    	}
		// Retrieve the settings for this section
		$settings = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( $section );
		if ( isset( $settings['form_type'] ) ) {
			$settings['form_type'] = 2 === intval( $settings['system'] ) ? 1 : $settings['form_type'];
		}
		if ( isset( $settings['result_page_fb_image'] ) && '' === $settings['result_page_fb_image'] ) {
			$settings['result_page_fb_image'] = '' !== $result_page_fb_image ? $result_page_fb_image : $settings['result_page_fb_image'];
		}
		?>
		<form action="" method="post">
			<?php wp_nonce_field( 'save_settings','save_settings_nonce' );
			$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			if ( 'options' === $current_tab ) :
			?>
				<div class="qsm-sub-tab-menu" style="display: inline-block;width: 100%;">
					<ul class="subsubsub">
						<li>
							<a href="javascript:void(0)" data-id="qsm_general" class="current quiz_style_tab"><?php esc_html_e( 'General', 'quiz-master-next' ); ?></a>
						</li>
						<li>
							<a href="javascript:void(0)" data-id="quiz_submission" class="quiz_style_tab"><?php esc_html_e( 'Quiz submission', 'quiz-master-next' ); ?></a>
						</li>
						<li>
							<a href="javascript:void(0)" data-id="display" class="quiz_style_tab"><?php esc_html_e( 'Display', 'quiz-master-next' ); ?></a>
						</li>
						<li>
							<a href="javascript:void(0)" data-id="contact_form" class="quiz_style_tab"><?php esc_html_e( 'Contact form', 'quiz-master-next' ); ?></a>
						</li>
						<li>
							<a href="javascript:void(0)" data-id="legacy" class="quiz_style_tab"><?php esc_html_e( 'Legacy', 'quiz-master-next' ); ?></a>
						</li>
					</ul>
				</div>
				<div id="qsm_general" class="quiz_style_tab_content">
					<table class="form-table" style="width: 100%;">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
						// Generate the field
							if ( isset( $field['option_tab'] ) && 'general' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
					</table>
				</div>
				<div id="quiz_submission" class="quiz_style_tab_content" style="display:none">
					<table class="form-table" style="width: 100%;">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'quiz_submission' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
					</table>
				</div>
				<div id="display" class="quiz_style_tab_content" style="display:none">
					<table class="form-table" style="width: 100%;">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'display' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
					</table>
				</div>
				<div id="contact_form" class="quiz_style_tab_content" style="display:none">
					<table class="form-table" style="width: 100%;">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'contact_form' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
					</table>
				</div>
				<div id="legacy" class="quiz_style_tab_content" style="display:none">
					<p><?php esc_html_e( 'All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next' ); ?></p>
					<table class="form-table" style="width: 100%;">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'legacy' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
					</table>
				</div>
			<?php
			elseif ( 'text' === $current_tab ) : ?>
				<div class="left-bar">
				<h2><?php esc_html_e( 'Select Labels', 'quiz-master-next' ); ?></h2>
					<ul class="qsm-custom-label-left-menu-ul">
						<li class="qsm-custom-label-left-menu currentli">
							<a href="javascript:void(0)" data-id="text-button" class="current quiz_text_tab_custom">
								<?php esc_html_e( 'Buttons', 'quiz-master-next' ); ?></a>
						</li>
						<li class="qsm-custom-label-left-menu">
							<a href="javascript:void(0)" data-id="text-validation-messages" class="quiz_text_tab_custom">
								<?php esc_html_e( 'Validation Messages', 'quiz-master-next' ); ?>
							</a>
						</li>
						<li class="qsm-custom-label-left-menu">
							<a href="javascript:void(0)" data-id="text-other" class="quiz_text_tab_custom">
								<?php esc_html_e( 'Other', 'quiz-master-next' ); ?>
							</a>
						</li>
						<li class="qsm-custom-label-left-menu">
							<a href="javascript:void(0)" data-id="text-legacy" class="quiz_text_tab_custom">
								<?php esc_html_e( 'Legacy', 'quiz-master-next' ); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="right-bar">
					<div id="text-button" class="quiz_style_tab_content qsm-text-content">
					<h2><?php esc_html_e( 'Buttons', 'quiz-master-next' ); ?></h2>
						<table class="form-table">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'text-button' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
						</table>
					</div>
					<div id="text-validation-messages" class="quiz_style_tab_content" style="display:none">
					<h2><?php esc_html_e( 'Validation Messages', 'quiz-master-next' ); ?></h2>
						<table class="form-table">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'text-validation-messages' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
						</table>
					</div>
					<div id="text-other" class="quiz_style_tab_content" style="display:none">
					<h2><?php esc_html_e( 'Other', 'quiz-master-next' ); ?></h2>
						<table class="form-table">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'text-other' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
						</table>
					</div>
					<div id="text-legacy" class="quiz_style_tab_content" style="display:none">
					<h2><?php esc_html_e( 'Legacy', 'quiz-master-next' ); ?></h2>
						<p><?php esc_html_e( 'All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next' ); ?></p>
						<table class="form-table">
						<?php
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'text-legacy' === $field['option_tab'] ) {
								QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
							}
						}
						?>
						</table>
					</div>
				</div>
			<?php else :
				foreach ( $fields as  $field ) {
					QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
				}
			endif; ?>
			<div class="qsm-tab-btns">
				<button class="button-primary" type="submit"> <?php esc_html_e('Save Changes', 'quiz-master-next'); ?></button>
				<?php if ( isset($_GET['tab'], $_GET['page']) && 'options' == sanitize_text_field( wp_unslash( $_GET['tab'] ) ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'mlw_quiz_options' ) {?>
					<a id="qsm-blobal-settings" href="javascript:void(0)" ><?php esc_html_e('Reset to Defaults', 'quiz-master-next'); ?></a>
				<?php } ?>
			</div>
		</form>
		<?php
  	}

	/**
	 * Prepares the field and calls the correct generate field function based on field's type
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 * @return bool False if the field is invalid, true if successful
	 */
	public static function generate_field( $field, $value ) {

		// Load default
		$defaults = array(
			'id'        => null,
			'label'     => '',
			'type'      => '',
			'options'   => array(),
			'variables' => array(),
		);
		$field = wp_parse_args( $field, $defaults );

		// If id is not valid, return false
		if ( is_null( $field["id"] ) || empty( $field["id"] ) ) {
			return false;
		}

		// If type is empty, assume text
		if ( empty( $field["type"] ) ) {
			$field["type"] = "text";
		}

		// Prepare function to call for field type
		$method = "generate_{$field["type"]}_field";
		QSM_Fields::$method( $field, $value );

		return true;
	}

	/**
	 * Generates a text field
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_text_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<input type="text" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}
	/**
	 * Generates a text field
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_url_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<input type="url" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>"
					value="<?php echo esc_url( $value ); ?>" />
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
  	}

  	public static function generate_select_page_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<select id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>">
					<option value="">Select Page</option>
					<?php
					$pages = get_pages();
					foreach ( $pages as $page ) { ?>
					<option value="<?php echo esc_url( get_page_link( $page->ID ) ); ?>"
						<?php selected($value, get_page_link( $page->ID )); ?>><?php echo wp_kses_post( $page->post_title ); ?></option>;
					<?php } ?>
				</select>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
				<br />
				<strong style="color: red;">Note: </strong><?php echo isset($field['note']) ? wp_kses_post( $field['note'] ) : ''; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generates a textarea field using the WP Editor
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_editor_field( $field, $value ) {
		?>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
					<?php
				if ( is_array( $field["variables"] ) ) {
					?>
					<br>
					<p><?php esc_html_e( "Allowed Variables:", 'quiz-master-next' ); ?></p>
					<?php
					foreach ( $field["variables"] as $variable ) {
					?>
					<p style="margin: 2px 0">- <?php echo wp_kses_post( $variable ); ?></p>
					<?php
					}
				}
				?>
				</label>
			</th>
			<td>
				<?php wp_editor( htmlspecialchars_decode( $value, ENT_QUOTES ), $field["id"] ); ?>
			</td>
		</tr>
		<?php
  	}

	/**
	 * Generates a date field
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_date_field( $field, $value ) {

		$date_field_script = "jQuery(function() {	jQuery('#" . $field["id"]."').datetimepicker({ format: 'm/d/Y H:i', step: 1});});";

		wp_add_inline_script( 'qsm_admin_js', $date_field_script);

		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td class="<?php echo esc_attr( $field["id"] ); ?>">
				<?php if ( isset($field['ph_text']) && '' !== $field['ph_text'] ) { ?>
				<span class="qsm-ph_text"><?php echo wp_kses_post( $field['ph_text'] ); ?></span>
				<?php } ?>
				<input autocomplete="off" type="text" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generates a number field
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_number_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<input type="number" step="1" min="0" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" value="<?php echo esc_attr($value); ?>" />
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
  	}

	/**
	 * Generates radio inputs
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_radio_field( $field, $value ) {
		$show_option = isset( $field['show_option'] ) ? $field['show_option'] : '';
		$class = $show_option ? $show_option . ' hidden qsm_hidden_tr' : '';
		?>
		<tr valign="top" class="<?php echo esc_attr( $class ); ?>">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<fieldset class="buttonset buttonset-hide" data-hide='1'>
					<?php
					foreach ( $field["options"] as $option ) {
						?>
						<input type="radio" id="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" <?php checked( $option["value"], $value ); ?> value="<?php echo esc_attr( $option["value"] ); ?>" />
						<label for="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>"><?php echo wp_kses_post( $option["label"] ); ?></label><br />
						<?php
					}
					?>
				</fieldset>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
  	}

	/**
	 * Generates radio inputs
	 *
	 * @since 5.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_select_field( $field, $value ) {
		$show_option = isset( $field['show_option'] ) ? $field['show_option'] : '';
		$class = $show_option ? $show_option : '';
		?>
		<tr valign="top" class="<?php echo esc_attr( $class ); ?>">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<select name="<?php echo esc_attr( $field["id"] ); ?>">
					<?php
					foreach ( $field["options"] as $option ) {
						?>
						<option <?php selected( $option["value"], $value ); ?> value="<?php echo esc_attr( $option["value"] ); ?>"><?php echo wp_kses_post( $option["label"] ); ?></option>
						<?php
					}
					?>
				</select>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
  	}

	/**
	 * Generates category checkbox
	 *
	 * @since 6.4.8
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_category_field( $field, $value ) {
		global $wpdb;
		$quiz_id = isset($_GET['quiz_id']) ? sanitize_text_field( wp_unslash( $_GET['quiz_id'] ) ) : 0;
		$explode_cat = explode(',', $value);
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<?php
				$categories = QSM_Questions::get_quiz_categories( $quiz_id );
				$categories_tree = (isset($categories['tree']) ? $categories['tree'] : array());
				$questions = QSM_Questions::load_questions_by_pages( $quiz_id );
				$cat_array = array();
				if ( $questions ) {
					$multiple_category_system    = false;
					// check if multiple category is enabled.
					$enabled                     = get_option( 'qsm_multiple_category_enabled' );
					if ( $enabled && 'cancelled' !== $enabled ) {
						$multiple_category_system = true;
					}
					foreach ( $questions as $single_question ) {
						if ( ! $multiple_category_system ) {
							$cat_array[] = $single_question['category'];
						}
					}
					$cat_array = array_unique( $cat_array );
					if ( $cat_array || $categories_tree ) {
						?><select class="category_selection_random" multiple=""><?php
						if ( $multiple_category_system ) {
							echo QSM_Fields::get_category_hierarchical_options( $categories_tree, $explode_cat );
						} else {
							foreach ( $cat_array as $single_cat ) {
								?><option <?php echo in_array( $single_cat, $explode_cat, true ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $single_cat ); ?>"><?php echo wp_kses_post( $single_cat ); ?></option><?php
							}
						}
						?></select><?php
					} else {
						echo 'No category found.';
					}
				} else {
					echo 'No catergory found.';
				}
				?>
				<input type="hidden" class="catergory_comma_values" name="<?php echo esc_attr( $field["id"] ); ?>"
					value='<?php echo esc_attr( $value ); ?>'>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
  	}

  	public static function get_category_hierarchical_options( $categories = array(), $selected = array(), $prefix = '' ) {
		$options = '';
		if ( ! empty($categories) ) {
			foreach ( $categories as $cat ) {
				$options .= '<option value="' . $cat->term_id . '" ' . ( in_array( intval( $cat->term_id ),  array_map( 'intval', $selected ) , true ) ? 'selected' : '' ) . '>' . $prefix . $cat->name . '</option>';
				if ( ! empty($cat->children) ) {
					$options .= QSM_Fields::get_category_hierarchical_options( $cat->children, $selected, $prefix . '&nbsp;&nbsp;&nbsp;' );
				}
			}
		}
		return $options;
	}

	/**
	 * @since 7.0
	 * @param Array $field
	 * @param String $value
	 *
	 * Generate the hide show div
	 */
	public static function generate_hide_show_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row" class="qsm-opt-tr">
				<a href="javascript:void(0)" id="<?php echo esc_attr( $field["id"] ); ?>"><?php echo esc_attr( $field["label"] ); ?></a>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generates h2 tag for label
	 *
	 * @since 7.0.0
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_section_heading_field( $field, $value ) {
		?>
		<tr valign="top">
			<th scope="row">
				<h2 class="section_heading"><?php echo wp_kses_post( $field['label'] ); ?></h2>
			</th>
			<td>
			</td>
		</tr>
		<?php
	}
	/**
	 * Generates checkbox inputs
	 *
	 * @since 7.1.10
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_checkbox_field( $field, $value ) {
		$show_option = isset( $field['show_option'] ) ? $field['show_option'] : '';
		global $mlwQuizMasterNext;
		$score_roundoff = $mlwQuizMasterNext->pluginHelper->get_section_setting('quiz_options',$field["id"] );
		$class = $show_option ? $show_option . ' hidden qsm_hidden_tr qsm_hidden_tr_gradingsystem' : '';
		$class .= isset( $field['id'] ) ? ' '.$field['id'] : '';
		?>
		<tr valign="top" class="<?php echo esc_attr( $class ); ?>">
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<fieldset class="buttonset buttonset-hide" data-hide='1'>
					<?php if ( isset($field['ph_text']) && '' !== $field['ph_text'] ) { ?>
						<span><?php echo wp_kses_post( $field['ph_text'] ); ?></span>
					<?php } ?>
					<?php
					foreach ( $field["options"] as $option ) {
						?>
					<input type="checkbox" id="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>"
						name="<?php echo esc_attr( $field["id"] ); ?>" <?php checked( $option["value"], $score_roundoff ); ?>
						value="<?php echo esc_attr( $option["value"] ); ?>" />
					<label for="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>"><?php echo isset( $option["label"] ) ? wp_kses_post( $option["label"] ) : ""; ?></label>
					<?php
					}
					?>
				</fieldset>
				<?php if ( isset($field['help']) && '' !== $field['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}
}
?>