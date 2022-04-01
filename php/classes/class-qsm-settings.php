<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This handles all of the settings data for each individual quiz.
 *
 * @since 5.0.0
 */
class QSM_Quiz_Settings {


	/**
	 * ID of the quiz
	 *
	 * @var int
	 * @since 5.0.0
	 */
	private $quiz_id;

	/**
	 * The settings for the quiz
	 *
	 * @var array
	 * @since 5.0.0
	 */
	private $settings;

	/**
	 * The fields that have been registered
	 *
	 * @var array
	 * @since 5.0.0
	 */
	private $registered_fields;

	/**
	 * Prepares the settings for the supplied quiz
	 *
	 * @since 5.0.0
	 * @param int $quiz_id the ID of the quiz that we are handling the settings data for
	 */
	public function prepare_quiz( $quiz_id ) {
		$this->quiz_id = intval( $quiz_id );
		$this->load_settings();
	}


	/**
	 * Registers a setting be shown on the Options or Text tab
	 *
	 * @since 5.0.0
	 * @param array $field_array An array of the components for the settings field
	 */
	public function register_setting( $field_array, $section = 'quiz_options' ) {
		// Adds field to registered fields
		$this->registered_fields[ $section ][] = $field_array;
	}

	/**
	 * Retrieves the registered setting fields
	 *
	 * @since 5.0.0
	 * @param string $section The section whose fields that are being retrieved
	 * @return array All the fields registered the the section provided
	 */
	public function load_setting_fields( $section = 'quiz_options' ) {
		// Checks if section exists in registered fields and returns it if it does
		if ( isset( $this->registered_fields[ $section ] ) ) {
			return $this->registered_fields[ $section ];
		} else {
			return false;
		}
	}

	/**
	 * Retrieves a setting value from a section based on name of section and setting
	 *
	 * @since 5.0.0
	 * @param string $section The name of the section the setting is registered in
	 * @param string $setting The name of the setting whose value we need to retrieve
	 * @param mixed  $default What we need to return if no setting exists with given $setting
	 * @return $mixed Value set for $setting or $default if setting does not exist
	 */
	public function get_section_setting( $section, $setting, $default = false ) {
		// Return if section or setting is empty
		if ( empty( $section ) || empty( $setting ) ) {
			return $default;
		}

		// Get settings in section
		$section_settings = $this->get_setting( $section );

		// Return default if section not found
		if ( ! $section_settings ) {
			return $default;
		}

		// Maybe unserailize
		$section_settings = maybe_unserialize( $section_settings );

		// Check if setting exists
		if ( isset( $section_settings[ $setting ] ) ) {
			// Try to unserialize it and then return it
			return maybe_unserialize( $section_settings[ $setting ] );
		} else {
			// Return the default if no setting exists
			return $default;
		}
	}

	/**
	 * Retrieves setting value based on name of setting
	 *
	 * @since 5.0.0
	 * @param string $setting The name of the setting whose value we need to retrieve
	 * @param mixed  $default What we need to return if no setting exists with given $setting
	 * @return $mixed Value set for $setting or $default if setting does not exist
	 */
	public function get_setting( $setting, $default = false ) {
		global $mlwQuizMasterNext;

		// Return if empty
		if ( empty( $setting ) ) {
			return false;
		}

		// Check if ID is not set, for backwards compatibility
		if ( ! $this->quiz_id ) {
			$quiz_id = $mlwQuizMasterNext->quizCreator->get_id();
			// If get_id doesn't work, return false
			if ( ! $quiz_id ) {
				return false;
			} else {
				$this->prepare_quiz( $quiz_id );
			}
		}

		// Check if setting exists
		if ( isset( $this->settings[ $setting ] ) ) {
			// Try to unserialize it and then return it
			return maybe_unserialize( $this->settings[ $setting ] );
		} else {
			// Return the default if no setting exists
			return $default;
		}
	}

	/**
	 * Updates a settings value, adding it if it didn't already exist
	 *
	 * @since 5.0.0
	 * @param string $setting The name of the setting whose value we need to retrieve.
	 * @param mixed  $value The value that needs to be stored for the setting.
	 * @return bool True if successful or false if fails
	 */
	public function update_setting( $setting, $value ) {
		global $mlwQuizMasterNext;

		// Return if empty.
		if ( empty( $setting ) ) {
			$mlwQuizMasterNext->log_manager->add( 'Error when updating setting', 'Setting was empty with value equal to ' . wp_json_encode( $value ), 0, 'error' );
			return false;
		}

		// Check if ID is not set, for backwards compatibility.
		if ( ! $this->quiz_id ) {
			$quiz_id = $mlwQuizMasterNext->quizCreator->get_id();

			// If get_id doesn't work, return false.
			if ( ! $quiz_id ) {
				$mlwQuizMasterNext->log_manager->add( 'Error when updating setting', 'Quiz ID was not found', 0, 'error' );
				return false;
			} else {
				$this->prepare_quiz( $quiz_id );
			}
		}

		$old_value = $this->get_setting( $setting );
		if ( isset( $_POST['global_setting'] ) ) {
            global $globalQuizsetting;
            $setDefaultvalue                                       = $old_value;
			$setDefaultvalue['form_type']                          = $globalQuizsetting['form_type'];
			$setDefaultvalue['system']                             = $globalQuizsetting['system'];
			$setDefaultvalue['score_roundoff']                     = $globalQuizsetting['score_roundoff'];
			$setDefaultvalue['progress_bar']                       = $globalQuizsetting['progress_bar'];
			$setDefaultvalue['require_log_in']                     = $globalQuizsetting['require_log_in'];
			$setDefaultvalue['pagination']                         = $globalQuizsetting['pagination'];
			$setDefaultvalue['timer_limit']                        = $globalQuizsetting['timer_limit'];
			$setDefaultvalue['enable_result_after_timer_end']      = $globalQuizsetting['enable_result_after_timer_end'];
			$setDefaultvalue['skip_validation_time_expire']        = $globalQuizsetting['skip_validation_time_expire'];
			$setDefaultvalue['total_user_tries']                   = $globalQuizsetting['total_user_tries'];
			$setDefaultvalue['limit_total_entries']                = $globalQuizsetting['limit_total_entries'];
			$setDefaultvalue['question_from_total']                = $globalQuizsetting['question_from_total'];
			$setDefaultvalue['question_per_category']              = $globalQuizsetting['question_per_category'];
			$setDefaultvalue['contact_info_location']              = $globalQuizsetting['contact_info_location'];
			$setDefaultvalue['loggedin_user_contact']              = $globalQuizsetting['loggedin_user_contact'];
			$setDefaultvalue['comment_section']                    = $globalQuizsetting['comment_section'];
			$setDefaultvalue['question_numbering']                 = $globalQuizsetting['question_numbering'];
			$setDefaultvalue['store_responses']                    = $globalQuizsetting['store_responses'];
			$setDefaultvalue['disable_answer_onselect']            = $globalQuizsetting['disable_answer_onselect'];
			$setDefaultvalue['ajax_show_correct']                  = $globalQuizsetting['ajax_show_correct'];
			$setDefaultvalue['contact_disable_autofill']           = $globalQuizsetting['contact_disable_autofill'];
			$setDefaultvalue['form_disable_autofill']              = $globalQuizsetting['form_disable_autofill'];
			$setDefaultvalue['show_category_on_front']             = $globalQuizsetting['show_category_on_front'];
			$setDefaultvalue['enable_quick_result_mc']             = $globalQuizsetting['enable_quick_result_mc'];
			$setDefaultvalue['end_quiz_if_wrong']                  = $globalQuizsetting['end_quiz_if_wrong'];
			$setDefaultvalue['enable_quick_correct_answer_info']   = $globalQuizsetting['enable_quick_correct_answer_info'];
			$setDefaultvalue['enable_retake_quiz_button']          = $globalQuizsetting['enable_retake_quiz_button'];
			$setDefaultvalue['enable_pagination_quiz']             = $globalQuizsetting['enable_pagination_quiz'];
			$setDefaultvalue['enable_deselect_option']             = $globalQuizsetting['enable_deselect_option'];
			$setDefaultvalue['disable_description_on_result']      = $globalQuizsetting['disable_description_on_result'];
			$setDefaultvalue['disable_scroll_next_previous_click'] = $globalQuizsetting['disable_scroll_next_previous_click'];
			$setDefaultvalue['disable_first_page']                 = $globalQuizsetting['disable_first_page'];
			$setDefaultvalue['quiz_animation']                     = $globalQuizsetting['quiz_animation'];
			$setDefaultvalue['result_page_fb_image']               = $globalQuizsetting['result_page_fb_image'];
			$setDefaultvalue['randomness_order']                   = $globalQuizsetting['randomness_order'];
			$setDefaultvalue['scheduled_time_start']               = $globalQuizsetting['scheduled_time_start'];
			$setDefaultvalue['scheduled_time_end']                 = $globalQuizsetting['scheduled_time_end'];
			$setDefaultvalue['not_allow_after_expired_time']       = $globalQuizsetting['not_allow_after_expired_time'];
			$setDefaultvalue['preferred_date_format']              = $globalQuizsetting['preferred_date_format'];
			$setDefaultvalue['default_answers']                    = $globalQuizsetting['default_answers'];
			// Try to serialize the value.
			return $this->save_quiz_settings( $setting, $setDefaultvalue );
		}
		// If the old value and new value are the same, return false.
		if ( $value === $old_value ) {
			return true;
		}
		// Try to serialize the value.
		return $this->save_quiz_settings( $setting, $value );
	}
	/**
	 * Update setting option fdor Quiz
	 *
	 * @since 5.0.0
	 */
	private function save_quiz_settings( $setting, $settingArray ) {
		global $mlwQuizMasterNext;
		$serialized_value = maybe_serialize( $settingArray );
		// Set the new value.
		$this->settings[ $setting ] = $serialized_value;
		// Update the database.
		global $wpdb;
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'quiz_settings' => maybe_serialize( $this->settings ) ),
			array( 'quiz_id' => $this->quiz_id ),
			array( '%s' ),
			array( '%d' )
		);
		if ( false === $results ) {
			$mlwQuizMasterNext->log_manager->add( 'Error when updating setting', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Loads the settings for the quiz
	 *
	 * @since 5.0.0
	 */
	private function load_settings() {
		global $wpdb;
		$settings_array = array();
		// Loads the settings from the database
		$settings = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_settings FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d", $this->quiz_id ) );

		// unserializes array
		$settings_array = maybe_unserialize( $settings );

		// If the value is not an array, create an empty array
		if ( ! is_array( $settings_array ) ) {
			$settings_array = array();
		}

		// If some options are missing
		if ( ! isset( $settings_array['quiz_options'] ) || ! isset( $settings_array['quiz_text'] ) || ! isset( $settings_array['quiz_leaderboards'] ) ) {
			// Load the old options system
			$quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $this->quiz_id ) );
			// If no leadboard is present
			if ( ! isset( $settings_array['quiz_leaderboards'] ) ) {
				$settings_array['quiz_leaderboards'] = maybe_serialize( array( 'template' => $quiz_options->leaderboard_template ) );
			}
			// If no options are present
			if ( ! isset( $settings_array['quiz_options'] ) ) {
				$scheduled_timeframe = maybe_unserialize( $quiz_options->scheduled_timeframe );

				// Sets up older scheduled timeframe settings
				if ( ! is_array( $scheduled_timeframe ) ) {
					$scheduled_timeframe = array(
						'start' => '',
						'end'   => '',
					);
				}
				// Prepares new quiz_options section's settings
				$settings_array['quiz_options'] = maybe_serialize(
					array(
						'system'                  => $quiz_options->quiz_system,
						'loggedin_user_contact'   => $quiz_options->loggedin_user_contact,
						'contact_info_location'   => $quiz_options->contact_info_location,
						'user_name'               => $quiz_options->user_name,
						'user_comp'               => $quiz_options->user_comp,
						'user_email'              => $quiz_options->user_email,
						'user_phone'              => $quiz_options->user_phone,
						'comment_section'         => $quiz_options->comment_section,
						'randomness_order'        => $quiz_options->randomness_order,
						'question_from_total'     => $quiz_options->question_from_total,
						'question_per_category'   => $quiz_options->question_per_category,
						'total_user_tries'        => $quiz_options->total_user_tries,
						'social_media'            => $quiz_options->social_media,
						'pagination'              => $quiz_options->pagination,
						'timer_limit'             => $quiz_options->timer_limit,
						'question_numbering'      => $quiz_options->question_numbering,
						'require_log_in'          => $quiz_options->require_log_in,
						'limit_total_entries'     => $quiz_options->limit_total_entries,
						'scheduled_time_start'    => $scheduled_timeframe['start'],
						'scheduled_time_end'      => $scheduled_timeframe['end'],
						'disable_answer_onselect' => $quiz_options->disable_answer_onselect,
						'ajax_show_correct'       => $quiz_options->ajax_show_correct,
						'preferred_date_format'   => $quiz_options->preferred_date_format,
					)
				);
			}

			// If no text is present
			if ( ! isset( $settings_array['quiz_text'] ) ) {
				$pagination_text = maybe_unserialize( $quiz_options->pagination_text );
				// Sets up older pagination text
				if ( ! is_array( $pagination_text ) ) {
					$pagination_text = array(
						__( 'Previous', 'quiz-master-next' ),
						__( 'Next', 'quiz-master-next' ),
					);
				}

				$social_media_text = maybe_unserialize( $quiz_options->social_media_text );
				// Sets up older social sharing text
				if ( ! is_array( $social_media_text ) ) {
					$social_media_text = array(
						'twitter'  => $quiz_options->social_media_text,
						'facebook' => $quiz_options->social_media_text,
					);
				}
				// Prepares new quiz_text section's settings
				$settings_array['quiz_text'] = maybe_serialize(
					array(
						'message_before'           => $quiz_options->message_before,
						'message_comment'          => $quiz_options->message_comment,
						'message_end_template'     => $quiz_options->message_end_template,
						'comment_field_text'       => $quiz_options->comment_field_text,
						'question_answer_template' => $quiz_options->question_answer_template,
						'submit_button_text'       => $quiz_options->submit_button_text,
						'name_field_text'          => $quiz_options->name_field_text,
						'business_field_text'      => $quiz_options->business_field_text,
						'email_field_text'         => $quiz_options->email_field_text,
						'phone_field_text'         => $quiz_options->phone_field_text,
						'total_user_tries_text'    => $quiz_options->total_user_tries_text,
						'twitter_sharing_text'     => $social_media_text['twitter'],
						'facebook_sharing_text'    => $social_media_text['facebook'],
						'previous_button_text'     => $pagination_text[0],
						'next_button_text'         => $pagination_text[1],
						'require_log_in_text'      => $quiz_options->require_log_in_text,
						'limit_total_entries_text' => $quiz_options->limit_total_entries_text,
						'scheduled_timeframe_text' => $quiz_options->scheduled_timeframe_text,
					)
				);
			}
			// Update new settings system
			$results = $wpdb->update(
				$wpdb->prefix . 'mlw_quizzes',
				array( 'quiz_settings' => maybe_serialize( $settings_array ) ),
				array( 'quiz_id' => $this->quiz_id ),
				array( '%s' ),
				array( '%d' )
			);
		}
		// Cycle through registered settings
		$registered_fields = $this->registered_fields;
		foreach ( $registered_fields as $section => $fields ) {
			// Check if section exists in settings and, if not, set it to empty array
			if ( ! isset( $settings_array[ $section ] ) ) {
				$settings_array[ $section ] = array();
			}
			$unserialized_section = maybe_unserialize( $settings_array[ $section ] );
			// Cycle through each setting in section
			foreach ( $fields as $field ) {
				// Check if setting exists in section settings and, if not, set it to the default
				if ( ! isset( $unserialized_section[ $field['id'] ] ) ) {
					$unserialized_section[ $field['id'] ] = $field['default'];
				}
			}
			$settings_array[ $section ] = maybe_serialize( $unserialized_section );
		}
		$this->settings = $settings_array;
	}

	/**
	 * Loads the old object model of options for backwards compatibility
	 *
	 * @since 5.0.0
	 */
	public function get_quiz_options() {
		global $wpdb;

		// Load the old options system
		$quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $this->quiz_id ), ARRAY_A );

		/**
		 * Merges all options and settings
		 */
		$leaderboards = $this->get_setting( 'quiz_leaderboards' );
		if ( is_array( $leaderboards ) ) {
			$quiz_options = array_merge( $quiz_options, $leaderboards );
		}

		$options = $this->get_setting( 'quiz_options' );
		if ( is_array( $options ) ) {
			unset( $options['quiz_name'] );
			$quiz_options = array_merge( $quiz_options, $options );
		}

		$text = $this->get_setting( 'quiz_text' );
		if ( is_array( $text ) ) {
			$quiz_options = array_merge( $quiz_options, $text );
		}

		// Return as old object model
		return (object) $quiz_options;
	}
}


