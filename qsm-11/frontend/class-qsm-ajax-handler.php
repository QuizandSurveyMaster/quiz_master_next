<?php
/**
 * QSM-11 AJAX Handler for Lazy Loading
 *
 * Handles AJAX requests for lazy loading questions
 *
 * @package QSM
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QSM_Ajax_Handler class
 *
 * Manages AJAX operations for lazy loading questions
 */
class QSM_Ajax_Handler {

	/**
	 * Initialize AJAX hooks
	 */
	public static function init() {
		add_action( 'wp_ajax_qsm_load_page_questions', array( __CLASS__, 'load_page_questions' ) );
		add_action( 'wp_ajax_nopriv_qsm_load_page_questions', array( __CLASS__, 'load_page_questions' ) );
	}

	/**
	 * AJAX handler to load questions for a specific page
	 */
	public static function load_page_questions() {
		global $mlwQuizMasterNext;
		
		// Get quiz_id first to verify nonce
		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
		
		if ( ! $quiz_id ) {
			wp_send_json_error( array( 'message' => 'Invalid quiz ID' ) );
		}
		
		// Verify quiz-specific nonce
		check_ajax_referer( 'qsm_lazy_load_' . $quiz_id, 'nonce' );
		
		// Get remaining request parameters
		$page_number = isset( $_POST['page_number'] ) ? intval( $_POST['page_number'] ) : 0;
		$question_ids_serialized = isset( $_POST['question_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['question_ids'] ) ) : '';
		$randomness_order = isset( $_POST['randomness_order'] ) ? json_decode( stripslashes( $_POST['randomness_order'] ), true ) : array();
		$question_start_number = isset( $_POST['question_start_number'] ) ? intval( $_POST['question_start_number'] ) : 1;
		
		// Normalize randomness order
		if ( ! empty( $randomness_order ) ) {
			$randomness_order = $mlwQuizMasterNext->pluginHelper->qsm_get_randomization_modes( $randomness_order );
		}
		
		// Validate parameters
		if ( ! $page_number ) {
			wp_send_json_error( array( 'message' => 'Invalid page number' ) );
		}

		// Parse question IDs for this page
		$question_ids = explode( ',', $question_ids_serialized );
		$question_ids = array_filter( array_map( 'intval', $question_ids ) );

		if ( empty( $question_ids ) ) {
			wp_send_json_error( array( 'message' => 'No questions found for this page' ) );
		}

		try {
			// Load quiz options
			global $wpdb;
			$quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );
			
			if ( ! $quiz_options ) {
				wp_send_json_error( array( 'message' => 'Quiz not found' ) );
			}

			// Render questions HTML
			$html = self::render_page_questions( 
				$quiz_id, 
				$question_ids, 
				$quiz_options, 
				$randomness_order,
				$question_start_number
			);

			wp_send_json_success( array(
				'html'           => $html,
				'page_number'    => $page_number,
				'question_count' => count( $question_ids ),
			) );

		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => 'Error loading questions: ' . $e->getMessage(),
			) );
		}
	}

	/**
	 * Render questions for a specific page
	 *
	 * @param int    $quiz_id              Quiz ID
	 * @param array  $question_ids         Question IDs to render
	 * @param object $quiz_options         Quiz options object
	 * @param array  $randomness_order     Randomness settings
	 * @param int    $question_start_number Starting question number
	 * @return string HTML output
	 */
	private static function render_page_questions( $quiz_id, $question_ids, $quiz_options, $randomness_order, $question_start_number ) {
		global $wpdb, $mlwQuizMasterNext, $qmn_total_questions;
		
		// Get questions from database
		$questions = array();
		foreach ( $question_ids as $question_id ) {
			$question = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d AND deleted = 0",
				$question_id
			), ARRAY_A );

			if ( $question ) {
				$questions[ $question_id ] = $question;
			}
		}

		if ( empty( $questions ) ) {
			return '';
		}
		
		// Get quiz settings
		$quiz_settings = maybe_unserialize( $quiz_options->quiz_settings );
		$quiz_options_settings = (object) maybe_unserialize( $quiz_settings['quiz_options'] ?? '[]' );

		// Create renderer instance for template methods (pass class_object to templates)
		$quiz_data = array( 'quiz_id' => $quiz_id );
		$shortcode_args = array();
		$renderer = new QSM_New_Pagination_Renderer( $quiz_options, $quiz_data, $shortcode_args );

		// Start output buffering
		ob_start();

		// Set starting question number
		$current_question_number = $question_start_number;

		// Render each question
		foreach ( $question_ids as $question_id ) {
			if ( ! isset( $questions[ $question_id ] ) ) {
				continue;
			}

			$question = $questions[ $question_id ];
			$question_settings = maybe_unserialize( $question['question_settings'] );
			$answer_array = maybe_unserialize( $question['answer_array'] );

			// Apply answer randomization if enabled
			if ( in_array( 'answers', $randomness_order ) ) {
				$answer_array = QMNPluginHelper::qsm_shuffle_assoc( $answer_array );
				global $quiz_answer_random_ids;
				if ( ! isset( $quiz_answer_random_ids ) ) {
					$quiz_answer_random_ids = array();
				}
				$answer_ids = array_keys( $answer_array );
				$quiz_answer_random_ids[ $question_id ] = $answer_ids;
			}

			?>
			<div class="quiz_section qsm-question-wrapper qsm-question-wrapper-<?php echo esc_attr( $question_id ); ?> question-section-id-<?php echo esc_attr( $question_id ); ?> question-type-<?php echo esc_attr( $question['question_type_new'] ); ?>" data-qid="<?php echo esc_attr( $question_id ); ?>">
				<span class='mlw_qmn_question_number'><?php echo esc_html( $current_question_number ); ?>.&nbsp;</span>
				<?php
				// Show category if enabled
				if ( isset( $quiz_options_settings->show_category_on_front ) && $quiz_options_settings->show_category_on_front ) {
					$categories = QSM_Questions::get_question_categories( $question_id );
					if ( ! empty( $categories['category_name'] ) ) {
						$cat_name = implode( ',', $categories['category_name'] );
						?>
						<div class="quiz-cat"><?php echo esc_html( $cat_name ); ?></div>
						<?php
					}
				}

				// Render question using template
				$args = array(
					'quiz_id'           => $quiz_id,
					'id'                => $question_id,
					'class_object'      => $renderer,
					'question'          => $question,
					'answers'           => $answer_array,
					'question_settings' => is_array( $question_settings ) ? $question_settings : array(),
					'quiz_options'      => $quiz_options_settings,
				);

				// Get question template
				$question_template = qsm_get_question_template( $question['question_type_new'], $args );
				
				if ( false === $question_template ) {
					// Fallback to legacy display method
					$question_type = array_filter( $mlwQuizMasterNext->pluginHelper->question_types, function( $item ) use ( $question ) {
						return $item['slug'] == $question['question_type_new'];
					} );
					$question_type = array_shift( $question_type );
					if ( $question_type && isset( $question_type['display'] ) ) {
						call_user_func( $question_type['display'], intval( $question_id ), $question['question_name'], $answer_array );
					}
				} else {
					echo $question_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				do_action( 'qsm_after_question', $question );
				?>
			</div>
			<?php
			$current_question_number++;
		}

		$html = ob_get_clean();
		return $html;
	}
}

// Initialize AJAX handler
QSM_Ajax_Handler::init();
