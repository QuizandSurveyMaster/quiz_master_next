<?php
/**
 * Abilities API integration for Quiz and Survey Master.
 *
 * @package QSM
 * @since 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers QSM abilities for WordPress 6.9+.
 *
 * @since 11.0.0
 */
class QSM_Abilities_API {

	/**
	 * Ability category slug.
	 *
	 * @since 11.0.0
	 * @var string
	 */
	const CATEGORY = 'qsm-quiz-management';

	/**
	 * Constructor.
	 *
	 * @since 11.0.0
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ), 5 );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ), 5 );
	}

	/**
	 * Registers the QSM ability category.
	 *
	 * @since 11.0.0
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Quiz and Survey Master', 'quiz-master-next' ),
				'description' => __( 'Abilities for creating, updating, publishing, duplicating, and reporting on Quiz and Survey Master quizzes.', 'quiz-master-next' ),
			)
		);
	}

	/**
	 * Registers all QSM abilities.
	 *
	 * @since 11.0.0
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		foreach ( $this->get_ability_definitions() as $ability_name => $ability ) {
			wp_register_ability(
				$ability_name,
				array(
					'label'               => $ability['label'],
					'description'         => $ability['description'],
					'category'            => self::CATEGORY,
					'input_schema'        => call_user_func( array( $this, $ability['input_schema'] ) ),
					'output_schema'       => call_user_func( array( $this, $ability['output_schema'] ) ),
					'execute_callback'    => array( $this, $ability['execute_callback'] ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'meta'                => $this->get_ability_meta(
						$ability['readonly'],
						$ability['destructive'],
						$ability['idempotent']
					),
				)
			);
		}
	}

	/**
	 * Returns the QSM ability registration definitions.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_ability_definitions() {
		return array(
			'qsm/create-quiz'     => array(
				'label'            => __( 'Create Quiz', 'quiz-master-next' ),
				'description'      => __( 'Creates a new Quiz and Survey Master quiz or survey record.', 'quiz-master-next' ),
				'input_schema'     => 'get_create_quiz_input_schema',
				'output_schema'    => 'get_create_quiz_output_schema',
				'execute_callback' => 'execute_create_quiz',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => false,
			),
			'qsm/publish-quiz'    => array(
				'label'            => __( 'Publish Quiz', 'quiz-master-next' ),
				'description'      => __( 'Publishes an existing Quiz and Survey Master quiz.', 'quiz-master-next' ),
				'input_schema'     => 'get_publish_quiz_input_schema',
				'output_schema'    => 'get_publish_quiz_output_schema',
				'execute_callback' => 'execute_publish_quiz',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => true,
			),
			'qsm/update-quiz'     => array(
				'label'            => __( 'Update Quiz', 'quiz-master-next' ),
				'description'      => __( 'Updates the title and settings for an existing Quiz and Survey Master quiz.', 'quiz-master-next' ),
				'input_schema'     => 'get_update_quiz_input_schema',
				'output_schema'    => 'get_update_quiz_output_schema',
				'execute_callback' => 'execute_update_quiz',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => false,
			),
			'qsm/create-question' => array(
				'label'            => __( 'Create Question', 'quiz-master-next' ),
				'description'      => __( 'Creates a new question for a Quiz and Survey Master quiz.', 'quiz-master-next' ),
				'input_schema'     => 'get_create_question_input_schema',
				'output_schema'    => 'get_create_question_output_schema',
				'execute_callback' => 'execute_create_question',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => false,
			),
			'qsm/update-question' => array(
				'label'            => __( 'Update Question', 'quiz-master-next' ),
				'description'      => __( 'Updates question content and option data for a Quiz and Survey Master question.', 'quiz-master-next' ),
				'input_schema'     => 'get_update_question_input_schema',
				'output_schema'    => 'get_update_question_output_schema',
				'execute_callback' => 'execute_update_question',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => false,
			),
			'qsm/get-results'     => array(
				'label'            => __( 'Get Results', 'quiz-master-next' ),
				'description'      => __( 'Returns Quiz and Survey Master results for a specific quiz.', 'quiz-master-next' ),
				'input_schema'     => 'get_results_input_schema',
				'output_schema'    => 'get_results_output_schema',
				'execute_callback' => 'execute_get_results',
				'readonly'         => true,
				'destructive'      => false,
				'idempotent'       => true,
			),
			'qsm/duplicate-quiz'  => array(
				'label'            => __( 'Duplicate Quiz', 'quiz-master-next' ),
				'description'      => __( 'Creates a duplicate of an existing Quiz and Survey Master quiz.', 'quiz-master-next' ),
				'input_schema'     => 'get_duplicate_quiz_input_schema',
				'output_schema'    => 'get_duplicate_quiz_output_schema',
				'execute_callback' => 'execute_duplicate_quiz',
				'readonly'         => false,
				'destructive'      => false,
				'idempotent'       => false,
			),
		);
	}

	/**
	 * Returns ability REST metadata.
	 *
	 * @since 11.0.0
	 * @param bool      $readonly    Whether the ability is read-only.
	 * @param bool      $destructive Whether the ability is destructive.
	 * @param bool|null $idempotent  Whether repeated calls have the same effect.
	 * @return array
	 */
	private function get_ability_meta( $readonly, $destructive, $idempotent ) {
		return array(
			'show_in_rest' => true,
			'annotations'  => array(
				'readonly'    => $readonly,
				'destructive' => $destructive,
				'idempotent'  => $idempotent,
			),
		);
	}

	/**
	 * Checks ability permissions.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return true|WP_Error
	 */
	public function permission_callback( $input = array() ) {
		unset( $input );

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return new WP_Error(
			'qsm_ability_forbidden',
			__( 'You are not allowed to execute QSM abilities.', 'quiz-master-next' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);
	}

	/**
	 * Execute callback for create-quiz.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_create_quiz( $input = array() ) {
		$input = $this->normalize_input( $input, 'create-quiz' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_name = $this->get_sanitized_string( $input, 'quiz_name' );
		$quiz_type = $this->get_sanitized_string( $input, 'quiz_type' );

		if ( '' === $quiz_name || '' === $quiz_type ) {
			return $this->get_required_field_error( 'create-quiz' );
		}

		$quiz_creator = $this->get_quiz_creator();
		if ( is_wp_error( $quiz_creator ) ) {
			return $quiz_creator;
		}

		$quiz_system = $this->map_quiz_type_to_system( $quiz_type );
		$quiz_creator->create_quiz(
			$quiz_name,
			'primary',
			array(
				'quiz_options' => array(
					'system' => $quiz_system,
				),
			)
		);
		$quiz_id = $quiz_creator->get_id();

		if ( ! $quiz_id ) {
			return new WP_Error(
				'qsm_quiz_creation_failed',
				__( 'Failed to create quiz.', 'quiz-master-next' ),
				array( 'status' => 500 )
			);
		}

		if ( is_wp_error( $this->update_quiz_system( $quiz_id, $quiz_system ) ) ) {
			return new WP_Error(
				'qsm_quiz_system_update_failed',
				__( 'Quiz was created, but the quiz type could not be finalized.', 'quiz-master-next' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'quiz_id' => $quiz_id,
			'status'  => 'created',
		);
	}

	/**
	 * Execute callback for publish-quiz.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_publish_quiz( $input = array() ) {
		$input = $this->normalize_input( $input, 'publish-quiz' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_id = $this->get_positive_int( $input, 'quiz_id' );
		if ( 1 > $quiz_id ) {
			return $this->get_required_field_error( 'publish-quiz' );
		}

		$quiz_post_id = $this->get_quiz_post_id( $quiz_id );

		if ( ! $quiz_post_id ) {
			return new WP_Error(
				'qsm_quiz_not_found',
				__( 'Quiz not found.', 'quiz-master-next' ),
				array( 'status' => 404 )
			);
		}

		$result = wp_update_post(
			array(
				'ID'          => $quiz_post_id,
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'success_message' => __( 'Quiz published successfully.', 'quiz-master-next' ),
		);
	}

	/**
	 * Execute callback for update-quiz.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_update_quiz( $input = array() ) {
		$input = $this->normalize_input( $input, 'update-quiz' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_id       = $this->get_positive_int( $input, 'quiz_id' );
		$new_title     = $this->get_sanitized_string( $input, 'new_title' );
		$settings_data = isset( $input['settings_array'] ) && is_array( $input['settings_array'] ) ? $input['settings_array'] : array();

		if ( 1 > $quiz_id || '' === $new_title || empty( $settings_data ) ) {
			return $this->get_required_field_error( 'update-quiz' );
		}

		global $wpdb;
		$quiz_post_id = $this->get_quiz_post_id( $quiz_id );

		if ( ! $quiz_post_id ) {
			return new WP_Error(
				'qsm_quiz_not_found',
				__( 'Quiz not found.', 'quiz-master-next' ),
				array( 'status' => 404 )
			);
		}

		$quiz_creator = $this->get_quiz_creator();
		if ( is_wp_error( $quiz_creator ) ) {
			return $quiz_creator;
		}
		$quiz_creator->edit_quiz_name( $quiz_id, $new_title, $quiz_post_id );

		$quiz_settings = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_settings FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );
		$quiz_settings = maybe_unserialize( $quiz_settings );
		if ( ! is_array( $quiz_settings ) ) {
			$quiz_settings = array();
		}

		foreach ( $settings_data as $setting ) {
			if ( isset( $setting['setting_key'] ) && isset( $setting['setting_value'] ) ) {
				$key   = sanitize_text_field( $setting['setting_key'] );
				$value = $this->normalize_setting_value( $setting['setting_value'] );
				$quiz_settings[ $key ] = $value;
			}
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'quiz_settings' => maybe_serialize( $quiz_settings ) ),
			array( 'quiz_id' => $quiz_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'qsm_quiz_update_failed',
				__( 'Failed to update quiz settings.', 'quiz-master-next' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'status' => 'updated',
		);
	}

	/**
	 * Execute callback for create-question.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_create_question( $input = array() ) {
		$input = $this->normalize_input( $input, 'create-question' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_id       = $this->get_positive_int( $input, 'quiz_id' );
		$question_text = $this->get_sanitized_string( $input, 'question_text' );
		$question_type = strtolower( $this->get_sanitized_string( $input, 'question_type' ) );

		if ( 1 > $quiz_id || '' === $question_text || '' === $question_type ) {
			return $this->get_required_field_error( 'create-question' );
		}

		$type_map = array(
			'multiple-choice'  => '0',
			'short-answer'     => '2',
			'essay'            => '3',
			'true-false'       => '7',
			'fill-in-blank'    => '14',
			'number'           => '4',
			'captcha'          => '5',
			'horizontal'       => '6',
			'dropdown'         => '12',
			'file-upload'      => '11',
			'date'             => '9',
			'polar'            => '13',
		);

		$type_id = isset( $type_map[ $question_type ] ) ? $type_map[ $question_type ] : '0';

		$data = array(
			'quiz_id' => $quiz_id,
			'type'    => $type_id,
			'name'    => $question_text,
		);

		$answers = array();
		$settings = array( 'required' => 1 );

		try {
			$question_id = QSM_Questions::create_question( $data, $answers, $settings );
			return array(
				'question_id' => $question_id,
			);
		} catch ( Exception $e ) {
			return new WP_Error(
				'qsm_question_creation_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Execute callback for update-question.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_update_question( $input = array() ) {
		$input = $this->normalize_input( $input, 'update-question' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$question_id   = $this->get_positive_int( $input, 'question_id' );
		$question_text = $this->get_sanitized_string( $input, 'question_text' );
		$options_array = isset( $input['options_array'] ) && is_array( $input['options_array'] ) ? $input['options_array'] : array();

		if ( 1 > $question_id || '' === $question_text || empty( $options_array ) ) {
			return $this->get_required_field_error( 'update-question' );
		}

		$question = QSM_Questions::load_question( $question_id );
		if ( empty( $question ) ) {
			return new WP_Error(
				'qsm_question_not_found',
				__( 'Question not found.', 'quiz-master-next' ),
				array( 'status' => 404 )
			);
		}

		$data = array(
			'quiz_id'         => $question['quiz_id'],
			'type'            => $question['question_type_new'],
			'name'            => $question_text,
			'answer_info'     => $question['question_answer_info'],
			'comments'        => $question['comments'],
			'hint'            => $question['hints'],
			'order'           => $question['question_order'],
			'category'        => $question['category'],
			'multicategories' => $question['multicategories'],
			'linked_question' => $question['linked_question'],
		);

		$answers = array();
		if ( isset( $options_array['choices'] ) && is_array( $options_array['choices'] ) ) {
			foreach ( $options_array['choices'] as $choice ) {
				$answers[] = array(
					$choice['label'],
					isset( $choice['is_correct'] ) && $choice['is_correct'] ? 1 : 0,
					isset( $choice['is_correct'] ) && $choice['is_correct'] ? 1 : 0,
				);
			}
		}

		$settings = $question['settings'];
		if ( isset( $options_array['shuffle_choices'] ) ) {
			$settings['randomize_answers'] = $options_array['shuffle_choices'] ? 1 : 0;
		}

		try {
			QSM_Questions::save_question( $question_id, $data, $answers, $settings );
			return array(
				'status' => 'updated',
			);
		} catch ( Exception $e ) {
			return new WP_Error(
				'qsm_question_update_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Execute callback for get-results.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_get_results( $input = array() ) {
		$input = $this->normalize_input( $input, 'get-results' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_id = $this->get_positive_int( $input, 'quiz_id' );
		$limit   = isset( $input['limit'] ) ? $this->get_positive_int( $input, 'limit' ) : 10;

		if ( 1 > $quiz_id ) {
			return $this->get_required_field_error( 'get-results' );
		}

		if ( $limit > 100 ) {
			$limit = 100;
		}

		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT quiz_system, correct_score, point_score, user_email, email FROM {$wpdb->prefix}mlw_results WHERE quiz_id = %d AND deleted = 0 ORDER BY result_id DESC LIMIT %d",
				$quiz_id,
				$limit
			),
			ARRAY_A
		);

		$results_array = array();
		if ( $results ) {
			foreach ( $results as $result ) {
				$results_array[] = array(
					'score'      => $this->get_result_score( $result ),
					'user_email' => isset( $result['email'] ) && ! empty( $result['email'] ) ? sanitize_email( $result['email'] ) : sanitize_email( $result['user_email'] ),
				);
			}
		}

		return array(
			'results_array' => $results_array,
		);
	}

	/**
	 * Execute callback for duplicate-quiz.
	 *
	 * @since 11.0.0
	 * @param mixed $input Ability input payload.
	 * @return array|WP_Error
	 */
	public function execute_duplicate_quiz( $input = array() ) {
		$input = $this->normalize_input( $input, 'duplicate-quiz' );
		if ( is_wp_error( $input ) ) {
			return $input;
		}

		$quiz_id = $this->get_positive_int( $input, 'quiz_id' );
		if ( 1 > $quiz_id ) {
			return $this->get_required_field_error( 'duplicate-quiz' );
		}

		global $wpdb;
		$original_quiz = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );

		if ( ! $original_quiz ) {
			return new WP_Error(
				'qsm_quiz_not_found',
				__( 'Quiz not found.', 'quiz-master-next' ),
				array( 'status' => 404 )
			);
		}

		$new_quiz_name = $original_quiz->quiz_name . ' (Copy)';
		$is_duplicating_questions = 1;

		$quiz_creator = $this->get_quiz_creator();
		if ( is_wp_error( $quiz_creator ) ) {
			return $quiz_creator;
		}

		$quiz_creator->duplicate_quiz( $quiz_id, $new_quiz_name, $is_duplicating_questions );
		$new_quiz_id = $quiz_creator->get_id();

		if ( ! $new_quiz_id ) {
			return new WP_Error(
				'qsm_quiz_duplication_failed',
				__( 'Failed to duplicate quiz.', 'quiz-master-next' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'new_quiz_id' => $new_quiz_id,
		);
	}

	/**
	 * Returns the create-quiz input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_create_quiz_input_schema() {
		return array(
			'type'                 => 'json',
			'description'          => __( 'Input payload for creating a new QSM quiz.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_name' => array(
					'type'        => 'string',
					'description' => __( 'Human-readable title for the new quiz.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
				'quiz_type' => array(
					'type'        => 'string',
					'description' => __( 'Quiz type identifier, such as quiz, survey, or assessment.', 'quiz-master-next' ),
					'enum'        => array( 'quiz', 'assessment', 'survey' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'quiz_name', 'quiz_type' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the create-quiz output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_create_quiz_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the create-quiz ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'The generated quiz ID.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
				'status' => array(
					'type'        => 'string',
					'description' => __( 'Execution status for the create-quiz ability.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'quiz_id', 'status' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the publish-quiz input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_publish_quiz_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for publishing an existing QSM quiz.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'Existing quiz ID to publish.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
			),
			'required'             => array( 'quiz_id' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the publish-quiz output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_publish_quiz_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the publish-quiz ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'success_message' => array(
					'type'        => 'string',
					'description' => __( 'Confirmation message describing the publish result.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'success_message' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the update-quiz input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_update_quiz_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for updating a QSM quiz title and settings.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'Existing quiz ID to update.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
				'new_title' => array(
					'type'        => 'string',
					'description' => __( 'New quiz title to save.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
				'settings_array' => array(
					'type'        => 'array',
					'description' => __( 'List of quiz settings to update.', 'quiz-master-next' ),
					'minItems'    => 1,
					'items'       => array(
						'type'                 => 'object',
						'properties'           => array(
							'setting_key' => array(
								'type'        => 'string',
								'description' => __( 'The setting name.', 'quiz-master-next' ),
								'minLength'   => 1,
							),
							'setting_value' => array(
								'type'        => 'object',
								'description' => __( 'Structured setting value payload.', 'quiz-master-next' ),
								'properties'  => array(
									'value' => array(
										'type'        => 'string',
										'description' => __( 'The serialized or stringified setting value.', 'quiz-master-next' ),
									),
								),
								'additionalProperties' => true,
							),
						),
						'required'             => array( 'setting_key', 'setting_value' ),
						'additionalProperties' => false,
					),
				),
			),
			'required'             => array( 'quiz_id', 'new_title', 'settings_array' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the update-quiz output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_update_quiz_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the update-quiz ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'status' => array(
					'type'        => 'string',
					'description' => __( 'Execution status for the update-quiz ability.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'status' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the create-question input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_create_question_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for creating a question in a QSM quiz.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'Quiz ID that will own the question.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
				'question_text' => array(
					'type'        => 'string',
					'description' => __( 'The question prompt or content.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
				'question_type' => array(
					'type'        => 'string',
					'description' => __( 'Question type slug, such as multiple-choice or short-answer.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'quiz_id', 'question_text', 'question_type' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the create-question output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_create_question_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the create-question ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'question_id' => array(
					'type'        => 'integer',
					'description' => __( 'The generated question ID.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
			),
			'required'             => array( 'question_id' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the update-question input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_update_question_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for updating a QSM question and its options.', 'quiz-master-next' ),
			'properties'           => array(
				'question_id' => array(
					'type'        => 'integer',
					'description' => __( 'Existing question ID to update.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
				'question_text' => array(
					'type'        => 'string',
					'description' => __( 'Updated question prompt or content.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
				'options_array' => array(
					'type'        => 'object',
					'description' => __( 'Structured question option data, including answer choices and feedback.', 'quiz-master-next' ),
					'properties'  => array(
						'choices' => array(
							'type'        => 'array',
							'description' => __( 'Available answer choices for the question.', 'quiz-master-next' ),
							'items'       => array(
								'type'                 => 'object',
								'properties'           => array(
									'label' => array(
										'type'        => 'string',
										'description' => __( 'Visible answer label.', 'quiz-master-next' ),
										'minLength'   => 1,
									),
									'value' => array(
										'type'        => 'string',
										'description' => __( 'Stored answer value.', 'quiz-master-next' ),
										'minLength'   => 1,
									),
									'is_correct' => array(
										'type'        => 'boolean',
										'description' => __( 'Whether the choice is marked as correct.', 'quiz-master-next' ),
									),
								),
								'required'             => array( 'label', 'value' ),
								'additionalProperties' => false,
							),
						),
						'feedback' => array(
							'type'        => 'object',
							'description' => __( 'Optional feedback messages associated with the question.', 'quiz-master-next' ),
							'properties'  => array(
								'correct' => array(
									'type'        => 'string',
									'description' => __( 'Feedback shown for a correct answer.', 'quiz-master-next' ),
								),
								'incorrect' => array(
									'type'        => 'string',
									'description' => __( 'Feedback shown for an incorrect answer.', 'quiz-master-next' ),
								),
							),
							'additionalProperties' => false,
						),
						'shuffle_choices' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether answer choices should be randomized.', 'quiz-master-next' ),
						),
					),
					'additionalProperties' => false,
				),
			),
			'required'             => array( 'question_id', 'question_text', 'options_array' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the update-question output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_update_question_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the update-question ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'status' => array(
					'type'        => 'string',
					'description' => __( 'Execution status for the update-question ability.', 'quiz-master-next' ),
					'minLength'   => 1,
				),
			),
			'required'             => array( 'status' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the get-results input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_results_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for retrieving QSM quiz results.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'Quiz ID whose results should be returned.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
				'limit' => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of results to return.', 'quiz-master-next' ),
					'minimum'     => 1,
					'maximum'     => 100,
					'default'     => 10,
				),
			),
			'required'             => array( 'quiz_id' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the get-results output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_results_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the get-results ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'results_array' => array(
					'type'        => 'array',
					'description' => __( 'Collection of quiz result records.', 'quiz-master-next' ),
					'items'       => array(
						'type'                 => 'object',
						'properties'           => array(
							'score' => array(
								'type'        => 'number',
								'description' => __( 'Stored score for the result.', 'quiz-master-next' ),
							),
							'user_email' => array(
								'type'        => 'string',
								'description' => __( 'Email address associated with the result.', 'quiz-master-next' ),
								'format'      => 'email',
							),
						),
						'required'             => array( 'score', 'user_email' ),
						'additionalProperties' => false,
					),
				),
			),
			'required'             => array( 'results_array' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the duplicate-quiz input schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_duplicate_quiz_input_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Input payload for duplicating an existing QSM quiz.', 'quiz-master-next' ),
			'properties'           => array(
				'quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'Existing quiz ID to duplicate.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
			),
			'required'             => array( 'quiz_id' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the duplicate-quiz output schema.
	 *
	 * @since 11.0.0
	 * @return array
	 */
	private function get_duplicate_quiz_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __( 'Response returned after the duplicate-quiz ability executes.', 'quiz-master-next' ),
			'properties'           => array(
				'new_quiz_id' => array(
					'type'        => 'integer',
					'description' => __( 'The duplicated quiz ID.', 'quiz-master-next' ),
					'minimum'     => 1,
				),
			),
			'required'             => array( 'new_quiz_id' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Normalizes ability input to an array.
	 *
	 * @since 11.0.0
	 * @param mixed  $input        Ability input payload.
	 * @param string $ability_name Ability name for error messages.
	 * @return array|WP_Error
	 */
	private function normalize_input( $input, $ability_name ) {
		if ( is_object( $input ) ) {
			$input = get_object_vars( $input );
		}

		if ( is_array( $input ) ) {
			return $input;
		}

		return new WP_Error(
			'qsm_invalid_ability_input',
			sprintf(
				/* translators: %s: Ability name. */
				__( 'Invalid input received for the %s ability.', 'quiz-master-next' ),
				$ability_name
			),
			array(
				'status' => 400,
			)
		);
	}

	/**
	 * Returns a sanitized string input value.
	 *
	 * @since 11.0.0
	 * @param array  $input Ability input payload.
	 * @param string $key   Input key.
	 * @return string
	 */
	private function get_sanitized_string( array $input, $key ) {
		if ( ! isset( $input[ $key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $input[ $key ] ) );
	}

	/**
	 * Returns a positive integer input value.
	 *
	 * @since 11.0.0
	 * @param array  $input Ability input payload.
	 * @param string $key   Input key.
	 * @return int
	 */
	private function get_positive_int( array $input, $key ) {
		if ( ! isset( $input[ $key ] ) ) {
			return 0;
		}

		return absint( $input[ $key ] );
	}

	/**
	 * Creates a required field validation error.
	 *
	 * @since 11.0.0
	 * @param string $ability_name Ability name for the error message.
	 * @return WP_Error
	 */
	private function get_required_field_error( $ability_name ) {
		return new WP_Error(
			'qsm_ability_missing_required_fields',
			sprintf(
				/* translators: %s: Ability name. */
				__( 'Missing or invalid required fields for the %s ability.', 'quiz-master-next' ),
				$ability_name
			),
			array(
				'status' => 400,
			)
		);
	}

	/**
	 * Returns the quiz creator object when available.
	 *
	 * @since 11.0.0
	 * @return QMNQuizCreator|WP_Error
	 */
	private function get_quiz_creator() {
		global $mlwQuizMasterNext;

		if ( isset( $mlwQuizMasterNext->quizCreator ) && $mlwQuizMasterNext->quizCreator instanceof QMNQuizCreator ) {
			return $mlwQuizMasterNext->quizCreator;
		}

		return new WP_Error(
			'qsm_quiz_creator_unavailable',
			__( 'Quiz creator is not available.', 'quiz-master-next' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Looks up the post ID for a QSM quiz.
	 *
	 * @since 11.0.0
	 * @param int $quiz_id Quiz ID.
	 * @return int
	 */
	private function get_quiz_post_id( $quiz_id ) {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'quiz_id' AND meta_value = %d LIMIT 1",
				$quiz_id
			)
		);
	}

	/**
	 * Maps the quiz_type input to QSM's stored quiz system.
	 *
	 * @since 11.0.0
	 * @param string $quiz_type Ability quiz type.
	 * @return int
	 */
	private function map_quiz_type_to_system( $quiz_type ) {
		switch ( strtolower( $quiz_type ) ) {
			case 'assessment':
				return 1;
			case 'survey':
				return 3;
			case 'quiz':
			default:
				return 0;
		}
	}

	/**
	 * Persists the quiz system type after creation.
	 *
	 * @since 11.0.0
	 * @param int $quiz_id      Quiz ID.
	 * @param int $quiz_system  Quiz system value.
	 * @return true|WP_Error
	 */
	private function update_quiz_system( $quiz_id, $quiz_system ) {
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_system' => $quiz_system,
			),
			array(
				'quiz_id' => $quiz_id,
			),
			array( '%d' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'qsm_quiz_system_update_failed',
				__( 'Failed to persist the quiz type.', 'quiz-master-next' ),
				array( 'status' => 500 )
			);
		}

		return true;
	}

	/**
	 * Normalizes a settings value before storage.
	 *
	 * @since 11.0.0
	 * @param mixed $value Raw settings value.
	 * @return mixed
	 */
	private function normalize_setting_value( $value ) {
		if ( is_array( $value ) ) {
			if ( isset( $value['value'] ) && ! is_array( $value['value'] ) && ! is_object( $value['value'] ) ) {
				return sanitize_text_field( wp_unslash( (string) $value['value'] ) );
			}

			return map_deep( $value, 'sanitize_text_field' );
		}

		if ( is_bool( $value ) ) {
			return $value ? 1 : 0;
		}

		if ( is_numeric( $value ) ) {
			return 0 + $value;
		}

		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Returns a normalized numeric result score.
	 *
	 * @since 11.0.0
	 * @param array $result Raw result row.
	 * @return float
	 */
	private function get_result_score( array $result ) {
		$quiz_system = isset( $result['quiz_system'] ) ? (int) $result['quiz_system'] : 0;

		if ( 0 === $quiz_system ) {
			return isset( $result['correct_score'] ) ? (float) $result['correct_score'] : 0.0;
		}

		return isset( $result['point_score'] ) ? (float) $result['point_score'] : 0.0;
	}
}