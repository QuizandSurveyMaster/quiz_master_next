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
		// If nonce is correct, save settings
		if ( ( isset( $_POST["save_settings_nonce"] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_settings_nonce'] ) ), 'save_settings' ) ) || ( isset( $_POST["set_global_default_settings_nonce"] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['set_global_default_settings_nonce'] ) ), 'set_global_default_settings' ) ) ) {
			// Cycle through fields to retrieve all posted values
			$settings_array_before_update = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( $section );
			$settings_array = array();
			foreach ( $fields as $field ) {
				// Sanitize the values based on type
				$sanitized_value = '';
				if ( ( isset( $_POST[ $field["id"] ] ) && 'multiple_fields' !== $field["type"] ) || 'selectinput' == $field["type"] ) {
					switch ( $field["type"] ) {
						case 'text':
							$sanitized_value = esc_html( sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) ) );
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
						case 'selectinput':
							$sanitized_value = array();
							$category_select_key = isset( $_POST["category_select_key"] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST["category_select_key"] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							$question_limit_key = isset( $_POST["question_limit_key"] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST["question_limit_key"] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							$sanitized_value['category_select_key'] = $category_select_key;
							$sanitized_value['question_limit_key'] = $question_limit_key ;
							$sanitized_value  = maybe_serialize( $sanitized_value );
							break;
						default:
							$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field["id"] ] ) );
							break;
					}
				}
				if ( 'multiple_fields' == $field["type"] ) {
					foreach ( $field["fields"] as $key => $value ) {
						switch ( $value["type"] ) {
							case 'url':
								$sanitized_value = isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : "";
								break;
							case 'checkbox':
								$sanitized_value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : 0;
								break;
							case 'number':
								$sanitized_value = isset( $_POST[ $key ] ) ? intval( $_POST[ $key ] ) : "";
								break;
							default:
								$sanitized_value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : "";
								break;
						}
						$settings_array[ $key ] = $sanitized_value;
					}
				}else {
					$settings_array[ $field["id"] ] = $sanitized_value;
				}
			}

			$quiz_id = isset( $_GET["quiz_id"] ) ? intval( $_GET["quiz_id"] ) : 0;
			// Update the settings and show alert based on outcome
			$settings_array = wp_parse_args( $settings_array, $settings_array_before_update );
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
								if ( ! empty( $field['type'] ) && 'multiple_fields' === $field['type'] ) {
									QSM_Fields::generate_field( $field, $settings );
								}else {
									QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
								}
							}
						}
						?>
					</table>
				</div>
				<div id="quiz_submission" class="quiz_style_tab_content" style="display:none">
					<table class="form-table" style="width: 100%;">
						<?php
						$settings = apply_filters( 'qsm_quiz_submission_section_before', $settings, $fields );
						// Cycles through each field
						foreach ( $fields as  $field ) {
							// Generate the field
							if ( isset( $field['option_tab'] ) && 'quiz_submission' === $field['option_tab'] ) {
								if ( ! empty( $field['type'] ) && 'multiple_fields' === $field['type'] ) {
									QSM_Fields::generate_field( $field, $settings );
								}else {
									QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
								}
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
								if ( ! empty( $field['type'] ) && 'multiple_fields' === $field['type'] ) {
									QSM_Fields::generate_field( $field, $settings );
								}else {
									QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
								}
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
								if ( ! empty( $field['type'] ) && 'multiple_fields' === $field['type'] ) {
									QSM_Fields::generate_field( $field, $settings );
								}else {
									QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
								}
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
			<div class="option-page-option-tab-footer">
				<div class="footer-bar-notice">
					<?php $mlwQuizMasterNext->alertManager->showAlerts() ?>
				</div>
				<div class="result-tab-footer-buttons">
					<?php if ( isset($_GET['tab'], $_GET['page']) && 'options' == sanitize_text_field( wp_unslash( $_GET['tab'] ) ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'mlw_quiz_options' ) {?>
						<a class="button-secondary" id="qsm-blobal-settings" href="javascript:void(0)" ><?php esc_html_e('Reset to Defaults', 'quiz-master-next'); ?></a>
					<?php } ?>
					<button class="button-primary" type="submit"> <?php esc_html_e('Save Changes', 'quiz-master-next'); ?></button>
				</div>
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
		if ( ( is_null( $field["id"] ) || empty( $field["id"] ) ) && 'multiple_fields' !== $field['type'] ) {
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
	 * Generate multiple fields
	 *
	 * @since 8.1.17
	 * @param array $fields The array that contains the data for all fields
	 * @param array $settings The array that holds the settings for this section
	 */
	public static function generate_multiple_fields_field( $fields, $value ) {
		?>
		<tr valign="top" class="<?php echo ! empty( $fields['container_class'] ) ? esc_attr( $fields['container_class'] ) : ''; ?>">
			<th scope="row" class="qsm-opt-tr">
				<label><?php echo wp_kses_post( $fields['label'] ); ?></label>
				<?php if ( isset($fields['tooltip']) && '' !== $fields['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $fields['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<?php
				foreach ( $fields['fields'] as $key => $field ) {
					if ( isset( $value[ $key ] ) ) {
						?>
						<fieldset class="buttonset buttonset-hide" data-hide='1' id="<?php echo esc_attr( $key ); ?>">
						<?php
						if ( ! empty( $field['prefix_text'] ) ) {
							echo wp_kses_post( $field['prefix_text'] );
						}
						switch ( $field["type"] ) {
							case 'checkbox':
								foreach ( $field["options"] as $option ) {
									?>
									<label class="qsm-option-label" for="<?php echo esc_attr( $key . '-' . $option["value"] ); ?>">
										<input type="checkbox" id="<?php echo esc_attr( $key . '-' . $option["value"] ); ?>"
											name="<?php echo esc_attr( $key ); ?>" <?php checked( $option["value"], $value[ $key ] ); ?>
											value="<?php echo esc_attr( $option["value"] ); ?>" />
										<?php echo isset( $option["label"] ) ? wp_kses_post( $option["label"] ) : ""; ?>
									</label>
									<?php
								}
								break;
							case 'radio':
								foreach ( $field["options"] as $option ) {
									?>
									<label class="qsm-option-label" for="<?php echo esc_attr( $key . '-' . $option["value"] ); ?>">
										<input type="radio" id="<?php echo esc_attr( $key . '-' . $option["value"] ); ?>" name="<?php echo esc_attr( $key ); ?>" <?php checked( $option["value"], $value[ $key ] ); ?> value="<?php echo esc_attr( $option["value"] ); ?>" />
										<?php
										$allowed_tags = wp_kses_allowed_html('post');
										$allowed_tags['input'] = array(
											'class' => 1,
											'id'    => 1,
											'type'  => 1,
											'name'  => 1,
											'value' => 1,
										);
										echo isset( $option["label"] ) ? wp_kses( $option["label"], $allowed_tags ) : ""; ?>
									</label>
									<?php
								}
								break;
							case 'date':
								?>
								<input autocomplete="off" class="qsm-date-picker" type="text" placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>-input" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value[ $key ] ); ?>" />
								<?php
								break;
							case 'number':
								?>
								<input class="small-text" type="number" placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" step="1" min="<?php echo ! empty($field['min']) ? esc_attr($field['min']) : 0; ?>" id="<?php echo esc_attr( $key ); ?>-input" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value[ $key ] ); ?>" />
								<?php
								break;
							case 'textarea':
								?>
								<textarea placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>-input" name="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value[ $key ] ); ?></textarea>
								<?php
								break;
							case 'image':
								?>
								<div class="qsm-image-field">
									<input placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" type="text" class="qsm-image-input" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value[ $key ] ); ?>">
									<a class="qsm-image-btn button" class="button"><span class="dashicons dashicons-format-image"></span> <?php echo esc_html( $field['button_label'] ); ?></a>
								</div>
								<?php
								break;
							case 'select':
								?>
								<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>-select">
									<?php
									foreach ( $field["options"] as $option ) {
										?>
										<option <?php selected( $option["value"], $value[ $key ] ); ?> value="<?php echo esc_attr( $option["value"] ); ?>"><?php echo wp_kses_post( $option["label"] ); ?></option>
										<?php
									}
									?>
								</select>
								<?php
								break;
							default:
								?>
								<input type="text" placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>-input" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value[ $key ] ); ?>" />
								<?php
								break;
						}
						if ( ! empty( $field['suffix_text'] ) ) {
							echo wp_kses_post( $field['suffix_text'] );
						}
						?>
						</fieldset>
						<?php
					}
				}
				if ( isset($fields['help']) && '' !== $fields['help'] ) { ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $fields['help'] ); ?></span>
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
				<?php
				wp_editor( htmlspecialchars_decode( $value, ENT_QUOTES ), $field["id"], array(
					'tinymce' => true,
				) );
				?>
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
				<input class="qsm-date-picker" autocomplete="off" type="text" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
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
		global $mlwQuizMasterNext;
		$limit_category_checkbox = $mlwQuizMasterNext->pluginHelper->get_section_setting('quiz_options','limit_category_checkbox');
		$display = "";
		if ( ! empty($limit_category_checkbox) && 'question_per_category' == $field["id"] ) {
			$display = "style='display:none;'";
		}
		$prefix_text = isset($field['prefix_text']) ? $field['prefix_text']." " : "";
		$suffix_text = isset($field['suffix_text']) ? " ".$field['suffix_text'] : "";
		?>
		<tr class="<?php echo ! empty($field['container_class']) ? esc_attr($field['container_class']) : ""; ?>" valign="top" <?php echo esc_html( $display ); ?>>
			<th scope="row" class="qsm-opt-tr">
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
				<?php if ( isset($field['tooltip']) && '' !== $field['tooltip'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php echo wp_kses_post( $field['tooltip'] ); ?></span>
				</span>
				<?php } ?>
			</th>
			<td>
				<?php echo wp_kses_post( $prefix_text ); ?><input class="small-text" type="number" step="1" min="<?php echo ! empty($field['min']) ? esc_attr($field['min']) : 0; ?>" id="<?php echo esc_attr( $field["id"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" value="<?php echo esc_attr($value); ?>" /><?php echo wp_kses_post( $suffix_text ); ?>
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
		if ( ! empty($field['container_class']) ) {
			$class .= ' '.$field['container_class'];
		}
		?>
		<tr valign="top" class="<?php echo esc_attr( $class ); ?>" >
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
						<label for="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>">
						<input type="radio" id="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>" name="<?php echo esc_attr( $field["id"] ); ?>" <?php checked( $option["value"], $value ); ?> value="<?php echo esc_attr( $option["value"] ); ?>" />
						<?php echo wp_kses_post( $option["label"] ); ?></label>
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
		global $wpdb,$mlwQuizMasterNext;
		$quiz_id = isset($_GET['quiz_id']) ? sanitize_text_field( wp_unslash( $_GET['quiz_id'] ) ) : 0;
		$explode_cat = explode(',', $value);
		$limit_category_checkbox = $mlwQuizMasterNext->pluginHelper->get_section_setting('quiz_options','limit_category_checkbox');
		$question_per_category = $mlwQuizMasterNext->pluginHelper->get_section_setting('quiz_options','question_per_category');
		?>
		<tr valign="top" <?php echo ! empty($limit_category_checkbox) || empty( $question_per_category ) ? 'style="display:none;"' : '';?> class="qsm-category-list-row">
			<th scope="row" class="qsm-opt-tr">

			</th>
			<td>
				<label for="qsm-option-<?php echo esc_attr( $field["id"] ); ?>"><strong><?php echo wp_kses_post( $field['label'] ); ?></strong></label>
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
						?>
						<select class="category_selection_random" multiple="" id="qsm-option-<?php echo esc_attr( $field["id"] ); ?>">
							<?php
							if ( $multiple_category_system ) {
								echo QSM_Fields::get_category_hierarchical_options( $categories_tree, $explode_cat );
							} else {
								foreach ( $cat_array as $single_cat ) {
									?><option <?php echo in_array( $single_cat, $explode_cat, true ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $single_cat ); ?>"><?php echo wp_kses_post( $single_cat ); ?></option><?php
								}
							}
							?>
						</select>
						<?php
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
		$class = "";
		if ( 'form_type_1' != $show_option ) {
			$class = $show_option ? $show_option . ' hidden qsm_hidden_tr qsm_hidden_tr_gradingsystem' : '';
		}
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
						<label for="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>">
							<input type="checkbox" id="<?php echo esc_attr( $field["id"] . '-' . $option["value"] ); ?>"
								name="<?php echo esc_attr( $field["id"] ); ?>" <?php checked( $option["value"], $score_roundoff ); ?>
								value="<?php echo esc_attr( $option["value"] ); ?>" />
							<?php echo isset( $option["label"] ) ? wp_kses_post( $option["label"] ) : ""; ?>
						</label>
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
	 * Generates checkbox inputs
	 *
	 * @since 7.1.10
	 * @param array $field The array that contains the data for the input field
	 * @param mixed $value The current value of the setting
	 */
	public static function generate_selectinput_field( $field, $value ) {
		global $wpdb,$mlwQuizMasterNext;
		$show_option = isset( $field['show_option'] ) ? $field['show_option'] : '';

		$value = ! empty($value) ? maybe_unserialize($value) : array(
			"category_select_key"     => array(),
			"question_limit_category" => array(),
		) ;
		$quiz_id = isset($_GET['quiz_id']) ? sanitize_text_field( wp_unslash( $_GET['quiz_id'] ) ) : 0;
		$limit_category_checkbox = $mlwQuizMasterNext->pluginHelper->get_section_setting('quiz_options','limit_category_checkbox');
		?>
		<tr valign="top" <?php echo empty( $limit_category_checkbox ) ? 'style="display:none;"' : '';?> class="qsm-category-list-row" >
			<th scope="row" class="qsm-opt-tr">

			</th>
			<td>
				<label for="<?php echo esc_attr( $field["id"] ); ?>"><strong><?php echo wp_kses_post( $field['label'] ); ?></strong></label>
				<div class="select-category-question-limit-maindiv">
				<?php
					$categories = QSM_Questions::get_quiz_categories( $quiz_id );
					$category_select_key = ( ! empty( $value['category_select_key'] ) ) ? $value['category_select_key'] : array();
					if ( count ( $category_select_key) == 0 && ! empty( $categories ) ) { ?>
						<div class = "select-category-question-limit-subdiv">
							<select class="question_limit_category" name="category_select_key[]">
								<option value=""><?php esc_html_e( 'Select Category', 'quiz-master-next' ); ?></option>
								<?php
								if ( ! empty($categories['list'] ) ) {
									foreach ( $categories['list'] as $key => $single_cat ) {
										?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $single_cat ); ?></option><?php
									}
								} ?>
							</select>
							<label>
								<input type="number" name="question_limit_key[]"  value=""  placeholder="Set Question Limit" >
							</label>
							<a href="javascript:void(0)" class="delete-category-button">
								<span class="dashicons dashicons-remove"></span>
							</a>
						</div>
					</div>
					<div class="add-more-link">
						<a href="javascript:void(0)" class="add-more-category" >+<?php esc_html_e('Add','quiz-master-next'); ?></a>
					</div>
				<?php
				} elseif ( ! empty( $category_select_key ) ) {
					$i = 0 ;
					foreach ( $category_select_key as $categorylist ) {
					?>
					<div class = "select-category-question-limit-subdiv">
						<select class="question_limit_category" name="category_select_key[]">
							<option value=""><?php esc_html_e( 'Select Category', 'quiz-master-next' ); ?></option>
							<?php
							if ( ! empty($categories['list'] ) ) {
								foreach ( $categories['list'] as $key => $single_cat ) {
									?><option <?php echo ( isset( $category_select_key [ $i ]) && ($key == $category_select_key[ $i ]) ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $single_cat ); ?></option><?php
								}
							}?>
						</select>
						<label>
							<input type="number" name="question_limit_key[]"  value="<?php  echo esc_attr( $value['question_limit_key'][ $i ] );?>"  placeholder="<?php esc_html_e('Set Question Limit','quiz-master-next'); ?>" >
						</label>
						<a href="javascript:void(0)" class="delete-category-button">
							<span class="dashicons dashicons-remove"></span>
						</a>
					</div>
				<?php $i++;
			 	}
				?>
				</div>
				<div class="add-more-link">
					<a href="javascript:void(0)" class="add-more-category" >+<?php esc_html_e('Add','quiz-master-next'); ?></a>
				</div>
			<?php
			} else {
				echo 'No category found.';
			} ?>
				<span class="qsm-opt-desc"><?php echo wp_kses_post( $field['help'] ); ?></span>
			</td>
		</tr>
		<?php
	}
}
?>