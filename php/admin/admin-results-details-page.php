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
        wp_enqueue_style( 'qsm_common_style', plugins_url( '../../css/common.css', __FILE__ ) );
        wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );        
        wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'results';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_results_tabs();        
	?>
        <style>
            .prettyprint {
                width: 200px;
            }
        </style>
	<div class="wrap">
                <h2 style="display: none;"><?php _e('Quiz Results', 'quiz-master-next'); ?></h2>
                <h2 class="nav-tab-wrapper">
			<?php
			foreach( $tab_array as $tab ) {
				$active_class = '';
				if ( $active_tab == $tab['slug'] ) {
					$active_class = 'nav-tab-active';
				}
				echo "<a href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $_GET["result_id"] ) . "&&tab=" . $tab['slug'] . "\" class=\"nav-tab $active_class\">" . $tab['title'] . "</a>";
			}
			?>
		</h2>
                <style type="text/css">
                    .result-tab-content p{
                        font-size: 16px;
                    }
                    .qmn_question_answer b {
                        font-size: 18px;
                        margin-bottom: 0;
                        display: block;
                    }
                    .qmn_question_answer{
                        margin-bottom: 30px;
                        font-size: 16px;
                        line-height: 1.5;
                    }
                </style>
                <div class="result-tab-content">
		<?php                                        
			foreach( $tab_array as $tab ) {
				if ( $active_tab == $tab['slug'] ) {
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
	$result_id    = intval( $_GET["result_id"] );
	$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id ) );

	// Prepare plugin helper.
	$quiz_id = intval( $results_data->quiz_id );
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );

    $previous_results = $wpdb->get_var( $wpdb->prepare("SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MAX(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id < %d)",  $result_id));
	$next_results     = $wpdb->get_var( $wpdb->prepare("SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MIN(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id > %d)", $result_id));

	// If there is previous or next results, show buttons.
        echo '<div style="text-align:right; margin-top: 20px; margin-bottom: 20px;">';
        echo '<h3 class="result-page-title">Quiz Result - '. $results_data->quiz_name .'</h3>';        
        echo '<a style="margin-right: 15px;" href="?page=mlw_quiz_results" class="button button-primary" title="Return to results">'. __( 'Back to Results', 'quiz-master-next' ) .'</a>';
	if ( ! is_null( $previous_results ) && $previous_results ) {
		echo "<a class='button button-primary' title='View Previous Result' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $previous_results ) . "\" ><span class='dashicons dashicons-arrow-left-alt2'></span></a> ";
	}else{
            echo "<a class='button button-primary' title='View Previous Result' href='#' disbled=disabled><span class='dashicons dashicons-arrow-left-alt2'></span></a> ";
        }
        if ( ! is_null( $next_results ) && $next_results ) {
		echo " <a class='button button-primary' title='View Next Result' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $next_results ) . "\" ><span class='dashicons dashicons-arrow-right-alt2'></span></a>";
	}else{
            echo " <a class='button button-primary' title='View Next Result' href='#' disabled=disabled><span class='dashicons dashicons-arrow-right-alt2'></span></a>";
        }        
        echo '</div>';

	// Get template for admin results.
	$settings = (array) get_option( 'qmn-settings' );
        $new_template_result_detail = '1';
        $results["contact"] = array();
        if (isset($settings['new_template_result_detail'])){
            $new_template_result_detail = esc_attr( $settings['new_template_result_detail'] );
        }
        if( $new_template_result_detail == 1 ){
            $template = '';    
            if ( is_serialized( $results_data->quiz_results ) && is_array( @unserialize( $results_data->quiz_results ) ) ) {
                $results_contact = unserialize($results_data->quiz_results);
                $template .= '<div class="overview-main-wrapper">';
                //User detail
                $template .= '<div class="candidate-detail-wrap overview-inner-wrap">';
                $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>User Detail</span></h2>';
                $template .= '<div class="inside">';                
                if( isset( $results_contact['contact'] ) && is_array( $results_contact['contact'] ) && !empty( $results_contact['contact'] ) ){
                    for ( $i = 0; $i < count( $results_contact["contact"] ); $i++ ) {
                        $template .= '<span class="result-candidate-span"><label>'. $results_contact["contact"][ $i ]["label"] .'</label><span>'. $results_contact["contact"][ $i ]["value"] .'</span></span>';
                    }
                }else{
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Name:', 'quiz-master-next' ) .'</label><span>'. $results_data->name .'</span></span>';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Business:', 'quiz-master-next' ) .'</label><span>'. $results_data->business .'</span></span>';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Phone:', 'quiz-master-next' ) .'</label><span>'. $results_data->phone .'</span></span>';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Email:', 'quiz-master-next' ) .'</label><span>'. $results_data->email .'</span></span>';
                }                
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';                
                if( isset( $results_data->form_type ) && $results_data->form_type == 0 ){
                    //Scoreboard design
                    $template .= '<div class="candidate-detail-wrap overview-inner-wrap">';
                    $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>Scorecard</span></h2>';
                    $template .= '<div class="inside">';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Correct Answers:', 'quiz-master-next' ) .'</label><span>%AMOUNT_CORRECT% Out of %TOTAL_QUESTIONS%</span></span>';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Points:', 'quiz-master-next' ) .'</label><span>%POINT_SCORE% </span></span>';
                    $template .= '<span class="result-candidate-span"><label>'. __( 'Percentage:', 'quiz-master-next' ) .'</label><span>%CORRECT_SCORE%%</span></span>';                
                    $template .= '</div>';
                    $template .= '</div>';
                    $template .= '</div>';
                }
                //Timer design
                $template .= '<div class="overview-inner-wrap">';
                $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>Time Taken</span></h2>';
                $template .= '<div class="inside">';
                $template .= '<div class="timer-div-wrapper">';                
                $mlw_qmn_results_array = @unserialize($results_data->quiz_results);
                if ( is_array( $mlw_qmn_results_array ) ) {
                        $mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);                        
                        if ( $mlw_complete_hours > 0 ) {
                                $template .= '<div>';
                                $template .= '<span class="hours timer-span">' . str_pad($mlw_complete_hours, 2, '0', STR_PAD_LEFT) . '</span>';
                                $hour_label = $mlw_complete_hours == 1 ? __( 'hour', 'quiz-master-next' ) : __( 'hours', 'quiz-master-next' );
                                $template .= '<span class="timer-text">'. $hour_label .'</span>';
                                $template .= '</div>';
                        }else{
                            $template .= '<div>';
                            $template .= '<span class="hours timer-span">00</span>';
                            $template .= '<span class="timer-text">hours</span>';
                            $template .= '</div>';
                        }
                        $mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
                        if ( $mlw_complete_minutes > 0 ) {
                            $template .= '<div>';
                            $template .= '<span class="minutes timer-span">' . str_pad($mlw_complete_minutes, 2, '0', STR_PAD_LEFT) . '</span>';
                            $min_label = $mlw_complete_minutes == 1 ? __( 'minute', 'quiz-master-next' ) : __( 'minutes', 'quiz-master-next' );
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
                        $sec_label = $mlw_complete_seconds == 1 ? __( 'second', 'quiz-master-next' ) : __( 'seconds', 'quiz-master-next' );
                        $template .= '<span class="timer-text">' . $sec_label . '</span>';
                        $template .= '</div>';                                                        
                }                
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
                //Comment entered text
                $template .= '<div class="comment-inner-wrap" style="display: none;">';
                $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>User Comments</span></h2>';
                $template .= '<div class="inside">';                
                $template .= '%COMMENT_SECTION%';                
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
                //Response div
                $template .= '<div class="response-inner-wrap">';
                $template .= '<div id="submitdiv" class="postbox "><h2 class="hndle ui-sortable-handle"><span>Responses</span></h2>';
                $template .= '<div class="inside">';                
                $template .= '%QUESTIONS_ANSWERS%';
                $template .= '</div>';
                $template .= '</div>';
                $template .= '</div>';
            }else{
                $template = 'Data is missing.';
            }
        }else{
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
        
        // Prepare responses array.
				$total_hidden_questions = 0;
        if ( is_serialized( $results_data->quiz_results ) && is_array( @unserialize( $results_data->quiz_results ) ) ) {
                $results = unserialize($results_data->quiz_results);
								$total_hidden_questions = isset($results['hidden_questions']) ? count($results['hidden_questions']) : 0;
                if ( ! isset( $results["contact"] ) ) {
                        $results["contact"] = array();
                }
        } else {
                $template = str_replace( "%QUESTIONS_ANSWERS%" , $results_data->quiz_results, $template);
                $template = str_replace( "%TIMER%" , '', $template);
                $template = str_replace( "%COMMENT_SECTION%" , '', $template);
                $results = array(
                        0,
                        array(),
                        '',
                        'contact' => array()
                );
        }
        
        // Prepare full results array.
        $results_array = array(
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
            'results'                => $results,
        );

        // Pass through template variable filter
        $template = apply_filters( 'mlw_qmn_template_variable_results_page', $template, $results_array );
        $template = str_replace( "\n" , "<br>", $template );
        if( $new_template_result_detail == 0 ){
            echo '<div class="old_template_result_wrap">';
        }
        echo wp_kses_post( $template );
        if( $new_template_result_detail == 0 ){
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
?>
