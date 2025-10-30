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
	private $pages;

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
	 * Constructor
	 *
	 * @param object $options Quiz options
	 * @param array  $quiz_data Quiz data
	 */

	/**
	 * Constructor
	 *
	 * @param object $options Quiz options
	 * @param array  $quiz_data Quiz data
	 */
	public function __construct( $options, $quiz_data ) {
		$this->options 			= $options;
		$this->quiz_data 		= $quiz_data;
		$this->quiz_settings 	= maybe_unserialize( $options->quiz_settings );
		$this->quiz_options 	= (object) maybe_unserialize( $this->quiz_settings['quiz_options'] );		
		$this->quiz_texts 		= (object) maybe_unserialize( $this->quiz_settings['quiz_text'] );		
		$this->contact_fields 	= maybe_unserialize( $this->quiz_settings['contact_form'] );
		$this->pages 			= maybe_unserialize( $this->quiz_settings['pages'] );
		
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
					$this->questions[ $question['question_id'] ] = $question;
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
		// If no manual pages defined, create automatic pagination
		if ( $this->quiz_options->pagination > 0 ) {
			$this->create_auto_pagination();
		}
	}

	/**
	 * Create automatic pagination based on questions per page setting
	 */
	private function create_auto_pagination() {
		$questions_per_page = intval( $this->quiz_options->pagination );
		$question_ids = array_keys( $this->questions );
		
		if ( $questions_per_page > 0 ) {
			$this->pages = array_chunk( $question_ids, $questions_per_page );
		} else {
			// Single page with all questions
			$this->pages = array( $question_ids );
		}
	}

	/**
	 * Render the complete quiz with new pagination system
	 */
	public function render() {
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

			// Hook before rendering to prevent recursion
			do_action( 'qsm_new_before_pagination_render', $this->options->quiz_id, $this->options, $this->quiz_data );
			
			// Start form - don't render form here as it's handled by main quiz manager
			echo $this->render_form_start();
			
			// Add hidden inputs
			echo $this->render_hidden_inputs();

			// Render quiz timer
			echo $this->render_quiz_timer();
			
			// Render first page if enabled
			if ( $this->should_show_first_page() ) {
				echo $this->render_first_page();
			}
			
			// Render question pages
			$this->render_quiz_pages();
			
			// Render last page if enabled
			if ( $this->should_show_last_page() ) {
				echo $this->render_last_page();
			}
			
			// End form here as it's handled by main quiz manager
			echo $this->render_form_end();
			
			// Render navigation
			echo $this->render_navigation();
			
			// Add JavaScript data
			echo $this->render_javascript_data();
			
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
			$output .= '<div class="qsm-timer qsm-quiz-timer-' . esc_attr( $this->options->quiz_id ) . '">';
			$output .= '<div class="mlw_qmn_timer qsm-timer-display">00:00:00</div>';
			$output .= '</div>';
		}
		
		return $output;
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
		
		return ( 
			count( $this->pages ) > 1 && 
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
	private function render_quiz_pages() {
		global $qmn_total_questions;
		$output = '';
		$animation_effect = isset( $this->options->quiz_animation ) && '' !== $this->options->quiz_animation ? ' animated ' . $this->options->quiz_animation : '';
		$enable_pagination_quiz = isset( $this->options->enable_pagination_quiz ) && 1 == $this->options->enable_pagination_quiz;
		
		// Check if we have pages
		if ( empty( $this->pages ) ) {
			return '<div class="qsm-error"> ' . esc_html__( 'No questions found for this quiz.', 'quiz-master-next' ) . '</div>';
		}
		
		// Apply filters to pages
		$pages = apply_filters( 'qsm_display_pages', $this->pages, $this->options->quiz_id, $this->options );
		
		if ( 2 == $this->quiz_options->randomness_order ) {
			shuffle( $pages );
		}
		
		$is_display_first_page = $this->should_show_first_page();
		$total_pages_count = count( $pages );
		$pages_count = 1;
		foreach ( $pages as $key => $page ) {
			$display_current_page = 'none';
			if ( 1 == $pages_count && ! $is_display_first_page ) {
				$display_current_page = 'block';
			}
			?>
			<section class="qsm-page qsm-question-page qsm-page-<?php echo esc_attr( $pages_count ); ?> <?php echo esc_attr( $animation_effect ); ?>" data-pid="<?php echo esc_attr( $pages_count ); ?>" data-qid="<?php echo esc_attr( $pages_count ); ?>" data-page="<?php echo esc_attr( $pages_count ); ?>" style="display: <?php echo $display_current_page; ?>;">
			<?php
			
			// Hook before page
			do_action( 'qsm_new_action_before_page', $pages_count, $page, $this );
			
			if ( 1 == $this->quiz_options->randomness_order || 2 == $this->quiz_options->randomness_order ) {
				shuffle( $page );
			}
			
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
					echo $this->display_question( $question['question_type_new'], $question_id, $this->options ); ?>
				</div>
				<?php
			}
			
			// Show page count if enabled
			?>
			<span class="pages_count">
			<?php
			$text_c = $pages_count . esc_html__( ' out of ', 'quiz-master-next' ) . $total_pages_count;
			echo apply_filters( 'qsm_total_pages_count', $text_c, $pages_count, $total_pages_count );
			?>
			</span>
			<?php

			do_action( 'qsm_new_action_after_page', $pages_count, $page );

			?>
			</section>
			<?php
			$pages_count++;
		}
		return;
	}

	/**
	 * Display a single question
	 *
	 * @param string $question_type Question type
	 * @param int    $question_id   Question ID
	 * @param object $quiz_options  Quiz options
	 * @return string
	 */
	private function display_question( $question_type, $question_id, $quiz_options ) {
		global $mlwQuizMasterNext;
		
		// Prepare question data for template
		$question_data = $this->questions[ $question_id ];
		$question_settings = maybe_unserialize( $question_data['question_settings'] );
		
		$randomness_order = $this->quiz_options->randomness_order;
		$answer_array = maybe_unserialize( $question_data['answer_array'] );
		
		if ( $randomness_order == 3 || $randomness_order == 2 ) {
			shuffle( $answer_array );
		}
		
		$args = array(
			'id' => $question_id,
			'class_object' => $this,
			'question' => $question_data,
			'answers' => $answer_array,
			'question_settings' => is_array( $question_settings ) ? $question_settings : array(),
			'quiz_options' => $this->quiz_options,
			'randomness_order' => $randomness_order,
		);

		// Use the new question template function
		return qsm_get_question_template( $question_type, $args );
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
	private function render_navigation() {
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

	/**
	 * Render form start
	 *
	 * @return string
	 */
	private function render_form_start() {
		$form_action = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		$output = '<form name="qsmForm' . esc_attr( $this->options->quiz_id ) . '" ';
		$output .= 'id="qsmForm' . esc_attr( $this->options->quiz_id ) . '" ';
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
		$output = '';
		
		// Question list
		$question_list = '';
		$total_questions = 0;
		foreach ( $this->pages as $page ) {
			foreach ( $page as $question_id ) {
				$question_list .= $question_id . 'Q';
				$total_questions++;
			}
		}
		$output .= '<input type="hidden" name="qmn_question_list" value="' . esc_attr( $question_list ) . '" />';
		
		// Other required inputs
		$output .= '<input type="hidden" name="qsm_nonce" id="qsm_nonce_' . esc_attr($this->options->quiz_id) . '" value="' . esc_attr( wp_create_nonce( 'qsm_submit_quiz_' . intval( $this->options->quiz_id ) ) ) . '">';
		$output .= '<input type="hidden" name="qsm_unique_key" id="qsm_unique_key_' . esc_attr($this->options->quiz_id) . '" value="' . esc_attr( uniqid() ) . '">';
		$output .= '<input type="hidden" name="qmn_quiz_id" value="' . esc_attr( $this->options->quiz_id ) . '" />';
		$output .= '<input type="hidden" name="complete_quiz" value="confirmation" />';
		$output .= '<input type="hidden" name="timer" id="timer" value="0" />';
		$output .= '<input type="hidden" name="total_questions" id="total_questions" value="'. esc_attr( $total_questions ).'" />';
		
		return $output;
	}

	/**
	 * Render JavaScript data
	 * Enhanced to match $qmn_json_data structure from legacy system
	 *
	 * @return string
	 */
	private function render_javascript_data() {
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
			'timer_limit' => intval( $this->quiz_options->timer_limit ?? 0 ),
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
			'qpages' => $this->get_pages_data(),
			'first_page' => $this->should_show_first_page(),
			
			// Questions data
			'question_list' => $this->get_questions_data(),
			
			// Error messages
			'error_messages' => $error_messages,
			
			// System data
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'qsm_quiz_nonce_' . $this->options->quiz_id ),
			
			// QSM-11 specific enhancements
			'template_system' => 'qsm-11',
			'version' => '2.0',
			'scroll_to_top' => true,
			'timer_auto_start' => true,
		);
		
		// Apply filters to allow customization (matching legacy filter)
		$quiz_data = apply_filters( 'qsm_new_json_data', $quiz_data, $this->options, $this->quiz_data );
		$quiz_data = apply_filters( 'qmn_json_data', $quiz_data, $this->options, $this->quiz_data );
		
		// Output JavaScript with both legacy and new variable names for compatibility
		$output = '<script type="text/javascript">';
		$output .= 'if (typeof window.qsmQuizData === "undefined") { window.qsmQuizData = {}; }';
		$output .= 'if (typeof window.qmn_quiz_data === "undefined") { window.qmn_quiz_data = {}; }';
		$output .= 'window.qsmQuizData[' . intval( $this->options->quiz_id ) . '] = ' . wp_json_encode( $quiz_data ) . ';';
		$output .= 'window.qmn_quiz_data[' . intval( $this->options->quiz_id ) . '] = ' . wp_json_encode( $quiz_data ) . ';';
		$output .= '</script>';
		
		return $output;
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
