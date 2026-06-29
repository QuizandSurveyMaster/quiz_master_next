<?php

/**
 * Resolve a QSM quiz_id to the qsm_quiz post ID for the CURRENT request language.
 *
 * QSM keeps a single internal quiz_id across all languages, while WPML
 * create a separate qsm_quiz post per language. This helper finds the base post
 * that carries the quiz_id meta (without any language filtering) and then maps it
 * to the post for the language currently being viewed.
 *
 * On a single-language site (no WPML) it simply returns that base post,
 * so the historical behaviour is unchanged.
 *
 * @since 11.1.6
 * @param int $quiz_id QSM internal quiz_id.
 * @return int Post ID for the current language, or 0 if none found.
 */
function qsm_get_translated_quiz_post_id( $quiz_id ) {
	$quiz_id = intval( $quiz_id );
	if ( $quiz_id < 1 ) {
		return 0;
	}

	// 1. Find the base qsm_quiz post WITHOUT language filtering, so the post that
	//    carries the quiz_id meta is always found regardless of the active language.
	$base_query = new WP_Query(
		array(
			'post_type'              => 'qsm_quiz',
			'meta_key'               => 'quiz_id',
			'meta_value'             => $quiz_id,
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'suppress_filters'       => true, // Bypass WPML language SQL filter.
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $base_query->posts ) ) {
		return 0;
	}
	$base_post_id = (int) $base_query->posts[0];

	// 2. Map the base post to the post for the CURRENT language.
	// WPML.
	if ( has_filter( 'wpml_object_id' ) ) {
		$current_lang = apply_filters( 'wpml_current_language', null );
		// The "true" flag returns the original post when no translation exists (graceful fallback).
		$translated_id = apply_filters( 'wpml_object_id', $base_post_id, 'qsm_quiz', true, $current_lang );
		if ( $translated_id ) {
			return (int) $translated_id;
		}
	}

	// 3. No multilingual plugin (or no translation found) -> base post.
	return $base_post_id;
}

/**
 * Displays a link to a quiz using ID. Used [qsm_link id=1]Click Here[/qsm_link]
 *
 * @since 5.1.0
 * @param array  $atts Attributes from add_shortcode function
 * @param string $content The text to be used for the link
 * @return string The HTML the shortcode will be replaced with
 */
function qsm_quiz_link_shortcode( $atts, $content = '' ) {

	$args = shortcode_atts(
		array(
			'id'     => 0,
			'class'  => '',
			'target' => '',
		),
		$atts
	);

	$id = intval( $args['id'] );
	$class = $args['class'];
	$target = $args['target'];

	// Find the permalink for the quiz, resolved to the CURRENT language when a
	// multilingual plugin (WPML) is active. On single-language sites this
	// returns the same post as before, so behaviour is unchanged.
	$post_id   = qsm_get_translated_quiz_post_id( $id );
	$permalink = $post_id ? get_permalink( $post_id ) : '';

	// Craft the target attribute if one is passed to shortcode
	$target_html = '';
	if ( ! empty( $target ) ) {
		return "<a href='" . esc_url( $permalink ) . "' target='" . esc_attr( $target ) . "' class='" . esc_attr( $class ) . "'>" . do_shortcode( $content ) . '</a>';
	} else {
		return "<a href='" . esc_url( $permalink ) . "' class='" . esc_attr( $class ) . "'>" . do_shortcode( $content ) . '</a>';
	}
}

add_shortcode( 'qsm_link', 'qsm_quiz_link_shortcode' );

/**
 * Displays a list of most recently created quizes [qsm_recent_quizzes]
 *
 * @param - attrs - array of shortcode attributes - no_of_quizzes, include_future_quizzes
 * @param - no_of_quizzes - Number of most recent quizzes to be displayed (default 10)
 * @param - include_future_quizzes - Whether to display future scheduled quizzes or not - yes/no (default yes)
 * @since 5.1.0
 * @return string - list of quizzes
 * Shortcode call - [qsm_recent_quizzes no_of_quizzes=5 include_future_quizzes='no' ]
 */
function qsm_display_recent_quizzes( $attrs ) {
	global $mlwQuizMasterNext;
	$no_of_quizzes          = isset( $attrs['no_of_quizzes'] ) ? $attrs['no_of_quizzes'] : 10;
	$include_future_quizzes = isset( $attrs['include_future_quizzes'] ) ? $attrs['include_future_quizzes'] : true;
	global $wpdb;
	wp_enqueue_style( 'quizzes-list', QSM_PLUGIN_CSS_URL.'/quizzes-list.css', array(), $mlwQuizMasterNext->version );

	$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes( false, 'quiz_id', 'DESC' );
	$result  = '<div class="outer-con">';
	$i       = 0;
	foreach ( $quizzes as $quiz ) {
		if ( $i < $no_of_quizzes ) {
			$setting = maybe_unserialize( $quiz->quiz_settings );
			$options = maybe_unserialize( $setting['quiz_options'] );
			$start_date = strtotime( $options['scheduled_time_start'] );
			$end_date   = strtotime( $options['scheduled_time_end'] );
			$now      = strtotime( current_time( 'm/d/Y H:i' ) );
			if ( $end_date && $end_date < $now ) {
				continue;
			} elseif ( $start_date && $start_date > $now && 'no' === $include_future_quizzes ) {
				continue;
			} else {
				$title   = esc_html( $quiz->quiz_name );
				$id      = $quiz->quiz_id;
				$url     = do_shortcode( "[qsm_link id='$id'] Take Quiz [/qsm_link]" );
				$result .= "<div class='ind-quiz'>
                                <div class='quiz-heading'>
                                    {$title}
                                </div>
                                <div class='quiz-url'>
                                    {$url}
                                </div>
                            </div>";
				$result .= "<div class='clear'></div>";
				++$i;
			}
		}
	}
	if ( 0 === $i ) {
		$result .= __( 'No quiz found', 'quiz-master-next' );
	}
	$result .= '</div>';
	return $result;
}

add_shortcode( 'qsm_recent_quizzes', 'qsm_display_recent_quizzes' );

/**
 * @since 6.4.1
 */
function qsm_load_main_scripts() {
	wp_enqueue_script( 'jquery' );
}

add_action( 'wp_enqueue_scripts', 'qsm_load_main_scripts' );

/**
 * Add Meta data for facebook share
 *
 * @global obj $mlwQuizMasterNext
 * @global obj $wpdb
 * @global obj $wp_query
 */
function qsm_generate_fb_header_metadata() {
	global $mlwQuizMasterNext, $wpdb, $wp_query;
	if ( isset( $_GET['result_id'] ) && '' !== $_GET['result_id'] ) {
		$settings        = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '594986844960937';
		if ( isset( $settings['facebook_app_id'] ) ) {
			$facebook_app_id = esc_js( $settings['facebook_app_id'] );
		}
		$result_id    = sanitize_text_field( wp_unslash( $_GET['result_id'] ) );
		$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE unique_id = %s", $result_id ) );
		if ( $results_data ) {
			// Prepare responses array.
			$is_new_format = $mlwQuizMasterNext->pluginHelper->is_new_format_result( $results_data );
			if ( $is_new_format ) {
				// Load answers and meta from new tables
				$results  = $mlwQuizMasterNext->pluginHelper->get_formated_result_data( $result_id );
			} else {
				// Load legacy serialized results
				$results = maybe_unserialize( $results_data->quiz_results );
			}
			if ( is_array( $results ) ) {
				if ( ! isset( $results['contact'] ) ) {
					$results['contact'] = array();
				}
			} elseif ( '' != $results_data->quiz_results ) {
				$template = str_replace( '%QUESTIONS_ANSWERS%', $results_data->quiz_results, $template );
				$template = str_replace( '%TIMER%', '', $template );
				$template = str_replace( '%COMMENT_SECTION%', '', $template );
				$results  = array(
					0,
					array(),
					'',
					'contact' => array(),
				);
			} else {
				$results  = array(
					0,
					array(),
					'',
					'contact' => array(),
				);
			}
			// Prepare full results array.
			$results_array = array(
				'quiz_id'                => $results_data->quiz_id,
				'quiz_name'              => $results_data->quiz_name,
				'quiz_system'            => $results_data->quiz_system,
				'user_name'              => $results_data->name,
				'user_business'          => $results_data->business,
				'user_email'             => $results_data->email,
				'user_phone'             => $results_data->phone,
				'user_id'                => $results_data->user,
				'timer'                  => $results[0],
				'time_taken'             => $results_data->time_taken,
				'total_points'           => $results_data->point_score,
				'total_score'            => $results_data->correct_score,
				'total_correct'          => $results_data->correct,
				'total_questions'        => $results_data->total,
				'comments'               => $results[2],
				'question_answers_array' => $results[1],
				'contact'                => $results['contact'],
				'results'                => $results,
			);

			$mlwQuizMasterNext->pluginHelper->prepare_quiz( $results_data->quiz_id );
			$sharing_page_id = qsm_get_post_id_from_quiz_id( $results_data->quiz_id );

			// Fb share description
			$sharing              = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'facebook_sharing_text', '' );
			$sharing              = apply_filters( 'mlw_qmn_template_variable_results_page', $sharing, $results_array );
			$default_fb_image     = QSM_PLUGIN_URL . 'assets/icon-200x200.png';
			$fb_sharing_image_quiz_options = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'result_page_fb_image', '' );
			$fb_sharing_image_quiz_text = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'result_page_fb_image', '' );
			$get_fb_sharing_image = ! empty($fb_sharing_image_quiz_options) ? $fb_sharing_image_quiz_options : $fb_sharing_image_quiz_text;
			if ( ! empty($get_fb_sharing_image) && filter_var($get_fb_sharing_image, FILTER_VALIDATE_URL) ) {
				$default_fb_image = esc_url($get_fb_sharing_image);
			}
			$post     = $wp_query->get_queried_object();
			$pagename = $post->post_title;
			$result_id = sanitize_text_field( wp_unslash( $_GET['result_id'] ) );
			?>
			<meta property="og:url" content="<?php echo esc_url( $sharing_page_id ) . '?result_id=' . esc_attr( $result_id ); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:title" content="<?php echo esc_attr( $pagename ); ?>" />
			<meta property="og:description" content="<?php echo esc_attr( $sharing ); ?>" />
			<meta property="og:image" content="<?php echo esc_url( $default_fb_image ); ?>" />
			<meta property="fb:app_id" content="<?php echo esc_attr( $facebook_app_id ); ?>" />
			<?php
		}
	}
}

add_action( 'wp_head', 'qsm_generate_fb_header_metadata' );


/**
 * @since QSM 6.4.6
 * @param int $quiz_id
 *
 * Get the post id from quiz id
 */
function qsm_get_post_id_from_quiz_id( $quiz_id ) {
	// Resolve the quiz post for the CURRENT language (WPML aware) so the
	// Open Graph share URL points to the correct translated page. On single-language
	// sites this returns the same permalink as before.
	$post_id = qsm_get_translated_quiz_post_id( $quiz_id );
	return $post_id ? get_the_permalink( $post_id ) : '';
}

add_filter( 'qmn_end_shortcode', 'qsm_display_popup_div', 11, 3 );
function qsm_display_popup_div( $return_display, $qmn_quiz_options ) {
	if ( 1 != $qmn_quiz_options->enable_result_after_timer_end && 0 < $qmn_quiz_options->timer_limit ) {
		$return_display .= '<div style="display: none;" class="qsm-popup qsm-popup-slide" id="modal-3" aria-hidden="false">';
		$return_display .= '<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">';
		$return_display .= '<div class="qsm-popup__container qmn_quiz_container" role="dialog" aria-modal="true">';
		$return_display .= '<div class="qsm-popup__content">';
		$return_display .= '<img src="' . QSM_PLUGIN_URL . 'assets/clock.png' . '" alt="clock.png"/>';
		$return_display .= '<p class="qsm-time-up-text">'. esc_html__( 'Time is Up!', 'quiz-master-next' ) .'</p>';
		$return_display .= '</div>';
		$return_display .= '<footer class="qsm-popup__footer"><button class="qsm-popup-secondary-button qmn_btn" data-micromodal-close="" aria-label="'. __( 'Close this dialog window', 'quiz-master-next' ) .'">'. apply_filters( 'qsm_timer_up_cancel_button_text_after', __( 'Cancel', 'quiz-master-next' ) ).'</button><button data-quiz_id="' . $qmn_quiz_options->quiz_id . '" class="submit-the-form qmn_btn">'. apply_filters( 'qsm_timer_up_submit_button_text_after', __( 'Submit Quiz', 'quiz-master-next' ) ).'</button></footer>';
		$return_display .= '</div>';
		$return_display .= '</div>';
		$return_display .= '</div>';
	}
	return $return_display;
}
add_filter( 'qmn_end_shortcode', 'qsm_display_popup_div_expired_quiz', 10, 3 );
function qsm_display_popup_div_expired_quiz( $return_display, $qmn_quiz_options ) {
	if ( 0 === intval( $qmn_quiz_options->enable_result_after_timer_end ) ) {
		$return_display .= '<div style="display: none;" class="qsm-popup qsm-popup-slide" id="modal-4" aria-hidden="false">';
		$return_display .= '<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">';
		$return_display .= '<div class="qsm-popup__container qmn_quiz_container" role="dialog" aria-modal="true">';
		$return_display .= '<div class="qsm-popup__content">';
		$return_display .= '<img src="' . QSM_PLUGIN_URL . 'assets/clock.png' . '" alt="clock.png"/>';
		$return_display .= '<p class="qsm-time-up-text"> '.__( 'Time\'s up', 'quiz-master-next' ) .'</p>';
		$return_display .= '</div>';
		$return_display .= '<footer class="qsm-popup__footer"><button class="qsm-popup-secondary-button qmn_btn" data-micromodal-close="" aria-label="Close this dialog window" onclick="location.reload();">'.  __('Cancel', 'quiz-master-next' ).'</button></footer>';
		$return_display .= '</div>';
		$return_display .= '</div>';
		$return_display .= '</div>';
	}
	return $return_display;
}