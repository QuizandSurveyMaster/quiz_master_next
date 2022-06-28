<?php
/**
 * Handles relevant functions for results pages
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class contains functions for loading, saving, and generating results pages.
 *
 * @since 6.2.0
 */
class QSM_Results_Pages {

	/**
	 * Creates the HTML for the results page.
	 *
	 * @since 6.2.0
	 * @param array $response_data The data for the user's submission.
	 * @return string The HTML for the page to be displayed.
	 */
	public static function generate_pages( $response_data ) {
		global $mlwQuizMasterNext;
		$pages            = QSM_Results_Pages::load_pages( $response_data['quiz_id'] );
		$default          = '%QUESTIONS_ANSWERS%';
		$redirect         = false;
		$default_redirect = false;
		ob_start();
		?>
<div class="qsm-results-page">
	<?php
			do_action( 'qsm_before_results_page' );

			// Cycles through each possible page.
			foreach ( $pages as $index => $page ) {

				$page_content = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $page['page'], "quiz-result-page-{$index}-{$response_data['quiz_id']}" );
				// Checks if any conditions are present. Else, set it as the default.
				if ( ! empty( $page['conditions'] ) ) {
					/**
					 * Since we have many conditions to test, we set this to true first.
					 * Then, we test each condition to see if it fails.
					 * If one condition fails, the value will be set to false.
					 * If all conditions pass, this will still be true and the page will
					 * be shown.
					 */
					$show = true;

					// Cycle through each condition to see if we should show this page.
					foreach ( $page['conditions'] as $condition ) {
						$value = $condition['value'];
						$category = '';
						if ( isset($condition['category']) ) {
							$category = $condition['category'];
						}
						// First, determine which value we need to test.
						switch ( $condition['criteria'] ) {
							case 'score':
								if ( '' !== $category ) {
									$test = apply_filters( 'mlw_qmn_template_variable_results_page', "%CATEGORY_SCORE_$category%", $response_data );
								} else {
									$test = $response_data['total_score'];
								}

								break;

							case 'points':
								if ( '' !== $category ) {
									$test = apply_filters( 'mlw_qmn_template_variable_results_page', "%CATEGORY_POINTS_$category%", $response_data );
								} else {
									$test = $response_data['total_points'];
								}
								break;

							default:
								$test = 0;
								break;
						}

						// Then, determine how to test the vaue.
						switch ( $condition['operator'] ) {
							case 'greater-equal':
								if ( $test < $value ) {
									$show = false;
								}
								break;

							case 'greater':
								if ( $test <= $value ) {
									$show = false;
								}
								break;

							case 'less-equal':
								if ( $test > $value ) {
									$show = false;
								}
								break;

							case 'less':
								if ( $test >= $value ) {
									$show = false;
								}
								break;

							case 'not-equal':
								if ( $test == $value ) {
									$show = false;
								}
								break;

							case 'equal':
								if ( $test != $value ) {
									$show = false;
								}
								break;
						}

						/**
						 * Added custom criterias/operators to the results pages?
						 * Use this filter to check if the condition passed.
						 * If it fails your conditions, return false to prevent the
						 * page from showing.
						 * If it passes your condition or is not your custom criterias
						 * or operators, then return the value as-is.
						 * DO NOT RETURN TRUE IF IT PASSES THE CONDITION!!!
						 * The value may have been set to false when failing a previous condition.
						 */
						$show = apply_filters( 'qsm_results_page_condition_check', $show, $condition, $response_data );
						if ( ! $show ) {
							break;
						}
					}

					// If we passed all conditions, show this page.
					if ( $show ) {
						$content = $page_content;
						if ( $page['redirect'] ) {
							$redirect = $page['redirect'];
						}
					}
				} else {
					$default = $page_content;
					if ( $page['redirect'] ) {
						$default_redirect = $page['redirect'];
					}
				}
			}

			// If no page was set to the content, set to the page that was a default page.
			if ( empty( $content ) ) {
				$content = $default;
			}

			// If no redirect was set, set to default redirect.
			if ( ! $redirect ) {
				$redirect = $default_redirect;
			}

			// Decodes special characters, runs through our template
			// variables, and then outputs the text.
			$page = htmlspecialchars_decode( $content, ENT_QUOTES );

			//last chance to filter $page
			$page = apply_filters( 'qsm_template_variable_results_page', $page, $response_data );

			echo apply_filters( 'mlw_qmn_template_variable_results_page', $page, $response_data );
			do_action( 'qsm_after_results_page' );
			?>
</div>
<?php
		return array(
			'display'  => do_shortcode( ob_get_clean() ),
			'redirect' => $redirect,
		);
	}

	/**
	 * Loads the results pages for a single quiz.
	 *
	 * @since 6.2.0
	 * @param int $quiz_id The ID for the quiz.
	 * @return bool|array The array of pages or false.
	 */
	public static function load_pages( $quiz_id ) {
		$pages   = array();
		$quiz_id = intval( $quiz_id );

		// If the parameter supplied turns to 0 after intval, returns false.
		if ( 0 === $quiz_id ) {
			return false;
		}

		global $wpdb;
		$results = $wpdb->get_var( $wpdb->prepare( "SELECT message_after FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );
		$results = maybe_unserialize( $results );

		// Checks if the results is an array.
		if ( is_array( $results ) ) {
			// Checks if the results array is not the newer version.
			if ( ! empty( $results ) && ! isset( $results[0]['conditions'] ) ) {
				$pages = QSM_Results_Pages::convert_to_new_system( $quiz_id );
			} else {
				$pages = $results;
			}
		} else {
			$pages = QSM_Results_Pages::convert_to_new_system( $quiz_id );
		}

		return $pages;
	}

	/**
	 * Loads and converts results pages from the old system to new system
	 *
	 * @since 6.2.0
	 * @param int $quiz_id The ID for the quiz.
	 * @return array The combined newer versions of the pages.
	 */
	public static function convert_to_new_system( $quiz_id ) {
		$pages   = array();
		$quiz_id = intval( $quiz_id );

		// If the parameter supplied turns to 0 after intval, returns empty array.
		if ( 0 === $quiz_id ) {
			return $pages;
		}

		/**
		 * Loads the old results pages and converts them.
		 */
		global $wpdb;
		global $mlwQuizMasterNext;
		$data      = $wpdb->get_row( $wpdb->prepare( "SELECT message_after FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ), ARRAY_A );
		$system    = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system', 0 );
		$old_pages = maybe_unserialize( $data['message_after'] );

		// If the value is an array, convert it.
		// If not, use it as the contents of the results page.
		if ( is_array( $old_pages ) ) {

			// Cycle through the older version of results pages.
			foreach ( $old_pages as $page ) {
				$new_page = array(
					'conditions' => array(),
					'page'       => $page[2],
					'redirect'   => false,
				);

				// If the page used the older version of the redirect, add it.
				if ( ! empty( $page['redirect_url'] ) ) {
					$new_page['redirect'] = $page['redirect_url'];
				}

				// Checks to see if the page is not the older version's default page.
				if ( 0 !== intval( $page[0] ) || 0 !== intval( $page[1] ) ) {

					// Checks if the system is points.
					if ( 1 === intval( $system ) ) {
						$new_page['conditions'][] = array(
							'criteria' => 'points',
							'operator' => 'greater-equal',
							'value'    => $page[0],
						);
						$new_page['conditions'][] = array(
							'criteria' => 'points',
							'operator' => 'less-equal',
							'value'    => $page[1],
						);
					} else {
						$new_page['conditions'][] = array(
							'criteria' => 'score',
							'operator' => 'greater-equal',
							'value'    => $page[0],
						);
						$new_page['conditions'][] = array(
							'criteria' => 'score',
							'operator' => 'less-equal',
							'value'    => $page[1],
						);
					}
				}

				$pages[] = $new_page;
			}
		} else {
			$pages[] = array(
				'conditions' => array(),
				'page'       => $old_pages,
				'redirect'   => false,
			);
		}

		// Updates the database with new array to prevent running this step next time.
		$wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'message_after' => maybe_serialize( $pages ) ),
			array( 'quiz_id' => $quiz_id ),
			array( '%s' ),
			array( '%d' )
		);

		return $pages;
	}

	/**
	 * Saves the results pages for a quiz.
	 *
	 * @since 6.2.0
	 * @param int   $quiz_id The ID for the quiz.
	 * @param array $pages The results pages to be saved.
	 * @return bool True or false depending on success.
	 */
	public static function save_pages( $quiz_id, $pages ) {
		global $wpdb, $mlwQuizMasterNext;
		if ( ! is_array( $pages ) ) {
			return false;
		}

		$quiz_id = intval( $quiz_id );
		if ( 0 === $quiz_id ) {
			return false;
		}

		$is_not_allow_html = apply_filters( 'qsm_admin_results_page_disallow_html', true );

		// Sanitizes data in pages.
		$total = count( $pages );
		for ( $i = 0; $i < $total; $i++ ) {

			// jQuery AJAX will send a string version of false.
			if ( 'false' === $pages[ $i ]['redirect'] ) {
				$pages[ $i ]['redirect'] = false;
			}

			/**
			 * The jQuery AJAX function won't send the conditions key
			 * if it's empty. So, check if it's set. If set, sanitize
			 * data. If not set, set to empty array.
			 */
			if ( isset( $pages[ $i ]['conditions'] ) ) {
				// Sanitizes the conditions.
				$total_conditions = count( $pages[ $i ]['conditions'] );
				for ( $j = 0; $j < $total_conditions; $j++ ) {
					$pages[ $i ]['conditions'][ $j ]['value'] = sanitize_text_field( $pages[ $i ]['conditions'][ $j ]['value'] );
				}
			} else {
				$pages[ $i ]['conditions'] = array();
			}

			// Sanitize template data
			if ( isset( $pages[ $i ]['page'] ) && $is_not_allow_html ) {
				// Sanitizes the conditions.
				$pages[ $i ]['page'] = wp_kses_post( $pages[ $i ]['page'] );

			}
			$mlwQuizMasterNext->pluginHelper->qsm_register_language_support( $pages[ $i ]['page'], "quiz-result-page-{$i}-{$quiz_id}" );
		}

		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array( 'message_after' => maybe_serialize( $pages ) ),
			array( 'quiz_id' => $quiz_id ),
			array( '%s' ),
			array( '%d' )
		);
                do_action('qsm_save_result_pages');
		if ( false !== $results ) {
			return true;
		} else {
			return false;
		}
	}
}
?>