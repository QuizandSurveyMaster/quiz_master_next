<?php
/**
 * WordPress Abilities API integration for Quiz and Survey Master.
 *
 * Registers QSM quiz, question, and result operations as discoverable abilities
 * so AI agents and automation tools can interact with QSM programmatically.
 *
 * Requires WordPress 6.9+ (Abilities API) or the Abilities API feature plugin.
 * Registration happens inside the Abilities API's own init hooks, so the plugin
 * stays compatible with older WordPress.
 *
 * @package QSM
 * @since   11.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers QSM categories and abilities with the WordPress Abilities API.
 *
 * @since 11.2.5
 */
class QSM_Abilities {

	/**
	 * Plugin namespace used for all ability names.
	 *
	 * @var string
	 */
	const NAMESPACE = 'quiz-master-next';

	/**
	 * Default and maximum page sizes for list abilities.
	 */
	const DEFAULT_LIMIT = 50;
	const MAX_LIMIT     = 200;

	/**
	 * Hooks into the WordPress Abilities API init actions.
	 *
	 * The hooks are added unconditionally. On a site that gets the Abilities API
	 * from the feature plugin, its functions may not exist yet when QSM loads, and
	 * checking for them here skipped registration with no notice. The callbacks
	 * check again before registering anything.
	 *
	 * @since 11.2.5
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Registers the QSM ability categories.
	 *
	 * Developers can add or modify categories using the 'qsm_ability_categories' filter.
	 *
	 * @since 11.2.5
	 * @return void
	 */
	public function register_categories() {
		// Abilities API is WP 6.9+; bail on older cores (also satisfies the compat check).
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}
		$categories = array(
			'qsm-quizzes'   => array(
				'label'       => __( 'QSM Quizzes', 'quiz-master-next' ),
				'description' => __( 'Abilities for creating, reading and configuring Quiz and Survey Master quizzes.', 'quiz-master-next' ),
			),
			'qsm-questions' => array(
				'label'       => __( 'QSM Questions', 'quiz-master-next' ),
				'description' => __( 'Abilities for managing questions within QSM quizzes.', 'quiz-master-next' ),
			),
			'qsm-results'   => array(
				'label'       => __( 'QSM Results', 'quiz-master-next' ),
				'description' => __( 'Abilities for reading the submissions a QSM quiz has received.', 'quiz-master-next' ),
			),
		);

		/**
		 * Filter the ability categories before registration.
		 *
		 * @since 11.2.5
		 * @param array $categories Array of category definitions keyed by slug.
		 */
		$categories = apply_filters( 'qsm_ability_categories', $categories );

		foreach ( $categories as $slug => $args ) {
			wp_register_ability_category( $slug, $args );
		}
	}

	/**
	 * Registers all QSM abilities from the definitions array.
	 *
	 * Developers can add or modify abilities using the 'qsm_ability_definitions' filter.
	 * Each ability automatically gets wrapped with hooks for extensibility.
	 *
	 * @since 11.2.5
	 * @return void
	 */
	public function register_abilities() {
		// Abilities API is WP 6.9+; bail on older cores (also satisfies the compat check).
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}
		/**
		 * Filter the ability definitions before registration.
		 *
		 * This allows developers to:
		 * - Add new custom abilities
		 * - Modify existing ability definitions
		 * - Remove abilities by unsetting them
		 *
		 * @since 11.2.5
		 * @param array $definitions Array of ability definitions keyed by ability name.
		 */
		$definitions = apply_filters( 'qsm_ability_definitions', $this->get_ability_definitions() );

		foreach ( $definitions as $name => $def ) {
			$ability_name      = $name;
			$permission_method = $def['permission'];
			$execute_method    = $def['execute'];
			$instance          = $this;

			wp_register_ability(
				self::NAMESPACE . '/' . $name,
				array(
					'label'               => $def['label'],
					'description'         => $def['description'],
					'category'            => $def['category'],
					'input_schema'        => $def['input_schema'],
					'output_schema'       => $def['output_schema'],
					'execute_callback'    => function( $input = null ) use ( $instance, $ability_name, $execute_method ) {
						$input = is_array( $input ) ? $input : array();

						/**
						 * Filter the input before ability execution.
						 *
						 * @since 11.2.5
						 * @param array  $input        The input data.
						 * @param string $ability_name The ability name (e.g., 'list-quizzes').
						 */
						$input = apply_filters( 'qsm_ability_input', $input, $ability_name );
						$input = apply_filters( "qsm_ability_input_{$ability_name}", $input );

						/**
						 * Action fired before ability execution.
						 *
						 * @since 11.2.5
						 * @param array  $input        The input data.
						 * @param string $ability_name The ability name.
						 */
						do_action( 'qsm_before_ability_execute', $input, $ability_name );
						do_action( "qsm_before_ability_execute_{$ability_name}", $input );

						// Execute the ability method
						if ( method_exists( $instance, $execute_method ) ) {
							$result = call_user_func( array( $instance, $execute_method ), $input );
						} else {
							$result = new WP_Error( 'qsm_invalid_method', __( 'Invalid ability execution method.', 'quiz-master-next' ) );
						}

						/**
						 * Action fired after ability execution.
						 *
						 * @since 11.2.5
						 * @param mixed  $result       The execution result.
						 * @param array  $input        The input data.
						 * @param string $ability_name The ability name.
						 */
						do_action( 'qsm_after_ability_execute', $result, $input, $ability_name );
						do_action( "qsm_after_ability_execute_{$ability_name}", $result, $input );

						/**
						 * Filter the output after ability execution.
						 *
						 * @since 11.2.5
						 * @param mixed  $result       The execution result.
						 * @param array  $input        The input data.
						 * @param string $ability_name The ability name.
						 */
						$result = apply_filters( 'qsm_ability_output', $result, $input, $ability_name );
						$result = apply_filters( "qsm_ability_output_{$ability_name}", $result, $input );

						return $result;
					},
					'permission_callback' => function( $input = null ) use ( $instance, $ability_name, $permission_method ) {
						$input = is_array( $input ) ? $input : array();

						// The input is passed so per-quiz ownership can be checked, not only the flat capability.
						if ( method_exists( $instance, $permission_method ) ) {
							$has_permission = call_user_func( array( $instance, $permission_method ), $input );
						} else {
							$has_permission = false;
						}

						/**
						 * Filter the permission check result.
						 *
						 * @since 11.2.5
						 * @since 11.3.1 Added the $input parameter.
						 * @param bool   $has_permission Whether the user has permission.
						 * @param string $ability_name   The ability name.
						 * @param array  $input          The input data.
						 */
						$has_permission = apply_filters( 'qsm_ability_permission', $has_permission, $ability_name, $input );
						$has_permission = apply_filters( "qsm_ability_permission_{$ability_name}", $has_permission, $input );

						return $has_permission;
					},
					'meta'                => $instance->make_meta( $def['annotations'], $ability_name ),
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
	 * @since 11.2.5
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

		$placement_output = array(
			'type'        => 'object',
			'description' => __( 'How to put the quiz on a page: paste the shortcode, or insert the block markup into post content.', 'quiz-master-next' ),
			'properties'  => array(
				'shortcode'   => array( 'type' => 'string', 'description' => __( 'Shortcode that renders the quiz, e.g. [qsm quiz=12].', 'quiz-master-next' ) ),
				'block'       => array( 'type' => 'string', 'description' => __( 'Serialized qsm/quiz block for the block editor.', 'quiz-master-next' ) ),
				'post_id'     => array( 'type' => 'integer', 'description' => __( 'ID of the quiz\'s own qsm_quiz post (0 if none).', 'quiz-master-next' ) ),
				'post_status' => array( 'type' => 'string', 'description' => __( 'Status of the quiz\'s own post. A draft quiz still renders through the shortcode or block but does NOT record responses; it must be "publish" to collect results.', 'quiz-master-next' ) ),
				'permalink'   => array( 'type' => 'string', 'description' => __( 'Public URL of the quiz\'s own post, empty until it is published.', 'quiz-master-next' ) ),
				'edit_url'    => array( 'type' => 'string', 'description' => __( 'wp-admin URL of the quiz editor.', 'quiz-master-next' ) ),
			),
		);

		$question_output = array(
			'type'       => 'object',
			'properties' => array(
				'question_id'       => array( 'type' => 'integer', 'description' => __( 'Unique question ID.', 'quiz-master-next' ) ),
				'quiz_id'           => array( 'type' => 'integer', 'description' => __( 'ID of the quiz this question belongs to.', 'quiz-master-next' ) ),
				'question_name'     => array( 'type' => 'string',  'description' => __( 'Question text.', 'quiz-master-next' ) ),
				'question_type_new' => array( 'type' => 'string',  'description' => __( 'Question type code. See create-question for the list of codes.', 'quiz-master-next' ) ),
				'answers'           => array( 'type' => 'array',   'description' => __( 'Answer options, each [text, points, correct (1/0), feedback].', 'quiz-master-next' ) ),
				'settings'          => array( 'type' => 'object',  'description' => __( 'Question settings (required, category, points, etc.).', 'quiz-master-next' ) ),
				'page'              => array( 'type' => 'integer', 'description' => __( 'Zero-based page of the quiz the question appears on.', 'quiz-master-next' ) ),
			),
			'required'   => array( 'question_id', 'question_name' ),
		);

		$answers_input = array(
			'type'        => 'array',
			'description' => __( 'Answer options. Each answer is an object with text, points, correct (1 for a correct answer, 0 otherwise) and feedback. Open-ended types (Short Answer, Paragraph, Number) use the first answer as the expected value.', 'quiz-master-next' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'text'     => array( 'type' => 'string' ),
					'points'   => array( 'type' => 'number' ),
					'correct'  => array( 'type' => 'integer', 'enum' => array( 0, 1 ) ),
					'feedback' => array( 'type' => 'string' ),
				),
				'required'   => array( 'text' ),
			),
		);

		$question_settings_input = array(
			'type'        => 'object',
			'description' => __( 'Optional question settings, e.g. {"required": 1}. Scalar values only.', 'quiz-master-next' ),
		);

		$quiz_id_input = array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the quiz. Use list-quizzes to find it.', 'quiz-master-next' ) );

		$question_id_input = array( 'type' => 'integer', 'minimum' => 1, 'description' => __( 'ID of the question. Use list-questions to find it.', 'quiz-master-next' ) );

		$pass_score_input = array( 'type' => 'number', 'minimum' => 0, 'maximum' => 100, 'description' => __( 'Optional pass mark as a percentage of correct answers. QSM has no built-in pass mark, so pass/fail is only computed when this is given.', 'quiz-master-next' ) );

		$date_inputs = array(
			'date_from' => array( 'type' => 'string', 'format' => 'date', 'description' => __( 'Only submissions on or after this date (YYYY-MM-DD, site time).', 'quiz-master-next' ) ),
			'date_to'   => array( 'type' => 'string', 'format' => 'date', 'description' => __( 'Only submissions on or before this date (YYYY-MM-DD, site time).', 'quiz-master-next' ) ),
		);

		$quiz_settings_properties = array(
			'form_type'           => array( 'type' => 'string', 'enum' => array_keys( $this->form_type_map() ), 'description' => __( 'quiz = graded quiz, survey = ungraded survey, simple_form = ungraded form.', 'quiz-master-next' ) ),
			'grading_system'      => array( 'type' => 'string', 'enum' => array_keys( $this->grading_system_map() ), 'description' => __( 'How a quiz is scored: correct = correct/incorrect answers, points = answer points, both = both.', 'quiz-master-next' ) ),
			'timer_limit'         => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Time limit in minutes. 0 = no limit.', 'quiz-master-next' ) ),
			'pagination'          => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Questions per page. 0 = use the quiz\'s page layout.', 'quiz-master-next' ) ),
			'require_log_in'      => array( 'type' => 'boolean', 'description' => __( 'Only logged-in users can take the quiz.', 'quiz-master-next' ) ),
			'progress_bar'        => array( 'type' => 'boolean', 'description' => __( 'Show a progress bar.', 'quiz-master-next' ) ),
			'total_user_tries'    => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Attempts allowed per user. 0 = unlimited.', 'quiz-master-next' ) ),
			'limit_total_entries' => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Total submissions the quiz accepts. 0 = unlimited.', 'quiz-master-next' ) ),
		);

		return array(
			// ----- Quizzes -----
			'list-quizzes'         => array(
				'label'         => __( 'List Quizzes', 'quiz-master-next' ),
				'description'   => __( 'Returns the quizzes and surveys the current user can edit. Use this to discover quiz IDs before calling get-quiz, list-questions or get-results-summary. Supports optional ordering and pagination.', 'quiz-master-next' ),
				'category'      => 'qsm-quizzes',
				'input_schema'  => $this->make_input_schema( array(
					'order_by' => array( 'type' => 'string', 'enum' => array( 'quiz_id', 'title', 'last_activity', 'quiz_views', 'quiz_taken' ), 'default' => 'quiz_id', 'description' => __( 'Column to sort results by.', 'quiz-master-next' ) ),
					'order'    => array( 'type' => 'string', 'enum' => array( 'ASC', 'DESC' ), 'default' => 'DESC', 'description' => __( 'Sort direction.', 'quiz-master-next' ) ),
					'limit'    => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => self::MAX_LIMIT, 'description' => __( 'Maximum number of quizzes to return.', 'quiz-master-next' ) ),
					'offset'   => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Number of quizzes to skip for pagination.', 'quiz-master-next' ) ),
				) ),
				'output_schema' => array( 'type' => 'array', 'items' => $quiz_output ),
				'execute'       => 'execute_list_quizzes',
				'permission'    => 'permission_edit_quizzes',
				'annotations'   => $this->read_annotations(),
			),
			'get-quiz'             => array(
				'label'         => __( 'Get Quiz', 'quiz-master-next' ),
				'description'   => __( 'Returns the settings, metadata and placement (shortcode and block) for a single quiz. Use list-quizzes first to obtain valid quiz IDs.', 'quiz-master-next' ),
				'category'      => 'qsm-quizzes',
				'input_schema'  => $this->make_input_schema( array( 'quiz_id' => $quiz_id_input ), array( 'quiz_id' ) ),
				'output_schema' => array_merge( $quiz_output, array( 'properties' => array_merge( $quiz_output['properties'], array(
					'quiz_settings' => array( 'type' => 'object', 'description' => __( 'Quiz settings as an object, already unserialised, keyed by section. quiz_options holds the main options (form_type: 0 quiz, 1 survey, 2 simple form; system: 0 correct, 1 points, 3 both; timer_limit; pagination; require_log_in; total_user_tries; limit_total_entries). Other keys include quiz_text, contact_form and pages (the question IDs on each page).', 'quiz-master-next' ) ),
					'placement'     => $placement_output,
				) ) ) ),
				'execute'       => 'execute_get_quiz',
				'permission'    => 'permission_edit_quiz',
				'annotations'   => $this->read_annotations(),
			),
			'create-quiz'          => array(
				'label'         => __( 'Create Quiz', 'quiz-master-next' ),
				'description'   => __( 'Creates a new quiz or survey with QSM\'s default settings, published so it records responses, and returns its ID with the shortcode and block to place it on a page. Add questions next with create-question.', 'quiz-master-next' ),
				'category'      => 'qsm-quizzes',
				'input_schema'  => $this->make_input_schema(
					array_merge(
						array(
							'quiz_name'           => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'Name of the quiz or survey.', 'quiz-master-next' ) ),
							'collect_contact'     => array( 'type' => 'boolean', 'default' => false, 'description' => __( 'Ask takers for their name and email.', 'quiz-master-next' ) ),
							'publish'             => array( 'type' => 'boolean', 'default' => true, 'description' => __( 'Publish the quiz (default true). QSM does not record responses for a draft quiz, even when it is placed on a page. Pass false to keep it a draft while you build it.', 'quiz-master-next' ) ),
						),
						$quiz_settings_properties
					),
					array( 'quiz_name' )
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'quiz_id'   => array( 'type' => 'integer', 'description' => __( 'ID of the new quiz.', 'quiz-master-next' ) ),
						'placement' => $placement_output,
						'warnings'  => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'Problems that did not stop the quiz being created, e.g. it could not be published.', 'quiz-master-next' ) ),
					),
					'required'   => array( 'quiz_id' ),
				),
				'execute'       => 'execute_create_quiz',
				'permission'    => 'permission_create_quizzes',
				'annotations'   => $this->write_annotations( false, false ),
			),
			'update-quiz-settings' => array(
				'label'         => __( 'Update Quiz Settings', 'quiz-master-next' ),
				'description'   => __( 'Changes common settings of a quiz: name, type, grading, time limit, attempts, login requirement and the default results page message. Only the fields given are changed.', 'quiz-master-next' ),
				'category'      => 'qsm-quizzes',
				'input_schema'  => $this->make_input_schema(
					array_merge(
						array(
							'quiz_id'         => $quiz_id_input,
							'quiz_name'       => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'New name of the quiz.', 'quiz-master-next' ) ),
							'publish'         => array( 'type' => 'boolean', 'description' => __( 'true publishes the quiz so it records responses; false returns it to draft.', 'quiz-master-next' ) ),
							'results_message' => array( 'type' => 'string', 'description' => __( 'HTML shown on the default results page (the one with no conditions). Supports QSM template variables such as %CORRECT_SCORE% and %QUESTIONS_ANSWERS%.', 'quiz-master-next' ) ),
						),
						$quiz_settings_properties
					),
					array( 'quiz_id' )
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'quiz_id'  => array( 'type' => 'integer' ),
						'updated'  => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'Names of the fields that were changed.', 'quiz-master-next' ) ),
						'settings' => array( 'type' => 'object', 'description' => __( 'Current values of the typed settings.', 'quiz-master-next' ) ),
					),
					'required'   => array( 'quiz_id', 'updated' ),
				),
				'execute'       => 'execute_update_quiz_settings',
				'permission'    => 'permission_edit_quiz',
				'annotations'   => $this->write_annotations( false, true ),
			),

			// ----- Questions -----
			'list-questions'       => array(
				'label'         => __( 'List Questions', 'quiz-master-next' ),
				'description'   => __( 'Returns the published questions shown on a quiz, with the page each is on. Use this to inspect a quiz before creating, updating or deleting questions.', 'quiz-master-next' ),
				'category'      => 'qsm-questions',
				'input_schema'  => $this->make_input_schema( array( 'quiz_id' => $quiz_id_input ), array( 'quiz_id' ) ),
				'output_schema' => array( 'type' => 'array', 'items' => $question_output ),
				'execute'       => 'execute_list_questions',
				'permission'    => 'permission_edit_quiz',
				'annotations'   => $this->read_annotations(),
			),
			'create-question'      => array(
				'label'         => __( 'Create Question', 'quiz-master-next' ),
				'description'   => __( 'Adds a new question to a quiz and places it on the quiz\'s last page (or the page given). Provide the quiz ID, question text, question type, and answer options. Returns the ID of the created question.', 'quiz-master-next' ),
				'category'      => 'qsm-questions',
				'input_schema'  => $this->make_input_schema(
					array(
						'quiz_id'       => $quiz_id_input,
						'question_name' => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'The question text.', 'quiz-master-next' ) ),
						'question_type' => $this->get_question_type_schema( true ),
						'question_title' => array( 'type' => 'string', 'description' => __( 'Optional heading shown above the question on the quiz and in results. The question text goes in question_name.', 'quiz-master-next' ) ),
						'answers'       => $answers_input,
						'settings'      => $question_settings_input,
						'page'          => array( 'type' => 'integer', 'minimum' => 0, 'description' => __( 'Zero-based page to add the question to. Defaults to the last page. Ignored while the quiz has no page layout yet (a new quiz): its questions then show as one page in creation order.', 'quiz-master-next' ) ),
					),
					array( 'quiz_id', 'question_name' )
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'question_id' => array( 'type' => 'integer', 'description' => __( 'ID of the newly created question.', 'quiz-master-next' ) ),
						'page'        => array( 'type' => 'integer', 'description' => __( 'Zero-based page the question was added to.', 'quiz-master-next' ) ),
					),
					'required'   => array( 'question_id' ),
				),
				'execute'       => 'execute_create_question',
				'permission'    => 'permission_edit_quiz',
				'annotations'   => $this->write_annotations( false, false ),
			),
			'update-question'      => array(
				'label'         => __( 'Update Question', 'quiz-master-next' ),
				'description'   => __( 'Changes an existing question. Only the fields given are changed; answers, when given, replace all existing answers. Settings are merged into the existing settings.', 'quiz-master-next' ),
				'category'      => 'qsm-questions',
				'input_schema'  => $this->make_input_schema(
					array(
						'question_id'   => $question_id_input,
						'question_name' => array( 'type' => 'string', 'minLength' => 1, 'description' => __( 'The question text.', 'quiz-master-next' ) ),
						'question_type' => $this->get_question_type_schema( false ),
						'question_title' => array( 'type' => 'string', 'description' => __( 'Optional heading shown above the question on the quiz and in results. The question text goes in question_name.', 'quiz-master-next' ) ),
						'answers'       => $answers_input,
						'answer_info'   => array( 'type' => 'string', 'description' => __( 'Correct answer explanation shown after answering.', 'quiz-master-next' ) ),
						'hint'          => array( 'type' => 'string', 'description' => __( 'Hint shown with the question.', 'quiz-master-next' ) ),
						'settings'      => $question_settings_input,
					),
					array( 'question_id' )
				),
				'output_schema' => $question_output,
				'execute'       => 'execute_update_question',
				'permission'    => 'permission_edit_question',
				'annotations'   => $this->write_annotations( false, true ),
			),
			'delete-question'      => array(
				'label'         => __( 'Delete Question', 'quiz-master-next' ),
				'description'   => __( 'Deletes a question and removes it from its quiz\'s pages. Results already recorded keep their answers.', 'quiz-master-next' ),
				'category'      => 'qsm-questions',
				'input_schema'  => $this->make_input_schema( array( 'question_id' => $question_id_input ), array( 'question_id' ) ),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'question_id' => array( 'type' => 'integer' ),
						'deleted'     => array( 'type' => 'boolean' ),
					),
					'required'   => array( 'question_id', 'deleted' ),
				),
				'execute'       => 'execute_delete_question',
				'permission'    => 'permission_edit_question',
				'annotations'   => $this->write_annotations( true, true ),
			),

			// ----- Results -----
			'list-results'         => array(
				'label'         => __( 'List Results', 'quiz-master-next' ),
				'description'   => __( 'Returns the submissions for a quiz, newest first: score, pass/fail (when pass_score is given), date, and the taker\'s name and email when the quiz collected them. Filter by score with min_score/max_score, or by outcome with status plus pass_score. For totals and averages use get-results-summary instead.', 'quiz-master-next' ),
				'category'      => 'qsm-results',
				'input_schema'  => $this->make_input_schema(
					array_merge(
						array( 'quiz_id' => $quiz_id_input ),
						$date_inputs,
						array(
							'pass_score' => $pass_score_input,
							'min_score'  => array( 'type' => 'number', 'minimum' => 0, 'maximum' => 100, 'description' => __( 'Only graded submissions scoring at least this percentage of correct answers.', 'quiz-master-next' ) ),
							'max_score'  => array( 'type' => 'number', 'minimum' => 0, 'maximum' => 100, 'description' => __( 'Only graded submissions scoring at most this percentage of correct answers.', 'quiz-master-next' ) ),
							'status'     => array( 'type' => 'string', 'enum' => array( 'all', 'passed', 'failed' ), 'default' => 'all', 'description' => __( 'passed = only submissions at or above pass_score, failed = only those below it. Requires pass_score.', 'quiz-master-next' ) ),
							'limit'      => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => self::MAX_LIMIT, 'default' => self::DEFAULT_LIMIT, 'description' => __( 'Maximum number of results to return.', 'quiz-master-next' ) ),
							'offset'     => array( 'type' => 'integer', 'minimum' => 0, 'default' => 0, 'description' => __( 'Number of results to skip for pagination.', 'quiz-master-next' ) ),
						)
					),
					array( 'quiz_id' )
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'total'   => array( 'type' => 'integer', 'description' => __( 'Number of submissions matching the filters.', 'quiz-master-next' ) ),
						'results' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'result_id'     => array( 'type' => 'integer' ),
									'graded'        => array( 'type' => 'boolean', 'description' => __( 'False for survey and simple form submissions, which have no score.', 'quiz-master-next' ) ),
									'score_percent' => array( 'type' => array( 'integer', 'null' ), 'description' => __( 'Percentage of correct answers.', 'quiz-master-next' ) ),
									'points'        => array( 'type' => array( 'number', 'null' ) ),
									'correct'       => array( 'type' => array( 'integer', 'null' ) ),
									'total'         => array( 'type' => array( 'integer', 'null' ) ),
									'passed'        => array( 'type' => array( 'boolean', 'null' ), 'description' => __( 'Null unless pass_score was given.', 'quiz-master-next' ) ),
									'name'          => array( 'type' => 'string', 'description' => __( 'Empty when the quiz did not collect it.', 'quiz-master-next' ) ),
									'email'         => array( 'type' => 'string', 'description' => __( 'Empty when the quiz did not collect it.', 'quiz-master-next' ) ),
									'user_id'       => array( 'type' => 'integer', 'description' => __( '0 for guests.', 'quiz-master-next' ) ),
									'submitted_at'  => array( 'type' => 'string', 'description' => __( 'Submission date/time (site time).', 'quiz-master-next' ) ),
									'details_url'   => array( 'type' => 'string', 'description' => __( 'wp-admin URL of the full result.', 'quiz-master-next' ) ),
								),
							),
						),
					),
					'required'   => array( 'total', 'results' ),
				),
				'execute'       => 'execute_list_results',
				'permission'    => 'permission_view_results',
				'annotations'   => $this->read_annotations(),
			),
			'get-results-summary'  => array(
				'label'         => __( 'Get Results Summary', 'quiz-master-next' ),
				'description'   => __( 'Answers "how is my quiz doing?": number of submissions, views, completion rate, average/highest/lowest score and, when pass_score is given, the pass rate. Use list-results to see individual submissions.', 'quiz-master-next' ),
				'category'      => 'qsm-results',
				'input_schema'  => $this->make_input_schema(
					array_merge( array( 'quiz_id' => $quiz_id_input ), $date_inputs, array( 'pass_score' => $pass_score_input ) ),
					array( 'quiz_id' )
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'quiz_id'           => array( 'type' => 'integer' ),
						'submissions'       => array( 'type' => 'integer', 'description' => __( 'Submissions matching the filters.', 'quiz-master-next' ) ),
						'graded'            => array( 'type' => 'integer', 'description' => __( 'Submissions that have a score (quiz form type).', 'quiz-master-next' ) ),
						'views'             => array( 'type' => 'integer', 'description' => __( 'All-time quiz views (not filtered by date).', 'quiz-master-next' ) ),
						'completion_rate'   => array( 'type' => array( 'number', 'null' ), 'description' => __( 'All-time submissions per view, 0-1. Null with a date filter or no views.', 'quiz-master-next' ) ),
						'average_score'     => array( 'type' => array( 'number', 'null' ), 'description' => __( 'Average percentage of correct answers.', 'quiz-master-next' ) ),
						'highest_score'     => array( 'type' => array( 'integer', 'null' ) ),
						'lowest_score'      => array( 'type' => array( 'integer', 'null' ) ),
						'average_points'    => array( 'type' => array( 'number', 'null' ) ),
						'pass_score'        => array( 'type' => array( 'number', 'null' ) ),
						'passed'            => array( 'type' => array( 'integer', 'null' ) ),
						'pass_rate'         => array( 'type' => array( 'number', 'null' ), 'description' => __( 'Share of graded submissions at or above pass_score, 0-1.', 'quiz-master-next' ) ),
						'last_submission'   => array( 'type' => array( 'string', 'null' ) ),
					),
					'required'   => array( 'quiz_id', 'submissions' ),
				),
				'execute'       => 'execute_get_results_summary',
				'permission'    => 'permission_view_results',
				'annotations'   => $this->read_annotations(),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Quiz
	// -------------------------------------------------------------------------

	/**
	 * Lists the quizzes the current user can edit.
	 *
	 * @since  11.2.5
	 * @param  array $input Validated input data.
	 * @return array
	 */
	public function execute_list_quizzes( $input ) {
		global $mlwQuizMasterNext;

		// Same scoping as the admin listings: users who cannot edit others' quizzes only see their own.
		$where = '';
		if ( ! current_user_can( 'edit_others_qsm_quizzes' ) ) {
			$quiz_ids = function_exists( 'qsm_get_editable_quiz_ids' ) ? qsm_get_editable_quiz_ids() : array();
			if ( empty( $quiz_ids ) ) {
				return array();
			}
			$where = 'quiz_id IN (' . implode( ',', array_map( 'intval', $quiz_ids ) ) . ')';
		}

		$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(
			false,
			isset( $input['order_by'] ) ? sanitize_key( $input['order_by'] ) : 'quiz_id',
			isset( $input['order'] ) && 'ASC' === strtoupper( $input['order'] ) ? 'ASC' : 'DESC',
			array(),
			'',
			isset( $input['limit'] ) ? intval( $input['limit'] ) : '',
			isset( $input['offset'] ) ? intval( $input['offset'] ) : ( isset( $input['limit'] ) ? 0 : '' ),
			$where
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
	 * @since  11.2.5
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_get_quiz( $input ) {
		$quiz = $this->get_quiz_row( $input['quiz_id'] );
		if ( empty( $quiz ) ) {
			return $this->quiz_not_found();
		}

		$quiz['quiz_id']       = intval( $quiz['quiz_id'] );
		$quiz['quiz_taken']    = intval( $quiz['quiz_taken'] );
		$quiz['quiz_views']    = intval( $quiz['quiz_views'] );
		$quiz['quiz_settings'] = $this->decode_quiz_settings( $quiz['quiz_settings'] );
		$quiz['placement']     = $this->get_placement( $quiz['quiz_id'] );

		return $quiz;
	}

	/**
	 * Creates a new quiz.
	 *
	 * Follows the same path as the "Create New Quiz" wizard in wp-admin: QSM's
	 * defaults, then the site's global quiz settings, then the values given.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_create_quiz( $input ) {
		global $mlwQuizMasterNext;

		$quiz_name = htmlspecialchars( sanitize_text_field( $input['quiz_name'] ), ENT_QUOTES );
		if ( '' === trim( $quiz_name ) ) {
			return new WP_Error( 'qsm_invalid_quiz_name', __( 'The quiz name cannot be empty.', 'quiz-master-next' ), array( 'status' => 400 ) );
		}
		$collect_contact = ! empty( $input['collect_contact'] );

		// Wizard defaults for the fields the wizard always sends.
		$defaults = array(
			'form_type'              => 0,
			'system'                 => 3,
			'timer_limit'            => 0,
			'pagination'             => 0,
			'enable_pagination_quiz' => 0,
			'progress_bar'           => 0,
			'require_log_in'         => 0,
			'disable_first_page'     => 0,
			'comment_section'        => 1,
			'contact_info_location'  => 0,
		);
		$global_settings = class_exists( 'QMNGlobalSettingsPage' ) ? QMNGlobalSettingsPage::get_global_quiz_settings() : get_option( 'qsm-quiz-settings', array() );
		$explicit        = array_merge(
			array(
				'quiz_name'             => $quiz_name,
				'contact_info_location' => $collect_contact ? 1 : 0,
			),
			$this->map_quiz_settings_input( $input )
		);
		$quiz_options = array_replace( $defaults, is_array( $global_settings ) ? $global_settings : array(), $explicit );
		// Same filter the wp-admin wizard applies, so add-ons see quizzes created here too.
		$quiz_options = apply_filters( 'qsm_quiz_wizard_settings_option_save', $quiz_options );

		$contact_form = array();
		if ( $collect_contact && class_exists( 'QSM_Contact_Manager' ) ) {
			$cf_fields = QSM_Contact_Manager::default_fields();
			foreach ( array( 'name', 'email' ) as $field ) {
				if ( isset( $cf_fields[ $field ] ) ) {
					$cf_fields[ $field ]['enable'] = 'true';
					$contact_form[]                = $cf_fields[ $field ];
				}
			}
		}

		/**
		 * Filter the settings of a quiz created through the create-quiz ability.
		 *
		 * @since 11.3.1
		 * @param array $quiz_settings Settings sections passed to QMNQuizCreator::create_quiz().
		 * @param array $input         The ability input.
		 */
		$quiz_settings = apply_filters(
			'qsm_ability_create_quiz_settings',
			array(
				'quiz_options' => $quiz_options,
				'contact_form' => $contact_form,
			),
			$input
		);

		// create_quiz() reports its new ID through the creator object; clear any stale one first.
		$creator = $mlwQuizMasterNext->quizCreator;
		$creator->set_id( 0 );
		$creator->create_quiz( $quiz_name, 0, $quiz_settings );
		$quiz_id = intval( $creator->get_id() );
		if ( $quiz_id <= 0 ) {
			return new WP_Error( 'qsm_create_quiz_failed', __( 'The quiz could not be created.', 'quiz-master-next' ), array( 'status' => 500 ) );
		}

		// Published by default: QSM renders a draft quiz but does not record its
		// responses, so a quiz an agent places on a page would collect nothing.
		$warnings = array();
		if ( ! isset( $input['publish'] ) || ! empty( $input['publish'] ) ) {
			$published = $this->set_quiz_published( $quiz_id, true );
			if ( is_wp_error( $published ) ) {
				$warnings[] = $published->get_error_message();
			}
		}

		return array(
			'quiz_id'   => $quiz_id,
			'placement' => $this->get_placement( $quiz_id ),
			'warnings'  => $warnings,
		);
	}

	/**
	 * Publishes or unpublishes a quiz's own post.
	 *
	 * The post status decides whether QSM records submissions: a draft quiz still
	 * renders through the shortcode or block, but its responses are discarded.
	 *
	 * @since  11.3.1
	 * @param  int  $quiz_id The quiz ID.
	 * @param  bool $publish True to publish, false to return it to draft.
	 * @return true|WP_Error
	 */
	private function set_quiz_published( $quiz_id, $publish ) {
		$post_id  = function_exists( 'qsm_get_post_id_for_quiz' ) ? intval( qsm_get_post_id_for_quiz( $quiz_id ) ) : 0;
		$post_obj = get_post_type_object( 'qsm_quiz' );
		if ( ! $post_id || ! $post_obj ) {
			return new WP_Error( 'qsm_quiz_post_missing', __( 'The quiz has no post to publish.', 'quiz-master-next' ), array( 'status' => 500 ) );
		}
		if ( ! current_user_can( $post_obj->cap->publish_posts ) ) {
			return new WP_Error( 'qsm_cannot_publish', __( 'You cannot publish quizzes, so the quiz stays a draft and will not record responses until an editor publishes it.', 'quiz-master-next' ), array( 'status' => 403 ) );
		}
		$result = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $publish ? 'publish' : 'draft',
			),
			true
		);
		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Updates a typed subset of a quiz's settings.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_update_quiz_settings( $input ) {
		global $mlwQuizMasterNext;

		$quiz_id = intval( $input['quiz_id'] );
		$quiz    = $this->get_quiz_row( $quiz_id );
		if ( empty( $quiz ) ) {
			return $this->quiz_not_found();
		}

		$updated = array();

		if ( isset( $input['quiz_name'] ) ) {
			$quiz_name = htmlspecialchars( sanitize_text_field( $input['quiz_name'] ), ENT_QUOTES );
			if ( '' === trim( $quiz_name ) ) {
				return new WP_Error( 'qsm_invalid_quiz_name', __( 'The quiz name cannot be empty.', 'quiz-master-next' ), array( 'status' => 400 ) );
			}
			$post_id = function_exists( 'qsm_get_post_id_for_quiz' ) ? intval( qsm_get_post_id_for_quiz( $quiz_id ) ) : 0;
			$mlwQuizMasterNext->quizCreator->edit_quiz_name( $quiz_id, $quiz_name, $post_id );
			$updated[] = 'quiz_name';
		}

		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		$quiz_options = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options', array() );
		$quiz_options = is_array( $quiz_options ) ? $quiz_options : array();

		$changes = $this->map_quiz_settings_input( $input );
		if ( isset( $quiz_name ) ) {
			$changes['quiz_name'] = $quiz_name;
		}
		if ( ! empty( $changes ) ) {
			$before       = $quiz_options;
			$quiz_options = array_replace( $quiz_options, $changes );
			if ( ! $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'quiz_options', $quiz_options ) ) {
				return new WP_Error( 'qsm_update_quiz_settings_failed', __( 'The quiz settings could not be saved.', 'quiz-master-next' ), array( 'status' => 500 ) );
			}
			do_action( 'qsm_saved_quiz_settings', $quiz_id, 'quiz_options', $quiz_options );
			$mlwQuizMasterNext->audit_manager->new_audit( 'Settings Have Been Edited', $quiz_id, wp_json_encode( array_diff_assoc( $quiz_options, $before ) ) );
			$updated = array_merge( $updated, array_keys( $this->map_quiz_settings_input( $input, true ) ) );
		}

		if ( isset( $input['results_message'] ) ) {
			if ( ! $this->save_default_results_message( $quiz_id, (string) $input['results_message'] ) ) {
				return new WP_Error( 'qsm_update_results_message_failed', __( 'The results page message could not be saved.', 'quiz-master-next' ), array( 'status' => 500 ) );
			}
			$updated[] = 'results_message';
		}

		if ( isset( $input['publish'] ) ) {
			$published = $this->set_quiz_published( $quiz_id, ! empty( $input['publish'] ) );
			if ( is_wp_error( $published ) ) {
				return $published;
			}
			$updated[] = 'publish';
		}

		$settings              = $this->get_typed_quiz_settings( $quiz_options );
		$placement             = $this->get_placement( $quiz_id );
		$settings['published'] = 'publish' === $placement['post_status'];

		return array(
			'quiz_id'  => $quiz_id,
			'updated'  => array_values( array_unique( $updated ) ),
			'settings' => $settings,
		);
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Questions
	// -------------------------------------------------------------------------

	/**
	 * Lists the questions shown on a quiz.
	 *
	 * Uses the quiz's page layout, so a question that sits only in the question
	 * bank is not reported as part of the quiz.
	 *
	 * @since  11.2.5
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_list_questions( $input ) {
		$quiz_id = intval( $input['quiz_id'] );
		if ( empty( $this->get_quiz_row( $quiz_id ) ) ) {
			return $this->quiz_not_found();
		}
		return array_values( (array) QSM_Questions::load_questions_by_pages( $quiz_id ) );
	}

	/**
	 * Creates a new question and adds it to the quiz's page layout.
	 *
	 * @since  11.2.5
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_create_question( $input ) {
		$quiz_id = intval( $input['quiz_id'] );
		if ( empty( $this->get_quiz_row( $quiz_id ) ) ) {
			return $this->quiz_not_found();
		}

		$type = isset( $input['question_type'] ) ? sanitize_text_field( (string) $input['question_type'] ) : '0';
		if ( ! $this->is_valid_question_type( $type ) ) {
			return $this->invalid_question_type( $type );
		}

		$data = array(
			'quiz_id' => $quiz_id,
			'name'    => sanitize_text_field( $input['question_name'] ),
			'type'    => $type,
			// A quiz with no page layout yet renders by question_order, so keep creation order.
			'order'   => $this->next_question_order( $quiz_id ),
		);

		$answers  = isset( $input['answers'] ) && is_array( $input['answers'] ) ? $this->normalize_answers( $input['answers'] ) : array();
		$answers  = $this->answers_for_type( $type, $data['name'], $answers );
		$settings = isset( $input['settings'] ) && is_array( $input['settings'] ) ? $this->sanitize_question_settings( $input['settings'] ) : array();
		if ( isset( $input['question_title'] ) ) {
			$settings['question_title'] = sanitize_text_field( $input['question_title'] );
		}

		try {
			$question_id = QSM_Questions::create_question( $data, $answers, $settings );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_create_question_failed', $e->getMessage() );
		}

		$page = $this->add_question_to_layout( $quiz_id, $question_id, isset( $input['page'] ) ? intval( $input['page'] ) : null );

		return array(
			'question_id' => intval( $question_id ),
			'page'        => $page,
		);
	}

	/**
	 * Updates an existing question, keeping every field that is not given.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_update_question( $input ) {
		$question_id = intval( $input['question_id'] );
		$question    = $this->get_live_question( $question_id );
		if ( empty( $question ) ) {
			return $this->question_not_found();
		}

		$type = isset( $input['question_type'] ) ? sanitize_text_field( (string) $input['question_type'] ) : (string) $question['question_type_new'];
		if ( isset( $input['question_type'] ) && ! $this->is_valid_question_type( $type ) ) {
			return $this->invalid_question_type( $type );
		}

		// save_question() rewrites the whole row, so start from what is stored. Text
		// columns are stored HTML-encoded and encoded again on save: decode them first.
		$data = array(
			'quiz_id'                     => intval( $question['quiz_id'] ),
			'type'                        => $type,
			'name'                        => isset( $input['question_name'] ) ? sanitize_text_field( $input['question_name'] ) : htmlspecialchars_decode( $question['question_name'], ENT_QUOTES ),
			'answer_info'                 => isset( $input['answer_info'] ) ? wp_kses_post( $input['answer_info'] ) : $question['question_answer_info'],
			'comments'                    => $question['comments'],
			'hint'                        => isset( $input['hint'] ) ? sanitize_text_field( $input['hint'] ) : $question['hints'],
			'order'                       => intval( $question['question_order'] ),
			'category'                    => $question['category'],
			'multicategories'             => $question['multicategories'],
			'linked_question'             => $question['linked_question'],
			'restrict_linked_to_editable' => true,
		);

		if ( isset( $input['answers'] ) && is_array( $input['answers'] ) ) {
			$answers = $this->normalize_answers( $input['answers'] );
		} else {
			$answers = array();
			foreach ( (array) $question['answers'] as $answer ) {
				if ( isset( $answer[0] ) ) {
					$answer[0] = htmlspecialchars_decode( $answer[0], ENT_QUOTES );
				}
				if ( isset( $answer[3] ) ) {
					$answer[3] = htmlspecialchars_decode( $answer[3], ENT_QUOTES );
				}
				$answers[] = $answer;
			}
		}

		$answers = $this->answers_for_type( $type, $data['name'], $answers );

		$settings = (array) $question['settings'];
		if ( isset( $input['settings'] ) && is_array( $input['settings'] ) ) {
			$settings = array_merge( $settings, $this->sanitize_question_settings( $input['settings'] ) );
		}
		if ( isset( $input['question_title'] ) ) {
			$settings['question_title'] = sanitize_text_field( $input['question_title'] );
		}

		try {
			QSM_Questions::save_question( $question_id, $data, $answers, $settings );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_update_question_failed', $e->getMessage() );
		}

		$updated = QSM_Questions::load_question( $question_id );
		$updated['question_id'] = intval( $updated['question_id'] );
		$updated['quiz_id']     = intval( $updated['quiz_id'] );
		return $updated;
	}

	/**
	 * Deletes a question and removes it from its quiz's pages.
	 *
	 * The row is soft-deleted (deleted = 1) through QSM_Questions::delete_question(),
	 * so it can be restored and recorded results still render. Unlike the
	 * "delete from database" action in wp-admin, linked copies in other quizzes
	 * are left alone; the link to this question is removed from them instead, so
	 * saving a linked copy later cannot write this row back to deleted = 0.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_delete_question( $input ) {
		$question_id = intval( $input['question_id'] );
		$question    = $this->get_live_question( $question_id );
		if ( empty( $question ) ) {
			return $this->question_not_found();
		}

		try {
			QSM_Questions::delete_question( $question_id );
		} catch ( Exception $e ) {
			return new WP_Error( 'qsm_delete_question_failed', $e->getMessage() );
		}

		$this->remove_question_from_layout( intval( $question['quiz_id'] ), $question_id );
		$this->unlink_question( $question_id, (string) $question['linked_question'] );

		return array(
			'question_id' => $question_id,
			'deleted'     => true,
		);
	}

	// -------------------------------------------------------------------------
	// Execute callbacks — Results
	// -------------------------------------------------------------------------

	/**
	 * Lists a quiz's submissions.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_list_results( $input ) {
		global $wpdb;

		$quiz_id = intval( $input['quiz_id'] );
		if ( empty( $this->get_quiz_row( $quiz_id ) ) ) {
			return $this->quiz_not_found();
		}

		$filter = $this->results_filter_sql( $quiz_id, $input );
		if ( is_wp_error( $filter ) ) {
			return $filter;
		}
		$score_filter = $this->score_filter_sql( $input );
		if ( is_wp_error( $score_filter ) ) {
			return $score_filter;
		}
		$filter    .= $score_filter;
		$limit      = isset( $input['limit'] ) ? max( 1, min( self::MAX_LIMIT, intval( $input['limit'] ) ) ) : self::DEFAULT_LIMIT;
		$offset     = isset( $input['offset'] ) ? max( 0, intval( $input['offset'] ) ) : 0;
		$pass_score = isset( $input['pass_score'] ) ? floatval( $input['pass_score'] ) : null;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is built with $wpdb->prepare().
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE {$filter}" ) );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT result_id, form_type, point_score, correct_score, correct, total, name, email, user, time_taken_real
				FROM {$wpdb->prefix}mlw_results WHERE {$filter} ORDER BY result_id DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$results = array();
		foreach ( (array) $rows as $row ) {
			$graded    = 0 === intval( $row->form_type );
			$results[] = array(
				'result_id'     => intval( $row->result_id ),
				'graded'        => $graded,
				'score_percent' => $graded ? intval( $row->correct_score ) : null,
				'points'        => $graded ? floatval( $row->point_score ) : null,
				'correct'       => $graded ? intval( $row->correct ) : null,
				'total'         => $graded ? intval( $row->total ) : null,
				'passed'        => $graded && null !== $pass_score ? intval( $row->correct_score ) >= $pass_score : null,
				'name'          => $this->contact_value( $row->name ),
				'email'         => $this->contact_value( $row->email ),
				'user_id'       => intval( $row->user ),
				'submitted_at'  => (string) $row->time_taken_real,
				'details_url'   => admin_url( 'admin.php?page=qsm_quiz_result_details&result_id=' . intval( $row->result_id ) ),
			);
		}

		return array(
			'total'   => $total,
			'results' => $results,
		);
	}

	/**
	 * Summarises a quiz's submissions.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return array|WP_Error
	 */
	public function execute_get_results_summary( $input ) {
		global $wpdb;

		$quiz_id = intval( $input['quiz_id'] );
		$quiz    = $this->get_quiz_row( $quiz_id );
		if ( empty( $quiz ) ) {
			return $this->quiz_not_found();
		}

		$filter = $this->results_filter_sql( $quiz_id, $input );
		if ( is_wp_error( $filter ) ) {
			return $filter;
		}
		$pass_score = isset( $input['pass_score'] ) ? floatval( $input['pass_score'] ) : null;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is built with $wpdb->prepare().
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS submissions,
					SUM( CASE WHEN form_type = 0 THEN 1 ELSE 0 END ) AS graded,
					AVG( CASE WHEN form_type = 0 THEN correct_score END ) AS average_score,
					MAX( CASE WHEN form_type = 0 THEN correct_score END ) AS highest_score,
					MIN( CASE WHEN form_type = 0 THEN correct_score END ) AS lowest_score,
					AVG( CASE WHEN form_type = 0 THEN point_score END ) AS average_points,
					SUM( CASE WHEN form_type = 0 AND correct_score >= %f THEN 1 ELSE 0 END ) AS passed,
					MAX( time_taken_real ) AS last_submission
				FROM {$wpdb->prefix}mlw_results WHERE {$filter}",
				null === $pass_score ? 0 : $pass_score
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$submissions = $row ? intval( $row->submissions ) : 0;
		$graded      = $row ? intval( $row->graded ) : 0;
		$views       = intval( $quiz['quiz_views'] );
		$has_dates   = ! empty( $input['date_from'] ) || ! empty( $input['date_to'] );
		$passed      = null !== $pass_score && $graded > 0 ? intval( $row->passed ) : null;

		return array(
			'quiz_id'         => $quiz_id,
			'submissions'     => $submissions,
			'graded'          => $graded,
			'views'           => $views,
			'completion_rate' => ! $has_dates && $views > 0 ? round( min( 1, $submissions / $views ), 4 ) : null,
			'average_score'   => $graded > 0 ? round( floatval( $row->average_score ), 2 ) : null,
			'highest_score'   => $graded > 0 ? intval( $row->highest_score ) : null,
			'lowest_score'    => $graded > 0 ? intval( $row->lowest_score ) : null,
			'average_points'  => $graded > 0 ? round( floatval( $row->average_points ), 2 ) : null,
			'pass_score'      => $pass_score,
			'passed'          => $passed,
			'pass_rate'       => null !== $passed ? round( $passed / $graded, 4 ) : null,
			'last_submission' => $row && $row->last_submission ? (string) $row->last_submission : null,
		);
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Builds the standard meta array for ability registration.
	 *
	 * Abilities are marked public for the MCP Adapter so its default server
	 * lists them. Each call still goes through the permission callback; the
	 * 'qsm_ability_mcp_public' filter can hide an ability from MCP clients.
	 *
	 * @since  11.2.5
	 * @param  array  $annotations  Annotations to set (readonly, destructive, idempotent).
	 * @param  string $ability_name The ability name.
	 * @return array
	 */
	private function make_meta( array $annotations = array(), $ability_name = '' ) {
		return array(
			'show_in_rest' => true,
			'annotations'  => $annotations,
			'mcp'          => array(
				/**
				 * Filter whether an ability is exposed through the MCP Adapter's default server.
				 *
				 * @since 11.3.1
				 * @param bool   $public       Whether the ability is listed for MCP clients.
				 * @param string $ability_name The ability name.
				 */
				'public' => (bool) apply_filters( 'qsm_ability_mcp_public', true, $ability_name ),
			),
		);
	}

	/**
	 * Annotations for abilities that only read.
	 *
	 * @since  11.3.1
	 * @return array
	 */
	private function read_annotations() {
		return array(
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		);
	}

	/**
	 * Annotations for abilities that write.
	 *
	 * @since  11.3.1
	 * Core's REST run controller maps destructive + idempotent to the DELETE method,
	 * so only abilities that remove data are marked destructive; updates use POST.
	 *
	 * @param  bool $destructive Whether the ability removes data.
	 * @param  bool $idempotent  Whether repeating the call has no further effect.
	 * @return array
	 */
	private function write_annotations( $destructive, $idempotent ) {
		return array(
			'readonly'    => false,
			'destructive' => (bool) $destructive,
			'idempotent'  => (bool) $idempotent,
		);
	}

	/**
	 * Wraps a properties map in the standard JSON Schema object envelope.
	 *
	 * @since  11.2.5
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
		} else {
			// Core rejects a missing input as "not of type object" unless the schema
			// has a top-level default, so abilities whose fields are all optional
			// (list-quizzes) can be called with no input at all.
			$schema['default'] = array();
		}

		return $schema;
	}

	/**
	 * Returns the registered question types as code => name.
	 *
	 * Built from the plugin's own registry so add-on types are included. The
	 * placeholder Pro types registered when the add-on is missing are left out,
	 * since a question of that type cannot be displayed.
	 *
	 * @since  11.3.1
	 * @return array<string, string>
	 */
	private function get_question_types() {
		global $mlwQuizMasterNext;
		$types = array();
		if ( empty( $mlwQuizMasterNext->pluginHelper->question_types ) ) {
			return $types;
		}
		foreach ( (array) $mlwQuizMasterNext->pluginHelper->question_types as $type ) {
			if ( ! isset( $type['slug'] ) || ( isset( $type['display'] ) && '-' === $type['display'] ) ) {
				continue;
			}
			$types[ (string) $type['slug'] ] = wp_strip_all_tags( $type['name'] );
		}
		return $types;
	}

	/**
	 * Builds the input schema for a question type field.
	 *
	 * @since  11.3.1
	 * @param  bool $with_default Whether to default to multiple choice.
	 * @return array
	 */
	private function get_question_type_schema( $with_default ) {
		$types = $this->get_question_types_for_description();
		$list  = array();
		foreach ( $types as $code => $name ) {
			$list[] = sprintf( '"%s"=%s', $code, $name );
		}
		$schema = array(
			'type'        => 'string',
			/* translators: %s: list of question type codes and names. */
			'description' => sprintf( __( 'Question type code: %s.', 'quiz-master-next' ), implode( ', ', $list ) ),
		);
		// Only enforce an enum built from the live registry, never from the fallback list.
		$registered = $this->get_question_types();
		if ( ! empty( $registered ) ) {
			$schema['enum'] = array_map( 'strval', array_keys( $registered ) );
		}
		if ( $with_default ) {
			$schema['default'] = '0';
		}
		return $schema;
	}

	/**
	 * Returns the registered question types, or the core codes when the registry
	 * has not been filled yet (it fills on 'init').
	 *
	 * @since  11.3.1
	 * @return array<string, string>
	 */
	private function get_question_types_for_description() {
		$types = $this->get_question_types();
		if ( ! empty( $types ) ) {
			return $types;
		}
		return array(
			'0'  => 'Multiple Choice',
			'1'  => 'Multiple Choice (Horizontal)',
			'2'  => 'Drop Down',
			'3'  => 'Short Answer',
			'4'  => 'Multiple Response',
			'5'  => 'Paragraph',
			'6'  => 'Text/HTML Section',
			'7'  => 'Number',
			'8'  => 'Opt-in',
			'9'  => 'Captcha',
			'10' => 'Multiple Response (Horizontal)',
			'11' => 'File Upload',
			'12' => 'Date',
			'13' => 'Polar',
			'14' => 'Fill In The Blank',
		);
	}

	/**
	 * Whether a question type code is registered.
	 *
	 * @since  11.3.1
	 * @param  string $type The question type code.
	 * @return bool
	 */
	private function is_valid_question_type( $type ) {
		$types = $this->get_question_types();
		// Unknown registry state: let QSM accept it, as before.
		return empty( $types ) || isset( $types[ (string) $type ] );
	}

	/**
	 * Error for an unregistered question type.
	 *
	 * @since  11.3.1
	 * @param  string $type The rejected question type code.
	 * @return WP_Error
	 */
	private function invalid_question_type( $type ) {
		return new WP_Error(
			'qsm_invalid_question_type',
			/* translators: 1: rejected type code, 2: valid codes. */
			sprintf( __( 'Unknown question type "%1$s". Valid types: %2$s.', 'quiz-master-next' ), $type, implode( ', ', array_keys( $this->get_question_types() ) ) ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Maps the typed quiz settings in an ability input onto quiz_options keys.
	 *
	 * @since  11.3.1
	 * @param  array $input      Ability input.
	 * @param  bool  $input_keys Return the input field names instead of quiz_options keys.
	 * @return array quiz_options key => value (or input key => value).
	 */
	private function map_quiz_settings_input( $input, $input_keys = false ) {
		$form_types = $this->form_type_map();
		$systems    = $this->grading_system_map();
		$map        = array();

		if ( isset( $input['form_type'], $form_types[ $input['form_type'] ] ) ) {
			$map['form_type'] = array( 'form_type', $form_types[ $input['form_type'] ] );
		}
		if ( isset( $input['grading_system'], $systems[ $input['grading_system'] ] ) ) {
			$map['grading_system'] = array( 'system', $systems[ $input['grading_system'] ] );
		}
		foreach ( array( 'timer_limit', 'pagination', 'total_user_tries', 'limit_total_entries' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$map[ $key ] = array( $key, absint( $input[ $key ] ) );
			}
		}
		foreach ( array( 'require_log_in', 'progress_bar' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$map[ $key ] = array( $key, $input[ $key ] ? 1 : 0 );
			}
		}

		$result = array();
		foreach ( $map as $input_key => $pair ) {
			$result[ $input_keys ? $input_key : $pair[0] ] = $pair[1];
		}
		return $result;
	}

	/**
	 * Reads the typed settings back out of quiz_options, in the ability's vocabulary.
	 *
	 * @since  11.3.1
	 * @param  array $quiz_options The quiz_options section.
	 * @return array
	 */
	private function get_typed_quiz_settings( $quiz_options ) {
		$form_types = array_flip( $this->form_type_map() );
		$systems    = array_flip( $this->grading_system_map() );
		$value      = function ( $key ) use ( $quiz_options ) {
			return isset( $quiz_options[ $key ] ) ? $quiz_options[ $key ] : 0;
		};
		return array(
			'quiz_name'           => isset( $quiz_options['quiz_name'] ) ? htmlspecialchars_decode( $quiz_options['quiz_name'], ENT_QUOTES ) : '',
			'form_type'           => isset( $form_types[ intval( $value( 'form_type' ) ) ] ) ? $form_types[ intval( $value( 'form_type' ) ) ] : '',
			'grading_system'      => isset( $systems[ intval( $value( 'system' ) ) ] ) ? $systems[ intval( $value( 'system' ) ) ] : '',
			'timer_limit'         => intval( $value( 'timer_limit' ) ),
			'pagination'          => intval( $value( 'pagination' ) ),
			'require_log_in'      => 1 === intval( $value( 'require_log_in' ) ),
			'progress_bar'        => 1 === intval( $value( 'progress_bar' ) ),
			'total_user_tries'    => intval( $value( 'total_user_tries' ) ),
			'limit_total_entries' => intval( $value( 'limit_total_entries' ) ),
		);
	}

	/**
	 * Ability vocabulary for quiz_options['form_type'].
	 *
	 * @since  11.3.1
	 * @return array
	 */
	private function form_type_map() {
		return array(
			'quiz'        => 0,
			'survey'      => 1,
			'simple_form' => 2,
		);
	}

	/**
	 * Ability vocabulary for quiz_options['system'].
	 *
	 * @since  11.3.1
	 * @return array
	 */
	private function grading_system_map() {
		return array(
			'correct' => 0,
			'points'  => 1,
			'both'    => 3,
		);
	}

	/**
	 * Sets the content of the quiz's default results page (the last page with no
	 * conditions), adding one if the quiz has none.
	 *
	 * @since  11.3.1
	 * @param  int    $quiz_id The quiz ID.
	 * @param  string $message Results page HTML.
	 * @return bool
	 */
	private function save_default_results_message( $quiz_id, $message ) {
		$pages = QSM_Results_Pages::load_pages( $quiz_id );
		$pages = is_array( $pages ) ? array_values( $pages ) : array();

		// Mirrors QSM_Results_Pages::generate_pages(): the fallback shown when no
		// conditional page matches is the LAST page that either has no conditions
		// or carries the default mark.
		$target = null;
		foreach ( $pages as $index => $page ) {
			$is_marked = isset( $page['default_mark'] ) && intval( $page['default_mark'] ) === $index + 1;
			if ( empty( $page['conditions'] ) || $is_marked ) {
				$target = $index;
			}
		}
		if ( null === $target ) {
			$pages[] = array( 'conditions' => array() );
			$target  = count( $pages ) - 1;
		}
		$pages[ $target ]['page'] = wp_kses_post( $message );

		// All pages go back through save_pages(), the same call the Results Pages tab
		// makes on every save, so the other pages get exactly the sanitising they
		// get there. save_pages() reads these keys on every page without checking them.
		foreach ( $pages as $index => $page ) {
			$pages[ $index ] = wp_parse_args( $page, array(
				'conditions'   => array(),
				'page'         => '',
				'redirect'     => false,
				'default_mark' => '',
			) );
		}

		return QSM_Results_Pages::save_pages( $quiz_id, $pages );
	}

	/**
	 * Builds the SQL WHERE clause for a quiz's results, with the optional date range.
	 *
	 * @since  11.3.1
	 * @param  int   $quiz_id The quiz ID.
	 * @param  array $input   Ability input (date_from, date_to).
	 * @return string|WP_Error Prepared SQL fragment.
	 */
	private function results_filter_sql( $quiz_id, $input ) {
		global $wpdb;
		$sql = $wpdb->prepare( 'deleted = 0 AND quiz_id = %d', $quiz_id );
		foreach ( array( 'date_from' => '>=', 'date_to' => '<' ) as $key => $operator ) {
			if ( empty( $input[ $key ] ) ) {
				continue;
			}
			$date = DateTime::createFromFormat( '!Y-m-d', (string) $input[ $key ] );
			// Round-trip check: createFromFormat() silently rolls 2026-02-31 over to March.
			if ( ! $date || $date->format( 'Y-m-d' ) !== (string) $input[ $key ] ) {
				/* translators: %s: input field name. */
				return new WP_Error( 'qsm_invalid_date', sprintf( __( '%s must be a date in YYYY-MM-DD format.', 'quiz-master-next' ), $key ), array( 'status' => 400 ) );
			}
			if ( 'date_to' === $key ) {
				// Inclusive end date: everything before the following midnight.
				$date->modify( '+1 day' );
			}
			$sql .= $wpdb->prepare( " AND time_taken_real {$operator} %s", $date->format( 'Y-m-d H:i:s' ) );
		}
		return $sql;
	}

	/**
	 * Builds the score conditions for list-results: min_score, max_score and
	 * status (passed/failed against pass_score). Any of them limits the list
	 * to graded submissions, since surveys and simple forms have no score.
	 *
	 * @since  11.3.1
	 * @param  array $input Validated input data.
	 * @return string|WP_Error SQL to append to the WHERE clause ('' when unfiltered).
	 */
	private function score_filter_sql( $input ) {
		global $wpdb;
		$min    = isset( $input['min_score'] ) ? floatval( $input['min_score'] ) : null;
		$max    = isset( $input['max_score'] ) ? floatval( $input['max_score'] ) : null;
		$status = isset( $input['status'] ) ? (string) $input['status'] : 'all';

		if ( null !== $min && null !== $max && $min > $max ) {
			return new WP_Error( 'qsm_invalid_score_range', __( 'min_score cannot be greater than max_score.', 'quiz-master-next' ), array( 'status' => 400 ) );
		}
		if ( 'all' !== $status && ! isset( $input['pass_score'] ) ) {
			return new WP_Error( 'qsm_missing_pass_score', __( 'status passed or failed needs pass_score, because QSM has no built-in pass mark.', 'quiz-master-next' ), array( 'status' => 400 ) );
		}

		$sql = '';
		if ( null !== $min ) {
			$sql .= $wpdb->prepare( ' AND correct_score >= %f', $min );
		}
		if ( null !== $max ) {
			$sql .= $wpdb->prepare( ' AND correct_score <= %f', $max );
		}
		if ( 'passed' === $status ) {
			$sql .= $wpdb->prepare( ' AND correct_score >= %f', floatval( $input['pass_score'] ) );
		} elseif ( 'failed' === $status ) {
			$sql .= $wpdb->prepare( ' AND correct_score < %f', floatval( $input['pass_score'] ) );
		}
		return '' === $sql ? '' : ' AND form_type = 0' . $sql;
	}

	/**
	 * A contact column from mlw_results. QSM writes the literal "None" when the
	 * quiz did not collect that field, which reads as a real name to an agent.
	 *
	 * @since  11.3.1
	 * @param  string $value Stored value.
	 * @return string
	 */
	private function contact_value( $value ) {
		$value = (string) $value;
		return 'None' === $value ? '' : $value;
	}

	/**
	 * Loads a quiz row that has not been deleted.
	 *
	 * @since  11.3.1
	 * @param  int $quiz_id The quiz ID.
	 * @return array|null
	 */
	private function get_quiz_row( $quiz_id ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT quiz_id, quiz_name, quiz_taken, quiz_views, last_activity, quiz_settings FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d AND deleted = 0 LIMIT 1",
				intval( $quiz_id )
			),
			ARRAY_A
		);
	}

	/**
	 * Loads a question that exists and has not been deleted.
	 *
	 * @since  11.3.1
	 * @param  int $question_id The question ID.
	 * @return array Empty when not found.
	 */
	private function get_live_question( $question_id ) {
		$question = QSM_Questions::load_question( $question_id );
		if ( empty( $question ) || ! empty( $question['deleted'] ) ) {
			return array();
		}
		return $question;
	}

	/**
	 * Error for a quiz that does not exist.
	 *
	 * @since  11.3.1
	 * @return WP_Error
	 */
	private function quiz_not_found() {
		return new WP_Error( 'qsm_quiz_not_found', __( 'Quiz not found. Use list-quizzes to get a valid quiz_id.', 'quiz-master-next' ), array( 'status' => 404 ) );
	}

	/**
	 * Error for a question that does not exist.
	 *
	 * @since  11.3.1
	 * @return WP_Error
	 */
	private function question_not_found() {
		return new WP_Error( 'qsm_question_not_found', __( 'Question not found. Use list-questions to get a valid question_id.', 'quiz-master-next' ), array( 'status' => 404 ) );
	}

	/**
	 * Decodes the quiz_settings column into an object of sections.
	 *
	 * The column is a serialised array whose section values are serialised again,
	 * so both levels are decoded. Classes are never instantiated.
	 *
	 * @since  11.3.1
	 * @param  string $raw The quiz_settings column.
	 * @return array
	 */
	private function decode_quiz_settings( $raw ) {
		$settings = $this->safe_unserialize( $raw );
		if ( ! is_array( $settings ) ) {
			return array();
		}
		foreach ( $settings as $section => $value ) {
			$settings[ $section ] = $this->safe_unserialize( $value );
		}
		return $settings;
	}

	/**
	 * Unserialises a value without allowing objects.
	 *
	 * allowed_classes:false stops instantiation but still yields
	 * __PHP_Incomplete_Class placeholders, so any object is dropped.
	 *
	 * @since  11.3.1
	 * @param  mixed $value Possibly serialised value.
	 * @return mixed
	 */
	private function safe_unserialize( $value ) {
		if ( is_string( $value ) && is_serialized( $value ) ) {
			$value = @unserialize( trim( $value ), array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged -- allowed_classes:false blocks object injection
		}
		return $this->drop_objects( $value );
	}

	/**
	 * Removes objects from a value, at any depth.
	 *
	 * @since  11.3.1
	 * @param  mixed $value Value to clean.
	 * @return mixed Null for an object.
	 */
	private function drop_objects( $value ) {
		if ( is_object( $value ) ) {
			return null;
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				if ( is_object( $item ) ) {
					unset( $value[ $key ] );
				} else {
					$value[ $key ] = $this->drop_objects( $item );
				}
			}
		}
		return $value;
	}

	/**
	 * Returns how to place a quiz on a page.
	 *
	 * @since  11.3.1
	 * @param  int $quiz_id The quiz ID.
	 * @return array
	 */
	private function get_placement( $quiz_id ) {
		$quiz_id     = intval( $quiz_id );
		$post_id     = function_exists( 'qsm_get_post_id_for_quiz' ) ? intval( qsm_get_post_id_for_quiz( $quiz_id ) ) : 0;
		$post_status = $post_id ? (string) get_post_status( $post_id ) : '';
		return array(
			'shortcode'   => sprintf( '[qsm quiz=%d]', $quiz_id ),
			'block'       => sprintf( '<!-- wp:qsm/quiz {"quizID":%d} /-->', $quiz_id ),
			'post_id'     => $post_id,
			'post_status' => $post_status,
			'permalink'   => 'publish' === $post_status ? (string) get_permalink( $post_id ) : '',
			'edit_url'    => admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id ),
		);
	}

	/**
	 * Adds a question to the quiz's page layout.
	 *
	 * A quiz whose layout is still empty renders every question it owns as one
	 * page, so nothing needs writing for it. Once the layout lists questions, a
	 * question missing from it exists only in the question bank, which is why
	 * create-question has to add it.
	 *
	 * @since  11.3.1
	 * @param  int      $quiz_id     The quiz ID.
	 * @param  int      $question_id The question ID.
	 * @param  int|null $page        Zero-based page, or null for the last page.
	 * @return int The zero-based page the question is on.
	 */
	private function add_question_to_layout( $quiz_id, $question_id, $page = null ) {
		global $mlwQuizMasterNext;

		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		// A non-empty caller returns the stored layout, unpublished questions included.
		$pages  = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array(), 'ability' );
		$qpages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array(), 'ability' );
		$pages  = is_array( $pages ) ? array_values( $pages ) : array();
		$qpages = is_array( $qpages ) ? array_values( $qpages ) : array();

		$has_questions = false;
		foreach ( $pages as $page_questions ) {
			if ( ! empty( $page_questions ) ) {
				$has_questions = true;
				break;
			}
		}
		if ( ! $has_questions ) {
			return 0;
		}

		$last   = count( $pages ) - 1;
		$target = null === $page ? $last : min( max( 0, intval( $page ) ), $last );

		$pages[ $target ]   = array_map( 'intval', (array) $pages[ $target ] );
		$pages[ $target ][] = intval( $question_id );
		if ( isset( $qpages[ $target ] ) && is_array( $qpages[ $target ] ) ) {
			$questions                       = isset( $qpages[ $target ]['questions'] ) ? array_map( 'intval', (array) $qpages[ $target ]['questions'] ) : array();
			$questions[]                     = intval( $question_id );
			$qpages[ $target ]['questions'] = $questions;
			$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'qpages', $qpages );
		}
		$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $pages );

		return $target;
	}

	/**
	 * Removes a question from the quiz's page layout.
	 *
	 * @since  11.3.1
	 * @param  int $quiz_id     The quiz ID.
	 * @param  int $question_id The question ID.
	 * @return void
	 */
	private function remove_question_from_layout( $quiz_id, $question_id ) {
		global $mlwQuizMasterNext;

		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		$pages  = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array(), 'ability' );
		$qpages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array(), 'ability' );
		$keep   = function ( $ids ) use ( $question_id ) {
			return array_values( array_diff( array_map( 'intval', (array) $ids ), array( intval( $question_id ) ) ) );
		};

		if ( is_array( $pages ) && ! empty( $pages ) ) {
			foreach ( $pages as $index => $ids ) {
				$pages[ $index ] = $keep( $ids );
			}
			$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $pages );
		}
		if ( is_array( $qpages ) && ! empty( $qpages ) ) {
			foreach ( $qpages as $index => $qpage ) {
				if ( is_array( $qpage ) && isset( $qpage['questions'] ) ) {
					$qpages[ $index ]['questions'] = $keep( $qpage['questions'] );
				}
			}
			$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'qpages', $qpages );
		}
	}

	/**
	 * Returns the question_order for a new question: after the quiz's last one.
	 *
	 * @since  11.3.1
	 * @param  int $quiz_id The quiz ID.
	 * @return int
	 */
	private function next_question_order( $quiz_id ) {
		global $wpdb;
		$max = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(question_order) FROM {$wpdb->prefix}mlw_questions WHERE quiz_id = %d AND deleted = 0", $quiz_id ) );
		return intval( $max ) + 1;
	}

	/**
	 * Applies the answer rules the question editor enforces for some types.
	 *
	 * Opt-in (8) stores its own text as the single answer, as qsm_rest_save_question() does.
	 *
	 * @since  11.3.1
	 * @param  string $type    Question type code.
	 * @param  string $name    Question text.
	 * @param  array  $answers Answers in QSM array format.
	 * @return array
	 */
	private function answers_for_type( $type, $name, $answers ) {
		if ( '8' === (string) $type ) {
			return array( array( $name, 0, 1 ) );
		}
		return $answers;
	}

	/**
	 * Removes a deleted question from the linked_question list of its linked copies.
	 *
	 * create_save_question() writes every row named in linked_question with
	 * deleted = 0, so a link left in place would undelete this question the next
	 * time a copy is saved. Only copies in quizzes the user may edit are changed.
	 *
	 * @since  11.3.1
	 * @param  int    $question_id     The deleted question.
	 * @param  string $linked_question Its linked_question column.
	 * @return void
	 */
	private function unlink_question( $question_id, $linked_question ) {
		global $wpdb;
		$table    = $wpdb->prefix . 'mlw_questions';
		$siblings = array_diff( array_filter( array_map( 'intval', explode( ',', $linked_question ) ) ), array( $question_id ) );
		if ( function_exists( 'qsm_filter_editable_question_ids' ) ) {
			$siblings = qsm_filter_editable_question_ids( $siblings );
		} elseif ( ! current_user_can( 'edit_others_qsm_quizzes' ) ) {
			$siblings = array();
		}
		foreach ( $siblings as $sibling_id ) {
			$links = (string) $wpdb->get_var( $wpdb->prepare( "SELECT linked_question FROM {$table} WHERE question_id = %d", $sibling_id ) );
			$links = array_diff( array_filter( array_map( 'intval', explode( ',', $links ) ) ), array( $question_id ) );
			$wpdb->update( $table, array( 'linked_question' => implode( ',', $links ) ), array( 'question_id' => intval( $sibling_id ) ), array( '%s' ), array( '%d' ) );
		}
		$wpdb->update( $table, array( 'linked_question' => '' ), array( 'question_id' => intval( $question_id ) ), array( '%s' ), array( '%d' ) );
	}

	/**
	 * Sanitises question settings from ability input.
	 *
	 * Only scalar values are kept. Numeric settings that end up in HTML
	 * attributes go through the same cast the REST editor uses.
	 *
	 * @since  11.3.1
	 * @param  array $settings Raw settings.
	 * @return array
	 */
	private function sanitize_question_settings( array $settings ) {
		$clean = array();
		foreach ( $settings as $key => $value ) {
			$key = sanitize_key( $key );
			if ( '' === $key || ! is_scalar( $value ) ) {
				continue;
			}
			$value = function_exists( 'qsm_sanitize_question_numeric_setting' ) ? qsm_sanitize_question_numeric_setting( $key, $value ) : $value;
			$clean[ $key ] = is_string( $value ) ? sanitize_text_field( $value ) : $value;
		}
		return $clean;
	}

	/**
	 * Normalizes answers from object or array format into QSM's internal array format.
	 *
	 * Accepts both [text, points, correct, feedback] arrays and
	 * {text, points, correct, feedback} objects (from the OpenAPI schema).
	 *
	 * @since  11.2.5
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
	 * Whether the current user may edit a given quiz.
	 *
	 * @since  11.3.1
	 * @param  int $quiz_id The quiz ID.
	 * @return bool
	 */
	private function can_edit_quiz( $quiz_id ) {
		if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
			return false;
		}
		if ( function_exists( 'qsm_current_user_can_edit_quiz' ) ) {
			return qsm_current_user_can_edit_quiz( intval( $quiz_id ) );
		}
		// Ownership helper unavailable: only users who may edit every quiz get through.
		return current_user_can( 'edit_others_qsm_quizzes' );
	}

	/**
	 * Checks if the current user can edit quizzes at all.
	 *
	 * Used by list-quizzes, which scopes its own output to the user's quizzes.
	 *
	 * @since  11.2.5
	 * @return bool
	 */
	public function permission_edit_quizzes() {
		return current_user_can( 'edit_qsm_quizzes' );
	}

	/**
	 * Checks if the current user can create quizzes.
	 *
	 * @since  11.3.1
	 * @return bool
	 */
	public function permission_create_quizzes() {
		return current_user_can( 'create_qsm_quizzes' ) && current_user_can( 'edit_qsm_quizzes' );
	}

	/**
	 * Checks if the current user can edit the quiz named in the input.
	 *
	 * Abilities used to check only the flat edit_qsm_quizzes capability, which
	 * let any Contributor read or change other authors' quizzes.
	 *
	 * @since  11.3.1
	 * @param  array $input Ability input.
	 * @return bool
	 */
	public function permission_edit_quiz( $input = array() ) {
		$quiz_id = isset( $input['quiz_id'] ) ? intval( $input['quiz_id'] ) : 0;
		return $quiz_id > 0 && $this->can_edit_quiz( $quiz_id );
	}

	/**
	 * Checks if the current user can edit the question named in the input,
	 * judged by the quiz that question belongs to.
	 *
	 * @since  11.3.1
	 * @param  array $input Ability input.
	 * @return bool
	 */
	public function permission_edit_question( $input = array() ) {
		$question_id = isset( $input['question_id'] ) ? intval( $input['question_id'] ) : 0;
		if ( $question_id <= 0 || ! current_user_can( 'edit_qsm_quizzes' ) ) {
			return false;
		}
		$quiz_ids = function_exists( 'qsm_get_question_quiz_ids' ) ? qsm_get_question_quiz_ids( array( $question_id ) ) : array();
		if ( ! isset( $quiz_ids[ $question_id ] ) ) {
			// Unknown question: let users who may edit everything reach the 404, refuse everyone else.
			return current_user_can( 'edit_others_qsm_quizzes' );
		}
		return $this->can_edit_quiz( $quiz_ids[ $question_id ] );
	}

	/**
	 * Checks if the current user can view the results of the quiz named in the input.
	 *
	 * Results hold takers' names and emails, so this needs both the results
	 * capability and the right to edit that quiz, as the results pages do.
	 *
	 * @since  11.3.1
	 * @param  array $input Ability input.
	 * @return bool
	 */
	public function permission_view_results( $input = array() ) {
		$quiz_id = isset( $input['quiz_id'] ) ? intval( $input['quiz_id'] ) : 0;
		return $quiz_id > 0 && current_user_can( 'view_qsm_quiz_result' ) && $this->can_edit_quiz( $quiz_id );
	}
}
