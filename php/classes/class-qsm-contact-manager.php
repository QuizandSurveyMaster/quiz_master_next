<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles the contact fields for the quiz
 *
 * @since 5.0.0
 */
class QSM_Contact_Manager {

	/** @var array The fields loaded for the quiz. */
	private static $fields = array();

	/**
	 * Get the default fields
	 *
	 * @since 8.0
	 * @return array The array of default contact fields
	 */
	public static function default_fields() {
		return array(
			'name'  => array(
				'label'      => 'Name',
				'type'       => 'text',
				'use'        => 'name',
				'required'   => 'false',
				'enable'     => 'false',
				'is_default' => 'true',
			),
			'email' => array(
				'label'      => 'Email',
				'type'       => 'email',
				'use'        => 'email',
				'required'   => 'false',
				'enable'     => 'false',
				'is_default' => 'true',
			),
			'comp'  => array(
				'label'      => 'Business',
				'type'       => 'text',
				'use'        => 'comp',
				'required'   => 'false',
				'enable'     => 'false',
				'is_default' => 'true',
			),
			'phone' => array(
				'label'      => 'Phone',
				'type'       => 'text',
				'use'        => 'phone',
				'required'   => 'false',
				'enable'     => 'false',
				'is_default' => 'true',
			),
		);
	}

	/**
	 * Displays the contact fields during form
	 *
	 * @since 5.0.0
	 * @param object $options The quiz options.
	 * @return string The HTML for the contact fields
	 */
	public static function display_fields( $options ) {

		global $mlwQuizMasterNext;
		$fields_hidden = false;
		$name          = '';
		$email         = '';

		ob_start();

		// Prepares name and email if user is logged in.
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$name         = $current_user->display_name;
			$email        = $current_user->user_email;
		}

		// If user is logged in and the option to allow users to edit is set to no...
		if ( is_user_logged_in() && 1 === intval( $options->loggedin_user_contact ) ) {
			// ..then, hide the fields.
			$fields_hidden = true;
			?>
			<div style="display:none;">
			<?php
		}

		// Loads fields.
		$fields = self::load_fields();
		$contact_disable_autofill = $options->contact_disable_autofill;

		// If fields are empty and backwards-compatible fields are turned on then, use older system.
		if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 !== intval($options->user_name) || 2 !== intval($options->user_comp) || 2 !== intval($options->user_email) || 2 !== intval($options->user_phone) ) ) {

			// Check for name field.
			if ( 2 !== intval($options->user_name) ) {
				$class = '';
				if ( 1 === intval($options->user_name) && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->name_field_text, "quiz_name_field_text-{$options->quiz_id}" ) ); ?></span>
				<input <?php if ( $contact_disable_autofill ) { echo "autocomplete='off'"; } ?> type='text' class='<?php echo esc_attr( $class ); ?>' name='mlwUserName' placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->name_field_text, "quiz_name_field_text-{$options->quiz_id}" ) ); ?>" value='<?php echo esc_attr( $name ); ?>' />
				<?php
			}

			// Check for comp field.
			if ( 2 !== intval($options->user_comp) ) {
				$class = '';
				if ( 1 === intval($options->user_comp) && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->business_field_text, "quiz_business_field_text-{$options->quiz_id}" ) ); ?></span>
				<input <?php if ( $contact_disable_autofill ) { echo "autocomplete='off'"; } ?> type='text' class='<?php echo esc_attr( $class ); ?>' name='mlwUserComp' placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->business_field_text, "quiz_business_field_text-{$options->quiz_id}" ) ); ?>" value='' />
				<?php
			}

			// Check for email field.
			if ( 2 !== intval($options->user_email) ) {
				$class = '';
				if ( 1 === intval($options->user_email) && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->email_field_text, "quiz_email_field_text-{$options->quiz_id}" ) ); ?></span>
				<input <?php if ( $contact_disable_autofill ) { echo "autocomplete='off'"; } ?> type='email' class='mlwEmail <?php echo esc_attr( $class ); ?>' name='mlwUserEmail' placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->email_field_text, "quiz_email_field_text-{$options->quiz_id}" ) ); ?>" value='<?php echo esc_attr( $email ); ?>' />
				<?php
			}

			// Check for phone field.
			if ( 2 !== intval($options->user_phone) ) {
				$class = '';
				if ( 1 === intval($options->user_phone) && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->phone_field_text, "quiz_phone_field_text-{$options->quiz_id}" ) ); ?></span>
				<input <?php if ( $contact_disable_autofill ) { echo "autocomplete='off'"; } ?> type='number' class='<?php echo esc_attr( $class ); ?>' name='mlwUserPhone' placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->phone_field_text, "quiz_phone_field_text-{$options->quiz_id}" ) ); ?>" value='' />
				<?php
			}
		} elseif ( ! empty( $fields ) && is_array( $fields ) ) {

			// Cycle through each of the contact fields.
			$total_fields = count( $fields );
			for ( $i = 0; $i < $total_fields; $i++ ) {

				if ( 'true' === $fields[ $i ]["enable"] || true === $fields[ $i ]["enable"] ) {
					$value = '';
					?>
					<div class="qsm_contact_div qsm-contact-type-<?php echo esc_attr( $fields[ $i ]['type'] ); ?>">
						<?php
						if ( 'name' === $fields[ $i ]['use'] ) {
							$value = $name;
						}
						if ( 'email' === $fields[ $i ]['use'] ) {
							$value = $email;
						}
						self::generate_contact_field($fields[ $i ], $i, $options, $value);
					?>
					</div>
					<?php
				}
			}
		}

		// Extend contact fields section.
		do_action( 'qsm_contact_fields_end' );

		// If logged in user should see fields.
		if ( is_user_logged_in() && 1 === intval( $options->loggedin_user_contact ) ) {
			?>
			</div>
			<?php
		}

		// Return contact field HTML.
		return ob_get_clean();
	}

	/**
	 * Process the contact fields and return the values
	 *
	 * @since 5.0.0
	 * @param object $options The quiz options.
	 * @return array An array of all labels and values for the contact fields
	 */
	public static function process_fields( $options ) {
		global $mlwQuizMasterNext;
		$responses = array();

		// Loads the fields for the quiz.
		$fields = self::load_fields();

		// If fields are empty, check for backwards compatibility.
		if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 !== intval( $options->user_name ) || 2 !== intval( $options->user_comp ) || 2 !== intval( $options->user_email ) || 2 !== intval( $options->user_phone ) ) ) {
			$responses[] = array(
				'label' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->name_field_text, "quiz_name_field_text-{$options->quiz_id}" ),
				'value' => isset( $_POST["mlwUserName"] ) ? sanitize_text_field( wp_unslash( $_POST["mlwUserName"] ) ) : 'None',
				'use'   => 'name',
			);
			$responses[] = array(
				'label' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->business_field_text, "quiz_business_field_text-{$options->quiz_id}" ),
				'value' => isset( $_POST["mlwUserComp"] ) ? sanitize_text_field( wp_unslash( $_POST["mlwUserComp"] ) ) : 'None',
				'use'   => 'comp',
			);
			$responses[] = array(
				'label' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->email_field_text, "quiz_email_field_text-{$options->quiz_id}" ),
				'value' => isset( $_POST["mlwUserEmail"] ) ? sanitize_email( wp_unslash( $_POST["mlwUserEmail"] ) ) : 'None',
				'use'   => 'email',
			);
			$responses[] = array(
				'label' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->phone_field_text, "quiz_phone_field_text-{$options->quiz_id}" ),
				'value' => isset( $_POST["mlwUserPhone"] ) ? sanitize_text_field( wp_unslash( $_POST["mlwUserPhone"] ) ) : 'None',
				'use'   => 'phone',
			);
		} elseif ( ! empty( $fields ) && is_array( $fields ) ) {
			$total_fields = count( $fields );
			for ( $i = 0; $i < $total_fields; $i++ ) {
				$field_label = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $fields[ $i ]['label'], "quiz_contact_field_text-{$i}-{$options->quiz_id}" );
				$field_array = array(
					'label' => $field_label,
					'value' => isset( $_POST[ "contact_field_$i" ] ) ? htmlentities( sanitize_text_field( wp_unslash( $_POST[ "contact_field_$i" ] ) ) ) : 'None',
				);
				if ( isset( $fields[ $i ]['use'] ) ) {
					$field_array['use'] = $fields[ $i ]['use'];
				}
				if ( isset( $fields[ $i ]['type'] ) ) {
					$field_array['type'] = $fields[ $i ]['type'];
				}
				$responses[] = $field_array;
			}
		}

		// For backwards compatibility, use the 'use' fields for setting $_POST values of older version of contact fields.
		foreach ( $responses as $field ) {
			if ( isset( $field['use'] ) ) {
				switch ( $field['use'] ) {
					case 'name':
						$_POST["mlwUserName"] = $field["value"];
						break;

					case 'comp':
						$_POST["mlwUserComp"] = $field["value"];
						break;

					case 'email':
						$_POST["mlwUserEmail"] = $field["value"];
						break;

					case 'phone':
						$_POST["mlwUserPhone"] = $field["value"];
						break;
				}
			}
		}

		return $responses;
	}

	/**
	 * Loads the fields
	 *
	 * @since 5.0.0
	 * @uses QMNPluginHelper::get_quiz_setting
	 * @return array The array of contact fields
	 */
	public static function load_fields( $type = 'display' ) {
		global $mlwQuizMasterNext;
		$default_fields  = self::default_fields();
		$fields          = maybe_unserialize( $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'contact_form' ) );
		if ( ! empty( $fields ) ) {
			$used_keys = array();
			foreach ( $fields as $index => $field ) {
				/**
				 * For backward compatibility, Set enable to true for existing fields.
				 */
				if ( ! isset( $field['enable'] ) ) {
					$fields[ $index ]['enable'] = 'true';
				}
				if ( isset( $field['use'] ) && array_key_exists( $field['use'], $default_fields ) ) {
					$fields[ $index ]['is_default'] = 'true';
					$used_keys[] = $field['use'];
				}
			}

			/**
			 * Add missing default fields for edit screen
			 */
			if ( 'edit' == $type ) {
				/**
				 * Find out missing fields.
				 */
				$missing = array_diff( array_keys( $default_fields ), $used_keys );
				foreach ( $default_fields as $key => $field ) {
					if ( in_array( $key, $missing, true ) ) {
						$fields[] = $field;
					}
				}
			}
		}

		/**
		 * If $field is empty, Return default fields for edit screen.
		 */
		if ( empty( $fields ) && 'edit' == $type ) {
			foreach ( $default_fields as $key => $field ) {
				$fields[] = $field;
			}
		}
		return $fields;
	}

	/**
	 * Saves the contact fields
	 *
	 * @since 5.0.0
	 * @uses QMNPluginHelper::prepare_quiz
	 * @uses QMNPluginHelper::update_quiz_setting
	 * @param int   $quiz_id The ID for the quiz.
	 * @param array $fields The fields for the quiz.
	 */
	public static function save_fields( $quiz_id, $fields ) {
		global $mlwQuizMasterNext;
		if ( self::load_fields() === $fields ) {
			return true;
		}

		if ( ! is_array( $fields ) || empty( $fields ) ) {
			//return false;
		}

		$quiz_id = intval( $quiz_id );
		if ( 0 === $quiz_id ) {
			return false;
		}

		//Allow br and anchor tag
		$allowed_html = array(
			"a" => array(
				"href" => array(),
			),
		);

		$is_not_allow_html = apply_filters( 'qsm_admin_contact_label_disallow_html', true );

		if ( ! empty( $fields ) ) {
			$total_fields = count( $fields );
			for ( $i = 0; $i < $total_fields; $i ++ ) {
				$label       = $is_not_allow_html ? $fields[ $i ]['label'] : wp_kses( wp_unslash( $fields[ $i ]['label'] ), $allowed_html );
				$fields[ $i ]['label']  = $label;
				$mlwQuizMasterNext->pluginHelper->qsm_register_language_support( $label, "quiz_contact_field_text-{$i}-{$quiz_id}" );
			}
		}

		$mlwQuizMasterNext->pluginHelper->prepare_quiz( intval( $quiz_id ) );
		return $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'contact_form', $fields );
	}

	/**
	 * Generate Contact Field HTML
	 * @param type $field
	 * @param type $quiz_options
	 * @param type $default_value
	 */
	public static function generate_contact_field( $field, $index, $quiz_options, $default_value = '' ) {
		global $mlwQuizMasterNext;
		$fields_hidden               = false;
		$contact_disable_autofill    = $quiz_options->contact_disable_autofill;
		if ( is_user_logged_in() && 1 === intval( $quiz_options->loggedin_user_contact ) ) {
			$fields_hidden = true;
		}
		$field_label = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $field['label'], "quiz_contact_field_text-{$index}-{$quiz_options->quiz_id}" );
		$fieldAttr   = " name='contact_field_" . esc_attr( $index ) . "' id='contact_field_" . esc_attr( $index ) . "' ";
		$class       = '';
		if ( ( 'true' === $field["required"] || true === $field["required"] ) && ! $fields_hidden ) {
			$class .= 'mlwRequiredText qsm_required_text';
			if ( 'checkbox' === $field["type"] ) {
				$class .= ' mlwRequiredAccept';
			}
		}
		switch ( $field['type'] ) {
			case 'text':
				if ( 'phone' === $field['use'] ) {
					$class .= 'mlwPhoneNumber';
				}
				// Filer Value
				if ( empty( $contact_disable_autofill ) ) {
					$default_value   = apply_filters( 'qsm_contact_text_field_value', $default_value, $field['use'] );
					$fieldAttr       .= " value='" . esc_attr( $default_value ) . "' ";
				} else {
					$fieldAttr .= " autocomplete='off' ";
				}
				/**
				 * Add Phone validation
				 */
				if ( 'phone' === $field['use'] ) {
					$fieldAttr .= " onkeydown='return event.keyCode !== 69' ";
				}
				/**
				 * Add minimum length validation
				 */
				if ( isset( $field['minlength'] ) && 0 < intval( $field['minlength'] ) ) {
					$fieldAttr   .= " minlength='" . intval( $field['minlength'] ) . "' ";
					$class       .= ' mlwMinLength ';
				}
				/**
				 * Add maximum length validation
				 */
				if ( isset( $field['maxlength'] ) && 0 < intval( $field['maxlength'] ) ) {
					$fieldAttr   .= " maxlength='" . intval( $field['maxlength'] ) . "' ";
					$class       .= ' mlwMaxLength ';
				}

				$fieldAttr   .= " placeholder='" . esc_attr( wp_strip_all_tags( $field_label ) ) . "' ";
				$class       = apply_filters( 'qsm_contact_text_field_class', $class, $field['use'] );
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo esc_attr( $field_label ); ?></span>
				<input type='text' class='<?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
				<?php
				break;

			case 'email':
				// Filer Value
				if ( empty( $contact_disable_autofill ) ) {
					$default_value   = apply_filters( 'qsm_contact_text_field_value', $default_value, $field['use'] );
					$fieldAttr       .= " value='" . esc_attr( $default_value ) . "' ";
				} else {
					$fieldAttr .= " autocomplete='off' ";
				}
				if ( isset( $field['allowdomains'] ) && ! empty( $field['allowdomains'] ) ) {
					$allowdomains    = array_map( 'trim', explode( ',', $field['allowdomains'] ) );
					$fieldAttr       .= " data-domains='" . implode( ',', array_filter( $allowdomains ) ) . "' ";
				}
				$class       = apply_filters( 'qsm_contact_email_field_class', $class, $field['use'] );
				$fieldAttr   .= " placeholder='" . esc_attr( wp_strip_all_tags( $field_label ) ) . "' ";
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo esc_attr( $field_label ); ?></span>
				<input type='email' class='mlwEmail <?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
				<?php
				break;

			case 'checkbox':
				$class = apply_filters( 'qsm_contact_checkbox_field_class', $class, $field['use'] );
				?>
				<input type='checkbox' class='<?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value='checked' />
				<label class='mlw_qmn_question qsm_question' for='contact_field_<?php echo esc_attr( $index ); ?>'><?php echo wp_kses_post( $field_label ); ?></label>
				<?php
				break;

			case 'date':
				// Filer Value
				if ( empty( $contact_disable_autofill ) ) {
					$default_value   = apply_filters( 'qsm_contact_text_field_value', $default_value, $field['use'] );
					$fieldAttr       .= " value='" . esc_attr( $default_value ) . "' ";
				} else {
					$fieldAttr .= " autocomplete='off' ";
				}
				$class = apply_filters( 'qsm_contact_date_field_class', $class, $field['use'] );
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo esc_attr( $field_label ); ?></span>
				<input type='date' class='<?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value='' />
				<?php
				break;

			case 'url':
				// Filer Value
				if ( empty( $contact_disable_autofill ) ) {
					$default_value   = apply_filters( 'qsm_contact_url_field_value', $default_value, $field['use'] );
					$fieldAttr       .= " value='" . esc_attr( $default_value ) . "' ";
				} else {
					$fieldAttr .= " autocomplete='off' ";
				}
				$class       = apply_filters( 'qsm_contact_url_field_class', $class, $field['use'] );
				$fieldAttr   .= " placeholder='" . esc_attr( wp_strip_all_tags( $field_label ) ) . "' ";
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo esc_attr( $field_label ); ?></span>
				<input type='url' class='mlwUrl <?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
				<?php
				break;

			case 'number':
				// Filer Value
				if ( empty( $contact_disable_autofill ) ) {
					$default_value   = apply_filters( 'qsm_contact_number_field_value', $default_value, $field['use'] );
					$fieldAttr       .= " value='" . esc_attr( $default_value ) . "' ";
				} else {
					$fieldAttr .= " autocomplete='off' ";
				}
				/**
				 * Add minimum length validation
				 */
				if ( isset( $field['minlength'] ) && 0 < intval( $field['minlength'] ) ) {
					$fieldAttr   .= " minlength='" . intval( $field['minlength'] ) . "' ";
					$class       .= ' mlwMinLength ';
				}
				/**
				 * Add maximum length validation
				 */
				if ( isset( $field['maxlength'] ) && 0 < intval( $field['maxlength'] ) ) {
					$class .= ' mlwMaxLength ';
				}
				$class       = apply_filters( 'qsm_contact_number_field_class', $class, $field['use'] );
				$fieldAttr   .= " placeholder='" . esc_attr( wp_strip_all_tags( $field_label ) ) . "' ";
				?>
				<span class='mlw_qmn_question qsm_question'><?php echo esc_attr( $field_label ); ?></span>
				<input type='number' class='mlwRequiredNumber <?php echo esc_attr( $class ); ?>' <?php echo $fieldAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php if ( isset( $field['maxlength'] ) && 0 < intval( $field['maxlength'] ) ) : ?>maxlength='<?php echo intval( $field['maxlength'] ); ?>' oninput='maxLengthCheck(this)' <?php endif; ?> />
				<?php
				break;
			default:
				do_action( 'qsm_extra_contact_form_field_display', $field, $quiz_options, $index,$default_value );
				break;
		}
	}
}
