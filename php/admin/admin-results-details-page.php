<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function generates the results details that are shown the results page.
*
* @return type void
* @since 4.4.0
*/
function qsm_generate_result_details() {
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}
	global $mlwQuizMasterNext;
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'results';
    $tab_array = $mlwQuizMasterNext->pluginHelper->get_results_tabs();
    ?>
	<style type="text/css">
		.prettyprint {width: 200px;}
		.result-tab-content p {font-size: 16px;}
		.qmn_question_answer b, .qmn_question_answer .mlw_qmn_question_number {font-size: 18px;margin-bottom: 0;}
		.qmn_question_answer {margin-bottom: 30px;font-size: 16px;line-height: 1.5;}
	</style>
    <div class="wrap">
        <h2 style="display: none;"><?php esc_html_e('Quiz Results', 'quiz-master-next'); ?></h2>
        <h2 class="nav-tab-wrapper">
        <?php
        foreach ( $tab_array as $tab ) {
            $active_class = '';
            if ( $active_tab === $tab['slug'] ) {
                $active_class = ' nav-tab-active';
            }
            $result_id = isset( $_GET["result_id"] ) ? intval( $_GET["result_id"] ) : '';

            echo '<a href="?page=qsm_quiz_result_details&result_id='.esc_attr( $result_id ).'&tab='.esc_attr( $tab['slug'] ).'" class="nav-tab '.esc_attr( $active_class ).'">' . esc_html( $tab['title'] ) . '</a>';
        }
        ?>
        </h2>
        <div class="result-tab-content">
            <?php
            foreach ( $tab_array as $tab ) {
                if ( $active_tab === $tab['slug'] ) {
                    call_user_func( $tab['function'] );
                }
            }
            ?>
        </div>
    </div>
    <?php
}


/**
* This function generates the results details tab that shows the details of the quiz
*
* @param type description
* @return void
* @since 4.4.0
*/
function qsm_generate_results_details_tab() {
	global $wpdb;
	global $mlwQuizMasterNext;

	// Gets results data.
    $result_id    = isset( $_GET["result_id"] ) ? intval( $_GET["result_id"] ) : 0;
	$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id ) );

	// Prepare plugin helper.
	$quiz_id = intval( $results_data->quiz_id );
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );

    //Get the data for comments
    $quiz_options = $mlwQuizMasterNext->quiz_settings->get_setting( 'quiz_options');
    $comments_enabled = $quiz_options['comment_section'];

    $previous_results = $wpdb->get_var( $wpdb->prepare("SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MAX(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id < %d)",  $result_id));
    $next_results     = $wpdb->get_var( $wpdb->prepare("SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MIN(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id > %d)", $result_id));

	// If there is previous or next results, show buttons.
	$disable_mathjax = isset( $quiz_options['disable_mathjax'] ) ? $quiz_options['disable_mathjax'] : '';
	if ( 1 != $disable_mathjax ) {
		wp_enqueue_script( 'math_jax', QSM_PLUGIN_JS_URL . '/mathjax/tex-mml-chtml.js', false, '3.2.0', true );
		wp_add_inline_script( 'math_jax', $mlwQuizMasterNext::$default_MathJax_script, 'before' );
	}
    echo '<div style="text-align:right; margin-top: 20px; margin-bottom: 20px;">';
    echo '<h3 class="result-page-title">Quiz Result - '. esc_html( $results_data->quiz_name ) .'</h3>';
    echo '<a style="margin-right: 15px;" href="?page=mlw_quiz_results" class="button button-primary" title="Return to results">'. esc_html__( 'Back to Results', 'quiz-master-next' ) .'</a>';
    if ( ! is_null( $previous_results ) && $previous_results ) {
        echo "<a class='button button-primary' title='View Previous Result' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $previous_results ) . "\" ><span class='dashicons dashicons-arrow-left-alt2'></span></a> ";
    }else {
        echo "<a class='button button-primary' title='View Previous Result' href='#' disbled=disabled><span class='dashicons dashicons-arrow-left-alt2'></span></a> ";
    }
    if ( ! is_null( $next_results ) && $next_results ) {
    echo " <a class='button button-primary' title='View Next Result' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $next_results ) . "\" ><span class='dashicons dashicons-arrow-right-alt2'></span></a>";
    }else {
        echo " <a class='button button-primary' title='View Next Result' href='#' disabled=disabled><span class='dashicons dashicons-arrow-right-alt2'></span></a>";
    }
    echo '</div>';

    // Prepare responses array.
    $total_hidden_questions = 0;
    $results = maybe_unserialize( $results_data->quiz_results );
    if ( is_array( $results ) ) {
        $total_hidden_questions = isset( $results['hidden_questions'] ) ? count( $results['hidden_questions'] ) : 0;
        if ( ! isset( $results["contact"] ) ) {
            $results["contact"] = array();
        }
    } else {
        $results = array(
            0,
            array(),
            '',
            'contact' => array(),
        );
    }

    // Prepare full results array.
    $results_array = apply_filters( 'mlw_qmn_template_variable_results_array', array(
        'quiz_id'                => $results_data->quiz_id,
        'quiz_name'              => $results_data->quiz_name,
        'quiz_system'            => $results_data->quiz_system,
        'form_type'              => $results_data->form_type,
        'user_name'              => $results_data->name,
        'user_business'          => $results_data->business,
        'user_email'             => $results_data->email,
        'user_phone'             => $results_data->phone,
        'user_id'                => $results_data->user,
        'timer'                  => isset($results[0]) ? $results[0] : 0,
        'time_taken'             => $results_data->time_taken,
        'total_points'           => $results_data->point_score,
        'total_score'            => $results_data->correct_score,
        'total_correct'          => $results_data->correct,
        'total_questions'        => $results_data->total - $total_hidden_questions,
        'comments'               => isset( $results[2] ) ? $results[2] : '',
        'question_answers_array' => isset( $results[1] ) ? $results[1] : array(),
        'contact'                => $results["contact"],
        'result_id'              => $result_id,
        'results'                => $results,
    ) );

    //convert to preferred date format
	$results_array = $mlwQuizMasterNext->pluginHelper->convert_to_preferred_date_format($results_array);

	// Get template for admin results.
    $settings = (array) get_option( 'qmn-settings' );
    $new_template_result_detail = '1';
    if ( isset($settings['new_template_result_detail']) ) {
        $new_template_result_detail = esc_attr( $settings['new_template_result_detail'] );
    }
    if ( 1 === intval( $new_template_result_detail ) ) {
        $template = '';
        $mlw_qmn_results_array = maybe_unserialize( $results_data->quiz_results );
        if ( is_array( $mlw_qmn_results_array ) ) {
            $span_start = '<span class="result-candidate-span"><label>';
            $span_end = '</label><span>';
            $spanend = '</span></span>';
            $template .= '<div class="overview-main-wrapper">';
            //User detail
            $template .= '<div class="candidate-detail-wrap overview-inner-wrap">';
            $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>User Detail</span></h2>';
            $template .= '<div class="inside">';
            if ( isset( $results_array['contact'] ) && is_array( $results_array['contact'] ) && ! empty( $results_array['contact'] ) ) {
                for ( $i = 0; $i < count( $results_array["contact"] ); $i++ ) {
                    $template .= $span_start. $results_array["contact"][ $i ]["label"] .$span_end. $results_array["contact"][ $i ]["value"] .$spanend;
                }
            } else {
                $template .= $span_start. __( 'Name:', 'quiz-master-next' ) .$span_end. $results_data->name .$spanend;
                $template .= $span_start. __( 'Business:', 'quiz-master-next' ) .$span_end. $results_data->business .$spanend;
                $template .= $span_start. __( 'Phone:', 'quiz-master-next' ) .$span_end. $results_data->phone .$spanend;
                $template .= $span_start. __( 'Email:', 'quiz-master-next' ) .$span_end. $results_data->email .$spanend;
            }
            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';

            if ( isset( $results_data->form_type ) && 0 === intval( $results_data->form_type ) ) {
                //Scoreboard design
                $template .= '<div class="candidate-detail-wrap overview-inner-wrap">';
                $template .= '<div id="submitdiv" class="postbox qsm-scorecard-box"><h2 class="hndle ui-sortable-handle"><span>Scorecard</span></h2>';
                $template .= '<div class="inside">';
                $template .= $span_start. __( 'Correct Answers:', 'quiz-master-next' ) .'</label><span>%AMOUNT_CORRECT% Out of %TOTAL_QUESTIONS%</span></span>';
                $template .= $span_start. __( 'Points:', 'quiz-master-next' ) .'</label><span class="qsm-total-point">%POINT_SCORE% </span></span>';
                $template .= $span_start. __( 'Percentage:', 'quiz-master-next' ) .'</label><span class="qsm-total-percentage">%CORRECT_SCORE%%</span></span>';
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
            }

            //Timer design
            $template .= '<div class="overview-inner-wrap">';
            $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>Time Taken</span></h2>';
            $template .= '<div class="inside">';
            $template .= '<div class="timer-div-wrapper">';

            $mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);
            if ( $mlw_complete_hours > 0 ) {
                $template .= '<div>';
                $template .= '<span class="hours timer-span">' . str_pad($mlw_complete_hours, 2, '0', STR_PAD_LEFT) . '</span>';
                $hour_label = 1 === $mlw_complete_hours ? __( 'hour', 'quiz-master-next' ) : __( 'hours', 'quiz-master-next' );
                $template .= '<span class="timer-text">'. $hour_label .'</span>';
                $template .= '</div>';
            } else {
                $template .= '<div>';
                $template .= '<span class="hours timer-span">00</span>';
                $template .= '<span class="timer-text">hours</span>';
                $template .= '</div>';
            }
            $mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
            if ( $mlw_complete_minutes > 0 ) {
                $template .= '<div>';
                $template .= '<span class="minutes timer-span">' . str_pad($mlw_complete_minutes, 2, '0', STR_PAD_LEFT) . '</span>';
                $min_label = 1 === $mlw_complete_minutes ? __( 'minute', 'quiz-master-next' ) : __( 'minutes', 'quiz-master-next' );
                $template .= '<span class="timer-text">' . $min_label . '</span>';
                $template .= '</div>';
            } else {
                $template .= '<div>';
                $template .= '<span class="minutes timer-span">00</span>';
                $template .= '<span class="timer-text">minutes</span>';
                $template .= '</div>';
            }

            $mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
            $template .= '<div>';
            $template .= '<span class="seconds timer-span">' . str_pad($mlw_complete_seconds, 2, '0', STR_PAD_LEFT) . '</span>';
            $sec_label = 1 === $mlw_complete_seconds ? __( 'second', 'quiz-master-next' ) : __( 'seconds', 'quiz-master-next' );
            $template .= '<span class="timer-text">' . $sec_label . '</span>';
            $template .= '</div>';

            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';
            //Comment entered text

            if ( 0 === intval( $comments_enabled ) ) {
                $template .= '<div class="comment-inner-wrap" style="">';
                $template .= '<div id="submitdiv" class="postbox" ><h2 class="hndle ui-sortable-handle"><span>User Comments</span></h2>';
                $template .= '<div class="inside">';
                $template .= '%COMMENT_SECTION%';
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
            }
            //Response div
            $template .= '<div class="response-inner-wrap">';
            $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>Responses</span></h2>';
            $template .= '<div class="inside check">';
            $template .= '%QUESTIONS_ANSWERS%';
            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';
        } else {
            $template = __( 'Data is missing.', 'quiz-master-next' );
        }
    } else {
        //Old template design
        if ( isset( $settings['results_details_template'] ) ) {
            $template = htmlspecialchars_decode( $settings['results_details_template'], ENT_QUOTES );
        } else {
            $template = "<h2>Quiz Results for %QUIZ_NAME%</h2>
            <p>%CONTACT_ALL%</p>
            <p>Name Provided: %USER_NAME%</p>
            <p>Business Provided: %USER_BUSINESS%</p>
            <p>Phone Provided: %USER_PHONE%</p>
            <p>Email Provided: %USER_EMAIL%</p>
            <p>Score Received: %AMOUNT_CORRECT%/%TOTAL_QUESTIONS% or %CORRECT_SCORE%% or %POINT_SCORE% points</p>
            <h2>Answers Provided:</h2>
            <p>The user took %TIMER% to complete quiz.</p>
            <p>Comments entered were: %COMMENT_SECTION%</p>
            <p>The answers were as follows:</p>
            %QUESTIONS_ANSWERS%";
        }
    }

    if ( ! is_array( maybe_unserialize( $results_data->quiz_results ) ) ) {
        $template = str_replace( "%QUESTIONS_ANSWERS%" , $results_data->quiz_results, $template );
        $template = str_replace( "%TIMER%" , '', $template );
        $template = str_replace( "%COMMENT_SECTION%" , '', $template );
    }

    // Pass through template variable filter
    $template = apply_filters( 'mlw_qmn_template_variable_results_page', $template, $results_array );
    $template = str_replace( "\n" , "<br>", $template );
    if ( 0 === intval( $new_template_result_detail ) ) {
        echo '<div class="old_template_result_wrap">';
    }

    $allowed_tags = wp_kses_allowed_html( 'post' );
    $is_allow_html = apply_filters('qsm_admin_results_details_page_allow_html', false);
    if ( $is_allow_html ) {
        $allowed_tags['script'] = array();
    }
    echo wp_kses( $template, $allowed_tags );

    if ( 0 === intval( $new_template_result_detail ) ) {
        echo '</div>';
    }
	// Hook for below admin results
    do_action( 'qsm_below_admin_results', $results_array );
}


/**
* Generates the results details tab in the quiz results page
*
* @return void
* @since 4.4.0
*/
function qsm_results_details_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_results_settings_tab( "Results", "qsm_generate_results_details_tab" );
}
add_action( "plugins_loaded", 'qsm_results_details_tab' );