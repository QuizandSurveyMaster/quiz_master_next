<?php
/**
 * WordPress Abilities API integration for Quiz and Survey Master.
 *
 * Registers QSM quiz, question, and result operations as discoverable abilities
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
class QSM_Abilities {

	/**
	 * Plugin namespace used for all ability names.
	 *
	 * @var string
	 */
	const NAMESPACE = 'quiz-master-next';

	/**
	 * Columns selected from the results table.
	 *
	 * @var string
	 */
	const RESULT_COLUMNS = 'result_id, quiz_id, correct, total, correct_score, point_score, time_taken, user_id, user_name';

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
				'description' => __( 'Abilities for creating, reading, updating, and deleting Quiz and Survey Master quizzes.', 'quiz-master-next' ),
			),
			'qsm-questions' => array(
				'label'       => __( 'QSM Questions', 'quiz-master-next' ),
				'description' => __( 'Abilities for managing questions within QSM quizzes.', 'quiz-master-next' ),
			),
			'qsm-results'   => array(
				'label'       => __( 'QSM Results', 'quiz-master-next' ),
				'description' => __( 'Abilities for reading quiz submission results in Quiz and Survey Master.', 'quiz-master-next' ),
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
	 * Returns the full map of ability definitions.
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

		$result_output = array(
			'type'       => 'object',
			'properties' => array(
				'result_id'     => array( 'type' => 'integer', 'description' => __( 'Unique result ID.', 'quiz-master-next' ) ),
				'quiz_id'       => array( 'type' => 'integer', 'description' => __( 'ID of the quiz this result belongs to.', 'quiz-master-next' ) ),
				'correct'       => array( 'type' => 'integer', 'description' => __( 'Number of correct answers.', 'quiz-master-next' ) ),
				'total'         => array( 'type' => 'integer', 'description' => __( 'Total number of questions answered.', 'quiz-master-next' ) ),
				'correct_score' => array( 'type' => 'number',  'description' => __( 'Percentage score (0–100).', 'quiz-master-next' ) ),
				'point_score'   => array( 'type' => 'number',  'description' => __( 'Point-based score.', 'quiz-master-next' ) ),
				'time_taken'    => array( 'type' => 'string',  'description' => __( 'Date/time the quiz was submitted (MySQL datetime).', 'quiz-master-next' ) ),
				'user_id'       => array( 'type' => 'integer', 'description' => __( 'WordPress user ID of the respondent (0 if anonymous).', 'quiz-master-next' ) ),
				'user_name'     => array( 'type' => 'string',  'description' => __( 'Name of the respondent.', 'quiz-master-next' ) ),
			),
			'required'   => array( 'result_id', 'quiz_id' ),
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
				'description'  => __( 'Returns a list of all quizzes and surveys. Use this to discover available quizzes before fetching details or results. Supports optional ordering and pagination.', 'quiz-master-next' ),
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
			'create-quiz'     => array(
				'label'        => __( 'Create Quiz', 'quiz-master-next' ),
				'description'  => __( 'Creates a new quiz or survey with the given name. Returns the ID of the newly created quiz. Use this when you need to build a new quiz programmatically.', 'quiz-master-next' ),
				'category'     => 'qsm-quizzes',
				'input_schema' => $this->make_input_schema(
					array(
						'quiz_name' => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'Title for the new quiz.', 'quiz-master-next' ) ),
						'theme_id'  => array( 'type' => 'integer', 'default' => 0, 'description' => __( 'Theme ID to apply to the new quiz. Defaults to 0 (primary theme).', 'quiz-master-next' ) ),
					),
					array( 'quiz_name' )
				),
				'output_schema' => $this->make_id_output_schema( 'quiz_id', __( 'ID of the newly created quiz.', 'quiz-master-next' ) ),
				'execute'      => 'execute_create_quiz',
				'permission'   => 'permission_create_quizzes',
				'annotations'  => array( 'idempotent' => false ),
			),
			'delete-quiz'     => array(
				'label'        => __( 'Delete Quiz', 'quiz-master-next' ),
				'description'  => __( 'Soft-deletes a quiz by its ID so it no longer appears in the quiz list. The quiz data is retained in the database and can be recovered by a site administrator. This is a destructive operation — confirm the quiz ID before proceeding.', 'quiz-master-next' ),
				'category'     => 'qsm-quizzes',
				'input_schema' => $this->make_input_schema(
					array(
						'quiz_id'   => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz to delete.', 'quiz-master-next' ) ),
						'quiz_name' => array( 'type' => 'string', 'description' => __( 'Name of the quiz. Used for audit logging only.', 'quiz-master-next' ) ),
					),
					array( 'quiz_id', 'quiz_name' )
				),
				'output_schema' => $this->make_success_output_schema( __( 'True if the quiz was deleted successfully.', 'quiz-master-next' ) ),
				'execute'      => 'execute_delete_quiz',
				'permission'   => 'permission_delete_quiz',
				'annotations'  => array( 'destructive' => true, 'idempotent' => false ),
			),
			'duplicate-quiz'  => array(
				'label'        => __( 'Duplicate Quiz', 'quiz-master-next' ),
				'description'  => __( 'Creates a full copy of an existing quiz. Optionally copies all questions as well. Returns the ID of the new duplicate quiz.', 'quiz-master-next' ),
				'category'     => 'qsm-quizzes',
				'input_schema' => $this->make_input_schema(
					array(
						'quiz_id'                  => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz to duplicate.', 'quiz-master-next' ) ),
						'quiz_name'                => array( 'type' => 'string', 'description' => __( 'Name for the duplicated quiz. Defaults to "Copy of <original name>".', 'quiz-master-next' ) ),
						'is_duplicating_questions' => array( 'type' => 'boolean', 'default' => true, 'description' => __( 'Whether to copy the questions into the new quiz.', 'quiz-master-next' ) ),
					),
					array( 'quiz_id', 'quiz_name' )
				),
				'output_schema' => $this->make_id_output_schema( 'quiz_id', __( 'ID of the newly duplicated quiz.', 'quiz-master-next' ) ),
				'execute'      => 'execute_duplicate_quiz',
				'permission'   => 'permission_create_quizzes',
				'annotations'  => array( 'idempotent' => false ),
			),
			// ----- Questions -----
			'list-questions'  => array(
				'label'        => __( 'List Questions', 'quiz-master-next' ),
				'description'  => __( 'Returns all active questions for a given quiz. Use this to inspect the questions in a quiz before creating or updating them.', 'quiz-master-next' ),
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
			'get-question'    => array(
				'label'        => __( 'Get Question', 'quiz-master-next' ),
				'description'  => __( 'Returns the full data for a single question by its ID, including answers and settings.', 'quiz-master-next' ),
				'category'     => 'qsm-questions',
				'input_schema' => $this->make_input_schema(
					array( 'question_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'The ID of the question to retrieve.', 'quiz-master-next' ) ) ),
					array( 'question_id' )
				),
				'output_schema' => $question_output,
				'execute'      => 'execute_get_question',
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
			'update-question' => array(
				'label'        => __( 'Update Question', 'quiz-master-next' ),
				'description'  => __( 'Updates an existing question\'s text, answers, or settings. Only the fields provided will be updated. Returns the question ID on success.', 'quiz-master-next' ),
				'category'     => 'qsm-questions',
				'input_schema' => $this->make_input_schema(
					array(
						'question_id'   => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the question to update.', 'quiz-master-next' ) ),
						'question_name' => array( 'type' => 'string', 'description' => __( 'New question text.', 'quiz-master-next' ) ),
						'question_type' => array( 'type' => 'string', 'description' => __( 'New question type identifier.', 'quiz-master-next' ) ),
						'answers'       => $answers_input,
						'settings'      => array( 'type' => 'object', 'description' => __( 'New settings (replaces existing settings).', 'quiz-master-next' ) ),
					),
					array( 'question_id' )
				),
				'output_schema' => $this->make_id_output_schema( 'question_id', __( 'ID of the updated question.', 'quiz-master-next' ) ),
				'execute'      => 'execute_update_question',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'idempotent' => false ),
			),
			'delete-question' => array(
				'label'        => __( 'Delete Question', 'quiz-master-next' ),
				'description'  => __( 'Soft-deletes a question by its ID. The question will no longer appear in the quiz but the data is retained. This is a destructive operation — confirm the question ID before proceeding.', 'quiz-master-next' ),
				'category'     => 'qsm-questions',
				'input_schema' => $this->make_input_schema(
					array( 'question_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the question to delete.', 'quiz-master-next' ) ) ),
					array( 'question_id' )
				),
				'output_schema' => $this->make_success_output_schema( __( 'True if the question was deleted successfully.', 'quiz-master-next' ) ),
				'execute'      => 'execute_delete_question',
				'permission'   => 'permission_edit_quizzes',
				'annotations'  => array( 'destructive' => true, 'idempotent' => false ),
			),
			// ----- Results -----
			'list-results'    => array(
				'label'        => __( 'List Quiz Results', 'quiz-master-next' ),
				'description'  => __( 'Returns up to 40 recent submission results for a given quiz. Each result includes score, time taken, and respondent information.', 'quiz-master-next' ),
				'category'     => 'qsm-results',
				'input_schema' => $this->make_input_schema(
					array(
						'quiz_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz whose results to retrieve.', 'quiz-master-next' ) ),
						'limit'   => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 40, 'description' => __( 'Maximum number of results to return.', 'quiz-master-next' ) ),
						'offset'  => array( 'type' => 'integer', 'minimum' => 0, 'default' => 0, 'description' => __( 'Number of results to skip for pagination.', 'quiz-master-next' ) ),
					),
					array( 'quiz_id' )
				),
				'output_schema' => array( 'type' => 'array', 'items' => $result_output ),
				'execute'      => 'execute_list_results',
				'permission'   => 'permission_view_results',
				'annotations'  => array( 'idempotent' => true ),
			),
			'get-result'      => array(
				'label'        => __( 'Get Quiz Result', 'quiz-master-next' ),
				'description'  => __( 'Returns the full data for a single quiz submission by its result ID, including score breakdown and respondent details.', 'quiz-master-next' ),
				'category'     => 'qsm-results',
				'input_schema' => $this->make_input_schema(
					array( 'result_id' => array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'The ID of the result to retrieve.', 'quiz-master-next' ) ) ),
					array( 'result_id' )
				),
				'output_schema' => $result_output,
				'execute'      => 'execute_get_result',
				'permission'   => 'permission_view_results',
				'annotations'  => array( 'idempotent' => true ),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Quiz
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
	 * Creates a new quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_create_quiz( $input ) {
		global $mlwQuizMasterNext;

		$mlwQuizMasterNext->quizCreator->create_quiz(
			sanitize_text_field( $input['quiz_name'] ),
			isset( $input['theme_id'] ) ? intval( $input['theme_id'] ) : 0
		);

		$quiz_id = $mlwQuizMasterNext->quizCreator->get_id();
		if ( ! $quiz_id ) {
			return new WP_Error( 'qsm_create_quiz_failed', __( 'Failed to create quiz.', 'quiz-master-next' ) );
		}

		return array( 'quiz_id' => intval( $quiz_id ) );
	}

	/**
	 * Deletes a quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array
	 */
	public function execute_delete_quiz( $input ) {
		global $mlwQuizMasterNext;

		$mlwQuizMasterNext->quizCreator->delete_quiz(
			intval( $input['quiz_id'] ),
			sanitize_text_field( $input['quiz_name'] )
		);

		return array( 'success' => true );
	}

	/**
	 * Duplicates a quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_duplicate_quiz( $input ) {
		global $mlwQuizMasterNext;

		$mlwQuizMasterNext->quizCreator->duplicate_quiz(
			intval( $input['quiz_id'] ),
			sanitize_text_field( $input['quiz_name'] ),
			isset( $input['is_duplicating_questions'] ) ? (bool) $input['is_duplicating_questions'] : true
		);

		$new_quiz_id = $mlwQuizMasterNext->quizCreator->get_id();
		if ( ! $new_quiz_id ) {
			return new WP_Error( 'qsm_duplicate_quiz_failed', __( 'Failed to duplicate quiz.', 'quiz-master-next' ) );
		}

		return array( 'quiz_id' => intval( $new_quiz_id ) );
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Questions
	// -------------------------------------------------------------------------

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
	 * Gets a single question.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_get_question( $input ) {
		$question = QSM_Questions::load_question( intval( $input['question_id'] ) );

		if ( empty( $question ) ) {
			return new WP_Error( 'qsm_question_not_found', __( 'Question not found.', 'quiz-master-next' ), array( 'status' => 404 ) );
		}

		return $question;
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

	/**
	 * Updates an existing question.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_update_question( $input ) {
		$question_id = intval( $input['question_id'] );
		$existing    = QSM_Questions::load_question( $question_id );

		if ( empty( $existing ) ) {
			return new WP_Error( 'qsm_question_not_found', __( 'Question not found.', 'quiz-master-next' ), array( 'status' => 404 ) );
		}

		$data = array();
		if ( isset( $input['question_name'] ) ) {
			$data['name'] = sanitize_text_field( $input['question_name'] );
		}
		if ( isset( $input['question_type'] ) ) {
			$data['type'] = sanitize_text_field( $input['question_type'] );
		}

		$answers  = isset( $input['answers'] ) && is_array( $input['answers'] ) ? $this->normalize_answers( $input['answers'] ) : $existing['answers'];
		$settings = isset( $input['settings'] ) && is_array( $input['settings'] ) ? $input['settings'] : $existing['settings'];

		try {
			$saved_id = QSM_Questions::save_question( $question_id, $data, $answers, $settings );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_update_question_failed', $e->getMessage() );
		}

		return array( 'question_id' => intval( $saved_id ) );
	}

	/**
	 * Deletes a question.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_delete_question( $input ) {
		try {
			QSM_Questions::delete_question( intval( $input['question_id'] ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_delete_question_failed', $e->getMessage() );
		}

		return array( 'success' => true );
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Results
	// -------------------------------------------------------------------------

	/**
	 * Lists results for a quiz.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_list_results( $input ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT " . self::RESULT_COLUMNS . "
				 FROM {$wpdb->prefix}mlw_results
				 WHERE deleted = '0' AND quiz_id = %d
				 ORDER BY result_id DESC
				 LIMIT %d OFFSET %d",
				intval( $input['quiz_id'] ),
				isset( $input['limit'] ) ? intval( $input['limit'] ) : 40,
				isset( $input['offset'] ) ? intval( $input['offset'] ) : 0
			),
			ARRAY_A
		);

		if ( null === $rows ) {
			return new WP_Error( 'qsm_db_error', __( 'Database error while retrieving results.', 'quiz-master-next' ) );
		}

		return array_map( array( $this, 'cast_result_row' ), $rows );
	}

	/**
	 * Gets a single result.
	 *
	 * @since  9.1.0
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_get_result( $input ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT " . self::RESULT_COLUMNS . "
				 FROM {$wpdb->prefix}mlw_results
				 WHERE result_id = %d AND deleted = '0' LIMIT 1",
				intval( $input['result_id'] )
			),
			ARRAY_A
		);

		if ( empty( $row ) ) {
			return new WP_Error( 'qsm_result_not_found', __( 'Result not found.', 'quiz-master-next' ), array( 'status' => 404 ) );
		}

		return $this->cast_result_row( $row );
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
	 * @param  string $field       The name of the ID field (e.g. 'quiz_id').
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
	 * Builds an output schema that returns a boolean success flag.
	 *
	 * @since  9.1.0
	 * @param  string $description Human-readable description for the success field.
	 * @return array JSON Schema object definition.
	 */
	private function make_success_output_schema( $description ) {
		return array(
			'type'       => 'object',
			'properties' => array(
				'success' => array( 'type' => 'boolean', 'description' => $description ),
			),
			'required'   => array( 'success' ),
		);
	}

	/**
	 * Casts numeric string fields in a result row to their correct types.
	 *
	 * @since  9.1.0
	 * @param  array $row Raw DB row.
	 * @return array
	 */
	private function cast_result_row( array $row ) {
		$row['result_id']     = intval( $row['result_id'] );
		$row['quiz_id']       = intval( $row['quiz_id'] );
		$row['correct']       = intval( $row['correct'] );
		$row['total']         = intval( $row['total'] );
		$row['correct_score'] = floatval( $row['correct_score'] );
		$row['point_score']   = floatval( $row['point_score'] );
		$row['user_id']       = intval( $row['user_id'] );
		return $row;
	}

	/**
	 * Normalizes answers from object or array format into QSM's internal array format.
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

	/**
	 * Checks if the current user can create quizzes.
	 *
	 * @since  9.1.0
	 * @return bool
	 */
	public function permission_create_quizzes() {
		return current_user_can( 'create_qsm_quizzes' );
	}

	/**
	 * Checks if the current user can delete quizzes.
	 *
	 * @since  9.1.0
	 * @return bool
	 */
	public function permission_delete_quiz() {
		return current_user_can( 'delete_qsm_quiz' );
	}

	/**
	 * Checks if the current user can view quiz results.
	 *
	 * @since  9.1.0
	 * @return bool
	 */
	public function permission_view_results() {
		return current_user_can( 'view_qsm_quiz_result' );
	}
}
