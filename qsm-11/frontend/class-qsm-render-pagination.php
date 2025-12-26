<?php
/**
 * QSM New Pagination Rendering Class
 *
 * Handles both manual and automatic pagination modes with template-based rendering
 * This is the new implementation that doesn't interfere with existing system
 *
 * @package QSM
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QSM_New_Pagination_Renderer class
 *
 * Manages pagination logic and template rendering for quizzes using new system
 */
class QSM_New_Pagination_Renderer {

	/**
	 * Quiz options
	 *
	 * @var object
	 */
	private $options;

	/**
	 * Quiz data
	 *
	 * @var array
	 */
	private $quiz_data;

	/**
	 * Questions array
	 *
	 * @var array
	 */
	private $questions;

	/**
	 * Pages array
	 *
	 * @var array
	 */
	public $pages;
	
	/**
	 * Question pages array
	 *
	 * @var array
	 */
	private $qpages;

	/**
	 * Quiz settings
	 *
	 * @var array
	 */
	private $quiz_settings;

	/**
	 * Quiz options
	 *
	 * @var object
	 */
	private $quiz_options;

	/**
	 * Contact fields
	 *
	 * @var array
	 */
	private $contact_fields;

	/**
	 * Quiz texts
	 *
	 * @var array
	 */
	private $quiz_texts;
	
	/**
	 * Randomness order
	 *
	 * @var array
	 */
	private $randomness_order;

	/**
	 * Shortcode arguments
	 *
	 * @var array
	 */
	private $shortcode_args;

	/**
	 * Constructor
	 *
	 * @param object $options Quiz options
	 * @param array  $quiz_data Quiz data
	 * @param array  $shortcode_args Shortcode arguments
	 */
	public function __construct( $options, $quiz_data, $shortcode_args = array() ) {
		global $mlwQuizMasterNext;
		$this->options 			= $options;
		$this->quiz_data 		= $quiz_data;
		$this->shortcode_args 	= $shortcode_args;
		$this->quiz_settings 	= maybe_unserialize( $options->quiz_settings );
		$this->quiz_options 	= (object) maybe_unserialize( $this->quiz_settings['quiz_options'] );		
		$this->quiz_texts 		= (object) maybe_unserialize( $this->quiz_settings['quiz_text'] );		
		$this->contact_fields 	= maybe_unserialize( $this->quiz_settings['contact_form'] );
		$this->pages 			= maybe_unserialize( $this->quiz_settings['pages'] );
		$this->qpages 			= maybe_unserialize( $this->quiz_settings['qpages'] );
		$this->randomness_order = $mlwQuizMasterNext->pluginHelper->qsm_get_randomization_modes( $this->quiz_options->randomness_order );
		
		// Ensure quiz_data has required fields
		if ( ! isset( $this->quiz_data['quiz_id'] ) ) {
			if ( isset( $this->options->quiz_id ) ) {
				$this->quiz_data['quiz_id'] = $this->options->quiz_id;
			} else {
				throw new Exception( 'Quiz ID not found in options or quiz_data' );
			}
		}
		
		$this->load_questions();
		$this->setup_pages();
	}

	/**
	 * Load questions for the quiz
	 * Enhanced with category support, question limiting, and proper filters
	 */
	private function load_questions() {
		global $mlwQuizMasterNext, $wpdb;
		
		try {
			$quiz_id = intval( $this->quiz_data['quiz_id'] );
			
			// Get questions directly from database
			$questions_query = $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id = %d AND deleted = 0 ORDER BY question_order ASC",
				$quiz_id
			);
			
			$questions_results = $wpdb->get_results( $questions_query, ARRAY_A );
			
			// Format questions array
			$this->questions = array();
			if ( $questions_results ) {
				foreach ( $questions_results as $question ) {
					// Convert object to array for consistency
					$question_array = (array) $question;
					$this->questions[ $question_array['question_id'] ] = $question_array;
				}
			}
			
		} catch ( Exception $e ) {
			// Fallback to empty array
			$this->questions = array();
			
			// Log error if debug is enabled
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'QSM New Rendering: Failed to load questions - ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Setup pages based on quiz configuration
	 */
	private function setup_pages() {
		$quiz_id = intval( $this->quiz_data['quiz_id'] );
		
		// If pagination value is greater than 0
		if ( $this->quiz_options->pagination <= 0 ) {
			if ( in_array( 'pages', $this->randomness_order ) ) {
				shuffle( $this->pages );
			}
			if ( in_array( 'questions', $this->randomness_order ) ) {
				foreach ( $this->pages as &$page ) {
					shuffle( $page );
				}
			}
		} else {
			$this->create_auto_pagination();
		}
	}

	/**
	 * Create automatic pagination based on questions per page setting
	 * Refactored to use already fetched questions and follow proper filtering order
	 */
	private function create_auto_pagination() {
		global $wpdb;
		
		$questions_per_page = intval( $this->quiz_options->pagination );
		$quiz_id = intval( $this->quiz_data['quiz_id'] );
		$randomness_order = $this->randomness_order;
		
		// Check if multiple category system is enabled
		$multiple_category_system = false;
		$enabled = get_option( 'qsm_multiple_category_enabled' );
		if ( $enabled && 'cancelled' !== $enabled ) {
			$multiple_category_system = true;
		}
		
		// STEP 1: Start with already fetched questions
		$questions = $this->questions;
		
		// STEP 2: Filter out unpublished questions first
		$questions = array_filter(
			$questions,
			function ( $question ) {
				$question_settings = maybe_unserialize( $question['question_settings'] );
				return ! isset( $question_settings['isPublished'] ) || 0 !== intval( $question_settings['isPublished'] );
			}
		);
		
		// Get question IDs for further processing
		$question_ids = array_merge( ...array_values( $this->pages ) );
		
		// STEP 3: Apply category-based filtering
		$exploded_arr = array();
		$categories = isset( $this->quiz_options->randon_category ) ? $this->quiz_options->randon_category : '';
		
		// Check if we need to filter by specific categories
		if ( $categories && ! empty( $this->quiz_options->question_per_category ) ) {
			$exploded_arr = explode( ',', $this->quiz_options->randon_category );
			if ( $multiple_category_system ) {
				$exploded_arr = array_map( 'intval', $exploded_arr );
			}
		}
		
		// Handle manual pages from quiz settings
		$pages = isset( $this->pages ) && is_array( $this->pages ) ? $this->pages : array();
		$total_pages = is_countable( $pages ) ? count( $pages ) : 0;
		
		// Get category question IDs if using multiple category system
		$category_question_ids = array();
		if ( $multiple_category_system && ! empty( $exploded_arr ) ) {
			$term_ids = implode( ', ', $exploded_arr );
			$query = $wpdb->prepare( 
				"SELECT DISTINCT question_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d AND term_id IN (%1s)", 
				$quiz_id, 
				$term_ids 
			);
			$question_data = $wpdb->get_results( $query, ARRAY_N );
			foreach ( $question_data as $q_data ) {
				$category_question_ids[] = $q_data[0];
			}
		}
		
		// If manual pages exist, use them to determine question order
		if ( $total_pages > 0 ) {
			$page_question_ids = array();
			for ( $i = 0; $i < $total_pages; $i++ ) {
				foreach ( $pages[ $i ] as $question ) {
					if ( ! empty( $category_question_ids ) ) {
						if ( in_array( intval( $question ), array_map( 'intval', $category_question_ids ), true ) ) {
							$page_question_ids[] = intval( $question );
						}
					} else {
						$page_question_ids[] = intval( $question );
					}
				}
			}
			
			// Filter questions to only include those from pages
			$question_ids = array_intersect( $question_ids, $page_question_ids );
			
			// Apply category-based question limiting
			// Mode 1: Standard question per category limit
			if ( ( '' == $this->quiz_options->limit_category_checkbox || 0 == $this->quiz_options->limit_category_checkbox ) 
				&& 0 != $this->quiz_options->question_per_category ) {
				
				$categories_data = QSM_Questions::get_quiz_categories( $quiz_id );
				$category_ids = ( isset( $categories_data['list'] ) ? array_keys( $categories_data['list'] ) : array() );
				$categories_tree = ( isset( $categories_data['tree'] ) ? $categories_data['tree'] : array() );
				
				if ( ! empty( $category_ids ) ) {
					$term_ids = implode( ',', $category_ids );
					$question_id_str = implode( ',', $question_ids );
					$term_ids = ( '' !== $this->quiz_options->randon_category ) ? $this->quiz_options->randon_category : $term_ids;
					
					$tq_ids = $wpdb->get_results(
						"SELECT DISTINCT qt.term_id, qt.question_id
						FROM {$wpdb->prefix}mlw_question_terms AS qt
						WHERE qt.question_id IN ($question_id_str)
							AND qt.term_id IN ($term_ids)
							AND qt.taxonomy = 'qsm_category'
						",
						ARRAY_A
					);
					
					$random = array();
					if ( ! empty( $tq_ids ) ) {
						$term_data = array();
						foreach ( $tq_ids as $key => $val ) {
							$term_data[ $val['term_id'] ][] = $val['question_id'];
						}
						
						// Remove parent categories if using tree structure
						if ( '' === $this->quiz_options->randon_category ) {
							foreach ( $categories_tree as $cat ) {
								if ( ! empty( $cat->children ) ) {
									unset( $term_data[ $cat->term_id ] );
								}
							}
						}
						
						// Randomly select questions from each category (randomness applied later)
						foreach ( $term_data as $tv ) {
							$random = array_merge( $random, array_slice( array_unique( $tv ), 0, intval( $this->quiz_options->question_per_category ) ) );
						}
					}
					$question_ids = array_unique( $random );
				}
			}
			// Mode 2: Advanced category question limit
			elseif ( 1 == $this->quiz_options->limit_category_checkbox 
				&& ! empty( maybe_unserialize( $this->quiz_options->select_category_question ) ) ) {
				
				$category_question_limit = maybe_unserialize( $this->quiz_options->select_category_question );
				$categories_data = QSM_Questions::get_quiz_categories( $quiz_id );
				$category_ids = ( isset( $categories_data['list'] ) ? array_keys( $categories_data['list'] ) : array() );
				
				if ( ! empty( $category_ids ) ) {
					$selected_questions = array();
					$exclude_ids = array( 0 );
					
					foreach ( $category_question_limit['category_select_key'] as $key => $category ) {
						if ( empty( $category ) || empty( $category_question_limit['question_limit_key'][ $key ] ) ) {
							continue;
						}
						
						$limit = intval( $category_question_limit['question_limit_key'][ $key ] );
						
						// Get questions from this category
						$category_questions = array();
						foreach ( $question_ids as $qid ) {
							// Check if question belongs to this category
							$has_term = $wpdb->get_var( $wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}mlw_question_terms 
								WHERE question_id = %d AND term_id = %d AND taxonomy = 'qsm_category'",
								$qid,
								$category
							) );
							
							if ( $has_term && ! in_array( $qid, $exclude_ids ) ) {
								$category_questions[] = $qid;
							}
						}
						
						// Take limited number from this category
						$category_questions = array_slice( $category_questions, 0, $limit );
						$selected_questions = array_merge( $selected_questions, $category_questions );
						$exclude_ids = array_merge( $exclude_ids, $category_questions );
					}
					
					$question_ids = array_unique( $selected_questions );
				}
			}
		} else {
			// No manual pages - apply filter hook for custom question selection
			$question_ids = apply_filters( 'qsm_load_questions_ids', array(), $quiz_id, $this->quiz_options );
			
			// If filter didn't provide question IDs, use all published questions
			if ( empty( $question_ids ) ) {
				$question_ids = array_keys( $questions );
			} else {
				// Filter to only include published questions from the filtered list
				$question_ids = array_intersect( $question_ids, array_keys( $questions ) );
			}
		}
		
		// Apply the main filter hook with same data as before
		$question_ids = apply_filters( 'qsm_load_questions_ids', $question_ids, $quiz_id, $this->quiz_options );
		
		// STEP 4: Apply randomness_order at the end
		if ( in_array( 'questions', $randomness_order ) || in_array( 'pages', $randomness_order ) ) {
			echo 'inside';
			// Check if we should use cookie to maintain order
			if ( isset( $_COOKIE[ 'question_ids_' . $quiz_id ] ) 
				&& empty( $this->quiz_options->question_per_category ) 
				&& empty( $this->quiz_options->limit_category_checkbox ) ) {
				
				$cookie_ids = sanitize_text_field( wp_unslash( $_COOKIE[ 'question_ids_' . $quiz_id ] ) );
				if ( preg_match( "/^\d+(,\d+)*$/", $cookie_ids ) ) {
					$cookie_question_ids = explode( ',', $cookie_ids );
					// Only use cookie IDs that are in our filtered list
					$question_ids = array_intersect( $cookie_question_ids, $question_ids );
				} else {
					// Invalid cookie format, shuffle normally
					$question_ids = QMNPluginHelper::qsm_shuffle_assoc( $question_ids );
				}
			} else {
				// Shuffle questions
				$question_ids = QMNPluginHelper::qsm_shuffle_assoc( $question_ids );
			}
		}
		
		// Reorder questions array based on final question_ids order
		$ordered_questions = array();
		foreach ( $question_ids as $qid ) {
			if ( isset( $questions[ $qid ] ) ) {
				$ordered_questions[ $qid ] = $questions[ $qid ];
			}
		}
		$questions = $ordered_questions;
		
		// Store cookie for randomized questions if needed
		if ( ( in_array( 'questions', $randomness_order ) || in_array( 'pages', $randomness_order ) )
			&& ! empty( $questions )
			&& ! isset( $_COOKIE[ 'question_ids_' . $quiz_id ] ) ) {
			echo 'inside1';
			
			$question_sql = implode( ',', array_unique( array_keys( $questions ) ) );
			?>
			<script>
				const d = new Date();
				d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000)); // Set cookie for 1 year
				let expires = "expires=" + d.toUTCString();
				document.cookie = "question_ids_<?php echo esc_js( $quiz_id ); ?>=" + "<?php echo esc_js( $question_sql ); ?>" + "; " + expires + "; path=/";
			</script>
			<?php
		}
		
		// Apply final filter hook with same data as before (convert to object format for compatibility)
		$questions_objects = array();
		foreach ( $questions as $question ) {
			$questions_objects[] = (object) $question;
		}
		$questions_objects = apply_filters( 'qsm_load_questions_filter', $questions_objects, $quiz_id, $this->quiz_options );
		
		// Convert back to array format
		$questions = array();
		foreach ( $questions_objects as $question ) {
			$question_array = (array) $question;
			$questions[ $question_array['question_id'] ] = $question_array;
		}
		
		// Update the main questions property
		$this->questions = $questions;
		
		// Create pages array for auto-pagination
		$question_ids = array_keys( $questions );
		
		if ( $questions_per_page > 0 ) {
			$this->pages = array_chunk( $question_ids, $questions_per_page );
		}
	}

	/**
	 * Render the complete quiz with new pagination system
	 */
	public function render( $shortcode_args = array() ) {
		global $mlwQuizMasterNext, $qmn_allowed_visit, $qmn_json_data, $qmn_total_questions, $qmn_all_questions_count, $mlw_qmn_section_count, $quiz_answer_random_ids;
		
		// Initialize global variables
		$qmn_total_questions = $qmn_all_questions_count = 0;
		$mlw_qmn_section_count = 0;
		
		// Debug: Log when render method is called
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'QSM Pagination Renderer: render() method called for quiz ID ' . $this->options->quiz_id );
		}
		
		// Prevent infinite recursion
		static $rendering = false;
		if ( $rendering ) {
			return '<div class="qsm-error">Error: Infinite recursion detected in new renderer</div>';
		}
		
		try {
			$rendering = true;
			
			// Debug: Check if we have required data
			if ( ! $this->quiz_options || ! $this->quiz_data ) {
				$rendering = false;
				return '<div class="qsm-error">Error: Missing quiz options or data</div>';
			}
			
			// Apply qmn_begin_quiz filter
			echo apply_filters( 'qmn_begin_quiz', '', $this->options, $this->quiz_data );
			$this->options = apply_filters( 'qmn_begin_quiz_options', $this->options, $this->quiz_data );
			
			if ( ! $qmn_allowed_visit ) {
				return;
			}

			// Setup error messages in qmn_json_data
			$qmn_json_data['error_messages'] = array(
				'email_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->email_error_text, "quiz_email_error_text-{$this->options->quiz_id}" ),
				'number_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->number_error_text, "quiz_number_error_text-{$this->options->quiz_id}" ),
				'incorrect_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->incorrect_error_text, "quiz_incorrect_error_text-{$this->options->quiz_id}" ),
				'empty_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->empty_error_text, "quiz_empty_error_text-{$this->options->quiz_id}" ),
				'url_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->url_error_text, "quiz_url_error_text-{$this->options->quiz_id}" ),
				'minlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->minlength_error_text, "quiz_minlength_error_text-{$this->options->quiz_id}" ),
				'maxlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->maxlength_error_text, "quiz_maxlength_error_text-{$this->options->quiz_id}" ),
				'recaptcha_error_text' => __( 'ReCaptcha is missing', 'quiz-master-next' ),
			);
			$qmn_json_data = apply_filters( 'qsm_json_error_message', $qmn_json_data, $this->options );
			
			// Enqueue additional scripts
			
			// Localize qsm_quiz script
			wp_localize_script(
				'qsm_quiz',
				'qmn_ajax_object',
				array(
					'site_url' => site_url(),
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'multicheckbox_limit_reach' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->options->quiz_limit_choice, "quiz_quiz_limit_choice-{$this->options->quiz_id}" ),
					'out_of_text' => esc_html__( ' out of ', 'quiz-master-next' ),
					'quiz_time_over' => esc_html__( 'Quiz time is over.', 'quiz-master-next' ),
					'security' => wp_create_nonce( 'qsm_submit_quiz' ),
					'start_date' => current_time( 'h:i:s A m/d/Y' ),
					'validate_process' => esc_html__( 'Validating file...', 'quiz-master-next' ),
					'remove_file' => esc_html__( 'Removing file...', 'quiz-master-next' ),
					'remove_file_success' => esc_html__( 'File removed successfully', 'quiz-master-next' ),
					'validate_success' => esc_html__( 'File validated successfully', 'quiz-master-next' ),
					'invalid_file_type' => esc_html__( 'Invalid file type. Allowed types: ', 'quiz-master-next' ),
					'invalid_file_size' => esc_html__( 'File is too large. Maximum size: ', 'quiz-master-next' ),
				)
			);
			
			// Enqueue MathJax if not disabled
			$disable_mathjax = isset( $this->options->disable_mathjax ) ? $this->options->disable_mathjax : '';
			if ( 1 != $disable_mathjax ) {
				$mathjax_url = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/MathJax.js?config=TeX-MML-AM_CHTML';
				wp_enqueue_script( 'math_jax', $mathjax_url, array(), '2.7.5', true );
				$default_MathJax_script = "if (typeof MathJax !== 'undefined') {\n\t\t\t\t\tMathJax.Hub.Config({\n\t\t\t\t\t\ttex2jax: {inlineMath: [['\\$','\\$'], ['\\\\(','\\\\)']]}\n\t\t\t\t\t});\n\t\t\t\t}";
				wp_add_inline_script( 'math_jax', $default_MathJax_script, 'before' );
			}
			
			// Initialize quiz answer random IDs if randomizing answers
			if ( in_array( 'answers', $this->randomness_order ) ) {
				$quiz_answer_random_ids = array();
			}

			// Hook before rendering to prevent recursion
			do_action( 'qsm_new_before_pagination_render', $this->options->quiz_id, $this->options, $this->quiz_data );
			
			// Display featured image for default theme
			$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme_path( $this->options->quiz_id );
			if ( 'default' == $saved_quiz_theme ) {
				$featured_image = get_option( "quiz_featured_image_{$this->options->quiz_id}" );
				$qsm_global_settings = (array) get_option( 'qmn-settings' );
				$qsm_preloader_setting = isset( $qsm_global_settings['enable_preloader'] ) ? $qsm_global_settings['enable_preloader'] : '';
				
				if ( isset( $qsm_preloader_setting ) && $qsm_preloader_setting > 0 && ! empty( $featured_image ) ) {
					echo '<link rel="preload" href="' . esc_url( $featured_image ) . '" as="image">';
				}
				
				if ( "" != $featured_image ) {
					echo '<img class="qsm-quiz-default-feature-image" src="' . esc_url( $featured_image ) . '" alt="' . esc_attr__( 'Featured Image', 'quiz-master-next' ) . '" />';
				}
			}
			
			// Hook before form
			echo apply_filters( 'qsm_display_before_form', '', $this->options, $this->quiz_data );
			
			// Start form - don't render form here as it's handled by main quiz manager
			echo $this->render_form_start();
			
			// Error message container
			echo '<div id="mlw_error_message" class="qsm-error-message qmn_error_message_section"></div>';
			echo '<span id="mlw_top_of_quiz"></span>';
			
			// Hook after form begin
			echo apply_filters( 'qmn_begin_quiz_form', '', $this->options, $this->quiz_data );

			// Render quiz timer
			// echo $this->render_quiz_timer();
			
			// Render first page if enabled
			if ( $this->should_show_first_page() ) {
				echo $this->render_first_page();
			}
			
			// Hook before questions
			echo apply_filters( 'qmn_begin_quiz_questions', '', $this->options, $this->quiz_data );
			
			// Render question pages
			$this->render_quiz_pages( $shortcode_args );
			
			// Hook before comment section
			echo apply_filters( 'qmn_before_comment_section', '', $this->options, $this->quiz_data );
			
			// Render last page if enabled
			if ( $this->should_show_last_page() ) {
				echo $this->render_last_page();
			}
			
			if ( $this->quiz_options->pagination == 0 ) {
				do_action( 'qsm_after_all_section' );
			} else {
				do_action( 'mlw_qmn_end_quiz_section' );
			}
			
			// Hook after comment section
			echo apply_filters( 'qmn_after_comment_section', '', $this->options, $this->quiz_data );
			
			// Hook before error message
			echo apply_filters( 'qmn_before_error_message', '', $this->options, $this->quiz_data );
		
			// Bottom error message
			echo '<div id="mlw_error_message_bottom" class="qsm-error-message qmn_error_message_section"></div>';
		
			// Hook before end quiz form
			echo apply_filters( 'qmn_end_quiz_form', '', $this->options, $this->quiz_data );
			do_action( 'qsm_before_end_quiz_form', $this->options, $this->quiz_data, array() );
			
			// Add hidden inputs
			echo $this->render_hidden_inputs();

			// End form here as it's handled by main quiz manager
			echo $this->render_form_end();
			
			// Hook after end quiz form
			do_action( 'qsm_after_end_quiz_form', $this->options, $this->quiz_data, array() );
			
			// Render navigation
			
			if ( apply_filters( 'qsm_should_render_default_navigation', true, $this->options, $this->quiz_data ) ) {
    			echo $this->render_navigation();
			}
			
			// Add JavaScript data
			$this->render_javascript_data();
			
			// Hook after rendering to prevent recursion
			do_action( 'qsm_new_after_pagination_render', $this->options->quiz_id, $this->options, $this->quiz_data );
			
			$rendering = false;
			
		} catch ( Exception $e ) {
			$rendering = false;
			
			// Debug output
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				return '<div class="qsm-error">Rendering Error: ' . esc_html( $e->getMessage() ) . '</div>';
			}
			return '<div class="qsm-error">An error occurred while rendering the quiz.</div>';
		}
	}

	/**
	 * Render quiz timer
	 *
	 * @return string
	 */
	private function render_quiz_timer() {
		$output = '';

		// Only render timer if timer limit is set
		$timer_limit = isset( $this->quiz_options->timer_limit ) ? intval( $this->quiz_options->timer_limit ) : 0;
		if ( $timer_limit > 0 ) {
		?>
		<div class="qsm-timer qsm-quiz-timer-<?php echo esc_attr( $this->options->quiz_id ); ?>">
			<div class="mlw_qmn_timer qsm-timer-display">00:00:00</div>
		</div>
		<?php
		}
	}

	/**
	 * Check if first page should be shown
	 *
	 * @return bool
	 */
	private function should_show_first_page() {
		$disable_first_page = isset( $this->quiz_options->disable_first_page ) ? intval( $this->quiz_options->disable_first_page ) : 0;
		$message_before = isset( $this->options->message_before ) ? $this->options->message_before : '';
		$contact_info_location = isset( $this->options->contact_info_location ) ? intval( $this->options->contact_info_location ) : 0;
		
		// Show first page if:
		// 1. First page is not disabled
		// 2. There's a message_before OR contact fields are set to show on first page
		// Note: Removed the page count check - welcome page should show regardless of question page count
		return ( 
			1 !== $disable_first_page && 
			( ! empty( $message_before ) || ( 0 == $contact_info_location && $this->contact_fields ) )
		);
	}

	/**
	 * Check if last page should be shown
	 *
	 * @return bool
	 */
	private function should_show_last_page() {
		// Check if contact fields are enabled and should be shown on last page
		$is_contact_fields_enabled = false;
		if ( is_array( $this->contact_fields ) ) {
			$enabled_fields = array_filter( $this->contact_fields, function( $field ) {
				return isset( $field['enable'] ) && ( 'true' === $field['enable'] || true === $field['enable'] );
			});
			$is_contact_fields_enabled = ! empty( $enabled_fields );
		}
		
		// Check if message after template exists
		$message_end_template = '';
		if ( isset( $this->quiz_texts->message_end_template ) ) {
			$message_end_template = $this->quiz_texts->message_end_template;
		} elseif ( isset( $this->options->message_end_template ) ) {
			$message_end_template = $this->options->message_end_template;
		}
		
		// Check contact info location (1 = show on last page)
		$contact_info_location = isset( $this->quiz_options->contact_info_location ) ? intval( $this->quiz_options->contact_info_location ) : 0;
		$show_contact_on_last = ( 1 === $contact_info_location && $is_contact_fields_enabled );
		
		return ( ! empty( $message_end_template ) || $show_contact_on_last );
	}

	/**
	 * Render first page using template
	 *
	 * @return string
	 */
	private function render_first_page() {
		global $mlwQuizMasterNext, $qmn_json_data;
		$qmn_json_data['first_page'] = true;
		$animation_effect = isset( $this->options->quiz_animation ) && '' !== $this->options->quiz_animation ? ' animated ' . $this->options->quiz_animation : '';
		
		$message_before = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			htmlspecialchars_decode( $this->options->message_before, ENT_QUOTES ), 
			"quiz_message_before-{$this->options->quiz_id}" 
		);
		$message_before = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_before ), $this->quiz_data );

		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'object_class_qsm_render_pagination' => $this,
			'animation_effect' => $animation_effect,
			'message_before' => $message_before,
			'show_contact_fields' => ( 0 == $this->quiz_options->contact_info_location && $this->contact_fields ),
		);
		
		return qsm_new_get_template_part( 'pages/page-first', $args );
	}

	/**
	 * Render question pages
	 *
	 * @return string
	 */
	private function render_quiz_pages( $shortcode_args = array() ) {
		global $qmn_total_questions;
		$output = '';
		$animation_effect = isset( $this->options->quiz_animation ) && '' !== $this->options->quiz_animation ? ' animated ' . $this->options->quiz_animation : '';
		
		// Check if we have pages
		if ( empty( $this->pages ) ) {
			return '<div class="qsm-error"> ' . esc_html__( 'No questions found for this quiz.', 'quiz-master-next' ) . '</div>';
		}
		
		// Apply filters to pages
		$pages = apply_filters( 'qsm_display_pages', $this->pages, $this->options->quiz_id, $this->options );
		
		// Check if lazy loading is enabled (default: true)
		$enable_lazy_loading = apply_filters( 'qsm_enable_lazy_loading', true, $this->options->quiz_id );
		
		// Determine how many pages to render initially (first page + first 2 question pages)
		$is_display_first_page = $this->should_show_first_page();
		$initial_pages_to_render = $is_display_first_page ? 3 : 2; // If first page exists, render 3 total (first + 2 question pages), else 2
		
		$total_pages_count = count( $pages );
		$pages_count = 1;
		foreach ( $pages as $key => $page ) {
			$qpage        = ( isset( $this->qpages[ $key ] ) ? $this->qpages[ $key ] : array() );
			$qpage_id     = ( isset( $this->qpage['id'] ) ? $this->qpage['id'] : $key );
			$page_key     = ( isset( $this->qpage['pagekey'] ) ? $this->qpage['pagekey'] : $key );
			$hide_prevbtn = ( isset( $this->qpage['hide_prevbtn'] ) ? $this->qpage['hide_prevbtn'] : 0 );
			$display_current_page = 'none';
			// Show first question page if:
			// 1. It's the first page AND there's no welcome page
			// 2. OR it's page 2 AND there IS a welcome page (handled by JS)
			if ( 1 == $pages_count && ! $is_display_first_page ) {
				$display_current_page = 'block';
			}
			
			// Determine if this page should be lazy loaded
			$should_lazy_load = $enable_lazy_loading && ( $pages_count > $initial_pages_to_render );
			$lazy_load_class = $should_lazy_load ? 'qsm-lazy-load-page' : 'qsm-loaded-page';
			$question_ids_csv = implode( ',', $page );
			?>
			<section class="qsm-page qsm-question-page <?php echo esc_attr( $lazy_load_class ); ?> qsm-page-<?php echo esc_attr( $pages_count ); ?> <?php echo esc_attr( $animation_effect ); ?>" 
			data-pid="<?php echo esc_attr( $pages_count ); ?>" 
			data-apid="<?php echo esc_attr( $pages_count ); ?>" 
			data-qpid="<?php echo esc_attr( $pages_count ); ?>" 
			data-page="<?php echo esc_attr( $pages_count ); ?>" 
			data-lazy-load="<?php echo esc_attr( $should_lazy_load ? '1' : '0' ); ?>"
			data-question-ids="<?php echo esc_attr( $question_ids_csv ); ?>"
			data-question-start-number="<?php echo esc_attr( $qmn_total_questions + 1 ); ?>"
			style="display: <?php echo esc_attr( $display_current_page ); ?>;">
			<?php
			
			// Hook before page
			do_action( 'qsm_action_before_page', $qpage_id, $qpage );
			
			// Only render questions if not lazy loading, or if within initial page limit
			if ( ! $should_lazy_load ) {
				// Render questions in this page
				foreach ( $page as $question_id ) {
					if ( ! isset( $this->questions[ $question_id ] ) ) {
						continue;
					}
					
					$qmn_total_questions += 1;
					$question = $this->questions[ $question_id ];
					?>
					<div class="quiz_section qsm-question-wrapper qsm-question-wrapper-<?php echo esc_attr( $question_id ); ?> question-section-id-<?php echo esc_attr( $question_id ); ?> question-type-<?php echo esc_attr( $question['question_type_new'] ); ?>" data-qid="<?php echo esc_attr( $question_id ); ?>">
						<span class='mlw_qmn_question_number'><?php echo esc_html( $qmn_total_questions ); ?>.&nbsp;</span>
						<?php
						if ( $this->quiz_options->show_category_on_front ) {
							$categories = QSM_Questions::get_question_categories( $question_id );
							if ( ! empty( $categories['category_name'] ) ) {
								$cat_name = implode( ',', $categories['category_name'] );
								?>
								<div class="quiz-cat"><?php echo esc_html( $cat_name ); ?></div>
								<?php
							}
						}
						echo $this->display_question( $question['question_type_new'], $question_id, $this->options, $shortcode_args );
						do_action('qsm_after_question', $question);
						?>
					</div>
					<?php
				}
			} else {
				// Lazy load page - add placeholder and loading indicator
				?>
				<div class="qsm-lazy-load-placeholder" style="text-align: center; padding: 20px;">
					<div class="qsm-lazy-load-spinner" style="display: none;">
						<span class="qsm-spinner"></span>
						<p><?php esc_html_e( 'Loading questions...', 'quiz-master-next' ); ?></p>
					</div>
				</div>
				<?php
				// Increment question count for lazy loaded pages
				$qmn_total_questions += count( $page );
			}
			
			// Show page count if enabled
			?>
			<span class="pages_count" style="display:none;">
			<?php
			$text_c = $pages_count . esc_html__( ' out of ', 'quiz-master-next' ) . $total_pages_count;
			echo apply_filters( 'qsm_total_pages_count', $text_c, $pages_count, $total_pages_count );
			?>
			</span>
			
			<?php
			$page_args = array(
				'current_page' => $pages_count,
				'total_pages'  => $total_pages_count,
				'quiz_id'      => $this->options->quiz_id,
			);
			if ( apply_filters( 'qsm_should_render_default_pages_count', true, $page_args,$this->options, $this->quiz_data ) ) {
				echo $this->render_page_count($page_args);
			}
			

			do_action( 'qsm_new_action_after_page', $pages_count, $page );

			?>
			</section>
			<?php
			$pages_count++;
		}
		return;
	}
	public function render_page_count( $page_args, $builder_args = array() ) {

		$page_args = is_array( $page_args ) ? $page_args : array();

		// Merge page args + builder args into flat $args
		$args = array_merge( $page_args, $builder_args );

		return qsm_new_get_template_part( 'pagination/page-count', $args );
	}



	/**
	 * Display a single question
	 *
	 * @param string $question_type Question type
	 * @param int    $question_id   Question ID
	 * @param object $quiz_options  Quiz options
	 * @return string
	 */
	private function display_question( $question_type, $question_id, $quiz_options, $shortcode_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Prepare question data for template
		$question_data = $this->questions[ $question_id ];
		$question_settings = maybe_unserialize( $question_data['question_settings'] );
		
		$answer_array = maybe_unserialize( $question_data['answer_array'] );
		
		if ( in_array( 'answers', $this->randomness_order ) ) {
			$answer_array = QMNPluginHelper::qsm_shuffle_assoc($answer_array);
			global $quiz_answer_random_ids;
			$answer_ids = array_keys( $answer_array );
			$quiz_answer_random_ids[ $question_id ] = $answer_ids;
		}
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'id' => $question_id,
			'class_object' => $this,
			'question' => $question_data,
			'answers' => $answer_array,
			'question_settings' => is_array( $question_settings ) ? $question_settings : array(),
			'quiz_options' => $this->quiz_options,
		);

		// Use the new question template function
		$question_template = qsm_get_question_template( $question_type, $args,$shortcode_args );
		if ( $question_template == false ) {
			$question_type = array_filter($mlwQuizMasterNext->pluginHelper->question_types, function($item) use ($question_type) {
				return $item['slug'] == $question_type;
			});
			$question_type = array_shift($question_type);
			call_user_func($question_type['display'], intval($question_id), $question_data['question_name'], $answer_array);
		}
		return $question_template;
	}

	/**
	 * Get question template
	 *
	 * @param string $question_type Question type
	 * @param array  $args          Question arguments
	 * @return string
	 */
	public function display_question_title( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
		$question_title = $question;
		global $wp_embed, $mlwQuizMasterNext;
		$question_title    = $wp_embed->run_shortcode( $question_title );
		$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
		$title_extra_classes = '';
		if ( 'polar' === $question_type ) {
			$title_extra_classes .= ' question-type-polar-s';
		}
		if ( 'fill_in_blank' === $question_type ) {
			$title_extra_classes .= ' qsm-align-fill-in-blanks';
		}
		$qmn_quiz_options = $this->quiz_options;
		$deselect_answer  = '';
		if ( isset( $qmn_quiz_options->enable_deselect_option ) && 1 == $qmn_quiz_options->enable_deselect_option && ( 'multiple_choice' === $question_type || 'horizontal_multiple_choice' === $question_type ) ) {
			$default_texts = QMNPluginHelper::get_default_texts();
			$deselect_answer_text = ! empty( $qmn_quiz_options->deselect_answer_text ) ? $qmn_quiz_options->deselect_answer_text : $default_texts['deselect_answer_text'];
			$deselect_answer = '<a href="javascript:void(0)" class="qsm-deselect-answer">'. $mlwQuizMasterNext->pluginHelper->qsm_language_support( $deselect_answer_text, "deselect_answer_text-{$this->options->quiz_id}" ) .'</a>';
		}
		do_action('qsm_question_title_function_before',$question, $question_type, $new_question_title, $question_id );
		if ( '' !== $new_question_title ) {
			$new_question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ), "Question-{$question_id}", "QSM Questions");
			$new_question_title = apply_filters( 'qsm_question_title_before', $new_question_title, $question_type, $question_id );
			if ( in_array( intval( get_question_type( $question_id ) ), [ 12, 7, 3, 5 ], true ) ) {
			?>
			<div class='mlw_qmn_new_question'><label class="qsm-question-title-label" for="question<?php echo esc_attr( $question_id ); ?>"><?php echo esc_html( $new_question_title ); ?> </label></div>
			<?php
			} else {
			?>
			<div class='mlw_qmn_new_question'><?php echo esc_html( $new_question_title ); ?> </div>
			<?php
			}
			$title_extra_classes .= ' qsm_remove_bold';
		}
		if ( $question_id ) {
			$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
			if ( $featureImageID ) {
				$qsm_global_settings = (array) get_option( 'qmn-settings' );
				$qsm_preloader_setting = isset($qsm_global_settings['enable_preloader']) ? $qsm_global_settings['enable_preloader'] : '';
				if ( $qsm_preloader_setting > 0 ) {
					$featured_image_url = wp_get_attachment_image_url( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) );
					echo '<link rel="preload" href="' . esc_url($featured_image_url) . '" as="image">';
				}
				?>
				<div class="qsm-featured-image"><?php echo wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ); ?></div>
				<?php
			}
		}
		if ( ! empty( $question_title ) && ! in_array( intval( get_question_type( $question_id ) ), [ 2, 14 ], true ) ) {
			$question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( html_entity_decode( $question_title, ENT_HTML5 ), ENT_QUOTES ), "question-description-{$question_id}", "QSM Questions" );
		}
		?>
		<div class='mlw_qmn_question <?php echo esc_attr( $title_extra_classes ); ?>' >
		<?php do_action('qsm_before_question_title',$question, $question_type, $new_question_title, $question_id );
			$allow_html = wp_kses_allowed_html('post');
			$allow_html['input']['autocomplete'] = 1;
			$allow_html['input']['name'] = 1;
			$allow_html['input']['class'] = 1;
			$allow_html['input']['id'] = 1;
			$allow_html = apply_filters( 'qsm_allow_html_question_title_after', $allow_html, $question_id );
			$pattern = '/<code>(.*?)<\/code>/s';
			$question_description = preg_replace_callback($pattern, function ( $matches ) {
				return preg_replace([ '/<(?!(\/?code|br)[ >])/', '/>(?!(\/?code|br)[ \/>])/' ], [ '&lt;', '&gt;' ], $matches[0]);
			}, $question_title);
			$question_description = str_replace([ 'code&gt;', 'br /&gt;' ],[ 'code/>', 'br />' ], $question_description );
			$question_description = apply_filters( 'qsm_question_description_before', $question_description, $question_type, $question_id );
		?>
		<p><?php echo do_shortcode( wp_kses( $question_description . $deselect_answer, $allow_html ) ); ?></p>
		</div>
		<?php
		do_action('qsm_question_title_func_after',$question, $question_type, $new_question_title, $question_id );
	}

	/**
	 * Render last page using template
	 *
	 * @return string
	 */
	private function render_last_page() {
		global $mlwQuizMasterNext;
		
		$message_after = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			htmlspecialchars_decode( $this->quiz_texts->message_end_template, ENT_QUOTES ), 
			"quiz_message_end_template-{$this->options->quiz_id}" 
		);
		$message_after = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_after ), $this->quiz_data );
		
		// Check if contact fields should be shown on last page
		$contact_info_location = isset( $this->quiz_options->contact_info_location ) ? intval( $this->quiz_options->contact_info_location ) : 0;
		$has_enabled_contact_fields = false;
		if ( is_array( $this->contact_fields ) ) {
			$enabled_fields = array_filter( $this->contact_fields, function( $field ) {
				return isset( $field['enable'] ) && ( 'true' === $field['enable'] || true === $field['enable'] );
			});
			$has_enabled_contact_fields = ! empty( $enabled_fields );
		}
		
		$show_contact_fields = ( 1 === $contact_info_location && $has_enabled_contact_fields );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'object_class_qsm_render_pagination' => $this,
			'message_after' => $message_after,
			'show_contact_fields' => $show_contact_fields,
		);
		
		return qsm_new_get_template_part( 'pages/page-last', $args );
	}

	/**
	 * Render navigation using template
	 *
	 * @return string
	 */
	public function render_navigation() {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		
		return qsm_new_get_template_part( 'pagination', $args );
	}
	public function render_start_btn( $builder_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		$args = array_merge($args,$builder_args);
		
		return qsm_new_get_template_part( 'pagination/start-btn', $args );
	}
	public function render_prev_btn( $builder_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		$args = array_merge($args,$builder_args);
		return qsm_new_get_template_part( 'pagination/prev-btn', $args );
	}
	public function render_next_btn( $builder_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		$args = array_merge($args,$builder_args);
		return qsm_new_get_template_part( 'pagination/next-btn', $args );
	}
	public function render_submit_btn( $builder_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		$args = array_merge($args,$builder_args);
		return qsm_new_get_template_part( 'pagination/submit', $args );
	}
	public function render_progress( $builder_args = array() ) {
		global $mlwQuizMasterNext;
		
		// Ensure quiz_texts is properly initialized
		if ( ! is_object( $this->quiz_texts ) ) {
			$this->quiz_texts = (object) array();
		}
		
		
		// Prepare button texts with fallbacks
		$start_button_text = ! empty( $this->quiz_texts->start_quiz_survey_text ) ? $this->quiz_texts->start_quiz_survey_text : ( ! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Start' );
		
		$args = array(
			'quiz_id' => $this->options->quiz_id,
			'options' => $this->options,
			'previous_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->previous_button_text ) ? $this->quiz_texts->previous_button_text : 'Previous', 
				"quiz_previous_button_text-{$this->options->quiz_id}" 
			),
			'next_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->next_button_text ) ? $this->quiz_texts->next_button_text : 'Next', 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'start_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$start_button_text, 
				"quiz_next_button_text-{$this->options->quiz_id}" 
			),
			'submit_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				! empty( $this->quiz_texts->submit_button_text ) ? $this->quiz_texts->submit_button_text : 'Submit', 
				"quiz_submit_button_text-{$this->options->quiz_id}" 
			),
		);
		$args = array_merge($args,$builder_args);
		return qsm_new_get_template_part( 'pagination/progress-bar', $args );
	}

	/**
	 * Render form start
	 *
	 * @return string
	 */
	private function render_form_start() {
		$form_action = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		$output = '<form name="quizForm' . esc_attr( $this->options->quiz_id ) . '" ';
		$output .= 'id="quizForm' . esc_attr( $this->options->quiz_id ) . '" ';
		$output .= 'action="' . esc_url( $form_action ) . '" ';
		$output .= 'method="POST" class="qsm-quiz-form qmn_quiz_form mlw_quiz_form" novalidate enctype="multipart/form-data">';
		
		return $output;
	}

	/**
	 * Render form end
	 *
	 * @return string
	 */
	private function render_form_end() {
		return '</form>';
	}

	/**
	 * Render hidden inputs
	 *
	 * @return string
	 */
	private function render_hidden_inputs() {
		global $mlwQuizMasterNext, $qmn_total_questions, $qmn_all_questions_count, $quiz_answer_random_ids;
		
		$output = '';
		
		// Hidden questions field (for legacy compatibility)
		$output .= '<input type="hidden" name="qsm_hidden_questions" id="qsm_hidden_questions" value="">';
		
		// Question list
		$question_list = array();
		$total_questions = 0;
		foreach ( $this->pages as $page ) {
			foreach ( $page as $question_id ) {
				$question_list[] = $question_id;
				$total_questions++;
			}
		}

		if ( $this->quiz_options->pagination <= 0 ) {
			?>
			<script>
				const d = new Date();
				d.setTime(d.getTime() + (365*24*60*60*1000));
				let expires = "expires="+ d.toUTCString();
				document.cookie = "question_ids_<?php echo esc_attr( $this->options->quiz_id ); ?> = <?php echo esc_attr( implode( ',', $question_list ) ); ?>; "+expires+"; path=/";
			</script>
			<?php
		}

		$output .= '<input type="hidden" name="qmn_question_list" value="' . esc_attr( implode( 'Q', $question_list ) ) . '" />';
		
		// Security and unique identifiers
		$output .= '<input type="hidden" name="qsm_nonce" id="qsm_nonce_' . esc_attr($this->options->quiz_id) . '" value="' . esc_attr( wp_create_nonce( 'qsm_submit_quiz_' . intval( $this->options->quiz_id ) ) ) . '">';
		$output .= '<input type="hidden" name="qsm_unique_key" id="qsm_unique_key_' . esc_attr($this->options->quiz_id) . '" value="' . esc_attr( uniqid() ) . '">';
		
		// Quiz identification
		$output .= '<input type="hidden" class="qmn_quiz_id" name="qmn_quiz_id" id="qmn_quiz_id" value="' . esc_attr( $this->quiz_data['quiz_id'] ) . '" />';
		$output .= '<input type="hidden" name="complete_quiz" value="confirmation" />';
		
		// Question counts (using global variables for legacy compatibility)
		$output .= '<input type="hidden" name="qmn_all_questions_count" id="qmn_all_questions_count" value="' . esc_attr( $qmn_all_questions_count ) . '" />';
		$output .= '<input type="hidden" name="total_questions" id="total_questions" value="' . esc_attr( $qmn_total_questions ) . '" />';
		
		// Timer fields
		$output .= '<input type="hidden" name="timer" id="timer" value="0" />';
		$output .= '<input type="hidden" name="timer_ms" id="timer_ms" value="0"/>';
		
		// Answer randomization IDs (if randomizing answers)
		if ( in_array( 'answers', $this->randomness_order ) && ! empty( $quiz_answer_random_ids ) ) {
			$output .= '<input type="hidden" name="quiz_answer_random_ids" id="quiz_answer_random_ids_' . esc_attr( $this->quiz_data['quiz_id'] ) . '" value="' . esc_attr( maybe_serialize( $quiz_answer_random_ids ) ) . '" />';
		}
		
		// Payment ID (if present in GET parameters)
		if ( isset( $_GET['payment_id'] ) && '' !== $_GET['payment_id'] ) {
			$payment_id = sanitize_text_field( wp_unslash( $_GET['payment_id'] ) );
			$output .= '<input type="hidden" name="main_payment_id" value="' . esc_attr( $payment_id ) . '" />';
		}
		
		return $output;
	}
	
	/**
	 * Render JavaScript data
	 * Enhanced to match $qmn_json_data structure from legacy system
	 *
	 * @return string
	 */
	/**
	 * Called from shortcode — prepares & outputs QSM JS data.
	 */
	public function render_javascript_data() {
		global $mlwQuizMasterNext;
		
		// Ensure mlwQuizMasterNext is available
		if ( ! $mlwQuizMasterNext || ! isset( $mlwQuizMasterNext->pluginHelper ) ) {
			return '<script>console.warn("QSM: mlwQuizMasterNext not available for quiz data localization");</script>';
		}
		
		// Get quiz settings
		$quiz_settings = maybe_unserialize( $this->options->quiz_settings );
		$quiz_options = maybe_unserialize( $quiz_settings['quiz_options'] ?? '[]' );
		
		// Get error messages from quiz texts with language support
		$error_messages = array(
			'email_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->email_error_text ?? 'Please enter a valid email address.', 
				"quiz_email_error_text-{$this->options->quiz_id}" 
			),
			'url_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->url_error_text ?? 'Please enter a valid URL.', 
				"quiz_url_error_text-{$this->options->quiz_id}" 
			),
			'empty_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->empty_error_text ?? 'This field is required.', 
				"quiz_empty_error_text-{$this->options->quiz_id}" 
			),
			'number_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->number_error_text ?? 'Please enter a valid number.', 
				"quiz_number_error_text-{$this->options->quiz_id}" 
			),
			'incorrect_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->incorrect_error_text ?? 'Incorrect answer.', 
				"quiz_incorrect_error_text-{$this->options->quiz_id}" 
			),
			'minlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->minlength_error_text ?? 'Minimum %minlength% characters required.', 
				"quiz_minlength_error_text-{$this->options->quiz_id}" 
			),
			'maxlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
				$this->options->maxlength_error_text ?? 'Maximum %maxlength% characters allowed.', 
				"quiz_maxlength_error_text-{$this->options->quiz_id}" 
			),
			'recaptcha_error_text' => __( 'ReCaptcha is missing', 'quiz-master-next' ),
		);
		
		// Get text messages with language support
		$correct_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			$this->options->quick_result_correct_answer_text ?? 'Correct!', 
			"quiz_quick_result_correct_answer_text-{$this->options->quiz_id}" 
		);
		$wrong_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			$this->options->quick_result_wrong_answer_text ?? 'Incorrect!', 
			"quiz_quick_result_wrong_answer_text-{$this->options->quiz_id}" 
		);
		$quiz_processing_message = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			$this->options->quiz_processing_message ?? 'Processing...', 
			"quiz_quiz_processing_message-{$this->options->quiz_id}" 
		);
		$quiz_limit_choice = $mlwQuizMasterNext->pluginHelper->qsm_language_support( 
			$this->options->quiz_limit_choice ?? 'You have reached the limit of choices.', 
			"quiz_quiz_limit_choice-{$this->options->quiz_id}" 
		);

		// Build comprehensive quiz data matching $qmn_json_data structure
		$quiz_data = array(
			// Core quiz info
			'quiz_id' => $this->options->quiz_id,
			'quiz_name' => $this->options->quiz_name,
			
			// Quiz behavior settings
			'disable_answer' => $this->quiz_options->disable_answer_onselect ?? 0,
			'ajax_show_correct' => $this->quiz_options->ajax_show_correct ?? 0,
			'progress_bar' => $this->quiz_options->progress_bar ?? 0,
			'contact_info_location' => $this->quiz_options->contact_info_location ?? 0,
			'skip_validation_time_expire' => $this->quiz_options->skip_validation_time_expire ?? 0,
			'timer_limit' => intval( $this->options->timer_limit ?? 0 ),
			'disable_scroll_next_previous_click' => $this->quiz_options->disable_scroll_next_previous_click ?? 0,
			'disable_scroll_on_result' => $this->quiz_options->disable_scroll_on_result ?? 0,
			'disable_first_page' => $this->quiz_options->disable_first_page ?? 0,
			'enable_result_after_timer_end' => $this->quiz_options->enable_result_after_timer_end ?? 0,
			'enable_quick_result_mc' => $this->quiz_options->enable_quick_result_mc ?? 0,
			'end_quiz_if_wrong' => $this->quiz_options->end_quiz_if_wrong ?? 0,
			'form_disable_autofill' => $this->quiz_options->form_disable_autofill ?? 0,
			'disable_mathjax' => $this->quiz_options->disable_mathjax ?? 0,
			'enable_quick_correct_answer_info' => $this->quiz_options->enable_quick_correct_answer_info ?? 0,
			'not_allow_after_expired_time' => $this->quiz_options->not_allow_after_expired_time ?? 0,
			'prevent_reload' => $this->quiz_options->prevent_reload ?? 0,
			'limit_email_based_submission' => $this->quiz_options->limit_email_based_submission ?? 0,
			'total_user_tries' => $this->quiz_options->total_user_tries ?? 0,
			'randomness_order' => $this->randomness_order,
			
			// Text messages
			'quick_result_correct_answer_text' => $correct_answer_text,
			'quick_result_wrong_answer_text' => $wrong_answer_text,
			'quiz_processing_message' => $quiz_processing_message,
			'quiz_limit_choice' => $quiz_limit_choice,
			
			// Time and scheduling
			'scheduled_time_end' => strtotime( $this->quiz_options->scheduled_time_end ?? 'now' ),
			
			// User context
			'is_logged_in' => is_user_logged_in(),
			
			// Pages data
			'qpages' => $this->get_quiz_properties('qpages'),
			'first_page' => $this->should_show_first_page(),
			
			// Questions data
			'question_list' => $this->get_questions_data(),
			
			// Error messages
			'error_messages' => $error_messages,
			
			// System data
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'qsm_quiz_nonce_' . $this->options->quiz_id ),
			'lazy_load_nonce' => wp_create_nonce( 'qsm_lazy_load_' . $this->options->quiz_id ),
			
			// QSM-11 specific enhancements
			'template_system' => 'qsm-11',
			'version' => '2.0',
			'scroll_to_top' => true,
			'timer_auto_start' => false,
			'render_type' => '11',
		);
		
		// Apply filters to allow customization (matching legacy filter)
		$quiz_data = apply_filters( 'qmn_json_data', $quiz_data, $this->options, $this->quiz_data, $this->shortcode_args );
		$correct_answer_logic = ! empty( $this->quiz_options->correct_answer_logic ) ? $this->quiz_options->correct_answer_logic : '';
		$encryption['correct_answer_logic'] = $correct_answer_logic;
		$question_ids = array();
		if ( ! empty( $this->pages ) ) {
			foreach ( $this->pages as $item ) {
				$question_ids = array_merge($question_ids, $item);
			}
		}
		$questions_settings = array();
		foreach ( $this->questions as $key => $question ) {
			if ( ! in_array($question['question_id'], $question_ids) ) {
				continue;
			}
			$unserialized_settings = maybe_unserialize( $question['question_settings'] );
			$question_type_new = $question['question_type_new'];
			if ( 11 == $question_type_new ) {
				$questions_settings[ $question['question_id'] ]['file_upload_type'] = $unserialized_settings['file_upload_type'];
				$questions_settings[ $question['question_id'] ]['file_upload_limit'] = $unserialized_settings['file_upload_limit'];
			}
			$encryption[ $question['question_id'] ]['question_type_new'] = $question_type_new;
			$encryption[ $question['question_id'] ]['answer_array'] = maybe_unserialize( $question['answer_array'] );
			$encryption[ $question['question_id'] ]['settings'] = $unserialized_settings;
			$encryption[ $question['question_id'] ]['correct_info_text'] = isset( $question['question_answer_info'] ) ? html_entity_decode( $question['question_answer_info'] ) : '';
			$encryption[ $question['question_id'] ]['correct_info_text'] = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $encryption[ $question['question_id'] ]['correct_info_text'], "correctanswerinfo-{$question['question_id']}" );
		}
		
		$quiz_data['questions_settings'] = $questions_settings;
		
		// Output JavaScript with both legacy and new variable names for compatibility
		// $output = '<script type="text/javascript">';
		// $output .= 'if (typeof window.qsmQuizData === "undefined") { window.qsmQuizData = {}; }';
		// $output .= 'if (typeof window.qmn_quiz_data === "undefined") { window.qmn_quiz_data = {}; }';
		// $output .= 'window.qsmQuizData[' . intval( $this->options->quiz_id ) . '] = ' . wp_json_encode( $quiz_data ) . ';';
		// $output .= 'window.qmn_quiz_data[' . intval( $this->options->quiz_id ) . '] = ' . wp_json_encode( $quiz_data ) . ';';
	
		// // Add encryption data if available
		// if ( ! empty( $encryption ) ) {
		// 	$output .= '
		// 	if (typeof encryptionKey === "undefined") {
		// 		var encryptionKey = {};
		// 	}
		// 	if (typeof data === "undefined") {
		// 		var data = {};
		// 	}
		// 	if (typeof jsonString === "undefined") {
		// 		var jsonString = {};
		// 	}
		// 	if (typeof encryptedData === "undefined") {
		// 		var encryptedData = {};
		// 	}
		// 	encryptionKey[' . $quiz_data['quiz_id'] . '] = "' . hash('sha256', time() . $quiz_data['quiz_id']) . '";

		// 	data[' . $quiz_data['quiz_id'] . '] = ' . wp_json_encode($encryption) . ';
		// 	jsonString[' . $quiz_data['quiz_id'] . '] = JSON.stringify(data[' . $quiz_data['quiz_id'] . ']);
		// 	encryptedData[' . $quiz_data['quiz_id'] . '] = CryptoJS.AES.encrypt(jsonString[' . $quiz_data['quiz_id'] . '], encryptionKey[' . $quiz_data['quiz_id'] . ']).toString();';
		// }
	
		// $output .= '</script>';
		$quiz_id     = intval( $this->options->quiz_id );
		$quiz_data   = $quiz_data;
		$encryption  = $encryption;
		$payload = [
        'quiz_id'    => $quiz_id,
        'quiz_data'  => $quiz_data,
        'encryption' => $encryption,
    	];
		// Below Json is used in Tatsu iframe
		if( isset($_GET['tatsu']) && $_GET['tatsu'] == '1'){			
			?>
			<script type="application/json" id="qsm-quiz-json-<?php echo esc_attr($quiz_id); ?>">
				<?php echo base64_encode(wp_json_encode( $payload )); ?>
			</script>
		<?php
		}
		add_action('wp_footer',function() use ( $quiz_id, $quiz_data, $encryption ){
		?>
    
			<script type="text/javascript" id="qsm-inline-quizdata-<?php echo esc_attr($quiz_id); ?>">
				window.qsmQuizData = window.qsmQuizData || {};
				window.qmn_quiz_data = window.qmn_quiz_data || {};

				window.qsmQuizData[<?php echo $quiz_id; ?>] = <?php echo wp_json_encode( $quiz_data ); ?>;
				window.qmn_quiz_data[<?php echo $quiz_id; ?>] = <?php echo wp_json_encode( $quiz_data ); ?>;

				<?php if ( ! empty( $encryption ) ) : 
					$key = hash( "sha256", time() . $quiz_id );
				?>
					if (typeof encryptionKey === 'undefined') { var encryptionKey = {}; }
					if (typeof data === 'undefined') { var data = {}; }
					if (typeof jsonString === 'undefined') { var jsonString = {}; }
					if (typeof encryptedData === 'undefined') { var encryptedData = {}; }

					encryptionKey[<?php echo $quiz_id; ?>] = "<?php echo $key; ?>";

					data[<?php echo $quiz_id; ?>] = <?php echo wp_json_encode( $encryption ); ?>;
					jsonString[<?php echo $quiz_id; ?>] = JSON.stringify( data[<?php echo $quiz_id; ?>] );

					encryptedData[<?php echo $quiz_id; ?>] =
						CryptoJS.AES.encrypt(
							jsonString[<?php echo $quiz_id; ?>],
							encryptionKey[<?php echo $quiz_id; ?>]
						).toString();
				<?php endif; ?>
			</script>

		<?php
		},1);
		return '';
	}


	/**
	 * Render contact form and return HTML
	 *
	 * @return string
	 */
	public function render_contact_form() {
		// Check if contact fields exist and are properly configured
		if ( empty( $this->contact_fields ) || ! is_array( $this->contact_fields ) ) {
			return '';
		}
		
		ob_start();
		
		$total_fields = count( $this->contact_fields );
		for ( $i = 0; $i < $total_fields; $i++ ) {
			if ( 'true' === $this->contact_fields[ $i ]["enable"] || true === $this->contact_fields[ $i ]["enable"] ) {
				$value = '';
				
				// Set default values for logged in users
				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					if ( 'name' === $this->contact_fields[ $i ]['use'] ) {
						$value = $current_user->display_name;
					}
					if ( 'email' === $this->contact_fields[ $i ]['use'] ) {
						$value = $current_user->user_email;
					}
				}
				?>
				<div class="qsm_contact_div qsm-contact-type-<?php echo esc_attr( $this->contact_fields[ $i ]['type'] ); ?>">
					<?php
					QSM_Contact_Manager::generate_contact_field($this->contact_fields[ $i ], $i, (object) array_merge((array)$this->quiz_options, (array)$this->options), $value);
					?>
				</div>
				<?php
			}
		}
		
		return ob_get_clean();
	}

	/**
	 * Get pages data for JavaScript
	 * Enhanced to match legacy qpages structure
	 *
	 * @return array
	 */
	public function get_pages_data() {
		return $this->pages;
	}

	/**
	 * Get quiz properties
	 *
	 * @return array
	 */
	public function get_quiz_properties( $param = false ) {
		if ( ! empty( $param ) ) {
			switch ( $param ) {
				case 'options':
					return $this->options;
				case 'quiz_data':
					return $this->quiz_data;
				case 'pages':
					return $this->pages;
				case 'qpages':
					return $this->qpages;
				case 'questions':
					return $this->questions;
				default:
					return false;
			}
		}
	}

	/**
	 * Get questions data for JavaScript
	 * Enhanced to provide question IDs and basic info
	 *
	 * @return array
	 */
	public function get_questions_data() {
		$questions_data = array();
		
		if ( ! empty( $this->questions ) && is_array( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( isset( $question['question_id'] ) ) {
					$questions_data[] = intval( $question['question_id'] );
				}
			}
		}
		
		return $questions_data;
	}
}
