<?php
/**
 * Handles relevant functions for emails
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class contains functions for loading, saving, and send quiz emails.
 *
 * @since 6.2.0
 */
class QSM_Emails {

	/**
	 * Sends the emails for the quiz.
	 *
	 * @since 6.2.0
	 * @param array $response_data The data for the user's submission.
	 */
	public static function send_emails( $response_data ) {
		$emails = self::load_emails( $response_data['quiz_id'] );

		if ( ! is_array( $emails ) || empty( $emails ) ) {
			return;
		}

		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );

		// Cycles through each possible email.
		foreach ( $emails as $email ) {

			// Checks if any conditions are present. Else, send it always.
			if ( ! empty( $email['conditions'] ) ) {
				/**
				 * Since we have many conditions to test, we set this to true first.
				 * Then, we test each condition to see if it fails.
				 * If one condition fails, the value will be set to false.
				 * If all conditions pass, this will still be true and the email will
				 * be sent.
				 */
				$show = true;

				// Cycle through each condition to see if we should sent this email.
				foreach ( $email['conditions'] as $condition ) {
					$value    = $condition['value'];
					$category = '';
					if ( isset( $condition['category'] ) ) {
						$category = $condition['category'];
					}
					// First, determine which value we need to test.
					switch ( $condition['criteria'] ) {
						case 'score':
							if ( '' !== $category ) {
								$test = apply_filters( 'mlw_qmn_template_variable_results_page', "%CATEGORY_SCORE_$category%", $response_data );
							} else {
								$test = $response_data['total_score'];
							}
							break;

						case 'points':
							if ( '' !== $category ) {
								$test = apply_filters( 'mlw_qmn_template_variable_results_page', "%CATEGORY_POINTS_$category%", $response_data );
							} else {
								$test = $response_data['total_points'];
							}
							break;

						default:
							$test = 0;
							break;
					}

					// Then, determine how to test the vaue.
					switch ( $condition['operator'] ) {
						case 'greater-equal':
							if ( $test < $value ) {
								$show = false;
							}
							break;

						case 'greater':
							if ( $test <= $value ) {
								$show = false;
							}
							break;

						case 'less-equal':
							if ( $test > $value ) {
								$show = false;
							}
							break;

						case 'less':
							if ( $test >= $value ) {
								$show = false;
							}
							break;

						case 'not-equal':
							if ( $test == $value ) {
								$show = false;
							}
							break;

						case 'equal':
						default:
							if ( $test != $value ) {
								$show = false;
							}
							break;
					}

					/**
					 * Added custom criterias/operators to the email?
					 * Use this filter to check if the condition passed.
					 * If it fails your conditions, return false to prevent the
					 * email from sending.
					 * If it passes your condition or is not your custom criterias
					 * or operators, then return the value as-is.
					 * DO NOT RETURN TRUE IF IT PASSES THE CONDITION!!!
					 * The value may have been set to false when failing a previous condition.
					 */
					$show = apply_filters( 'qsm_email_condition_check', $show, $condition, $response_data );
				}

				if ( $show ) {
					self::send_results_email( $response_data, $email['to'], $email['subject'], $email['content'], $email['replyTo'] );
				}
			} else {
				self::send_results_email( $response_data, $email['to'], $email['subject'], $email['content'], $email['replyTo'] );
			}
		}

		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	}

	/**
	 * Sends the results email.
	 *
	 * @since 6.2.0
	 * @param array  $response_data The data for the user's submission.
	 * @param string $to The email(s) to send to. Can be separated with commas.
	 * @param string $subject The subject of the email.
	 * @param string $content The body of the email.
	 * @param bool   $reply_to True if set user email as Reply To header.
	 */
	public static function send_results_email( $response_data, $to, $subject, $content, $reply_to ) {

		global $mlwQuizMasterNext;
		global $qmn_total_questions;
		$qmn_total_questions = 0;
		// Sets up our to email addresses.
		$user_email = sanitize_email( $response_data['user_email'] );
		$count      = 0;
		if ( is_email( $user_email ) ) {
			$to = str_replace( '%USER_EMAIL%', $response_data['user_email'], $to, $count );
		} else {
			$to = str_replace( '%USER_EMAIL%', '', $to );
		}
		$to       = apply_filters( 'qsm_send_results_email_addresses', $to, $response_data );
		$to_array = explode( ',', $to );
		$to_array = array_unique( $to_array );
		if ( empty( $to_array ) ) {
			return;
		}
		// Prepares our subject.
		$subject = apply_filters( 'mlw_qmn_template_variable_results_page', $subject, $response_data );
		// Prepares our content.
		$content                               = htmlspecialchars_decode( $content, ENT_QUOTES );
		$response_data['email_template_array'] = true;
		$content                               = apply_filters( 'mlw_qmn_template_variable_results_page', $content, $response_data );
		$content                               = apply_filters( 'qmn_email_template_variable_results', $content, $response_data );
		// convert css classes to inline.
		$content                               = $mlwQuizMasterNext->pluginHelper->qsm_results_css_inliner( $content );
		$content                               = html_entity_decode( $content );

		// Prepares our from name and email.
		$settings   = (array) get_option( 'qmn-settings' );
		$from_email = get_option( 'admin_email', 'a@example.com' );
		$from_name  = get_bloginfo( 'name' );
		if ( ! isset( $settings['from_email'] ) && ! isset( $settings['from_name'] ) ) {
			$options    = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
			$from_array = maybe_unserialize( $options->email_from_text );
			if ( isset( $from_array['from_email'] ) ) {
				$from_email = $from_array['from_email'];
				$from_name  = $from_array['from_name'];

				// Updates option with this quiz's from values.
				$settings['from_email'] = $from_email;
				$settings['from_name']  = $from_name;
				update_option( 'qmn-settings', $settings );
			}
		} else {
			if ( isset( $settings['from_email'] ) ) {
				$from_email = $settings['from_email'];
			}
			if ( isset( $settings['from_name'] ) ) {
				$from_name = $settings['from_name'];
			}
		}

		// Prepares our headers.
		$headers = array();
		if ( is_email( $from_email ) ) {
			$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
		}
		if ( is_email( $user_email ) && true === $reply_to ) {
			$name      = sanitize_text_field( $response_data['user_name'] );
			$headers[] = 'Reply-To: ' . $name . ' <' . $user_email . '>';
		}
		/**
		 * Filter to modify email headers.
		 */
		$headers = apply_filters( 'qsm_send_results_email_headers', $headers, $response_data );

		// Prepares our attachments. If %USER_EMAIL% was in the $to, then use the user email attachment filter.
		$attachments = array();
		if ( 0 < intval( $count ) ) {
			$attachments = apply_filters( 'qsm_user_email_attachments', $attachments, $response_data );
		} else {
			$attachments = apply_filters( 'qsm_admin_email_attachments', $attachments, $response_data );
		}

		// Cycle through each to email address and send the email.
		foreach ( $to_array as $to_email ) {
			if ( is_email( $to_email ) ) {
				wp_mail( $to_email, $subject, $content, $headers, $attachments );
			}
		}
	}

	/**
	 * Loads the emails for a single quiz.
	 *
	 * @since 6.2.0
	 * @param int $quiz_id The ID for the quiz.
	 * @return bool|array The array of emails or false.
	 */
	public static function load_emails( $quiz_id ) {
		$emails  = array();
		$quiz_id = intval( $quiz_id );

		// If the parameter supplied turns to 0 after intval, returns false.
		if ( 0 === $quiz_id ) {
			return false;
		}

		global $wpdb;
		$data = $wpdb->get_var( $wpdb->prepare( "SELECT user_email_template FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );

		$data = maybe_unserialize( $data );
		// Checks if the emails is an array.
		if ( is_array( $data ) ) {
			// Checks if the emails array is not the newer version.
			if ( ! empty( $data ) && ! isset( $data[0]['conditions'] ) ) {
				$emails = self::convert_to_new_system( $quiz_id );
			} else {
				$emails = $data;
			}
		} else {
			$emails = self::convert_to_new_system( $quiz_id );
		}

		return $emails;
	}

	/**
	 * Loads and converts emails from the old system to new system
	 *
	 * @since 6.2.0
	 * @param int $quiz_id The ID for the quiz.
	 * @return array The combined newer versions of the emails.
	 */
	public static function convert_to_new_system( $quiz_id ) {
		$emails  = array();
		$quiz_id = intval( $quiz_id );

		// If the parameter supplied turns to 0 after intval, returns empty array.
		if ( 0 === $quiz_id ) {
			return $emails;
		}

		/**
		 * Loads the old user and admin emails. Checks if they are enabled and converts them.
		 */
		global $wpdb;
		global $mlwQuizMasterNext;
		$data   = $wpdb->get_row( $wpdb->prepare( "SELECT send_user_email, user_email_template, send_admin_email, admin_email_template, email_from_text, admin_email FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ), ARRAY_A );
		$system = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system', 0 );
		if ( 0 === intval( $data['send_user_email'] ) ) {
			$emails = array_merge( $emails, self::convert_emails( $system, $data['user_email_template'] ) );
		}
		if ( 0 === intval( $data['send_admin_email'] ) ) {
			$from_email_array = maybe_unserialize( $data['email_from_text'] );
			if ( ! is_array( $from_email_array ) || ! isset( $from_email_array['reply_to'] ) ) {
				$from_email_array = array(
					'reply_to' => 1,
				);
			}
			$emails = array_merge( $emails, self::convert_emails( $system, $data['admin_email_template'], $data['admin_email'], $from_email_array['reply_to'] ) );
		}

		// Updates the database with new array to prevent running this step next time.
		$wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'user_email_template' => maybe_serialize( $emails ) ),
			array( 'quiz_id' => $quiz_id ),
			array( '%s' ),
			array( '%d' )
		);

		return $emails;
	}

	/**
	 * Converts emails to new system.
	 *
	 * @since 6.2.0
	 * @param int    $system The grading system of the quiz.
	 * @param array  $emails The emails to convert.
	 * @param string $admin_emails The emails to send admin emails to, separated by comma.
	 * @param int    $reply_to Whether to add user email as Reply-To header (0 is enabled).
	 * @return array The emails that have been converted.
	 */
	public static function convert_emails( $system, $emails, $admin_emails = false, $reply_to = false ) {
		$new_emails = array();
		$emails     = maybe_unserialize( $emails );

		// Checks if emails is an array to cycle through.
		if ( is_array( $emails ) ) {

			// Cycles through the emails.
			foreach ( $emails as $email ) {

				// Because I didn't appreciate consistency as a young developer...
				if ( isset( $email['subject'] ) ) {
					$subject = $email['subject'];
					$message = $email['message'];
					$begin   = $email['begin_score'];
					$end     = $email['end_score'];
				} else {
					$subject = $email[3];
					$message = $email[2];
					$begin   = $email[0];
					$end     = $email[1];
				}

				// Sets up our basic email.
				$new_email = array(
					'conditions' => array(),
					'subject'    => $subject,
					'content'    => $message,
					'replyTo'    => false,
				);

				// Prepares the to email.
				if ( false === $admin_emails ) {
					$new_email['to'] = '%USER_EMAIL%';
				} elseif ( is_string( $admin_emails ) ) {
					$new_email['to'] = $admin_emails;
				} else {
					$new_email['to'] = get_option( 'admin_email ', 'test@example.com' );
				}

				// Sets reply to option to True if was enabled previously.
				if ( false !== $reply_to ) {
					if ( 0 === intval( $reply_to ) ) {
						$new_email['replyTo'] = true;
					}
				}

				// Checks to see if the email is not the older version's default page.
				if ( 0 !== intval( $begin ) || 0 !== intval( $end ) ) {

					// Checks if the system is points.
					if ( 1 === intval( $system ) ) {
						$new_email['conditions'][] = array(
							'criteria' => 'points',
							'operator' => 'greater-equal',
							'value'    => $begin,
						);
						$new_email['conditions'][] = array(
							'criteria' => 'points',
							'operator' => 'less-equal',
							'value'    => $end,
						);
					} else {
						$new_email['conditions'][] = array(
							'criteria' => 'score',
							'operator' => 'greater-equal',
							'value'    => $begin,
						);
						$new_email['conditions'][] = array(
							'criteria' => 'score',
							'operator' => 'less-equal',
							'value'    => $end,
						);
					}
				}

				$new_emails[] = $new_email;
			}
		} else {
			$new_emails[] = array(
				'conditions' => array(),
				'content'    => $emails,
				'subject'    => 'Quiz results for %QUIZ_NAME%',
				'replyTo'    => false,
			);

			// Prepares the to email.
			if ( false === $admin_emails ) {
				$new_emails[0]['to'] = '%USER_EMAIL%';
			} elseif ( is_string( $admin_emails ) ) {
				$new_emails[0]['to'] = $admin_emails;
			} else {
				$new_emails[0]['to'] = get_option( 'admin_email ', 'test@example.com' );
			}
		}
		return $new_emails;
	}

	/**
	 * Saves the emails for a quiz.
	 *
	 * @since 6.2.0
	 * @param int   $quiz_id The ID for the quiz.
	 * @param array $emails The emails to be saved.
	 * @return bool True or false depending on success.
	 */
	public static function save_emails( $quiz_id, $emails ) {
		if ( ! is_array( $emails ) ) {
			return false;
		}

		$quiz_id = intval( $quiz_id );
		if ( 0 === $quiz_id ) {
			return false;
		}

		// Sanitizes data in emails.
		$total = count( $emails );
		for ( $i = 0; $i < $total; $i++ ) {
			$emails[ $i ]['to']      = sanitize_text_field( $emails[ $i ]['to'] );
			$emails[ $i ]['subject'] = sanitize_text_field( $emails[ $i ]['subject'] );

			/**
			 * The jQuery AJAX function won't send the conditions key
			 * if it's empty. So, check if it's set. If set, sanitize
			 * data. If not set, set to empty array.
			 */
			if ( isset( $emails[ $i ]['conditions'] ) ) {
				// Sanitizes the conditions.
				$total_conditions = count( $emails[ $i ]['conditions'] );
				for ( $j = 0; $j < $total_conditions; $j++ ) {
					$emails[ $i ]['conditions'][ $j ]['value'] = sanitize_text_field( $emails[ $i ]['conditions'][ $j ]['value'] );
				}
			} else {
				$emails[ $i ]['conditions'] = array();
			}

			if ( 'true' === $emails[ $i ]['replyTo'] || true === $emails[ $i ]['replyTo'] ) {
				$emails[ $i ]['replyTo'] = true;
			} else {
				$emails[ $i ]['replyTo'] = false;
			}
		}

		global $wpdb;
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'user_email_template' => maybe_serialize( $emails ) ),
			array( 'quiz_id' => $quiz_id ),
			array( '%s' ),
			array( '%d' )
		);
		if ( false !== $results ) {
			return true;
		} else {
			return false;
		}
	}
}

