<?php
/**
 * WordPress Abilities API integration for Quiz and Survey Master.
 *
 * Registers QSM quiz and question operations as discoverable abilities
 * so AI agents and automation tools can interact with QSM programmatically.
 *
 * Requires WordPress 6.9+ (Abilities API). All registrations are guarded by
 * function_exists checks so the plugin remains compatible with older WordPress.
 *
 * @package QSM
 * @since   9.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers QSM categories and abilities with the WordPress Abilities API.
 *
 * @since 9.1.0
 */
class QSM_Abilities { // NOSONAR

	/**
	 * Plugin namespace used for all ability names.
	 *
	 * @var string
	 */
	const NAMESPACE = 'quiz-master-next';

	/**
	 * Hooks into the WordPress Abilities API init actions.
	 *
	 * @since 9.1.0
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_register_ability_category' ) || ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Registers the QSM ability categories.
	 *
	 * @since 9.1.0
	 * @return void
	 */
	public function register_categories() {
		$categories = array(
			'qsm-quizzes'   => array(
				'label'       => __( 'QSM Quizzes', 'quiz-master-next' ),
				'description' => __( 'Abilities for reading Quiz and Survey Master quizzes.', 'quiz-master-next' ),
			),
			'qsm-questions' => array(
				'label'       => __( 'QSM Questions', 'quiz-master-next' ),
				'description' => __( 'Abilities for managing questions within QSM quizzes.', 'quiz-master-next' ),
			),
		);

		foreach ( $categories as $slug => $args ) {
			wp_register_ability_category( $slug, $args );
		}
	}

	/**
	 * Registers all QSM abilities from the definitions array.
	 *
	 * Each definition specifies the ability's metadata, schemas, and the names of
	 * the execute/permission callbacks on this class. This single loop is the only
	 * place wp_register_ability() is called, eliminating structural duplication.
	 *
	 * @since 9.1.0
	 * @return void
	 */
	public function register_abilities() {
		foreach ( $this->get_ability_definitions() as $name => $def ) {
			wp_register_ability(
				self::NAMESPACE . '/' . $name,
				array(
					'label'               => $def['label'],
					'description'         => $def['description'],
					'category'            => $def['category'],
					'input_schema'        => $def['input_schema'],
					'output_schema'       => $def['output_schema'],
					'execute_callback'    => array( $this, $def['execute'] ),
					'permission_callback' => array( $this, $def['permission'] ),
					'meta'                => $this->make_meta( $def['annotations'] ),
				)
			);
		}
	}

	// -------------------------------------------------------------------------
	// Ability definitions
	// -------------------------------------------------------------------------

	/**
	 * Returns the map of ability definitions.
	 *
	 * Keys are the ability slug (appended to the plugin namespace).
	 * Each value is an array with: label, description, category, input_schema,
	 * output_schema, execute (method name), permission (method name), annotations.
	 *
	 * @since 9.1.0
	 * @return array<string, array>
	 */
	private function get_ability_definitions() {
		$quiz_output = array(
			'type'       => 'object',
			'properties' => array(
				'quiz_id'       => array( 'type' => 'integer', 'description' => __( 'Unique quiz ID.', 'quiz-master-next' ) ),
				'quiz_name'     => array( 'type' => 'string',  'description' => __( 'Quiz title.', 'quiz-master-next' ) ),
				'quiz_taken'    => array( 'type' => 'integer', 'description' => __( 'Submission count.', 'quiz-master-next' ) ),
				'quiz_views'    => array( 'type' => 'integer', 'description' => __( 'View count.', 'quiz-master-next' ) ),
				'last_activity' => array( 'type' => 'string',  'description' => __( 'Date/time of last submission.', 'quiz-master-next' ) ),
			),
			'required'   => array( 'quiz_id', 'quiz_name' ),
		);

		$question_output = array(
			'type'       => 'object',
			'properties' => array(
				'question_id'   => array( 'type' => 'integer', 'description' => __( 'Unique question ID.', 'quiz-master-next' ) ),
				'quiz_id'       => array( 'type' => 'integer', 'description' => __( 'ID of the quiz this question belongs to.', 'quiz-master-next' ) ),
				'question_name' => array( 'type' => 'string',  'description' => __( 'Question text.', 'quiz-master-next' ) ),
				'question_type' => array( 'type' => 'string',  'description' => __( 'Type of question (e.g. 0=multiple choice, 1=true/false).', 'quiz-master-next' ) ),
				'answers'       => array( 'type' => 'array',   'description' => __( 'Array of answer options.', 'quiz-master-next' ) ),
				'settings'      => array( 'type' => 'object',  'description' => __( 'Question settings (required, category, points, etc.).', 'quiz-master-next' ) ),
			),
			'required'   => array( 'question_id', 'question_name' ),
		);

		$answers_input = array(
			'type'        => 'array',
			'description' => __( 'Each answer is an object with text, points, correct, and feedback fields.', 'quiz-master-next' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'text'     => array( 'type' => 'string' ),
					'points'   => array( 'type' => 'number' ),
					'correct'  => array( 'type' => 'integer' ),
					'feedback' => array( 'type' => 'string' ),
				),
			),
		);

		return array(
			// ----- Quizzes -----
			'list-quizzes'    => array(
				'label'        => __( 'List Quizzes', 'quiz-master-next' ),
				'description'  => __( 'Returns a list of all quizzes and surveys. Use this to discover available quizzes before fetching details or adding questions. Supports optional ordering and pagination.', 'quiz-master-next' ),
				'category'     => 'qsm-quizzes',
				'input_schema' => $this->make_input_schema( array(
					'order_by' => array( 'type' => 'string', 'enum' => array( 'quiz_id', 'title', 'last_activity', 'quiz_views', 'quiz_taken' ), 'default' => 'quiz_id', 'description' => __( 'Column to sort results by.', 'quiz-master-next' ) ),
					'order'    => array( 'type' => 'string', 'enum' => array( 'ASC', 'DESC' ), 'default' => 'DESC', 'description' => __( 'Sort direction.', 'quiz-master-next' ) ),
					'limit'    => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 200, 'description' => __( 'Maximum number of quizzes to return.', 'quiz-master-next' ) ),
					'offset'   => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Number of quizzes to skip for pagination.', 'quiz-master-next' ) ),
				) ),
				'output_schema' => array( 'type' => 'array', 'items' => $quiz_output ),
				'execute'      => 'execute_list_quizzes',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'idempotent' => true ),
			),
			'get-quiz'        => array(
				'label'        => __( 'Get Quiz', 'quiz-master-next' ),
				'description'  => __( 'Returns the full settings and metadata for a single quiz identified by its ID. Use list-quizzes first to obtain valid quiz IDs.', 'quiz-master-next' ),
				'category'     => 'qsm-quizzes',
				'input_schema' => $this->make_input_schema(
					array( 'quiz_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'The ID of the quiz to retrieve.', 'quiz-master-next' ) ) ),
					array( 'quiz_id' )
				),
				'output_schema' => array_merge( $quiz_output, array( 'properties' => array_merge( $quiz_output['properties'], array(
					'quiz_settings' => array( 'type' => 'object', 'description' => __( 'Serialised quiz settings array.', 'quiz-master-next' ) ),
				) ) ) ),
				'execute'      => 'execute_get_quiz',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'idempotent' => true ),
			),
			// ----- Questions -----
			'list-questions'  => array(
				'label'        => __( 'List Questions', 'quiz-master-next' ),
				'description'  => __( 'Returns all active questions for a given quiz. Use this to inspect existing questions before adding new ones.', 'quiz-master-next' ),
				'category'     => 'qsm-questions',
				'input_schema' => $this->make_input_schema(
					array( 'quiz_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz whose questions to retrieve.', 'quiz-master-next' ) ) ),
					array( 'quiz_id' )
				),
				'output_schema' => array( 'type' => 'array', 'items' => $question_output ),
				'execute'      => 'execute_list_questions',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'idempotent' => true ),
			),
			'create-question' => array(
				'label'        => __( 'Create Question', 'quiz-master-next' ),
				'description'  => __( 'Adds a new question to a quiz. Provide the quiz ID, question text, question type, and answer options. Returns the ID of the created question.', 'quiz-master-next' ),
				'category'     => 'qsm-questions',
				'input_schema' => $this->make_input_schema(
					array(
						'quiz_id'       => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz to add the question to.', 'quiz-master-next' ) ),
						'question_name' => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'The question text.', 'quiz-master-next' ) ),
						'question_type' => array( 'type' => 'string', 'default' => '0', 'description' => __( 'Question type: "0"=multiple choice, "1"=true/false, "8"=open-ended.', 'quiz-master-next' ) ),
						'answers'       => $answers_input,
						'settings'      => array( 'type' => 'object', 'description' => __( 'Optional question settings such as required, points, category.', 'quiz-master-next' ) ),
					),
					array( 'quiz_id', 'question_name' )
				),
				'output_schema' => $this->make_id_output_schema( 'question_id', __( 'ID of the newly created question.', 'quiz-master-next' ) ),
				'execute'      => 'execute_create_question',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'idempotent' => false ),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Execute callbacks
	// -------------------------------------------------------------------------

	/**
	 * Lists all quizzes.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array
	 */
	public function execute_list_quizzes( $input ) {
		global $mlwQuizMasterNext;

		$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(
			false,
			isset( $input['order_by'] ) ? sanitize_key( $input['order_by'] ) : 'quiz_id',
			isset( $input['order'] ) && 'ASC' === strtoupper( $input['order'] ) ? 'ASC' : 'DESC',
			array(),
			'',
			isset( $input['limit'] ) ? intval( $input['limit'] ) : '',
			isset( $input['offset'] ) ? intval( $input['offset'] ) : ''
		);

		$result = array();
		foreach ( (array) $quizzes as $quiz ) {
			$result[] = array(
				'quiz_id'       => intval( $quiz->quiz_id ),
				'quiz_name'     => $quiz->quiz_name,
				'quiz_taken'    => intval( $quiz->quiz_taken ),
				'quiz_views'    => intval( $quiz->quiz_views ),
				'last_activity' => $quiz->last_activity,
			);
		}

		return $result;
	}

	/**
	 * Gets a single quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_get_quiz( $input ) {
		global $wpdb;

		$quiz = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT quiz_id, quiz_name, quiz_taken, quiz_views, last_activity, quiz_settings FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d AND deleted = 0 LIMIT 1",
				intval( $input['quiz_id'] )
			),
			ARRAY_A
		);

		if ( empty( $quiz ) ) {
			return new WP_Error( 'qsm_quiz_not_found', __( 'Quiz not found.', 'quiz-master-next' ), array( 'status' => 404 ) );
		}

		$quiz['quiz_id']       = intval( $quiz['quiz_id'] );
		$quiz['quiz_taken']    = intval( $quiz['quiz_taken'] );
		$quiz['quiz_views']    = intval( $quiz['quiz_views'] );
		$settings              = maybe_unserialize( $quiz['quiz_settings'] );
		$quiz['quiz_settings'] = is_array( $settings ) ? $settings : array();

		return $quiz;
	}

	/**
	 * Lists questions for a quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array
	 */
	public function execute_list_questions( $input ) {
		return array_values( (array) QSM_Questions::load_questions( intval( $input['quiz_id'] ) ) );
	}

	/**
	 * Creates a new question.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_create_question( $input ) {
		$data = array(
			'quiz_id' => intval( $input['quiz_id'] ),
			'name'    => sanitize_text_field( $input['question_name'] ),
			'type'    => isset( $input['question_type'] ) ? sanitize_text_field( $input['question_type'] ) : '0',
		);

		$answers  = isset( $input['answers'] ) && is_array( $input['answers'] ) ? $this->normalize_answers( $input['answers'] ) : array();
		$settings = isset( $input['settings'] ) && is_array( $input['settings'] ) ? $input['settings'] : array();

		try {
			$question_id = QSM_Questions::create_question( $data, $answers, $settings );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_create_question_failed', $e->getMessage() );
		}

		return array( 'question_id' => intval( $question_id ) );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Builds the standard meta array for ability registration.
	 *
	 * @since  9.1.0
	 * @param  array $annotations Annotations to set (e.g. idempotent, destructive).
	 * @return array
	 */
	private function make_meta( array $annotations = array() ) {
		return array(
			'show_in_rest' => true,
			'annotations'  => $annotations,
		);
	}

	/**
	 * Wraps a properties map in the standard JSON Schema object envelope.
	 *
	 * @since  9.1.0
	 * @param  array $properties Schema properties array.
	 * @param  array $required   List of required property names.
	 * @return array JSON Schema object definition.
	 */
	private function make_input_schema( array $properties, array $required = array() ) {
		$schema = array(
			'type'       => 'object',
			'properties' => $properties,
		);

		if ( ! empty( $required ) ) {
			$schema['required'] = $required;
		}

		return $schema;
	}

	/**
	 * Builds an output schema that returns a single integer ID field.
	 *
	 * @since  9.1.0
	 * @param  string $field       The name of the ID field (e.g. 'question_id').
	 * @param  string $description Human-readable description for the field.
	 * @return array JSON Schema object definition.
	 */
	private function make_id_output_schema( $field, $description ) {
		return array(
			'type'       => 'object',
			'properties' => array(
				$field => array( 'type' => 'integer', 'description' => $description ),
			),
			'required'   => array( $field ),
		);
	}

	/**
	 * Normalizes answers from object format into QSM's internal array format.
	 *
	 * Accepts both [text, points, correct, feedback] arrays and
	 * {text, points, correct, feedback} objects (from the OpenAPI schema).
	 *
	 * @since  9.1.0
	 * @param  array $answers Raw answers from input.
	 * @return array Normalized answers in QSM array format.
	 */
	private function normalize_answers( array $answers ) {
		$normalized = array();
		foreach ( $answers as $answer ) {
			if ( isset( $answer['text'] ) ) {
				$normalized[] = array(
					$answer['text'],
					isset( $answer['points'] ) ? floatval( $answer['points'] ) : 0,
					isset( $answer['correct'] ) ? intval( $answer['correct'] ) : 0,
					isset( $answer['feedback'] ) ? $answer['feedback'] : '',
				);
			} else {
				$normalized[] = $answer;
			}
		}
		return $normalized;
	}

	// -------------------------------------------------------------------------
	// Permission callbacks
	// -------------------------------------------------------------------------

	/**
	 * Checks if the current user can edit quizzes.
	 *
	 * @since  9.1.0
	 * @return bool
	 */
	public function permission_edit_quizzes() {
		return current_user_can( 'edit_qsm_quizzes' );
	}
}
