<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * This class is a helper class to be used for extending the plugin
 *
 * This class contains many functions for extending the plugin
 *
 * @since 4.0.0
 */
class QMNPluginHelper {

	/**
	 * Addon Page tabs array
	 *
	 * @var   array
	 * @since 4.0.0
	 */
	public $addon_tabs = array();

	/**
	 * Stats Page tabs array
	 *
	 * @var   array
	 * @since 4.0.0
	 */
	public $stats_tabs = array();

	/**
	 * Admin Results Page tabs array
	 *
	 * @var   array
	 * @since 5.0.0
	 */
	public $admin_results_tabs = array();

	/**
	 * Results Details Page tabs array
	 *
	 * @var   array
	 * @since 4.1.0
	 */
	public $results_tabs = array();

	/**
	 * Settings Page tabs array
	 *
	 * @var   array
	 * @since 4.0.0
	 */
	public $settings_tabs = array();

	/**
	 * Question types array
	 *
	 * @var   array
	 * @since 4.0.0
	 */
	public $question_types = array();

	/**
	 * Template array
	 *
	 * @var   array
	 * @since 4.5.0
	 */
	public $quiz_templates = array();

	/**
	 * Main Construct Function
	 *
	 * Call functions within class
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_qmn_question_type_change', array( $this, 'get_question_type_edit_content' ) );
		add_action( 'admin_init', array( $this, 'qsm_add_default_translations' ), 9999 );
		add_action( 'qsm_saved_question', array( $this, 'qsm_add_question_translations' ), 10, 2 );
		add_action( 'qsm_saved_text_message', array( $this, 'qsm_add_text_message_translations' ), 10, 3 );
		add_action( 'qsm_saved_quiz_settings', array( $this, 'qsm_add_quiz_settings_translations' ), 10, 3 );

		add_action( 'qsm_register_language_support', array( $this, 'qsm_register_language_support' ), 10, 3 );
		add_filter( 'qsm_language_support', array( $this, 'qsm_language_support' ), 10, 3 );
	}

	/**
	 * Calls all class functions to initialize quiz
	 *
	 * @param  int $quiz_id The ID of the quiz or survey to load.
	 * @return bool True or False if ID is valid.
	 */
	public function prepare_quiz( $quiz_id ) {
		$quiz_id = intval( $quiz_id );

		// Tries to load quiz name to ensure this is a valid ID.
		global $wpdb;
		$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
		if ( is_null( $quiz_name ) ) {
			return false;
		}

		global $mlwQuizMasterNext;
		$mlwQuizMasterNext->quizCreator->set_id( $quiz_id );
		$mlwQuizMasterNext->quiz_settings->prepare_quiz( $quiz_id );

		return true;
	}

	/**
	 * Retrieves all quizzes.
	 *
	 * @param  bool   $include_deleted If set to true, returned array will include all deleted quizzes
	 * @param  string $order_by        The column the quizzes should be ordered by
	 * @param  string $order           whether the $order_by should be ordered as ascending or decending. Can be "ASC" or "DESC"
	 * @param  arr    $user_role       role of current user
	 * @param  int    $user_id         Get the quiz based on user id
	 * @return array All of the quizzes as a numerical array of objects
	 */
	public function get_quizzes( $include_deleted = false, $order_by = 'quiz_id', $order = 'DESC', $user_role = array(), $user_id = '', $limit = '', $offset = '', $where = '' ) {
		global $wpdb;

		// Set order direction
		$order_direction = 'DESC';
		if ( 'ASC' === $order ) {
			$order_direction = 'ASC';
		}

		// Set field to sort by
		switch ( $order_by ) {
			case 'last_activity':
				$order_field = 'last_activity';
				break;

			case 'quiz_views':
				$order_field = 'quiz_views';
				break;

			case 'quiz_taken':
				$order_field = 'quiz_taken';
				break;

			case 'title':
				$order_field = 'quiz_name';
				break;

			default:
				$order_field = 'quiz_id';
				break;
		}

		// Should we include deleted?
		$delete = 'WHERE deleted=0';
		if ( '' !== $where ) {
			$delete = $delete . ' AND ' . $where;
		}
		if ( $include_deleted ) {
			$delete = '';
		}
		$user_str = '';
		if ( in_array( 'author', (array) $user_role, true ) ) {
			if ( $user_id && '' === $delete ) {
				$user_str = "WHERE quiz_author_id = '$user_id'";
			} elseif ( $user_id && '' !== $delete ) {
				$user_str = " AND quiz_author_id = '$user_id'";
			}
		}
		if ( '' !== $where && '' !== $user_str ) {
			$user_str = $user_str . ' AND ' . $where;
		}
		$where_str = '';
		if ( '' === $user_str && '' === $delete && '' !== $where ) {
			$where_str = "WHERE $where";
		}
		if ( '' !== $limit ) {
			$limit = ' limit ' . $offset . ', ' . $limit;
		}
		// Get quizzes and return them
		$delete  = apply_filters( 'quiz_query_delete_clause', $delete );
		$quizzes = $wpdb->get_results( stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes %1s %2s %3s ORDER BY %4s %5s %6s", $delete, $user_str, $where_str, $order_field, $order_direction, $limit ) ) );
		return $quizzes;
	}

	/**
	 * Registers a quiz setting
	 *
	 * @since 5.0.0
	 * @param array $field_array An array of the components for the settings field
	 */
	public function register_quiz_setting( $field_array, $section = 'quiz_options' ) {
		global $mlwQuizMasterNext;
		$mlwQuizMasterNext->quiz_settings->register_setting( $field_array, $section );
	}

	/**
	 * Retrieves a setting value from a section based on name of section and setting
	 *
	 * @since  5.0.0
	 * @param  string $section The name of the section the setting is registered in
	 * @param  string $setting The name of the setting whose value we need to retrieve
	 * @param  mixed  $default What we need to return if no setting exists with given $setting
	 * @return $mixed Value set for $setting or $default if setting does not exist
	 */
	public function get_section_setting( $section, $setting, $default = false ) {
		global $mlwQuizMasterNext;
		return apply_filters( 'qsm_section_setting_text', $mlwQuizMasterNext->quiz_settings->get_section_setting( $section, $setting, $default ) );
	}

	/**
	 * Retrieves setting value based on name of setting
	 *
	 * @since  4.0.0
	 * @param  string $setting The name of the setting whose value we need to retrieve
	 * @param  mixed  $default What we need to return if no setting exists with given $setting
	 * @return $mixed Value set for $setting or $default if setting does not exist
	 */
	public function get_quiz_setting( $setting, $default = false ) {
		global $mlwQuizMasterNext;
		return $mlwQuizMasterNext->quiz_settings->get_setting( $setting, $default );
	}

	/**
	 * Updates a settings value, adding it if it didn't already exist
	 *
	 * @since  4.0.0
	 * @param  string $setting The name of the setting whose value we need to retrieve
	 * @param  mixed  $value   The value that needs to be stored for the setting
	 * @return bool True if successful or false if fails
	 */
	public function update_quiz_setting( $setting, $value ) {
		global $mlwQuizMasterNext;
		return $mlwQuizMasterNext->quiz_settings->update_setting( $setting, $value );
	}

	/**
	 * Outputs the section of input fields
	 *
	 * @since 5.0.0
	 * @since 7.0 Added new parameter settings_fields for default setting
	 * @param string $section The section that the settings were registered with
	 */
	public function generate_settings_section( $section = 'quiz_options', $settings_fields = array() ) {
		global $mlwQuizMasterNext;
		if ( empty( $settings_fields ) ) {
			$settings_fields = $mlwQuizMasterNext->quiz_settings->load_setting_fields( $section );
		}
		QSM_Fields::generate_section( $settings_fields, $section );
	}

	/**
	 * Registers Quiz Templates
	 *
	 * @since 4.5.0
	 * @param $name      String of the name of the template
	 * @param $file_path String of the path to the css file
	 */
	public function register_quiz_template( $name, $file_path ) {
		$slug                          = strtolower( str_replace( ' ', '-', $name ) );
		$this->quiz_templates[ $slug ] = array(
			'name' => $name,
			'path' => $file_path,
		);
	}

	/**
	 * Returns Template Array
	 *
	 * @since  4.5.0
	 * @param  $name String of the name of the template. If left empty, will return all templates
	 * @return array The array of quiz templates
	 */
	public function get_quiz_templates( $slug = null ) {
		if ( is_null( $slug ) ) {
			return $this->quiz_templates;
		} elseif ( isset( $this->quiz_templates[ $slug ] ) ) {
			return $this->quiz_templates[ $slug ];
		} else {
			return false;
		}
	}

	/**
	 * Register Question Types
	 *
	 * Adds a question type to the question type array using the parameters given
	 *
	 * @since  4.0.0
	 * @param  string $name             The name of the Question Type which will be shown when selecting type
	 * @param  string $display_function The name of the function to call when displaying the question
	 * @param  bool   $graded           Tells the plugin if this question is graded or not. This will affect scoring.
	 * @param  string $review_function  The name of the function to call when scoring the question
	 * @param  string $slug             The slug of the question type to be stored with question in database
	 * @param  array  $options          The options for show and hide question validation settings and answer types
	 * @return void
	 */
	public function register_question_type( $name, $display_function, $graded, $review_function = null, $edit_args = null, $save_edit_function = null, $slug = null, $options = array() ) {
		if ( is_null( $slug ) ) {
			$slug = strtolower( str_replace( ' ', '-', $name ) );
		} else {
			$slug = strtolower( str_replace( ' ', '-', $slug ) );
		}
		if ( is_null( $edit_args ) || ! is_array( $edit_args ) ) {
			$validated_edit_function = array(
				'inputs'       => array(
					'question',
					'answer',
					'hint',
					'correct_info',
					'comments',
					'category',
					'required',
				),
				'information'  => '',
				'extra_inputs' => array(),
				'function'     => '',
			);
		} else {
			$validated_edit_function = array(
				'inputs'       => $edit_args['inputs'],
				'information'  => $edit_args['information'],
				'extra_inputs' => $edit_args['extra_inputs'],
				'function'     => $edit_args['function'],
			);
		}
		if ( is_null( $save_edit_function ) ) {
			$save_edit_function = '';
		}
		$new_type                      = array(
			'name'    => $name,
			'display' => $display_function,
			'review'  => $review_function,
			'graded'  => $graded,
			'edit'    => $validated_edit_function,
			'save'    => $save_edit_function,
			'slug'    => $slug,
			'options' => $options,
		);
		$this->question_types[ $slug ] = $new_type;
	}

	/**
	 * Retrieves List Of Question Types
	 *
	 * retrieves a list of the slugs and names of the question types
	 *
	 * @since  4.0.0
	 * @return array An array which contains the slug and name of question types that have been registered
	 */
	public function get_question_type_options() {
		$type_array = array();
		foreach ( $this->question_types as $type ) {
			$type_array[] = array(
				'slug'    => $type['slug'],
				'name'    => $type['name'],
				'options' => $type['options'],
			);
		}
		return $type_array;
	}

	/**
	 *
	 */
	public function set_question_type_meta( $type_id, $meta_key, $meta_value ) {

		$this->question_types[ $type_id ][ $meta_key ] = $meta_value;

	}

	public function get_question_type_edit_fields() {
		$type_array = array();
		foreach ( $this->question_types as $type ) {
			$type_array[ $type['slug'] ] = $type['edit'];
		}
		return $type_array;
	}

	/**
	 * Displays A Question
	 *
	 * Retrieves the question types display function and creates the HTML for the question
	 *
	 * @since  4.0.0
	 * @param  string $slug         The slug of the question type that the question is
	 * @param  int    $question_id  The id of the question
	 * @param  array  $quiz_options An array of the columns of the quiz row from the database
	 * @return string The HTML for the question
	 */
	public function display_question( $slug, $question_id, $quiz_options ) {
		global $wpdb;
		global $qmn_total_questions, $qmn_all_questions_count;
		$question = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mlw_questions WHERE question_id=%d', intval( $question_id ) ) );
		$answers  = array();
		if ( is_serialized( $question->answer_array ) && is_array( maybe_unserialize( $question->answer_array ) ) ) {
			$answers = maybe_unserialize( $question->answer_array );
		} else {
			$mlw_answer_array_correct                                  = array( 0, 0, 0, 0, 0, 0 );
			$mlw_answer_array_correct[ $question->correct_answer - 1 ] = 1;
			$answers = array(
				array( $question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0] ),
				array( $question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1] ),
				array( $question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2] ),
				array( $question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3] ),
				array( $question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4] ),
				array( $question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5] ),
			);
		}
		$answers_original = $answers;
		if ( 2 === intval( $quiz_options->randomness_order ) || 3 === intval( $quiz_options->randomness_order ) ) {
			$answers = self::qsm_shuffle_assoc( $answers );
			update_post_meta( $question_id, 'qsm_random_quetion_answer', $answers );
		}

		// convert answer array into key value pair
		$answers_kvpair = array();
		foreach ( $answers as $answer_item ) {
			$key                    = array_search( $answer_item, $answers_original, true );
			$answers_kvpair[ $key ] = $answer_item;
		}
		unset( $answer_item );
		$answers = $answers_kvpair;

		/**
		 * Filter Answers of specific question before display
		 */
		$answers = apply_filters( 'qsm_single_question_answers', $answers, $question, $quiz_options );
		foreach ( $this->question_types as $type ) {
			if ( strtolower( str_replace( ' ', '-', $slug ) ) === $type['slug'] ) {
				$qmn_all_questions_count += 1;
				if ( $type['graded'] ) {
					$qmn_total_questions += 1;
					if ( 1 === intval( $quiz_options->question_numbering ) ) { ?>
						<span class='mlw_qmn_question_number'><?php echo esc_html( $qmn_total_questions ); ?>.&nbsp;</span>
						<?php
					}
				}
				if ( $quiz_options->show_category_on_front ) {
					$categories = QSM_Questions::get_question_categories( $question_id );
					if ( ! empty( $categories['category_name'] ) ) {
						$cat_name = implode( ',', $categories['category_name'] );
						?>
						<div class="quiz-cat">[<?php echo esc_html( $cat_name ); ?>]</div>
						<?php
					}
				}
				call_user_func( $type['display'], intval( $question_id ), $question->question_name, $answers );
				do_action( 'qsm_after_question', $question );
			}
		}
	}

	public function get_questions_count( $quiz_id = 0 ) {
		global $wpdb;
		$quiz_id = intval( $quiz_id );
		$count   = 0;
		if ( empty( $quiz_id ) || 0 == $quiz_id ) {
			return $count;
		}

		$quiz_settings = $wpdb->get_var( $wpdb->prepare( "SELECT `quiz_settings` FROM `{$wpdb->prefix}mlw_quizzes` WHERE `quiz_id`=%d", $quiz_id ) );
		if ( ! empty( $quiz_settings ) ) {
			$settings    = maybe_unserialize( $quiz_settings );
			$pages       = isset( $settings['pages'] ) ? maybe_unserialize( $settings['pages'] ) : array();
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					$count += count( $page );
				}
			}
		}
		return $count;
	}

	/**
	 * Shuffle assoc array
	 *
	 * @since  7.3.11
	 * @param  array $list An array
	 * @return array
	 */
	public static function qsm_shuffle_assoc( $list ) {
		if ( ! is_array( $list ) ) {
			return $list;
		}
		$keys    = array_keys( $list );
		shuffle( $keys );
		$random  = array();
		foreach ( $keys as $key ) {
			$random[ $key ] = $list[ $key ];
		}
		return $random;
	}

	/**
	 * Find the key of the first occurrence of a substring in an array
	 */
	public static function qsm_stripos_array( $str, array $arr ) {
		if ( is_array( $arr ) ) {
			foreach ( $arr as $a ) {
				if ( stripos( $str, $a ) !== false ) {
					return $a;
				}
			}
		}
		return false;
	}

	/**
	 * Default strings
	 */
	public static function get_default_texts() {
		$defaults = array(
			'message_before'                   => 'Welcome to your %QUIZ_NAME%',
			'message_comment'                  => 'Please fill in the comment box below.',
			'message_end_template'             => '',
			'question_answer_template'         => '%QUESTION%<br />%USER_ANSWERS_DEFAULT%<br/>%CORRECT_ANSWER_INFO%',
			'question_answer_email_template'   => '%QUESTION%<br />Answer Provided: %USER_ANSWER%<br/>Correct Answer: %CORRECT_ANSWER%<br/>Comments Entered: %USER_COMMENTS%',
			'total_user_tries_text'            => 'You have utilized all of your attempts to pass this quiz.',
			'require_log_in_text'              => 'This quiz is for logged in users only.',
			'limit_total_entries_text'         => 'Unfortunately, this quiz has a limited amount of entries it can recieve and has already reached that limit.',
			'scheduled_timeframe_text'         => '',
			'twitter_sharing_text'             => 'I just scored %CORRECT_SCORE%% on %QUIZ_NAME%!',
			'facebook_sharing_text'            => 'I just scored %CORRECT_SCORE%% on %QUIZ_NAME%!',
			'submit_button_text'               => 'Submit',
			'retake_quiz_button_text'          => 'Retake Quiz',
			'previous_button_text'             => 'Previous',
			'next_button_text'                 => 'Next',
			'empty_error_text'                 => 'Please complete all required fields!',
			'email_error_text'                 => 'Not a valid e-mail address!',
			'number_error_text'                => 'This field must be a number!',
			'incorrect_error_text'             => 'The entered text is not correct!',
			'url_error_text'                   => 'The entered URL is not valid!',
			'minlength_error_text'             => 'Required atleast %minlength% characters.',
			'maxlength_error_text'             => 'Minimum %maxlength% characters allowed.',
			'comment_field_text'               => 'Comments',
			'hint_text'                        => 'Hint',
			'quick_result_correct_answer_text' => 'Correct! You have selected correct answer.',
			'quick_result_wrong_answer_text'   => 'Wrong! You have selected wrong answer.',
			'quiz_processing_message'          => '',
			'name_field_text'                  => 'Name',
			'business_field_text'              => 'Business',
			'email_field_text'                 => 'Email',
			'phone_field_text'                 => 'Phone Number',
		);
		return apply_filters( 'qsm_default_texts', $defaults );
	}

	/**
	 * Register string in WPML for translation
	 */
	public static function qsm_register_language_support( $translation_text = '', $translation_slug = '', $domain = 'QSM Meta' ) {
		if ( ! empty( $translation_text ) && is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
			$translation_slug = sanitize_title( $translation_slug );
			/**
			 * Register the string for translation
			 */
			do_action( 'wpml_register_single_string', $domain, $translation_slug, $translation_text );
		}
	}

	/**
	 * Translate string before display
	 */
	public static function qsm_language_support( $translation_text = '', $translation_slug = '', $domain = 'QSM Meta' ) {
		/**
		 * Decode HTML Special characters.
		 */
		$translation_text = htmlspecialchars_decode( $translation_text, ENT_QUOTES );
		/**
		 * Check if WPML String Translation plugin is activated.
		 */
		if ( ! empty( $translation_text ) && is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
			$translation_slug    = sanitize_title( $translation_slug );
			$new_text            = apply_filters( 'wpml_translate_single_string', $translation_text, $domain, $translation_slug );
			$new_text            = htmlspecialchars_decode( $new_text, ENT_QUOTES );
			/**
			 * Return translation for non-default strings.
			 */
			if ( "QSM Meta" != $domain ) {
				return $new_text;
			}
			/**
			 * Check if translation exist.
			 */
			if ( 0 !== strcasecmp( $translation_text, $new_text ) ) {
				return $new_text;
			}
			/**
			 * Check if translation exist for default string.
			 */
			$default_texts   = self::get_default_texts();
			$default_key     = self::qsm_stripos_array( $translation_slug, array_keys( $default_texts ) );
			if ( false !== $default_key && 0 === strcasecmp( $translation_text, $default_texts[ $default_key ] ) ) {
				return apply_filters( 'wpml_translate_single_string', $translation_text, 'QSM Defaults', 'quiz_' . $default_key );
			}
		}
		return $translation_text;
	}

	public function qsm_add_default_translations() {
		$default_texts = self::get_default_texts();
		if ( empty( $default_texts ) ) {
			return;
		}
		if ( is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
			foreach ( $default_texts as $key => $text ) {
				if ( ! empty( $text ) ) {
					$translation_slug = sanitize_title( 'quiz_' . $key );
					/**
					 * Register the string for translation
					 */
					do_action( 'wpml_register_single_string', 'QSM Defaults', $translation_slug, $text );
				}
			}
		}
	}

	public function qsm_add_question_translations( $question_id, $question_data ) {
		$settings    = isset( $question_data['question_settings'] ) ? maybe_unserialize( $question_data['question_settings'] ) : array();
		$hints       = isset( $question_data['hints'] ) ? $question_data['hints'] : '';
		$answer_info = isset( $question_data['question_answer_info'] ) ? html_entity_decode( $question_data['question_answer_info'] ) : '';

		$this->qsm_register_language_support( htmlspecialchars_decode( $settings['question_title'], ENT_QUOTES ), "Question-{$question_id}", "QSM Questions" );
		$this->qsm_register_language_support( htmlspecialchars_decode( $question_data['question_name'], ENT_QUOTES ), "question-description-{$question_id}", "QSM Questions" );
		$this->qsm_register_language_support( $hints, "hint-{$question_id}" );
		$this->qsm_register_language_support( $answer_info, "correctanswerinfo-{$question_id}" );

		$answers = isset( $question_data['answer_array'] ) ? maybe_unserialize( $question_data['answer_array'] ) : array();
		if ( ! empty( $answers ) ) {
			$answerEditor = isset( $settings['answerEditor'] ) ? $settings['answerEditor'] : 'text';
			foreach ( $answers as $ans ) {
				if ( 'image' === $answerEditor ) {
					$caption_text = trim( htmlspecialchars_decode( $ans[3], ENT_QUOTES ) );
					$this->qsm_register_language_support( $caption_text, 'caption-' . $caption_text, 'QSM Answers' );
				} else {
					$answer_text = trim( htmlspecialchars_decode( $ans[0], ENT_QUOTES ) );
					$this->qsm_register_language_support( $answer_text, 'answer-' . $answer_text, 'QSM Answers' );
				}
			}
		}
	}

	public function qsm_add_text_message_translations( $quiz_id, $text_id, $message ) {
		$message = htmlspecialchars_decode( $message, ENT_QUOTES );
		$this->qsm_register_language_support( $message, "quiz_{$text_id}-{$quiz_id}" );
	}

	public function qsm_add_quiz_settings_translations( $quiz_id, $section, $settings_array ) {
		if ( 'quiz_text' == $section && ! empty( $settings_array ) ) {
			foreach ( $settings_array as $key => $val ) {
				if ( ! empty( $val ) ) {
					$this->qsm_register_language_support( htmlspecialchars_decode( $val, ENT_QUOTES ), "quiz_{$key}-{$quiz_id}" );
				}
			}
		}
	}

	/**
	 * Calculates Score For Question
	 *
	 * Calculates the score for the question based on the question type
	 *
	 * @since  4.0.0
	 * @param  string $slug        The slug of the question type that the question is
	 * @param  int    $question_id The id of the question
	 * @return array An array of the user's score from the question
	 */
	public function display_review( $slug, $question_id ) {
		$results_array = array();
		global $wpdb;
		$question = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mlw_questions WHERE question_id=%d', intval( $question_id ) ) );
		$answers  = maybe_unserialize( $question->answer_array );
		if ( empty( $answers ) || ! is_array( $answers ) ) {
			$mlw_answer_array_correct                                  = array( 0, 0, 0, 0, 0, 0 );
			$mlw_answer_array_correct[ $question->correct_answer - 1 ] = 1;
			$answers = array(
				array( $question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0] ),
				array( $question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1] ),
				array( $question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2] ),
				array( $question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3] ),
				array( $question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4] ),
				array( $question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5] ),
			);
		}
		foreach ( $this->question_types as $type ) {
			if ( strtolower( str_replace( ' ', '-', $slug ) ) === $type['slug'] ) {
				if ( ! is_null( $type['review'] ) ) {
					$results_array = call_user_func( $type['review'], intval( $question_id ), $question->question_name, $answers );
				} else {
					$results_array = array( 'null_review' => true );
				}
			}
		}
		return $results_array;
	}

	/**
	 * Retrieves A Question Setting
	 *
	 * Retrieves a setting stored in the question settings array
	 *
	 * @since  4.0.0
	 * @param  int    $question_id The id of the question
	 * @param  string $setting     The name of the setting
	 * @return string The value stored for the setting
	 */
	public function get_question_setting( $question_id, $setting ) {
		global $wpdb;
		$settings           = $wpdb->get_var( $wpdb->prepare( 'SELECT question_settings FROM ' . $wpdb->prefix . 'mlw_questions WHERE question_id=%d', $question_id ) );
		$qmn_settings_array = maybe_unserialize( $settings );

		if ( is_array( $qmn_settings_array ) && isset( $qmn_settings_array[ $setting ] ) ) {
			return $qmn_settings_array[ $setting ];
		} else {
			return '';
		}
	}

	/**
	 * Registers Addon Settings Tab
	 *
	 * Registers a new tab on the addon settings page
	 *
	 * @since  4.0.0
	 * @param  string $title    The name of the tab
	 * @param  string $function The function that displays the tab's content
	 * @return void
	 */
	public function register_addon_settings_tab( $title, $function ) {
		$slug               = strtolower( str_replace( ' ', '-', $title ) );
		$new_tab            = array(
			'title'    => $title,
			'function' => $function,
			'slug'     => $slug,
		);
		$this->addon_tabs[] = $new_tab;
	}

	/**
	 * Retrieves Addon Settings Tab Array
	 *
	 * Retrieves the array of titles and functions of the registered tabs
	 *
	 * @since  4.0.0
	 * @return array The array of registered tabs
	 */
	public function get_addon_tabs() {
		return $this->addon_tabs;
	}

	/**
	 * Registers Stats Tab
	 *
	 * Registers a new tab on the stats page
	 *
	 * @since  4.3.0
	 * @param  string $title    The name of the tab
	 * @param  string $function The function that displays the tab's content
	 * @return void
	 */
	public function register_stats_settings_tab( $title, $function ) {
		$slug               = strtolower( str_replace( ' ', '-', $title ) );
		$new_tab            = array(
			'title'    => $title,
			'function' => $function,
			'slug'     => $slug,
		);
		$this->stats_tabs[] = $new_tab;
	}

	/**
	 * Retrieves Stats Tab Array
	 *
	 * Retrieves the array of titles and functions of the registered tabs
	 *
	 * @since  4.3.0
	 * @return array The array of registered tabs
	 */
	public function get_stats_tabs() {
		return $this->stats_tabs;
	}

	/**
	 * Registers tabs for the Admin Results page
	 *
	 * Registers a new tab on the admin results page
	 *
	 * @since  5.0.0
	 * @param  string $title    The name of the tab
	 * @param  string $function The function that displays the tab's content
	 * @return void
	 */
	public function register_admin_results_tab( $title, $function ) {
		$slug                       = strtolower( str_replace( ' ', '-', $title ) );
		$new_tab                    = array(
			'title'    => $title,
			'function' => $function,
			'slug'     => $slug,
		);
		$this->admin_results_tabs[] = $new_tab;
	}

	/**
	 * Retrieves Admin Results Tab Array
	 *
	 * Retrieves the array of titles and functions for the tabs registered for the admin results page
	 *
	 * @since  5.0.0
	 * @return array The array of registered tabs
	 */
	public function get_admin_results_tabs() {
		return $this->admin_results_tabs;
	}

	/**
	 * Registers Results Tab
	 *
	 * Registers a new tab on the results page
	 *
	 * @since  4.1.0
	 * @param  string $title    The name of the tab
	 * @param  string $function The function that displays the tab's content
	 * @return void
	 */
	public function register_results_settings_tab( $title, $function ) {
		$slug                 = strtolower( str_replace( ' ', '-', $title ) );
		$new_tab              = array(
			'title'    => $title,
			'function' => $function,
			'slug'     => $slug,
		);
		$this->results_tabs[] = $new_tab;
	}

	/**
	 * Retrieves Results Tab Array
	 *
	 * Retrieves the array of titles and functions of the registered tabs
	 *
	 * @since  4.1.0
	 * @return array The array of registered tabs
	 */
	public function get_results_tabs() {
		return $this->results_tabs;
	}

	/**
	 * Registers Quiz Settings Tab
	 *
	 * Registers a new tab on the quiz settings page
	 *
	 * @since  4.0.0
	 * @param  string $title    The name of the tab
	 * @param  string $function The function that displays the tab's content
	 * @return void
	 */
	public function register_quiz_settings_tabs( $title, $function, $slug = '' ) {
		if ( '' === $slug ) {
			$slug = strtolower( str_replace( ' ', '-', $title ) );
		}
		$new_tab               = array(
			'title'    => $title,
			'function' => $function,
			'slug'     => $slug,
		);
		$this->settings_tabs[] = $new_tab;
	}

	/**
	 * Echos Registered Tabs Title Link
	 *
	 * Echos the title link of the registered tabs
	 *
	 * @since  4.0.0
	 * @return array The array of registered tabs
	 */
	public function get_settings_tabs() {
		return apply_filters( 'qmn_quiz_setting_tabs', $this->settings_tabs );
	}

	/**
	 * global animatiocv array return
	 *
	 * @since 4.7.1
	 */
	public function quiz_animation_effect() {

		return array(
			array(
				'label' => __( 'bounce', 'quiz-master-next' ),
				'value' => 'bounce',
			),
			array(
				'label' => __( 'flash', 'quiz-master-next' ),
				'value' => 'flash',
			),
			array(
				'label' => __( 'pulse', 'quiz-master-next' ),
				'value' => 'pulse',
			),
			array(
				'label' => __( 'rubberBand', 'quiz-master-next' ),
				'value' => 'rubberBand',
			),
			array(
				'label' => __( 'shake', 'quiz-master-next' ),
				'value' => 'shake',
			),
			array(
				'label' => __( 'swing', 'quiz-master-next' ),
				'value' => 'swing',
			),
			array(
				'label' => __( 'tada', 'quiz-master-next' ),
				'value' => 'tada',
			),
			array(
				'label' => __( 'wobble', 'quiz-master-next' ),
				'value' => 'wobble',
			),
			array(
				'label' => __( 'jello', 'quiz-master-next' ),
				'value' => 'jello',
			),
			array(
				'label' => __( 'heartBeat', 'quiz-master-next' ),
				'value' => 'heartBeat',
			),
			array(
				'label' => __( 'No animation', 'quiz-master-next' ),
				'value' => '',
			),
		);

	}

	/**
	 * converts dates into preferred date format
	 *
	 * @since  7.3.3
	 * @param  array $qsm_qna_array The array of results for the quiz
	 * @uses   QMNQuizManager:submit_results() submits and displays results
	 * @uses   qsm_generate_results_details_tab() generates admin results page
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_to_preferred_date_format( $qsm_qna_array ) {
		global $mlwQuizMasterNext;
		$quiz_options        = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
		$qsm_quiz_settings   = maybe_unserialize( $quiz_options->quiz_settings );
		$qsm_quiz_options    = maybe_unserialize( $qsm_quiz_settings['quiz_options'] );
		$qsm_global_settings = get_option( 'qsm-quiz-settings' );
		// check if preferred date format is set at quiz level or plugin level. Default to WP date format otherwise
		if ( isset( $qsm_quiz_options['preferred_date_format'] ) ) {
			$preferred_date_format = $qsm_quiz_options['preferred_date_format'];
		} elseif ( isset( $qsm_global_settings['preferred_date_format'] ) ) {
			$preferred_date_format = isset( $qsm_global_settings['preferred_date_format'] );
		} else {
			$preferred_date_format = get_option( 'date_format' );
		}
		// filter date format
		$GLOBALS['qsm_date_format'] = apply_filters( 'qms_preferred_date_format', $preferred_date_format );

		$qsm_qna_array = $this->convert_contacts_to_preferred_date_format( $qsm_qna_array );
		$qsm_qna_array = $this->convert_answers_to_preferred_date_format( $qsm_qna_array );
		$this->convert_questions_to_preferred_date_format();

		return $qsm_qna_array;
	}

	/**
	 * converts contacts into preferred date format
	 *
	 * @since  7.3.3
	 * @param  array $qsm_qna_array The array of results for the quiz
	 * @uses   convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_contacts_to_preferred_date_format( $qsm_qna_array ) {

		$qsm_contact_array = $qsm_qna_array['contact'];
		foreach ( $qsm_contact_array as $qsm_contact_id => $qsm_contact ) {
			if ( 'date' === $qsm_contact['type'] && null !== $GLOBALS['qsm_date_format'] ) {
				$qsm_qna_array['contact'][ $qsm_contact_id ]['value'] = date_i18n( $GLOBALS['qsm_date_format'], strtotime( ( $qsm_contact['value'] ) ) );
			}
		}
		return $qsm_qna_array;
	}

	/**
	 * converts answers into preferred date format
	 *
	 * @since  7.3.3
	 * @param  array $qsm_qna_array The array of results for the quiz
	 * @uses   convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_answers_to_preferred_date_format( $qsm_qna_array ) {

		$qsm_qna_list = $qsm_qna_array['question_answers_array'];
		foreach ( $qsm_qna_list as $qna_id => $qna ) {
			if ( '12' === $qna['question_type'] && null !== $GLOBALS['qsm_date_format'] ) {
				$qsm_qna_array['question_answers_array'][ $qna_id ]['1'] = date_i18n( $GLOBALS['qsm_date_format'], strtotime( ( $qna['1'] ) ) );
				$qsm_qna_array['question_answers_array'][ $qna_id ]['2'] = date_i18n( $GLOBALS['qsm_date_format'], strtotime( ( $qna['2'] ) ) );
			}
		}
		return $qsm_qna_array;
	}

	/**
	 * converts questions into preferred date format
	 *
	 * @since  7.3.3
	 * @param  array $qsm_qna_array The array of results for the quiz
	 * @uses   convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_questions_to_preferred_date_format() {
		if ( ! function_exists( 'qsm_convert_question_array_date_format' ) ) {
			function qsm_convert_question_array_date_format( $questions ) {
				foreach ( $questions as $question_id => $question_to_convert ) {
					if ( '12' === $question_to_convert['question_type_new'] ) {
						foreach ( $question_to_convert['answers'] as $answer_id => $answer_value ) {
							$questions[ $question_id ]['answers'][ $answer_id ][0] = date_i18n( $GLOBALS['qsm_date_format'], strtotime( $answer_value[0] ) );
						}
					}
				}
				return $questions;
			}
		}
		add_filter( 'qsm_load_questions_by_pages', 'qsm_convert_question_array_date_format' );
	}

	/**
	 *
	 *
	 * @since  7.3.5
	 * @param  array
	 * @uses
	 * @return array
	 */

	public function qsm_results_css_inliner( $html ) {

		$html    = str_replace( '<br/>', '<br>', $html );
		$html    = str_replace( '<br />', '<br>', $html );
		$html    = str_replace( "class='qmn_question_answer", "style='margin-bottom:30px' class='", $html );
		$html    = preg_replace( '/<span class="qsm-text-simple-option(.*?)">(.*?)<\/span>/', "<span style='color:#808080;display:block;margin-bottom:5px;'>&#8226;&nbsp;$2</span>", $html );
		$html    = preg_replace( '/<span class="qsm-text-wrong-option(.*?)">(.*?)<\/span>/', "<span style='color:red;display:block;margin-bottom:5px;'>&#x2715;$2</span>", $html );
		$html    = preg_replace( '/<span class="qmn_user_incorrect_answer(.*?)">(.*?)<\/span>/', "<span style='color:red;display:block;margin-bottom:5px;'>&#x2715;$2</span>", $html );
		$html    = preg_replace( '/<span class="qsm-text-correct-option(.*?)">(.*?)<\/span>/', "<span style='color:green;display:block;margin-bottom:5px;'>&#10003;$2</span>", $html );
		$html    = preg_replace( '/<span class="qmn_user_correct_answer(.*?)">(.*?)<\/span>/', "<span style='color:green;display:block;margin-bottom:5px;'>&#10003;$2</span>", $html );

		return $html;
	}

	/** */
	public function categorize_question_types() {
		$question_type_categorized   = array();
		$question_type_others        = array();
		$question_type_uncategorized = array();
		foreach ( $this->question_types as $question_type ) {
			$is_categorized = isset( $question_type ['category'] ) && '' !== $question_type ['category'];
			if ( $is_categorized ) {
				if ( 'others' === mb_strtolower( $question_type ['category'] ) ) {
					$question_type_others[ $question_type ['category'] ] [ $question_type['slug'] ] = array(
						'slug' => $question_type['slug'],
						'name' => $question_type['name'],
					);
				} else {
					$question_type_categorized[ $question_type ['category'] ] [ $question_type['slug'] ] = array(
						'slug' => $question_type['slug'],
						'name' => $question_type['name'],
					);
				}
			} else {
				$question_type_uncategorized['uncategorized'][ $question_type['slug'] ] = array(
					'slug' => $question_type['slug'],
					'name' => $question_type['name'],
				);

			}
		}
		$question_type_categorized = array_merge( $question_type_categorized, $question_type_others );
		$question_type_categorized = array_merge( $question_type_categorized, $question_type_uncategorized );
		return $question_type_categorized;
	}
}
