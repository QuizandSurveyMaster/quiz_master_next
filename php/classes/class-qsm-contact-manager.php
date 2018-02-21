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
	 * Displays the contact fields during form
	 *
	 * @since 5.0.0
	 * @param object $options The quiz options.
	 * @return string The HTML for the contact fields
	 */
	public static function display_fields( $options ) {

		$return = '';
		$fields_hidden = false;

		// Prepare name and email values from profile if logged in.
		$name = '';
		$email = '';
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$name = $current_user->display_name;
			$email = $current_user->user_email;
		}

		// If user is logged in and the option to allow users to edit is set to no...
		if ( is_user_logged_in() && 1 == $options->loggedin_user_contact ) {
			// ..then, hide the fields.
			$fields_hidden = true;
			$return = '<div style="display:none;">';
		}

		// Loads fields.
		$fields = self::load_fields();

		// If fields are empty and backwards-compatible fields are turned on then, use older system.
		if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 != $options->user_name || 2 != $options->user_comp || 2 != $options->user_email || 2 != $options->user_phone ) ) {

			// Check for name field.
			if ( 2 != $options->user_name ) {
				$class = '';
				if ( 1 == $options->user_name && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				$return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->name_field_text, ENT_QUOTES ) . "</span>";
				$return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserName' value='$name' />";
			}

			// Check for comp field.
			if ( 2 != $options->user_comp ) {
				$class = '';
				if ( 1 == $options->user_comp && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				$return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->business_field_text, ENT_QUOTES ) . "</span>";
				$return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserComp' value='' />";
			}

			// Check for email field.
			if ( 2 != $options->user_email ) {
				$class = '';
				if ( 1 == $options->user_email && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				$return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->email_field_text, ENT_QUOTES ) . "</span>";
				$return .= "<input type='email' class='mlwEmail $class' x-webkit-speech name='mlwUserEmail' value='$email' />";
			}

			// Check for phone field.
			if ( 2 != $options->user_phone ) {
				$class = '';
				if ( 1 == $options->user_phone && ! $fields_hidden ) {
					$class = 'mlwRequiredText qsm_required_text';
				}
				$return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->phone_field_text, ENT_QUOTES ) . "</span>";
				$return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserPhone' value='' />";
			}
		} elseif ( ! empty( $fields ) && is_array( $fields ) ) {

			// Cycle through each of the contact fields.
			$total_fields = count( $fields );
			for ( $i = 0; $i < $total_fields; $i++ ) {

				$return .= '<div class="qsm_contact_div">';
				$class = '';
				$return .= "<span class='mlw_qmn_question qsm_question'>{$fields[ $i ]['label']}</span>";
				$value = '';
				if ( 'name' == $fields[ $i ]['use'] ) {
					$value = $name;
				}
				if ( 'email' == $fields[ $i ]['use'] ) {
					$value = $email;
				}

				// Switch for contact field type.
				switch ( $fields[ $i ]['type'] ) {
					case 'text':
						if ( ( 'true' === $fields[ $i ]["required"] || true === $fields[ $i ]["required"] ) && ! $fields_hidden ) {
							$class = 'mlwRequiredText qsm_required_text';
						}
						$return .= "<input type='text' class='$class' x-webkit-speech name='contact_field_$i' value='$value' />";
						break;

					case 'email':
						if ( ( 'true' === $fields[ $i ]["required"] || true === $fields[ $i ]["required"] ) && ! $fields_hidden ) {
							$class = 'mlwRequiredText qsm_required_text';
						}
						$return .= "<input type='text' class='mlwEmail $class' x-webkit-speech name='contact_field_$i' value='$value' />";
						break;

					case 'checkbox':
						if ( ( 'true' === $fields[ $i ]["required"] || true === $fields[ $i ]["required"] ) && ! $fields_hidden ) {
							$class = 'mlwRequiredAccept qsm_required_accept';
						}
						$return .= "<input type='checkbox' class='$class' x-webkit-speech name='contact_field_$i' value='checked' />";
						break;

					default:
						break;
				}

				$return .= '</div>';
			}
		}

		// If logged in user should see fields.
		if ( is_user_logged_in() && 1 == $options->loggedin_user_contact ) {
			$return .= '</div>';
		}

		// Return contact field HTML.
		return $return;
	}

	/**
	 * Process the contact fields and return the values
	 *
	 * @since 5.0.0
	 * @param object $options The quiz options.
	 * @return array An array of all labels and values for the contact fields
	 */
	public static function process_fields( $options ) {

		$responses = array();

		// Loads the fields for the quiz.
		$fields = self::load_fields();

		// If fields are empty, check for backwards compatibility.
		if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 != $options->user_name || 2 != $options->user_comp || 2 != $options->user_email || 2 != $options->user_phone ) ) {
			$responses[] = array(
			'label' => 'Name',
			'value' => isset( $_POST["mlwUserName"] ) ? sanitize_text_field( $_POST["mlwUserName"] ) : 'None',
			'use' => 'name'
			);
			$responses[] = array(
			'label' => 'Business',
			'value' => isset( $_POST["mlwUserComp"] ) ? sanitize_text_field( $_POST["mlwUserComp"] ) : 'None',
			'use' => 'comp'
			);
			$responses[] = array(
			'label' => 'Email',
			'value' => isset( $_POST["mlwUserEmail"] ) ? sanitize_text_field( $_POST["mlwUserEmail"] ) : 'None',
			'use' => 'email'
			);
			$responses[] = array(
			'label' => 'Phone',
			'value' => isset( $_POST["mlwUserPhone"] ) ? sanitize_text_field( $_POST["mlwUserPhone"] ) : 'None',
			'use' => 'phone'
			);
		} elseif ( ! empty( $fields ) && is_array( $fields ) ) {
			$total_fields = count( $fields );
			for ( $i = 0; $i < $total_fields; $i++ ) {
				$field_array = array(
					'label' => $fields[ $i ]['label'],
					'value' => isset( $_POST["contact_field_$i"] ) ? sanitize_text_field( $_POST["contact_field_$i"] ) : 'None'
				);
				if ( isset( $fields[ $i ]['use'] ) ) {
					$field_array['use'] = $fields[ $i ]['use'];
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
	public static function load_fields() {
		global $mlwQuizMasterNext;
		return maybe_unserialize( $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'contact_form' ) );
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
		if ( self::load_fields() === $fields ) {
			return true;
		}
		global $mlwQuizMasterNext;
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( intval( $quiz_id ) );
		return $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'contact_form', serialize( $fields ) );
	}
}
?>
