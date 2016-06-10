<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This handles all of the settings data for each individual quiz.
 *
 * @since 4.8.0
 */
class QSM_Quiz_Settings {

  /**
   * ID of the quiz
   *
   * @var int
   * @since 4.8.0
   */
  private $quiz_id;

  /**
   * The settings for the quiz
   *
   * @var array
   * @since 4.8.0
   */
  private $settings;

  /**
   * The fields that have been registered
   *
   * @var array
   * @since 4.8.0
   */
  private $registered_fields;

  /**
   * Prepares the settings for the supplied quiz
   *
   * @since 4.8.0
   * @param int $quiz_id the ID of the quiz that we are handling the settings data for
   */
  public function prepare_quiz( $quiz_id ) {
    $this->quiz_id = intval( $quiz_id );
    $this->load_settings();
  }


  /**
   * Registers a setting be shown on the Options or Text tab
   *
   * @since 4.8.0
   * @param array $field_array An array of the components for the settings field
   */
  public function register_setting( $field_array, $section = 'quiz_options' ) {

    /*
      Example field array
      $field_array = array(
        'id' => 'system',
        'label' => 'Which system is this quiz graded on?',
        'type' => 'text',
        'options' => array(
          array(
            'label' => '',
            'value' => ''
          )
        ),
        'default' => ''
      );
    */

    // Adds field to registered fields
    $this->registered_fields[ $section ][] = $field_array;

    // Adds the default value into the settings if the setting doesn't exist
    if ( false === $this->get_setting( $field_array["id"] ) ) {
      $this->update_setting( $field_array["id"], $field_array["default"] );
    }
  }

  /**
   * Retrieves the registered setting fields
   *
   * @since 4.8.0
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
   * Retrieves setting value based on name of setting
   *
   * @since 4.8.0
   * @param string $setting The name of the setting whose value we need to retrieve
   * @param mixed $default What we need to return if no setting exists with given $setting
   * @return $mixed Value set for $setting or $default if setting does not exist
   */
  public function get_setting( $setting, $default = false ) {

    // Return if empty
    if ( empty( $setting ) ) {
      return false;
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
   * @since 4.8.0
   * @param string $setting The name of the setting whose value we need to retrieve
   * @param mixed $value The value that needs to be stored for the setting
   * @return bool True if successful or false if fails
   */
  public function update_setting( $setting, $value ) {

    // Return if empty
    if ( empty( $setting ) ) {
      return false;
    }

    $old_value = $this->get_setting( $setting );

    // If the old value and new value are the same, return false
    if ( $value === $old_value ) {
      return false;
    }

    // Try to serialize the value
    $serialized_value = maybe_serialize( $value );

    // Set the new value
    $this->settings[ $setting ] = $serialized_value;

    // Update the database
    $serialized_settings = serailize( $this->settings );
    $results = $wpdb->update(
      $wpdb->prefix . "mlw_quizzes",
      array( 'quiz_settings' => $serialized_settings ),
      array( 'quiz_id' => $this->quiz_id ),
      array( '%s' ),
      array( '%d' )
    );

    if ( ! $results ) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Loads the settings for the quiz
   *
   * @since 4.8.0
   */
  private function load_settings() {

    global $wpdb;
		$settings_array = array();

    // Loads the settings from the database
		$settings = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_settings FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d", $this->quiz_id ) );

    // Unserializes array
		if ( is_serialized( $settings ) && is_array( @unserialize( $settings ) ) ) {
			$settings_array = @unserialize( $settings );
		}

    // If the value is not an array, create an empty array
		if ( ! is_array( $settings_array ) ) {
      $settings_array = array();
		}

    // If some options are missing
    if ( ! isset( $settings_array['quiz_options'] ) || ! isset( $settings_array["quiz_text"] ) ) {

      // Load the old options system
      $quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $this->quiz_id ) );

      // If no options are present
      if ( ! isset( $settings_array['quiz_options'] ) ) {
        $settings_array['quiz_options'] = array(
          'system' => $quiz_options->system,
  				'loggedin_user_contact' => $quiz_options->loggedin_user_contact,
  				'contact_info_location' => $quiz_options->contact_info_location,
  				'user_name' => $quiz_options->user_name,
  				'user_comp' => $quiz_options->user_comp,
  				'user_email' => $quiz_options->user_email,
  				'user_phone' => $quiz_options->user_phone,
  				'comment_section' => $quiz_options->comment_section,
  				'randomness_order' => $quiz_options->randomness_order,
  				'question_from_total' => $quiz_options->question_from_total,
  				'total_user_tries' => $quiz_options->total_user_tries,
  				'social_media' => $quiz_options->social_media,
  				'pagination' => $quiz_options->pagination,
  				'timer_limit' => $quiz_options->timer_limit,
  				'question_numbering' => $quiz_options->question_numbering,
  				'require_log_in' => $quiz_options->require_log_in,
  				'limit_total_entries' => $quiz_options->limit_total_entries,
  				'scheduled_timeframe' => $quiz_options->scheduled_timeframe,
  				'disable_answer_onselect' => $quiz_options->disable_answer_onselect,
  				'ajax_show_correct' => $quiz_options->ajax_show_correct
        );
      }

      // If no text is present
      if ( ! isset( $settings_array["quiz_text"] ) ) {
        $settings_array["quiz_text"] = array(
          'message_before' => $quiz_options->message_before,
  				'message_comment' => $quiz_options->message_comment,
  				'message_end_template' => $quiz_options->message_end_template,
  				'comment_field_text' => $quiz_options->comment_field_text,
  				'question_answer_template' => $quiz_options->question_answer_template,
  				'submit_button_text' => $quiz_options->submit_button_text,
  				'name_field_text' => $quiz_options->name_field_text,
  				'business_field_text' => $quiz_options->business_field_text,
  				'email_field_text' => $quiz_options->email_field_text,
  				'phone_field_text' => $quiz_options->phone_field_text,
  				'total_user_tries_text' => $quiz_options->total_user_tries_text,
  				'social_media_text' => $quiz_options->social_media_text,
  				'pagination_text' => $quiz_options->pagination_text,
  				'require_log_in_text' => $quiz_options->require_log_in_text,
  				'limit_total_entries_text' => $quiz_options->limit_total_entries_text,
  				'scheduled_timeframe_text' => $quiz_options->scheduled_timeframe_text
        );
      }

      // Update new settings system
      $serialized_array = serialize( $settings_array );
      $results = $wpdb->update(
  			$wpdb->prefix . "mlw_quizzes",
  			array( 'quiz_settings' => $serialized_array ),
  			array( 'quiz_id' => $this->quiz_id ),
  			array( '%s' ),
  			array( '%d' )
  		);
    }

    $this->settings = $settings_array;
  }
}

?>
