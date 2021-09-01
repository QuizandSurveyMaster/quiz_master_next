<?php
/**
 * File that contains class for questions.
 *
 * @package QSM
 */

/**
 * Class that handles all creating, saving, and deleting of questions.
 *
 * @since 5.2.0
 */
class QSM_Questions {

	/**
	 * Loads single question using question ID
	 *
	 * @since 5.2.0
	 * @param int $question_id The ID of the question.
	 * @return array The data for the question.
	 */
	public static function load_question( $question_id ) {
		global $wpdb;
		$question_id = intval( $question_id );
		$question    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d LIMIT 1", $question_id ), 'ARRAY_A' );
		if ( ! is_null( $question ) ) {
			$multicategories = array();
			$multicategories_res = $wpdb->get_results("SELECT `term_id` FROM `{$wpdb->prefix}mlw_question_terms` WHERE `question_id`='{$question['question_id']}' AND `taxonomy`='qsm_category'", ARRAY_A);
			if (!empty($multicategories_res)){
				foreach ($multicategories_res as $cat) {
					$multicategories[] = $cat['term_id'];
				}
			}
			$question['multicategories'] = $multicategories;
			// Prepare answers.
			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$question['answers'] = $answers;

			$settings = maybe_unserialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			$question['settings'] = $settings;

			return apply_filters( 'qsm_load_question', $question, $question_id );
		}
		return array();
	}

	/**
	 * Loads questions for a quiz using the new page system
	 *
	 * @since 5.2.0
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of questions.
	 */
	public static function load_questions_by_pages( $quiz_id ) {

		// Prepares our variables.
		global $wpdb;
		global $mlwQuizMasterNext;
		$quiz_id      = intval( $quiz_id );
		$question_ids = array();
		$questions    = array();
		$page_for_ids = array();

		// Gets the pages for the quiz.
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );

		// Get all question IDs needed.
		$total_pages = count( $pages );
		for ( $i = 0; $i < $total_pages; $i++ ) {
			foreach ( $pages[ $i ] as $question ) {
				$question_id                  = intval( $question );
				$question_ids[]               = $question_id;
				$page_for_ids[ $question_id ] = $i;
			}
		}

		// If we have any question IDs, get the questions.
		if ( count( $question_ids ) > 0 ) {

			$question_sql = implode( ', ', $question_ids );

			// Get all questions.
			$question_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (%1s)", $question_sql ), 'ARRAY_A' );

			// Loop through questions and prepare serialized data.
			foreach ( $question_array as $question ) {
				$multicategories = self::get_question_categories($question['question_id']);
				//get_question_categories
				
				$question['multicategories'] = isset($multicategories['category_tree'])  && !empty($multicategories['category_tree'] ) ? array_keys($multicategories['category_name']) : array();
				$question['multicategoriesobject'] = isset($multicategories['category_tree'])  && !empty($multicategories['category_tree'] ) ? $multicategories['category_tree'] : array();
				// Prepare answers.
				$answers = maybe_unserialize( $question['answer_array'] );
				if ( ! is_array( $answers ) ) {
					$answers = array();
				}
				$question['answers'] = $answers;

				// Prepares settings.
				$settings = maybe_unserialize( $question['question_settings'] );
				if ( ! is_array( $settings ) ) {
					$settings = array( 'required' => 1 );
				}
				$question['settings'] = $settings;

				// Get the page.
				$question_id      = intval( $question['question_id'] );
				$question['page'] = intval( $page_for_ids[ $question_id ] );

				$questions[ $question_id ] = $question;
			}
		} else {
			// If we do not have pages on this quiz yet, use older load_questions and add page to them.
			$questions = self::load_questions( $quiz_id );
			foreach ( $questions as $key => $question ) {
				$questions[ $key ]['page'] = isset( $question['page'] ) ? $question['page'] : 0;
			}
		}
		return apply_filters( 'qsm_load_questions_by_pages', $questions, $quiz_id );
	}

	/**
	 * Loads questions for a quiz
	 *
	 * @since 5.2.0
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of questions.
	 */
	public static function load_questions( $quiz_id ) {

		global $wpdb;
		$question_array = array();

		// Get all questions.
		if ( 0 !== $quiz_id ) {
			$quiz_id   = intval( $quiz_id );
			$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id=%d AND deleted='0' ORDER BY question_order ASC", $quiz_id ), 'ARRAY_A' );
		} else {
			$questions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted='0' ORDER BY question_order ASC", 'ARRAY_A' );
		}

		// Loop through questions and prepare serialized data.
		foreach ( $questions as $question ) {
			$multicategories = array();
			$multicategories_res = $wpdb->get_results("SELECT `term_id` FROM `{$wpdb->prefix}mlw_question_terms` WHERE `question_id`='{$question['question_id']}' AND `taxonomy`='qsm_category'", ARRAY_A);
			if (!empty($multicategories_res)){
				foreach ($multicategories_res as $cat) {
					$multicategories[] = $cat['term_id'];
				}
			}
			$question['multicategories'] = $multicategories;
			// Prepare answers.
			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$question['answers'] = $answers;

			$settings = maybe_unserialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			$question['settings'] = $settings;

			$question_array[ $question['question_id'] ] = $question;
		}
		return apply_filters( 'qsm_load_questions', $question_array, $quiz_id );
	}

	/**
	 * Creates a new question
	 *
	 * @since 5.2.0
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was created.
	 */
	public static function create_question( $data, $answers = array(), $settings = array() ) {
		return self::create_save_question( $data, $answers, $settings );
	}

	/**
	 * Saves a question
	 *
	 * @since 5.2.0
	 * @param int   $question_id The ID of the question to be saved.
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was saved.
	 */
	public static function save_question( $question_id, $data, $answers = array(), $settings = array() ) {
		$data['ID'] = intval( $question_id );
		return self::create_save_question( $data, $answers, $settings, false );
	}

	/**
	 * Deletes a question
	 *
	 * @since 5.2.0
	 * @param int $question_id The ID for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return bool True if successful
	 */
	public static function delete_question( $question_id ) {
		global $wpdb;

		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_questions',
			array(
				'deleted' => 1,
			),
			array( 'question_id' => intval( $question_id ) ),
			array(
				'%d',
			),
			array( '%d' )
		);

		if ( false === $results ) {
			$msg = $wpdb->last_error . ' from ' . $wpdb->last_query;
			$mlwQuizMasterNext->log_manager->add( 'Error when deleting question', $msg, 0, 'error' );
			throw new Exception( $msg );
		}

		return true;
	}

	/**
	 * Creates or saves a question
	 *
	 * This is used internally. Use create_question or save_question instead.
	 *
	 * @since 5.2.0
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @param bool  $is_creating True if question is being created, false if being saved.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was created/saved.
	 */
	private static function create_save_question( $data, $answers, $settings, $is_creating = true ) {
		global $wpdb;

		// Prepare defaults and parse.
		$defaults = array(
			'quiz_id'     => 0,
			'type'        => '0',
			'name'        => '',
			'answer_info' => '',
			'comments'    => '1',
			'hint'        => '',
			'order'       => 1,
			'category'    => '',
			'multicategories' => '',
		);
		$data     = wp_parse_args( $data, $defaults );

		$defaults = array(
			'required' => 1,
		);
		$settings = wp_parse_args( $settings, $defaults );

		foreach ( $answers as $key => $answer ) {
			$answers_array = array(
				htmlspecialchars( $answer[0], ENT_QUOTES ),
				floatval( $answer[1] ),
				intval( $answer[2] ),
			);
			if ( isset( $answer[3] ) ) {
				array_push( $answers_array, htmlspecialchars( $answer[3], ENT_QUOTES ) );
			}
			$answers[ $key ] = $answers_array;
		}

		$values = array(
			'quiz_id'              => intval( $data['quiz_id'] ),
			'question_name'        => trim( preg_replace( '/\s+/', ' ', htmlspecialchars( wp_kses_post( $data['name'] ), ENT_QUOTES ) ) ),
			'answer_array'         => serialize( $answers ),
			'question_answer_info' => $data['answer_info'],
			'comments'             => htmlspecialchars( $data['comments'], ENT_QUOTES ),
			'hints'                => htmlspecialchars( $data['hint'], ENT_QUOTES ),
			'question_order'       => intval( $data['order'] ),
			'question_type_new'    => sanitize_text_field( $data['type'] ),
			'question_settings'    => serialize( $settings ),
			'category'             => sanitize_text_field( $data['category'] ),
			'deleted'              => 0,
		);
		$values = apply_filters('qsm_save_question_data', $values);

		$types = array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
		);

		$question_id = intval($data['ID']);
		if ( $is_creating ) {
			$results = $wpdb->insert(
				$wpdb->prefix . 'mlw_questions',
				$values,
				$types
			);
			$question_id = $wpdb->insert_id;
		} else {
			$results = $wpdb->update(
				$wpdb->prefix . 'mlw_questions',
				$values,
				array( 'question_id' => $question_id ),
				$types,
				array( '%d' )
			);
		}

		if ( false === $results ) {
			$msg = $wpdb->last_error . ' from ' . $wpdb->last_query;
			$mlwQuizMasterNext->log_manager->add( 'Error when creating/saving question', $msg, 0, 'error' );
			throw new Exception( $msg );
		}
		
		/**
		 * Process Question Categories
		 */
		$question_terms_table = $wpdb->prefix . "mlw_question_terms";
		$wpdb->delete($question_terms_table, array('question_id' => $question_id, 'taxonomy' => 'qsm_category'));
		if (!empty($data['multicategories'])) {
			foreach ($data['multicategories'] as $term_id) {
				$term_rel_data = array(
					'question_id' => $question_id,
					'quiz_id' => intval($data['quiz_id']),
					'term_id' => $term_id,
					'taxonomy' => 'qsm_category',
				);
				$wpdb->insert($question_terms_table, $term_rel_data);
			}
		}

		return $question_id;
	}

	/**
	 * Get categories for a quiz
	 *
	 * @since 7.2.1
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of categories.
	 */
	public static function get_quiz_categories( $quiz_id = 0 ) {
		global $wpdb;
		$categories = array();
		if ( 0 !== $quiz_id ) {
			$question_terms = $wpdb->get_results( "SELECT `term_id` FROM `{$wpdb->prefix}mlw_question_terms` WHERE `quiz_id`='{$quiz_id}' AND `taxonomy`='qsm_category'", ARRAY_A );
			if ( ! empty( $question_terms ) ) {
				$term_ids = array_unique( array_column( $question_terms, 'term_id' ) );
				if ( ! empty( $term_ids ) ) {
					$categories_names	 = array();
					$categories_tree	 = array();
					$terms				 = get_terms( array( 'taxonomy' => 'qsm_category', 'include' => array_unique( $term_ids ), 'hide_empty' => false, 'orderby' => '', 'order' => '' ) );
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $tax ) {
							$categories_names[$tax->term_id] = $tax->name;
							$taxs[$tax->parent][]			 = $tax;
						}
						$categories_tree = self::create_terms_tree( $taxs, $taxs[0] );
					}
					$categories = array(
						'list'	 => $categories_names,
						'tree'	 => $categories_tree,
					);
				}
			}
		}
		return $categories;
	}
	/**
	 * Get categories for a Question 
	 *
	 * @since 7.2.1
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of categories.
	 */
	public static function get_question_categories( $question_id = 0 ) {
		global $wpdb;
		$categories_tree	 = array();
		$categories_names	 = array();		
		if ( 0 !== $question_id ) {
			$question_terms = $wpdb->get_results( "SELECT `term_id` FROM `{$wpdb->prefix}mlw_question_terms` WHERE `question_id`='{$question_id}' AND `taxonomy`='qsm_category'", ARRAY_A );
			if ( ! empty( $question_terms ) ) {
				$term_ids = array_unique( array_column( $question_terms, 'term_id' ) );
				if ( ! empty( $term_ids ) ) {												
					$terms				 = get_terms( array( 'taxonomy' => 'qsm_category', 'include' => array_unique( $term_ids ), 'hide_empty' => false, 'orderby' => '', 'order' => '' ) );
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $tax ) {
							$categories_names[$tax->term_id] = $tax->name;										
							$taxs[$tax->parent][]			 = $tax;		
						}
						$categories_tree = self::create_terms_tree( $taxs, $taxs[0] );
						
					}					
				}
			}
		}
		return array('category_name'=>$categories_names,"category_tree"=>$categories_tree);
	}
	/**
	 * Create tree structure of terms.
	 *
	 * @since 7.2.1
	 */
	public static function create_terms_tree( &$list, $parent ) {
		$taxTree = array();
		foreach ( $parent as $ind => $val ) {
			if ( isset( $list[$val->term_id] ) ) {
				$val->children = self::create_terms_tree( $list, $list[$val->term_id] );
			}
			$taxTree[] = $val;
		}
		return $taxTree;
	}

}
